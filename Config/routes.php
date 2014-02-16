<?php

/*********************************************************************************************//**
    @file routes.php

    @brief

    Part of plugin `JDWordpress`

    @details

    Routes configuration for JDWordpress.

    Use this file to to define the base URL path for everything
    in this plugin.


    @note

    For more information and examples look at the comments in the source file.

    @date           2014-02-12
    @author         Jim Derry
    @copyright      ©2014 by Jim Derry and balthisar.com
    @copyright      MIT License (http://www.opensource.org/licenses/mit-license.php)

 *************************************************************************************************/


	/*
        Define the base URL path for everything in this plugin.

        This Constant is used througout the plugin to ensure that all resources
        loaded from the Wordpress database resolve to the correct location, and
        also in some of the class methods. DO NOT DELETE OR UNDEFINE IT. YOU
        MAY CHANGE IT TO SUIT YOUR NEEDS.
	*/
	if (!defined('JDWP_PATH'))
	{
		define('JDWP_PATH', '/blog');
	}


	/*
	    Configure our JDWordpress routes.
	*/

	// This route connects a naked folder path that has no actions.
	Router::connect(JDWP_PATH,
		array('plugin' => 'JDWordpress', 'controller' => 'JDBlogPosts', 'action' => 'index')
	);

	// These are actions that don't take parameters, and actions that can work without parameters.
	$requestActions = '|getOneRandomPost|getSitemapBody|tocArticlesWithDateHeaders|tocForTaxonomyType|tocRSSForTaxonomyType|tocRecent|tocTaxonomyArticles';
	$jimderru= "HELLO";
	Router::connect(JDWP_PATH . '/:action',
		array('plugin' => 'JDWordpress', 'controller' => 'JDBlogPosts', 'action' => ':action'),
		array('action' => 'tags|categories|archives|rss|dump_sql|dump_wp' . $requestActions )
	);

	// An action to grab our slug for the default view controller.
	Router::connect(JDWP_PATH . '/:slug',
		array('plugin' => 'JDWordpress', 'controller' => 'JDBlogPosts', 'action' => 'view'),
		array('pass' => array('slug'))
	);

	// These are actions that require parameters (such as accepting a slug)
	$requestActions = '|tocRecent|tocTaxonomyArticles';
	Router::connect(JDWP_PATH . '/:action/:slug',
		array('plugin' => 'JDWordpress', 'controller' => 'JDBlogPosts', 'action' => ':action'),
		array('action' => 'rss|dump_sql|dump_wp' . $requestActions,
		      'pass' => array('slug') )
	);



	/*
        YOU SHOULD DELETE EVERYTHING FROM HERE DOWN IF I DIDN'T REMOVE IT BEFORE PUBLISHING.

        It won't hurt anything if you leave it in. It's only here so that I can leave the
        rest of the source code generic enough to release without much custom configuration.
        What it's doing is checking my production flag to pick the directory that Wordpress
        lives in on dev vs production, and is used ONLY for setting |$dump_wordpressdir|
        in the JDBlogPostsController. This is a public var that you will set, too.

        Say, if you want to, you can leave this here and use it, but it's not very Cake-like.
	*/

	if (defined('JD_PRODUCTION'))
	{
		define('JDWP_INSTALL', JD_PRODUCTION ? ROOT . DS . 'site-balthisar' . DS . 'wordpress' : ROOT . DS . 'wordpress');
	}
