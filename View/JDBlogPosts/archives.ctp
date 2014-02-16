<?php
/**
 * Overriding plugin views from inside your application
 * You can override any plugin views from inside your app using special paths. If you have a plugin
 * called ‘JDWordpress’ you can override the view files of the plugin with more application
 * specific view logic by creating files using the following template
 * “app/View/Plugin/JDWorpress/JDBlogPosts/[view].ctp”.
 */
	echo "\n<article>";
	echo $this->element('content-archives', ['cache' => true], ['plugin' => 'JDWordpress'] );
	echo "</article>\n";
?>
