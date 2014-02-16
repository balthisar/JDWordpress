<?php
	$postTitle = $post['JDBlogPost']['post_title'];
	$postLink = $post['JDBlogPost']['post_permalink_cake'];
	$postDate = $post['JDBlogPost']['post_date_formatted'];
	$postTime = $post['JDBlogPost']['post_time_formatted'];
	$pAuthorName = $post['JDBlogAuthor']['display_name'];
	$pAuthorArticles = $post['JDBlogAuthor']['user_nicename'];
	$pAuthorUrl = $post['JDBlogAuthor']['user_url'];

	$display = $jd_vars['elementComments'] ? "\n\t<!-- rendered by content-post-excerpt.ctp -->\n" : "\n";

	if ( !empty($post['FeaturedImage']) )
	{
        $pLoc = $post['FeaturedImage']['image_location_relative'];
        $pAlt = $post['FeaturedImage']['image_title'];
	    $display .= "\t<img src='$pLoc' title='$pAlt' alt='$pAlt' class='{$jd_vars['blogpost-img']}' />\n";
	}

	$display .= <<<_HTML
	<h1 class='{$jd_vars['blogpost-title']}'>{$postTitle}</h1>
	<div class='{$jd_vars['blogpost-content-excerpt']}'>
		<p>{$post['JDBlogPost']['post_excerpt']}</p>
	</div>
	<ul class='{$jd_vars['blogpost-list-category']}'>

_HTML;

	foreach ($post['Categories'] as $category)
	{
		$display .= "\t\t<li class='{$jd_vars['blogpost-list-category-item']}'><span>{$category['Category']['name']}</span></li>\n";
	}

	foreach ($post['Tags'] as $category)
	{
		$display .=  "\t\t<li class='{$jd_vars['blogpost-list-category-tag']}'><span>{$category['Category']['name']}<span></li>\n";
	}

	$display .= "\t</ul>\n";

	echo $display;

?>
