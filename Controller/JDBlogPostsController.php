<?php
/*************************************************************************************************
 * @file           JDBlogPostsController.php
 *
 * @brief
 *
 * Part of plugin `JDWordpress`
 *
 * @details
 *
 * Very simple read-only Wordpress Blog controller.
 * This file will render Wordpress blog posts into your main site in your own style.
 *
 *
 * @date           2014-02-12
 * @author         Jim Derry
 * @copyright      ©2014 by Jim Derry and balthisar.com
 * @copyright      MIT License (http://www.opensource.org/licenses/mit-license.php)
 *************************************************************************************************/


App::uses('AppController', 'Controller');


/** This class implements all actions for JDWordpress. */
class JDBlogPostsController extends AppController
{
	/// You probably don’t want to use the default CakePHP layout.
	public $layout = 'default';


	// The dump actions need a little setup. This "security" is provided as being better
	// than nothing. You should setup your own CakePHP authorization mechanism, but this
	// will protect your files and db minimally until you do so.
	private $dump_use_internal_security = true;      // false if managing your own ACL
	private $dump_secretpassword = 'secretpassword'; // consider changing if not using ACL
	private $dump_wordpressdir = JDWP_INSTALL;       // set this to your filesystem path for WP.


	// Normal stuff you shouldn't have to touch.
	public $components = array( 'JDWordpress.Zip', 'JDWordpress.JDBlogPostData' );      ///< Components to use.
	public $helpers = array( 'Rss' => array( 'className' => 'JDWordpress.FixedRss' ) ); ///< Helpers to use.


	/**---------------------------------------------------------------------------*
	 * Pass some common view variables to whatever view is going to be called.
	 **---------------------------------------------------------------------------*/
	public function beforeRender()
	{
		parent::beforeRender();
		$this->set('jd_vars', $this->JDBlogPostData->jd_vars);
	}


/// @name Methods that display entire pages.
/// @{


	/**---------------------------------------------------------------------------*
	 * Displays the newest blog post.
	 *
	 * @details
	 * This is really a rather boring index. You'd probably like to route to your
	 * own index and do something interesting there.
	 **---------------------------------------------------------------------------*/
	public function index()
	{
		$wppost = $this->JDBlogPost->getRecentPosts(1);
		$this->set('wpposts', $wppost);
		// you might use these as unique identifiers for other page items,
		$this->set('guid', $wppost[0]['JDBlogPost']['guid']);
		$this->set('guid_title', $wppost[0]['JDBlogPost']['post_title']);

		return;
	}


	/**---------------------------------------------------------------------------*
	 * Displays the archives index page.
	 **---------------------------------------------------------------------------*/
	public function archives()
	{
		$wpposts = $this->JDBlogPost->getAllPostsAsArchives();
		$this->set('archivePosts', $wpposts);

		return;
	}


	/**---------------------------------------------------------------------------*
	 * Displays the categories index page.
	 **---------------------------------------------------------------------------*/
	public function categories()
	{
		$wpposts = $this->JDBlogPost->getAllPostsForTaxonomyByTerm('category');
		$this->set('tocType', 'category');
		$this->set('wpTocPosts', $wpposts);

		return;
	}


	/**---------------------------------------------------------------------------*
	 * Displays the tags index page.
	 **---------------------------------------------------------------------------*/
	public function tags()
	{
		$wpposts = $this->JDBlogPost->getAllPostsForTaxonomyByTerm('post_tag');
		$this->set('tocType', 'post_tag');
		$this->set('wpTocPosts', $wpposts);

		return;
	}


	/**---------------------------------------------------------------------------*
	 * Displays a single post if it exists, then tries to show summaries by
	 * Author-, Category-, or Tag-slugs respectively, and finally x number of
	 * posts if the parameter is numeric.
	 *
	 * @details
	 * This gives an interesting user experience whereby nearly any taxonomy,
	 * article name, author name, etc. will magically work.
	 **---------------------------------------------------------------------------*/
	public function view()
	{
		$path  = func_get_args();
		$count = count($path);

		//---------------------------------------------
		// if there's no path, then redirect to index.
		// This shouldn't be needed, but if you don't
		// have .htaccess setup to strip trailing
		// slashes, then you might arrive here.
		//---------------------------------------------
		if ( $count < 1 )
		{
			return $this->redirect(array( 'controller' => $this->name, 'action' => 'index' ));
		}

		//---------------------------------------------
		// Try to find a post with the name.
		//---------------------------------------------
		$wppost = $this->JDBlogPost->getNamedPost($path[0]);

		if ( !empty($wppost) )
		{
			$this->set('post', $wppost[0]);
			// you might use these as unique indentifiers for other page items,
			$this->set('guid', $wppost[0]['JDBlogPost']['guid']);
			$this->set('guid_title', $wppost[0]['JDBlogPost']['post_title']);
			$this->set('blogcategoryname', $wppost[0]['Categories'][0]['Category']['name']);
			$this->set('blogcategoryslug', $wppost[0]['Categories'][0]['Category']['slug']);

			return;
		}

		//---------------------------------------------
		// Try to find a matching author slug.
		//---------------------------------------------
		$wppost = $this->JDBlogPost->getAuthorPosts($path[0]);
		if ( !empty($wppost) )
		{
			$blogcategoryname = $wppost[0]['JDBlogAuthor']['display_name'];
			$blogcategoryslug = $wppost[0]['JDBlogAuthor']['user_nicename'];
			$message          = "All articles by author “{$blogcategoryname}”";
		}


		//---------------------------------------------
		// Try to find a matching category slug.
		//---------------------------------------------
		if ( empty($wppost) )
		{
			$wppost = $this->JDBlogPost->getTaxonomyPosts($path[0], 'category');
			if ( !empty($wppost) )
			{
				$blogcategoryslug = $path[0];
				$blogcategoryname = $this->JDBlogPost->getNameForTaxonomySlug($blogcategoryslug);
				$message          = "All articles with category “{$blogcategoryname}”";
			}
		}

		//---------------------------------------------
		// Try to find a matching tag slug.
		//---------------------------------------------
		if ( empty($wppost) )
		{
			$wppost = $this->JDBlogPost->getTaxonomyPosts($path[0], 'post_tag');
			if ( !empty($wppost) )
			{
				$blogcategoryslug = $path[0];
				$blogcategoryname = $this->JDBlogPost->getNameForTaxonomySlug($blogcategoryslug);
				$message          = "All articles with tag “{$blogcategoryname}”";
			}
		}

		//---------------------------------------------
		// Try to display {x} number of recent posts.
		//---------------------------------------------
		if ( empty($wppost) )
		{
			// if it's a number, display that many recent posts' summaries.
			if ( is_numeric($path[0]) )
			{
				$wppost           = $this->JDBlogPost->getRecentPosts($path[0]);
				$blogcategoryslug = "";
				$blogcategoryname = "";
				$message          = "The $path[0] most recent articles";
			}

		}

		//---------------------------------------------
		// Render the summary
		//---------------------------------------------
		if ( !empty($wppost) )
		{
			$this->set('posts', $wppost);
			$this->set('headline', $message);
			$this->set('blogcategoryname', $blogcategoryname);
			$this->set('blogcategoryslug', $blogcategoryslug);

			return $this->render('view-summary');
		}

		//---------------------------------------------
		// Nothing matching was found, so trigger 404.
		//---------------------------------------------
		throw new NotFoundException();
	}


	/**---------------------------------------------------------------------------*
	 * Returns an RSS feed (instead of an article) if called.
	 *
	 * @details
	 * We'll do the same as for `view` and magically deliver a feed using a
	 * taxonomy if it exists:
	 *
	 * If there's no trailing path, then deliver whole-site RSS limited to the
	 * past 30 days. Otherwise respectively deliver RSS results by treating
	 * the trailing path as Author, Category, then Tag.
	 **---------------------------------------------------------------------------*/
	public function rss()
	{
		$path  = func_get_args();
		$count = count($path);

		if ( $count < 1 )
		{
			$path[0] = "";
		}

		//-----------------------------
		// Limit results to 31 days.
		//-----------------------------
		$dateLimit = date("Y-m-d H:i:s", strtotime("-31 days"));

		//---------------------------------------------
		// Initial channelData setup.
		//---------------------------------------------
		$channelData = [
			'link'          => Router::url('/', true),
			'atom:link'     => [ 'attrib' => [ 'href' => Router::url(JDWP_PATH . '/rss' . ($path[0] == "" ? "" : "/" . $path[0]), true), 'rel' => "self", 'type' => "application/rss+xml" ] ],
			'category'      => $path[0],
			'docs'          => 'http://www.rssboard.org/rss-specification',
			'generator'     => 'JDWordpress plugin from www.balthisar.com',
			'lastBuildDate' => time(),
		];

		//---------------------------------------------
		// if there's no path, then all posts.
		//---------------------------------------------
		if ( $count < 1 )
		{
			$wppost                     = $this->JDBlogPost->getAllPosts([ 'post_date_gmt >=' => $dateLimit ]);
			$channelData['description'] = 'RSS feed for all blog posts at ' . Router::url('/', true);
			$channelData['category']    = 'All Categories';
		}
		else
		{
			//---------------------------------------------
			// Try to find a matching author slug.
			//---------------------------------------------
			if ( empty($wppost) )
			{
				$wppost = $this->JDBlogPost->getAuthorPosts($path[0]);
				if ( !empty($wppost) )
				{
					$channelData['description'] = 'RSS feed for all blog posts by author ' . $wppost[0]['JDBlogAuthor']['display_name'];
					$channelData['category']    = $wppost[0]['JDBlogAuthor']['user_nicename'];
				}
			}


			//---------------------------------------------
			// Try to find a matching category slug.
			//---------------------------------------------
			if ( empty($wppost) )
			{
				$wppost = $this->JDBlogPost->getTaxonomyPosts($path[0], 'category');
				if ( !empty($wppost) )
				{
					$channelData['description'] = 'RSS feed for blog posts with the category slug ' . $path[0];
					$channelData['category']    = $path[0];
				}
			}

			//---------------------------------------------
			// Try to find a matching tag slug.
			//---------------------------------------------
			if ( empty($wppost) )
			{
				$wppost = $this->JDBlogPost->getTaxonomyPosts($path[0], 'post_tag');
				if ( !empty($wppost) )
				{
					$channelData['description'] = 'RSS feed for blog posts with the tag slug ' . $path[0];
					$channelData['category']    = $path[0];
				}
			}
		}

		//---------------------------------------------
		// Render the xml
		//---------------------------------------------
		if ( !empty($wppost) or ($count < 1) )
		{
			$this->layout = "rss-layout";
			$this->RequestHandler->respondAs('xml');
			$this->set('posts', $wppost);
			$this->set('channelSrc', $channelData);

			return;
		}

		//---------------------------------------------
		// Nothing matching was found, so trigger 404.
		//---------------------------------------------
		throw new NotFoundException();
	}


/// @}
/// @name Methods for convenience.
/// @{

	/**---------------------------------------------------------------------------*
	 * Zips the server's wordpress uploads directory and then sends it to the
	 * web browser for download.
	 *
	 * @details
	 * This is just a simple means of getting your remote WP data for local
	 * development.
	 *
	 * If using the simple built-in security mechanism then this action requires
	 * a parameter, e.g.,
	 * `http://www.mysite.net/blog/dump_wp/secretpassword`
	 **---------------------------------------------------------------------------*/
	public function dump_wp()
	{
		//---------------------------------------------
		// Get our parameters for later use.
		//---------------------------------------------
		$path  = func_get_args();
		$count = count($path);

		//-----------------------------------------------
		// Handle security and make sure command exists.
		//-----------------------------------------------
		if ( $this->dump_use_internal_security )
		{
			if ( ($count < 1) || ($path[0] != $this->dump_secretpassword) )
			{
				throw new NotFoundException();
			}
		}

		if ( class_exists('ZipArchive') )
		{
			$tmpDir   = ROOT . DS . APP_DIR . DS . 'tmp';
			$fileName = 'wordpress-uploads-backup-' . date('Y-m-d_H-i-s') . '.zip';
			$dataDir  = $this->dump_wordpressdir . DS . 'wp-content' . DS . 'uploads';

			$this->Zip->begin($tmpDir . DS . $fileName);
			$this->Zip->addDirectory($dataDir, "uploads");
			$this->Zip->end();

			$this->autoRender = false;
			$this->response->type('Content-Type: application/zip');
			$this->response->file($tmpDir . DS . $fileName, array( 'download' => true, 'name' => $fileName ));

			return $this->response;
		}
		else
		{
			throw new InternalErrorException('This PHP version doesn’t have ZIP file support!', 501);
		}
	}


	/**---------------------------------------------------------------------------*
	 * Dumps the MySQL database that this controller's model is attached to and
	 * then serve the file as a download,
	 *
	 * @details
	 * This is just a simple means of getting your remote WP data for local
	 * development.
	 *
	 * If using the simple built-in security mechanism then this action requires
	 * a parameter, e.g.,
	 * `http://www.mysite.net/blog/dump_sql/secretpassword`
	 **---------------------------------------------------------------------------*/
	public function dump_sql()
	{
		//---------------------------------------------
		// Get our parameters for later use.
		//---------------------------------------------
		$path  = func_get_args();
		$count = count($path);

		//-----------------------------------------------
		// Handle security and make sure command exists.
		//-----------------------------------------------
		if ( $this->dump_use_internal_security )
		{
			if ( ($count < 1) || ($path[0] != $this->dump_secretpassword) )
			{
				throw new NotFoundException();
			}
		}

		$dataRecord       = $this->JDBlogPost->prepareSQLDump();
		$this->autoRender = false;
		$this->response->type('Content-Type: text/x-sql');
		$this->response->download($dataRecord['filename']);
		$this->response->body($dataRecord['data']);
	}

/// @}


	/****************************************************************************
	 * SUPPORTING PIECES
	 * These are actions that should only ever be used with requestAction.
	 * HOWEVER, if an exception is pending requestAction can trigger
	 * rending of the exception's page immediately, so that's another
	 * good reason to avoid requestAction. Try using the helper instead.
	 ****************************************************************************/
/// @name Methods intended to be used via `requestAction`.
/// @{

	/**---------------------------------------------------------------------------*
	 * Gets one random post.
	 **---------------------------------------------------------------------------*/
	public function getOneRandomPost()
	{
		if ( empty($this->request->params['requested']) )
		{
			throw new ForbiddenException();
		}

		$randomPost = $this->JDBlogPost->getRandomPosts(1);
		$this->set('post', $randomPost[0]);

		return $this->render('../Elements/requestAction/getOneRandomPost');
	}


	/**---------------------------------------------------------------------------*
	 * Returns the sitemap body (only) so that other controllers might compile
	 * the results into a full sitemap.
	 **---------------------------------------------------------------------------*/
	public function getSitemapBody()
	{
		if ( empty($this->request->params['requested']) )
		{
			throw new ForbiddenException();
		}

		$wpposts = $this->JDBlogPost->getAllPosts();

		$this->set('posts', $wpposts);
		$this->set('post_changefreq', 'monthly');
		$this->set('post_priority', '0.5');

		return $this->render('../Elements/requestAction/getSitemapBody');
	}


	/**---------------------------------------------------------------------------*
	 * Will return a toc of all articles (optionally for $slug only) divided
	 * into year and month.
	 **---------------------------------------------------------------------------*/
	public function tocArticlesWithDateHeaders()
	{
		if ( empty($this->request->params['requested']) )
		{
			throw new ForbiddenException();
		}

		//---------------------------------------------
		// Get our parameters for later use.
		//---------------------------------------------
		$path  = func_get_args();
		$count = count($path);

		if ( $count > 0 )
		{

			$slug = $path[0];

			//---------------------------------------------
			// Try to find a matching slug.
			//---------------------------------------------
			$wppost = $this->JDBlogPost->getAuthorPostsAsArchives($slug);

			if ( empty($wppost) )
			{
				$wppost = $this->JDBlogPost->getTaxonomyPostsAsArchives($slug, 'category');
			}

			if ( empty($wppost) )
			{
				$wppost = $this->JDBlogPost->getTaxonomyPostsAsArchives($slug, 'post_tag');
			}
		}
		else
		{
			$slug   = "";
			$wppost = $this->JDBlogPost->getAllPostsAsArchives();
		}

		$this->set('archivePosts', $wppost);

		return $this->render('../Elements/requestAction/tocArticlesWithDateHeaders');
	}


	/**---------------------------------------------------------------------------*
	 * Provides a list of all taxonomy types in a `<ul>` toc format; for example
	 * can provide a list of each 'category'.
	 **---------------------------------------------------------------------------*/
	public function tocForTaxonomyType()
	{
		if ( empty($this->request->params['requested']) )
		{
			throw new ForbiddenException();
		}

		$path  = func_get_args();
		$count = count($path);

		$type = 'category';
		if ( $count > 0 )
		{
			$type = $path[0];
		}

		$terms = $this->JDBlogPost->getTermsForTaxonomy($type);

		$this->set('terms', $terms);
		$this->set('rssFlag', false);
		$this->set('type', $type);

		return $this->render('../Elements/requestAction/tocForTaxonomyType');
	}


	/**---------------------------------------------------------------------------*
	 * Provides a list of all taxonomy types in a `<ul>` toc format with links
	 * to RSS feeds; for example can provide a list of each 'category'.
	 **---------------------------------------------------------------------------*/
	public function tocRSSForTaxonomyType()
	{
		if ( empty($this->request->params['requested']) )
		{
			throw new ForbiddenException();
		}

		$path  = func_get_args();
		$count = count($path);

		$type = 'category';
		if ( $count > 0 )
		{
			$type = $path[0];
		}

		$terms = $this->JDBlogPost->getTermsForTaxonomy($type);

		$this->set('terms', $terms);
		$this->set('rssFlag', true);
		$this->set('type', $type);

		return $this->render('../Elements/requestAction/tocForTaxonomyType');
	}


	/**---------------------------------------------------------------------------*
	 * Returns the most recent 5 post titles in `<ul>` format.
	 *
	 * @details
	 * If an optional |slug| is given then the most recent five
	 * posts from the slug will be returned, on order of preference
	 * for author, category, and post_tag.
	 **---------------------------------------------------------------------------*/
	public function tocRecent()
	{
		if ( empty($this->request->params['requested']) )
		{
			throw new ForbiddenException();
		}

		//---------------------------------------------
		// Get our paramaters for later use.
		//---------------------------------------------
		$path  = func_get_args();
		$count = count($path);

		$limit = 5;

		//---------------------------------------------
		// All recent, or find a matching slugs.
		//---------------------------------------------
		if ( $count == 0 )
		{
			$wppost = $this->JDBlogPost->getRecentPosts($limit);
		}
		if ( empty($wppost) )
		{
			$wppost = $this->JDBlogPost->getAuthorPosts($path[0], $limit);
		}
		if ( empty($wppost) )
		{
			$wppost = $this->JDBlogPost->getTaxonomyPosts($path[0], 'category', $limit);
		}

		if ( empty($wppost) )
		{
			$wppost = $this->JDBlogPost->getTaxonomyPosts($path[0], 'post_tag', $limit);
		}

		//---------------------------------------------
		// Render the summary
		//---------------------------------------------
		$this->set('slug', $count == 0 ? 'All' : $path[0]);
		$this->set('posts', $wppost);

		return $this->render('../Elements/requestAction/tocRecent');
	}


	/**---------------------------------------------------------------------------*
	 * Provides a list of all terms for a specified taxonomy and its article
	 * titles in a `<ul>` toc format; for example can provide a list of all
	 * 'category' and articles for each 'category.'
	 **---------------------------------------------------------------------------*/
	public function tocTaxonomyArticles()
	{
		if ( empty($this->request->params['requested']) )
		{
			throw new ForbiddenException();
		}

		$path  = func_get_args();
		$count = count($path);
		if ( $count > 0 )
		{
			$type = $path[0];
		}
		else
		{
			$type = 'category';
		}

		$taxonomyList = $this->JDBlogPost->getAllPostsForTaxonomyByTerm($type);

		$this->set('wpTocPosts', $taxonomyList);
		$this->set('tocType', $type);

		return $this->render('../Elements/requestAction/tocTaxonomyArticles');
	}

/// @}


} // class

