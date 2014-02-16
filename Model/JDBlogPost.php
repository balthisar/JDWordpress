<?php
/*********************************************************************************************//**
	@file JDBlogPost.php

	@brief

	Part of plugin `JDWordpress`

	@details

	This file represents the interface to posts.


    @date           2014-02-12
    @author         Jim Derry
    @copyright      ©2014 by Jim Derry and balthisar.com
    @copyright      MIT License (http://www.opensource.org/licenses/mit-license.php)

 *************************************************************************************************/


// CakePHP doesn't understand namespaced classes and autoloading, so
// We will perform an autoload of referenced classes here. This supports
// loading of the Markdown classes.
spl_autoload_register(function($class)
{
    foreach(App::path('Vendor', 'JDWordpress') as $base)
    {
        $path = $base . str_replace('\\', DS, $class) . '.php';
        if (file_exists($path))
        {
            return include $path;
        }
    }
});

// We will support the use of GeSHi in code, so
// load the Geshi file from Vendors.
App::import(
	'Vendor',
	'JDWordpress.geshi',
	array('file' => 'Geshi' . DS . 'geshi.php')
	);


App::uses('AppModel', 'Model', 'ConnectionManager');


/// This file represents the interface to posts.
class JDBlogPost extends AppModel
{
	public $useTable			= 'posts';
	public $primaryKey			= 'ID';
	public $useDbConfig			= 'JDWordpressDB';
	public $displayField		= 'post_name';

	public $order =
	[
		'post_date_gmt' => 'DESC',
		'post_name' => 'ASC'
	];

    public $belongsTo =
	[
		'JDBlogAuthor' =>
		[
			'className' => 'JDWordpress.JDBlogAuthor',
			'foreignKey' => 'post_author',
			'order' =>
			[
				'display_name' => 'ASC'
			],
		]
	];

	/*===========================================================*
		Define the standard query options. Note that the data
		structure for all of the get* posts methods will add
		our own calculated, convenience fields to the returned
		array (i.e., these aren't the only fields available).
	 *===========================================================*/
	private $optionsStandard =
	[
		'fields' =>
		[
			'ID',
			'guid',
			'post_date',
			'post_date_gmt',
			'post_modified',
			'post_modified_gmt',
			'post_title',
			'post_excerpt',
			'post_content',
			'post_name',
			'JDBlogAuthor.user_nicename',
			'JDBlogAuthor.user_email',
			'JDBlogAuthor.user_url',
			'JDBlogAuthor.display_name',
		],

		'conditions' =>
		[
		 	'post_type' => 'post',
		 	'post_status' => 'publish',
		],

		'order' =>
		[
			'post_date_gmt' => 'DESC',
			'post_name' => 'ASC'
		],
	];


/********************************************************************//**
	@brief
		Retrieves and processes the post by its slug.

	@param string $postName The slug to retrieve.
	@returns Array structure with the post content and details.
 ********************************************************************/
public function getNamedPost( $postName )
{
	if (strlen($postName) > 0)
	{
		$myOptions = $this->__getLocalOptions( ['post_name' => $postName] );
		$post = $this->find('first', $myOptions);
		if (!empty($post))
		{
			$result[0] = $post;
			return $this->__process_posts($result);
		}
	}

	return [];
}


/********************************************************************//**
	@brief
		Retrieves and processes all posts using `$optionsStandard`.
		If given `$addedConditions` and/or `$limit`, then those apply
		as well (so not really "getAllPosts" if params used).

	@param array $addedConditions CakePHP conditions array to add.
	@param string $limit Number of posts to return.
	@returns Array structure with the posts content and details.
 ********************************************************************/
public function getAllPosts( $addedConditions = [], $limit = -1 )
{
	$myOptions = $this->__getLocalOptions( $addedConditions, $limit );
	return $this->__process_posts($this->find('all', $myOptions));
}


/********************************************************************//**
	@brief
		Retrieves and processes all posts using `$optionsStandard`.
		If given `$addedConditions` and/or `$limit`, then those apply
		as well (so not really "getAllPosts" if params used).

	@param array $addedConditions CakePHP conditions array to add.
	@param string $limit Number of posts to return.
	@returns Array structure with the posts content and details.
 ********************************************************************/
public function getAllPostsAsArchives( $addedConditions = [], $limit = -1 )
{
	return $this->__refactorAsArchives($this->getAllPosts($addedConditions, $limit));
}


/********************************************************************//**
	@brief
		Retrieves and processes recent posts.

	@param Integer $limit	Number of posts to retrieve.
	@returns Array structure with the posts content and details.
 ********************************************************************/
public function getRecentPosts( $limit = 5 )
{
	return $this->getAllPosts( [], $limit );
}


/********************************************************************//**
	@brief
		Retrieves and processes recent posts.

	@param Integer $limit	Number of posts to retrieve.
	@returns Array structure with the posts content and details.
 ********************************************************************/
public function getRecentPostsAsArchives( $limit = 5 )
{
	return $this->__refactorAsArchives($this->getRecentPosts( $limit ));
}


/********************************************************************//**
	@brief
		Retrieves and processes a random post.

	@param Integer $limit	Number of posts to retrieve.
	@returns Array structure with the posts content and details.
 ********************************************************************/
public function getRandomPosts( $limit = 1 )
{
	$myOptions = $this->__getLocalOptions( [], $limit );
	$myOptions['order'] = 'rand()';
	return $this->__process_posts($this->find('all', $myOptions));
}


/********************************************************************//**
	@brief
		Retrieves and processes a random post.

	@param Integer $limit	Number of posts to retrieve.
	@returns Array structure with the posts content and details.
 ********************************************************************/
public function getRandomPostsAsArchives( $limit = 1 )
{
	return $this->__refactorAsArchives($this->getRandomPosts($limit));
}


/********************************************************************//**
	@brief
		Retrieves summaries for the author slug $author

	@param String $author	Author slug.
	@param Integer $limit	Number of posts to retrieve.
	@returns Array structure with the posts content and details.
 ********************************************************************/
public function getAuthorPosts( $author, $limit = -1 )
{
	if (strlen($author) > 0)
	{
		return $this->getAllPosts( ['user_nicename' => $author], $limit );
	}

	return [];
}


/********************************************************************//**
	@brief
		Retrieves summaries for the author slug $author

	@param String $author	Author slug.
	@param Integer $limit	Number of posts to retrieve.
	@returns Array structure with the posts content and details.
 ********************************************************************/
public function getAuthorPostsAsArchives( $author, $limit = -1 )
{
	return $this->__refactorAsArchives($this->getAuthorPosts($author, $limit));
}


/********************************************************************//**
	@brief
		Retrieves posts for the taxonomy $term and $type.

	@details
		For	example can retrieve all posts that have "category" of
		"news" or all posts with "post_tag" of "cool".

	@param String $term		Taxonomy term.
	@param String $type		Taxonomy type.
	@param Integer $limit	Number of posts to retrieve.
	@returns Array structure with the posts content and details.
 ********************************************************************/
public function getTaxonomyPosts( $term = 'uncategorized', $type = 'category', $limit = -1 )
{
	if (strlen($term) > 0)
	{
		$filter = $this->__getPostIDsForTaxonomy( $term, $type );
		if (!empty($filter))
		{
			return $this->getAllPosts( [$this->name.'.ID' => $filter], $limit );
		}
	}

	return [];
}


/********************************************************************//**
	@brief
		Retrieves posts for the taxonomy $term and $type.

	@details
		For	example can retrieve all posts that have "category" of
		"news" or all posts with "post_tag" of "cool".

	@param String $term		Taxonomy term.
	@param String $type		Taxonomy type.
	@param Integer $limit	Number of posts to retrieve.
	@returns Array structure with the posts content and details.
 ********************************************************************/
public function getTaxonomyPostsAsArchives( $term = 'uncategorized', $type = 'category', $limit = -1 )
{
	return $this->__refactorAsArchives($this->getTaxonomyPosts($term, $type, $limit));
}


/********************************************************************//**
	@brief
		Retrieves a list of all posts for the given $taxonomy
		broken down into groups by $term.

	@param String $type		Taxonomy type.
	@returns Array structure with the posts content and details.
 ********************************************************************/
public function getAllPostsForTaxonomyByTerm( $type = 'category')
{
	// build the list of terms and get posts for each term
	$taxonomyList = $this->getTermsForTaxonomy($type);
	foreach ($taxonomyList as &$record)
	{
		$record['Posts'] = $this->getTaxonomyPosts($record['Terms']['slug'], $type);
	}
	return $taxonomyList;
}


/********************************************************************//**
	@brief
		Retrieves a list of all terms for the given taxonomy

	@param String $type		Taxonomy type.
	@returns Array structure with the posts content and details.
 ********************************************************************/
public function getTermsForTaxonomy( $type = 'category')
{
	$ds = ConnectionManager::getDataSource($this->useDbConfig);
	$dsc = $ds->config;
	$tterms = $dsc['prefix'] . 'terms';
	$ttermrelationships = $dsc['prefix'] . 'term_relationships';
	$ttermtaxonomy = $dsc['prefix'] . 'term_taxonomy';
	$routesPath = JDWP_PATH;

	$query = <<< _SQL
		SELECT * FROM (SELECT taxonomy, description, name, slug,
		concat_ws('/', '$routesPath', slug) as cake_slug,
		concat_ws('/', '$routesPath', 'rss', slug) as rss_slug
		FROM $ttermrelationships AS wtr
		LEFT JOIN $ttermtaxonomy AS wtt
		   ON (wtr.term_taxonomy_id = wtt.term_taxonomy_id)
		LEFT JOIN $tterms AS wt on wtt.term_taxonomy_id = wt.term_id
		WHERE wtt.taxonomy = '$type'
		GROUP BY wtt.term_id
		ORDER BY wt.name) as Terms;
_SQL;

	$result = $this->Query($query);
	return $result;
}


/********************************************************************//**
	@brief
		Returns the `name` field given a slug.

	@param String $slug
	@returns A string indicating the `name` field for the slug.
 ********************************************************************/
public function getNameForTaxonomySlug( $slug )
{
	$ds = ConnectionManager::getDataSource($this->useDbConfig);
	$dsc = $ds->config;
	$tterms = $dsc['prefix'] . 'terms';

	$query = <<< _SQL
        SELECT * FROM (SELECT name FROM $tterms
        WHERE slug = '$slug'
        LIMIT 1) as terms;
_SQL;

	$result = $this->Query($query);
	return $result[0]['terms']['name'];
}


/*——————————————————————————————————————————————————————————————————*
	__getPostIDsForTaxonomy
		Builds an array of post IDs for $term and $type. For
		example will return a list of all ID's that have a
		"category" called "recipes".
 *——————————————————————————————————————————————————————————————————*/
private function __getPostIDsForTaxonomy( $term = 'uncategorized', $type = 'category' )
{
	$ds = ConnectionManager::getDataSource($this->useDbConfig);
	$dsc = $ds->config;
	$tterms = $dsc['prefix'] . 'terms';
	$ttermrelationships = $dsc['prefix'] . 'term_relationships';
	$ttermtaxonomy = $dsc['prefix'] . 'term_taxonomy';
	$tposts = $dsc['prefix'] . 'posts';

	$query = <<< _SQL
		SELECT * FROM (SELECT wpr.object_id
		FROM $tterms AS term
		INNER JOIN $ttermtaxonomy AS tax
		ON term.term_id = tax.term_id
		INNER JOIN $ttermrelationships AS wpr
		ON wpr.term_taxonomy_id = tax.term_taxonomy_id
		INNER JOIN $tposts AS p
		ON p.ID = wpr.object_id
		WHERE taxonomy = '$type' AND p.post_type = 'post' AND term.slug = '$term'
		ORDER BY object_id) as IdList;
_SQL;

	$result = $this->Query($query);

	$myList = array();

	foreach ( $result as $outer)
	{
		$myList[] = $outer['IdList']['object_id'];
	}

	return $myList;
}


/*——————————————————————————————————————————————————————————————————*
	__getTaxonomyForPost
		Retrieves an array of taxonomy terms for a $postID. For
		example a post may have "post_tag" of "cool" and "new".
 *——————————————————————————————————————————————————————————————————*/
private function __getTaxonomyForPost( $postID, $type = 'category' )
{
	$ds = ConnectionManager::getDataSource($this->useDbConfig);
	$dsc = $ds->config;
	$tterms = $dsc['prefix'] . 'terms';
	$ttermrelationships = $dsc['prefix'] . 'term_relationships';
	$ttermtaxonomy = $dsc['prefix'] . 'term_taxonomy';

	$query = <<< _SQL
		SELECT * FROM (SELECT t.name, t.slug FROM $tterms AS t
		INNER JOIN $ttermtaxonomy AS tt ON (tt.term_id = t.term_id)
		INNER JOIN $ttermrelationships AS tr ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
		WHERE tt.taxonomy IN ('$type') AND tr.object_id IN ($postID)
		ORDER BY t.name ASC) as Category;
_SQL;

	return $this->Query($query);
}


/*——————————————————————————————————————————————————————————————————*
	__getFeaturedImageForPost
		Retrieves the post data for a featured image for post ID.
 *——————————————————————————————————————————————————————————————————*/
private function __getFeaturedImageForPost( $postID )
{
	$ds = ConnectionManager::getDataSource($this->useDbConfig);
	$dsc = $ds->config;
	$tpost = $dsc['prefix'] . 'posts';
	$tmeta = $dsc['prefix'] . 'postmeta';

	$query = <<< _SQL
		SELECT post_title as image_title, post_name as image_name, guid as image_location
		FROM (SELECT * FROM $tpost as p
		INNER JOIN $tmeta as m
		ON ( m.meta_value = p.ID)
		WHERE (m.post_id) = $postID and (m.meta_key = '_thumbnail_id')
		ORDER BY p.post_date ASC
		LIMIT 1) as FeaturedImage;
_SQL;

	$result = $this->Query($query);

	if ( !empty( $result ) )
	{
		$result = $result[0]['FeaturedImage'];
		$result['image_location_relative'] = parse_url($result['image_location'], PHP_URL_PATH);;
	}
	return $result;

}


/*——————————————————————————————————————————————————————————————————*
	__process_posts
		Clean up some data types and adds some calculated fields.
 *——————————————————————————————————————————————————————————————————*/
private function __process_posts($resultset)
{
	if (empty($resultset))
	{
		return [];
	}
	// Get access to our WordPress settings
	$wpsettings = ClassRegistry::init('JDWordpress.JDBlogSetting');
	$formatDate = $wpsettings->field('option_value', [ 'option_name' => 'date_format' ]);
	$formatTime = $wpsettings->field('option_value', [ 'option_name' => 'time_format' ]);
	$formatPermalink = $wpsettings->field('option_value', [ 'option_name' => 'permalink_structure' ]);
	$siteurl = $wpsettings->field('option_value', [ 'option_name' => 'siteurl' ]);

	// Other locals
	$_name = $this->name;

	foreach ( $resultset as &$record)
	{
		// Make some new fields with pretty dates and times
		$record[$_name]['post_date_gmt_formatted'] = date($formatDate, strtotime($record[$_name]['post_date_gmt']));
		$record[$_name]['post_date_formatted'] = date($formatDate, strtotime($record[$_name]['post_date']));
		$record[$_name]['post_time_gmt_formatted'] = date($formatTime, strtotime($record[$_name]['post_date_gmt']));
		$record[$_name]['post_time_formatted'] = date($formatTime, strtotime($record[$_name]['post_date']));

		// Make some new date fields so that views can do fancy sorting or whatnot. Using local date.
		$record[$_name]['post_date_parts'] = date_parse($record[$_name]['post_date']);
		$record[$_name]['post_date_parts']['month_name'] = date("F", mktime(0, 0, 0, $record[$_name]['post_date_parts']['month'], 10));

		// Make a sitemap.xml-compatible post_modified_gmt
		$tmpDate = new DateTime($record[$_name]['post_modified_gmt']);
		$record[$_name]['post_modified_gmt_sitemap'] = $tmpDate->format('Y-m-d\TH:i:sP');

		// Convert from Markdown if the shebang is detected
		if (substr($record[$_name]['post_content'], 0, 4) == '#!md')
		{
			$record[$_name]['post_content'] = $this->__convertFromMarkdown($record[$_name]['post_content']);
		}

		// Let's see if we should bother running this through GeSHi or not.
		// This is just a simple check to avoid running *everything* through
		// PHP's DOMDocument.
		if ( 1 === preg_match('/geshi/i', $record[$_name]['post_content']) )
		{
			$record[$_name]['post_content'] = $this->__syntaxHighlight($record[$_name]['post_content']);
		}


		// Fake a post excerpt if one doesn't exist
		if (strlen($record[$_name]['post_excerpt']) == 0)
		{
			$record[$_name]['post_excerpt'] = strip_tags($this->__truncate( $record[$_name]['post_content'], 240));
		}

		// Make a permalink in the WP format. Adopted from code by Henning Stein, www.atomtigerzoo.com
		$wp_permalink_tags =
		[
			"%year%",
			"%monthnum%",
			"%day%",
			"%hour%",
			"%minute%",
			"%second%",
			"%postname%",
			"%post_id%"
		];

		$replace =
		[
			date("Y", strtotime($record[$_name]['post_date'])),
			date("m", strtotime($record[$_name]['post_date'])),
			date("d", strtotime($record[$_name]['post_date'])),
			date("H", strtotime($record[$_name]['post_date'])),
			date("i", strtotime($record[$_name]['post_date'])),
			date("s", strtotime($record[$_name]['post_date'])),
			$record[$_name]['post_name'],
			$record[$_name]['ID']
		];

		// Replace the wordpress tags
		$record[$_name]['post_permalink_uri'] = str_replace($wp_permalink_tags, $replace, $formatPermalink);

		if(substr($siteurl, -1) == '/')
		{
			$siteurl = substr($siteurl, 0, -1);
		}
		$record[$_name]['post_permalink_real'] = $siteurl . $record[$_name]['post_permalink_uri'];
		$record[$_name]['post_permalink_cake'] = JDWP_PATH . '/' . $record[$_name]['post_name'];

		// Add Categories and tags and Featured Image
		$record['Categories'] = $this->__getTaxonomyForPost($record[$_name]['ID']);
		$record['Tags'] = $this->__getTaxonomyForPost($record[$_name]['ID'], 'post_tag');
		$record['FeaturedImage'] = $this->__getFeaturedImageForPost($record[$_name]['ID']);
	}

	return $resultset;
}


/*——————————————————————————————————————————————————————————————————*
	__refactorAsArchives
		Given $wpposts will return a posts array refactored into
		year and month keys.
 *——————————————————————————————————————————————————————————————————*/
private function __refactorAsArchives( $wpposts = [] )
{
	//---------------------------------------------
	// Refactor the data structure.
	//---------------------------------------------
    $groupedArray = array();

    // refactor our array into year and month groups
    foreach ($wpposts as $post)
    {
        $year = $post[$this->name]['post_date_parts']['year'];
        $month = $post[$this->name]['post_date_parts']['month'];
        $groupedArray[$year][$month][] = $post;
    }

    return $groupedArray;
}


/*——————————————————————————————————————————————————————————————————*
	__getLocalOptions
		Given $addedConditions and a $limit will return modified
		copy of $optionsStandard with the added values.
 *——————————————————————————————————————————————————————————————————*/
private function __getLocalOptions( $addedConditions = [], $limit = -1 )
{
	$result = $this->optionsStandard;

	foreach ( $addedConditions as $key => $value )
	{
		$result['conditions'][$key] = $value;
	}

	if ( $limit > 0 ) {
		$result['limit'] = $limit;
	}

	return $result;
}


/*——————————————————————————————————————————————————————————————————*
  prepareSQLDump
		Outputs the SQL database to a file and returns a reference
		to the filename so that it may be downloaded.
 *——————————————————————————————————————————————————————————————————*/
public function prepareSQLDump()
{
	$tables = '*';
	$return = '';

	$dataSource = $this->getDataSource();
	$databaseName = $dataSource->getSchemaName();

	// Do a short header
	$return .= '-- Database: `' . $databaseName . '`' . "\n";
	$return .= '-- Generation time: ' . date('D jS M Y H:i:s') . "\n\n\n";

	if ($tables == '*')
	{
		$tables = array();
		$result = $this->query('SHOW TABLES');

		foreach ($result as $resultKey => $resultValue)
		{
			$tables[] = current($resultValue['TABLE_NAMES']);
		}
	}
	else
	{
		$tables = is_array($tables) ? $tables : explode(',', $tables);
	}

	// Run through all the tables
	foreach ($tables as $table)
	{
		$tableData = $this->query('SELECT * FROM ' . $table);

		$return .= 'DROP TABLE IF EXISTS ' . $table . ';';
		$createTableResult = $this->query('SHOW CREATE TABLE ' . $table);
		$createTableEntry = current(current($createTableResult));
		$return .= "\n\n" . $createTableEntry['Create Table'] . ";\n\n";

		// Output the table data
		foreach ($tableData as $tableDataIndex => $tableDataDetails)
		{
			$return .= 'INSERT INTO ' . $table . ' VALUES(';

			foreach ($tableDataDetails[$table] as $dataKey => $dataValue)
			{
				if (is_null($dataValue))
				{
					$escapedDataValue = 'NULL';
				}
				else
				{
					// Convert the encoding
					$escapedDataValue = $dataValue; // mb_convert_encoding( $dataValue, "UTF-8", "ISO-8859-1" );

					// Escape any apostrophes using the datasource of the model.
					$escapedDataValue = $this->getDataSource()->value($escapedDataValue);
				}

				$tableDataDetails[$table][$dataKey] = $escapedDataValue;
			}
			$return .= implode(',', $tableDataDetails[$table]);

			$return .= ");\n";
		}

		$return .= "\n\n\n";
	}

	$fileName = $databaseName . '-backup-' . date('Y-m-d_H-i-s') . '.sql';

	return array('filename' => $fileName, 'data' => $return);
}


/*——————————————————————————————————————————————————————————————————*
  __truncate
		Original PHP code by Chirp Internet: www.chirp.com.au
		Please acknowledge use of this code by including this header.
 *——————————————————————————————————————————————————————————————————*/
private function __Truncate($string, $limit, $break=".", $pad="…")
{
	if(strlen($string) <= $limit)
	{
		return $string;
	}

    // is $break present between $limit and the end of the string?
	if(false !== ($breakpoint = strpos($string, $break, $limit)))
	{
		if($breakpoint < strlen($string) - 1)
		{
			$string = substr($string, 0, $breakpoint) . $pad;
		}
	}

	return $string;
}


/*——————————————————————————————————————————————————————————————————*
  __convertFromMarkdown
		Original PHP code by Michel Fortin
		<http://michelf.ca/projects/php-markdown/>
 *——————————————————————————————————————————————————————————————————*/
private function __convertFromMarkdown($string)
{
	if (substr($string,0,4) == '#!md')
	{
		$string = substr($string, 4);
	}
	return Michelf\MarkdownExtra::defaultTransform($string);
}


/*——————————————————————————————————————————————————————————————————*
  __syntaxHighlight
		Will look for blocks of <code> where the class indicates
		"geshi" and also the name of the language. The main
		document must be formed such:
			<pre><code class='geshi mylanguage …'>…</code></pre>
		Output will be
			<pre><code class='mylanguage …'>…</code></pre>
		'geshi' can be anywhere in the class list, but the the
		first class once 'geshi' is removed will be used as the
		language. Other classes can be assigned and will have
		GeSHi do the following:
		'geshilinenumbers' will add line numbers.
		@warning: assumes UTF8.
 *——————————————————————————————————————————————————————————————————*/
private function __syntaxHighlight($string)
{
	$document = new DOMDocument('1.0', 'UTF-8');
	if (!$document->loadHTML('<?xml encoding="UTF-8">' . $string, LIBXML_NOWARNING | LIBXML_NOERROR))
	{
		throw new InternalErrorException('The document provided by Wordpress has something wrong.', 501);
	}
	$xpath = new DOMXpath($document);
	$fragments = $xpath->query("//code[contains(concat(' ',normalize-space(@class),' '),' geshi ')]");

	$stylesheets = array();
	foreach ($fragments as $node)
	{
		$lineNums = preg_match('/geshilinenumbers/i', $node->getAttribute('class'));

		// Remove the .geshi and others flags from the class list.
		$values = array_values(array_filter( explode(' ', $node->getAttribute('class')) ,
				function ($item) { return !in_array(strtolower($item), ['geshi', 'geshilinenumbers']); } ));

		if (!empty($values[0]))
		{
			// Use GeSHi to process contents.
			$geshi = new GeSHi($node->nodeValue, $values[0]);
			$geshi->set_header_type(GESHI_HEADER_NONE);
			$geshi->enable_classes();
			if ($lineNums)
			{
				$geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
			}
			$insertMe = $geshi->parse_code();

			// Setup the original node to our desired values.
			$node->setAttribute( 'class', implode(' ', $values) );
			$node->nodeValue = '';				// Don't want the old text

			// Setup a new DOM document with the GeSHi'd values for
			// insertion back to the original document.
			$tmpDoc = new DOMDocument;
			$tmpDoc->loadHTML($insertMe);

			// The $tmpDoc is a whole document; we only want the stuff in the body.
			foreach ($tmpDoc->getElementsByTagName('body')->item(0)->childNodes as $newNode)
			{
				$node->appendChild($document->importNode($newNode, TRUE));
			}

			$stylesheets[] = $geshi->get_stylesheet();
			unset($tmpDoc);
			unset($geshi);
		}
	}

	$stylesheets = array_flip(array_flip($stylesheets)); // faster than array_unique
	$stylesheets = array_merge(["\n<style type='text/css'><!--"], $stylesheets, ["--></style>\n"]);

	// we could avoid the regex if we knew libxml >= 2.7.8 were installed, which would
	// allow LIBXML_HTML_NODEFDTD and LIBXML_HTML_NOIMPLIED.
	//return implode("\n", $stylesheets) . preg_replace('~<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>\s*~i', '', $document->saveHTML());
	return implode("\n", $stylesheets) . preg_replace('~<(?:!DOCTYPE|/?(?:html|head|body|\?xml))[^>]*>\s*~i', '', $document->saveHTML());
}


} // class



/*——————————————————————————————————————————————————————————————————*
	Data Return Sample
 *——————————————————————————————————————————————————————————————————*/
/********************************************************************//**
	@brief
		Pass some common view variables to whatever view is going
		to be called.
 ********************************************************************/

/*
array(1) {
  [0]=>
  array(5) {
    ["BlogPost"]=>
    array(16) {
      ["ID"]=>
      string(2) "25"
      ["guid"]=>
      string(36) "http://balthisar.dev/wordpress/?p=25"
      ["post_date"]=>
      string(19) "2013-12-16 19:12:23"
      ["post_date_gmt"]=>
      string(19) "2013-12-16 11:12:23"
      ["post_title"]=>
      string(42) "This is a sample post having a subcategory"
      ["post_excerpt"]=>
      string(27) "Now is the epluribus eatem."
      ["post_content"]=>
      string(27) "Now is the epluribus eatem."
      ["post_name"]=>
      string(25) "sample-having-subcategory"
      ["post_date_gmt_formatted"]=>
      string(17) "December 16, 2013"
      ["post_date_formatted"]=>
      string(17) "December 16, 2013"
      ["post_time_gmt_formatted"]=>
      string(8) "11:12 am"
      ["post_time_formatted"]=>
      string(7) "7:12 pm"
      ["post_date_parts"]=>
      array(13) {
        ["year"]=>
        int(2013)
        ["month"]=>
        int(12)
        ["day"]=>
        int(16)
        ["hour"]=>
        int(19)
        ["minute"]=>
        int(12)
        ["second"]=>
        int(23)
        ["fraction"]=>
        float(0)
        ["warning_count"]=>
        int(0)
        ["warnings"]=>
        array(0) {
        }
        ["error_count"]=>
        int(0)
        ["errors"]=>
        array(0) {
        }
        ["is_localtime"]=>
        bool(false)
        ["month_name"]=>
        string(8) "December"
      }
      ["post_permalink_uri"]=>
      string(27) "/sample-having-subcategory/"
      ["post_permalink_real"]=>
      string(57) "http://balthisar.dev/wordpress/sample-having-subcategory/"
      ["post_permalink_cake"]=>
      string(31) "/blog/sample-having-subcategory"
    }
    ["BlogAuthor"]=>
    array(4) {
      ["user_nicename"]=>
      string(9) "balthisar"
      ["user_email"]=>
      string(25) "bal.thi.sar@balthisar.com"
      ["user_url"]=>
      string(24) "http://www.balthisar.com"
      ["display_name"]=>
      string(13) "balthisar.com"
    }
    ["Categories"]=>
    array(2) {
      [0]=>
      array(1) {
        ["Category"]=>
        array(2) {
          ["name"]=>
          string(16) "Cooking in China"
          ["slug"]=>
          string(13) "china-cooking"
        }
      }
      [1]=>
      array(1) {
        ["Category"]=>
        array(2) {
          ["name"]=>
          string(4) "Food"
          ["slug"]=>
          string(4) "food"
        }
      }
    }
    ["Tags"]=>
    array(3) {
      [0]=>
      array(1) {
        ["Category"]=>
        array(2) {
          ["name"]=>
          string(4) "cool"
          ["slug"]=>
          string(4) "cool"
        }
      }
      [1]=>
      array(1) {
        ["Category"]=>
        array(2) {
          ["name"]=>
          string(3) "new"
          ["slug"]=>
          string(3) "new"
        }
      }
      [2]=>
      array(1) {
        ["Category"]=>
        array(2) {
          ["name"]=>
          string(6) "useful"
          ["slug"]=>
          string(6) "useful"
        }
      }
    }
    ["FeaturedImage"]=>
    array(3) {
      ["image_title"]=>
      string(11) "big at sign"
      ["image_name"]=>
      string(13) "adorn-contact"
      ["image_location"]=>
      string(75) "http://balthisar.dev/wordpress/wp-content/uploads/2013/12/adorn-contact.png"
    }
  }
}
*/
