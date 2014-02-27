<?php
/*************************************************************************************************
 * @file           JDBlogPostDataComponent.php
 *
 * @brief
 *
 * Part of plugin `JDWordpress`
 *
 * @details
 *
 * This component provides functions to add to your AppController or other controller in
 * order to populate your views for including JDWordpress content in other areas of your
 * application. to other areas of your site.
 *
 * Alternatives are to use `requestAction` (see the actions available in `JDBlogPost.php`)
 * which has some inherent issues, or to use the helpers in JDBlogPostHelper (which uses
 * this Component to do the heavy lifting).
 *
 * Note that this component does break MVC by communicating directly with the database, but
 * it's a black box to you and allows you to avoid breaking the paradigm yourself. This is
 * the most Cake-like way to include the Wordpress data in your own controllers' views.
 *
 * - `getOneRandomPost()`
 * - `getSitemapBody()`
 * - `tocArticlesWithDateHeaders( $slug = {all} )`
 * - `tocForTaxonomyType( $type = {category} )`
 * - `tocRSSForTaxonomyType( $type = {category} )`
 * - `tocRecent( $slug = {all} )`
 * - `tocTaxonomyArticles( $type = {category} )`
 *
 * @date           2014-02-12
 * @author         Jim Derry
 * @copyright      ©2014 by Jim Derry and balthisar.com
 * @copyright      MIT License (http://www.opensource.org/licenses/mit-license.php)
 *************************************************************************************************/


App::uses('Component', 'Controller');


/**
 * This class implements a component for including Wordpress data
 * in other controllers' view variables.
 */
class JDBlogPostDataComponent extends Component
{
	/**
		This provides a couple of things to all of your views and things rendered by this
		Plugin. You can permanently modify them here, or override any of the setting by
		including a `jd_vars` array in the `$settings` array in your `$components`
		declaration of the controllers that use it.
	 */
	public $jd_vars = array(
		'routesPath'                  => JDWP_PATH, // you should not change this!
		'elementComments'             => true,      // render HTML comments in elements?

		'archives-outer'              => 'blogpost_outter',
		'archives-year'               => 'blogpost_year',
		'archives-month'              => 'blogpost_month',

		'blogpost-article'            => 'blogpost-article',
		'blogpost-article-excerpt'    => 'blogpost-article-compact',
		'blogpost-enclosure-anchor'   => 'blogpost-enclosure-anchor',
		'blogpost-random'             => 'blogpost-random',

		'blogpost-img'                => 'blogpost-img',
		'blogpost-title'              => 'blogpost-title',
		'blogpost-byline'             => 'blogpost-byline',
		'blogpost-date'               => 'blogpost-date',
		'blogpost-author'             => 'blogpost-author',
		'blogpost-author-url'         => 'blogpost-author-url',
		'blogpost-content-excerpt'    => 'blogpost-content-excerpt',
		'blogpost-content'            => 'blogpost-content',
		'blogpost-list-readmore'      => 'blogpost-readmore_list',
		'blogpost-list-readmore-item' => 'blogpost-readmore',
		'blogpost-list-category'      => 'blogpost-category_list',
		'blogpost-list-category-item' => 'blogpost-category',
		'blogpost-list-category-tag'  => 'blogpost-tag',

		'prefix-listTaxonomy'         => 'toc-',
		'prefix-listTaxonomyRSS'      => 'rss-',

		'taxonomy-by-term-outer'      => 'blog-tocTaxonomyArticles',
		'taxonomy-by-term-term'       => 'blog-tocTaxonomyArticles-term',
		'taxonomy-by-term-title'      => 'blog-tocTaxonomyArticles-title',
		'taxonomy-by-term-prefix'     => 'type-',

		'toc-Recent'                  => 'blog-tocRecent',
		'toc-Recent-prefix-slug'      => 'slug-',

		'view-summary-headline'       => 'drop-shadow curled antiquewhite',
	);


	private $JDBlogPost;


	/**---------------------------------------------------------------------------*
	 * Create a reference to our model of interest.
	 *
	 * @param mixed $collection inherited from super.
	 * @param array $settings   inherited from super.
	 **---------------------------------------------------------------------------*/
	public function __construct( $collection, $settings = array() )
	{
		if ( isset($settings['jd_vars']) )
		{
			$this->jd_vars = array_merge($this->jd_vars, $settings['jd_vars']);
			unset($settings['jd_vars']);
		}
		parent::__construct($collection, $settings = array());

		$this->JDBlogPost = ClassRegistry::init('JDWordpress.JDBlogPost');
	}


	/**---------------------------------------------------------------------------*
	 * Returns one random post.
	 * - works with `Elements/requestAction/getOneRandomPost`
	 **---------------------------------------------------------------------------*/
	public function getOneRandomPost()
	{
		$result = array(
			'jd_vars' => $this->jd_vars,
			'post'    => $this->JDBlogPost->getRandomPosts(1)[0],
			'cache'   => true,
		);

		return $result;
	}


	/**---------------------------------------------------------------------------*
	 * Returns the sitemap body (only) so that other controllers
	 * might compile the results into a full sitemap.
	 * - works with `Elements/requestAction/getSitemapBody`
	 **---------------------------------------------------------------------------*/
	public function getSitemapBody()
	{
		$result = array(
			'jd_vars'         => $this->jd_vars,
			'posts'           => $this->JDBlogPost->getAllPosts(),
			'post_changefreq' => 'monthly',
			'post_priority'   => '0.5',
			'cache'           => true
		);

		return $result;
	}


	/**---------------------------------------------------------------------------*
	 * Will return a toc of all articles (optionally for `$slug` only) divided
	 * into year and month.
	 * - works with `Elements/requestAction/tocArticlesWithDateHeaders`
	 *
	 * @param string $slug Optional; specify the slug for TOC articles.
	 * @return string
	 **---------------------------------------------------------------------------*/
	public function tocArticlesWithDateHeaders( $slug = '' )
	{
		if ( !empty($slug) )
		{
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
			$wppost = $this->JDBlogPost->getAllPostsAsArchives();
		}

		$result = array(
			'jd_vars'      => $this->jd_vars,
			'archivePosts' => $wppost,
			'cache'        => true
		);

		return $result;
	}


	/**---------------------------------------------------------------------------*
	 * Provides a list of all taxonomy types in a `<ul>` toc format; for example
	 * it can provide a list of each 'category'.
	 * - works with `Elements/requestAction/tocForTaxonomyType`
	 *
	 * @param   String $type Specify the taxonomy for TOC.
	 * @return string
	 **---------------------------------------------------------------------------*/
	public function tocForTaxonomyType( $type = 'category' )
	{
		$result = array(
			'jd_vars' => $this->jd_vars,
			'terms'   => $this->JDBlogPost->getTermsForTaxonomy($type),
			'rssFlag' => false,
			'type'    => $type,
			'cache'   => true
		);

		return $result;
	}


	/**---------------------------------------------------------------------------*
	 * Provides a list of all taxonomy types in a `<ul>` toc format linking to
	 * RSS feeds; for example can provide a list of each 'category'.
	 * - works with `Elements/requestAction/tocForTaxonomyType`
	 *
	 * @param string $type Specify the taxonomy for TOC.
	 * @return string
	 **---------------------------------------------------------------------------*/
	public function tocRSSForTaxonomyType( $type = 'category' )
	{
		$result = array(
			'jd_vars' => $this->jd_vars,
			'terms'   => $this->JDBlogPost->getTermsForTaxonomy($type),
			'rssFlag' => true,
			'type'    => $type,
			'cache'   => true
		);

		return $result;
	}


	/**---------------------------------------------------------------------------*
	 * Returns the most recent 5 post titles in `<ul>` format.
	 *
	 * @details
	 * If an optional `$slug` is given then the most recent five posts from the
	 * slug will be returned, on order of preference for author, category, and
	 * post_tag. Optional |$limit| can change the number of post titles returned.
	 * - works with Elements/requestAction/tocRecent
	 *
	 * @param   string  $slug  Specify the slug for a TOC.
	 * @param   integer $limit Specify the number of TOC entries wanted.
	 * @return string
	 **---------------------------------------------------------------------------*/
	public function tocRecent( $slug = '', $limit = 5 )
	{
		//---------------------------------------------
		// All recent, or find a matching slugs.
		//---------------------------------------------
		if ( empty($slug) )
		{
			$wppost = $this->JDBlogPost->getRecentPosts($limit);
		}
		if ( empty($wppost) )
		{
			$wppost = $this->JDBlogPost->getAuthorPosts($slug, $limit);
		}
		if ( empty($wppost) )
		{
			$wppost = $this->JDBlogPost->getTaxonomyPosts($slug, 'category', $limit);
		}

		if ( empty($wppost) )
		{
			$wppost = $this->JDBlogPost->getTaxonomyPosts($slug, 'post_tag', $limit);
		}

		$result = array(
			'jd_vars' => $this->jd_vars,
			'posts'   => $wppost,
			'slug'    => empty($slug) ? 'All' : $slug,
			'cache'   => true
		);

		return $result;
	}


	/**---------------------------------------------------------------------------*
	 * Provides a list of all terms for a specified taxonomy and its article
	 * titles in a `<ul>` toc format; for example can provide a list of all
	 * 'category' with articles for each 'category.'
	 * - works with Elements/requestAction/tocTaxonomyArticles
	 *
	 * @param string $type Specify the taxonomy for TOC.
	 * @return string
	 **---------------------------------------------------------------------------*/
	public function tocTaxonomyArticles( $type = 'category' )
	{
		$taxonomyList = $this->JDBlogPost->getAllPostsForTaxonomyByTerm($type);

		$result = array(
			'jd_vars'    => $this->jd_vars,
			'wpTocPosts' => $this->JDBlogPost->getAllPostsForTaxonomyByTerm($type),
			'tocType'    => $type,
			'cache'      => true
		);

		return $result;
	}


} // class
