<?php
/*********************************************************************************************//**
	@file JDBlogPostHelper.php

	@brief

	Part of plugin `JDWordpress`

	@details

	This helper provides functions to add to your layout and/or views to
	display some of the convenience features offered by the `JDWordpress`
	plugin.

	This helper helps you display several things from the Wordpress
	database without having to use `requestAction`. The tradeoff is that
	internally we are not abiding by Cake's MVC convention. This helper
	accesses the Component directly. It will not affect performance of your
	application, and in theory should be much quicker than `requestAction`.

	Instead of using this helper, you might consider instead using the
	included `JDBlogPostDataComponent` to set data for the view in your
	AppController (or any of your controllers) and display the data
	normally.

	- `getOneRandomPost()`
	- `getSitemapBody()`
	- `tocArticlesWithDateHeaders( $slug = {all} )`
	- `tocForTaxonomyType( $type = {category} )`
	- `tocRSSForTaxonomyType( $type = {category} )`
	- `tocRecent( $slug = {all} )`
	- `tocTaxonomyArticles( $type = {category} )`


    @date           2014-02-12
    @author         Jim Derry
    @copyright      ©2014 by Jim Derry and balthisar.com
    @copyright      MIT License (http://www.opensource.org/licenses/mit-license.php)

 *************************************************************************************************/


App::uses('AppHelper', 'View/Helper');


/// This class implements the display helper for the JDWordpress plugin.
class JDBlogPostHelper extends AppHelper
{
	private $JDBlogPostData;


/*——————————————————————————————————————————————————————————————————*
	__construct
		Create a reference to the component.
 *——————————————————————————————————————————————————————————————————*/
public function __construct( $view, $settings = array())
{
	parent::__construct( $view, $settings = array());
	App::import('Component', 'JDWordpress.JDBlogPostData');
	$this->JDBlogPostData = new JDBlogPostDataComponent(new ComponentCollection());
}


/********************************************************************//**
	@brief
		Gets one random post.
 ********************************************************************/
public function getOneRandomPost()
{
	$result = $this->JDBlogPostData->getOneRandomPost();

	return $this->_View->element('requestAction/getOneRandomPost', $result, ['plugin' => 'JDWordpress'] );
}


/********************************************************************//**
	@brief
		Returns the sitemap body (only) so that other controllers
		might compile the results into a full sitemap.
 ********************************************************************/
public function getSitemapBody()
{
	$result = $this->JDBlogPostData->getSitemapBody();

	return $this->_View->element('/requestAction/getSitemapBody', $result, ['plugin' => 'JDWordpress']);
}


/********************************************************************//**
	@brief
		Will return a toc of all articles -- optionally for `$slug`
		only -- broken into year and month.
 ********************************************************************/
public function tocArticlesWithDateHeaders( $slug = '' )
{
	$result = $this->JDBlogPostData->tocArticlesWithDateHeaders( $slug );

	return $this->_View->element('/requestAction/tocArticlesWithDateHeaders', $result, ['plugin' => 'JDWordpress']);
}


/********************************************************************//**
	@brief
		Provides a list of all taxonomy types in a \<ul> toc format.
		For example can provide a list of each 'category'.
 ********************************************************************/
public function tocForTaxonomyType( $type = 'category' )
{
	$result = $this->JDBlogPostData->tocForTaxonomyType( $type );

	return $this->_View->element('/requestAction/tocForTaxonomyType', $result, ['plugin' => 'JDWordpress']);
}


/********************************************************************//**
	@brief
		Provides a list of all taxonomy types in a \<ul> toc format.
		For example can provide a list of each 'category'.
		This provides a list of links to the RSS feeds for each
		term.
 ********************************************************************/
public function tocRSSForTaxonomyType( $type = 'category' )
{
	$result = $this->JDBlogPostData->tocRSSForTaxonomyType( $type );

	return $this->_View->element('/requestAction/tocForTaxonomyType', $result, ['plugin' => 'JDWordpress']);
}


/********************************************************************//**
	@brief
		Returns the most recent 5 post titles in \<ul> format.
		If an optional |slug| is given then the most recent five
		post from the slug will be returned, on order of preference
		for author, category, and post_tag.
 ********************************************************************/
public function tocRecent( $slug = '', $limit = 5 )
{
	$result = $this->JDBlogPostData->tocRecent( $slug, $limit );

	return $this->_View->element('/requestAction/tocRecent', $result, ['plugin' => 'JDWordpress']);
}


/********************************************************************//**
	@brief
		Provides a list of all terms for a specified taxonomy and its
		article titles in a \<ul> toc format. For example can provide
		a list of all 'category' and articles for each 'category.'
 ********************************************************************/
public function tocTaxonomyArticles( $type = 'category' )
{
	$result = $this->JDBlogPostData->tocTaxonomyArticles( $type );

	return $this->_View->element('/requestAction/tocTaxonomyArticles', $result, ['plugin' => 'JDWordpress']);
}

} // class
