<?php
	$postTitle = $post['JDBlogPost']['post_title'];
	$postLink = $post['JDBlogPost']['post_permalink_cake'];
	$postDate = $post['JDBlogPost']['post_date_formatted'];
	$postTime = $post['JDBlogPost']['post_time_formatted'];
	$pAuthorName = $post['JDBlogAuthor']['display_name'];
	$pAuthorArticles = $post['JDBlogAuthor']['user_nicename'];
	$pAuthorUrl = $post['JDBlogAuthor']['user_url'];

	$display = $jd_vars['elementComments'] ? "\n\t<!-- rendered by content-post-single.ctp -->\n" : "\n";

	if ( !empty($post['FeaturedImage']) )
	{
        $pLoc = $post['FeaturedImage']['image_location_relative'];
        $pAlt = $post['FeaturedImage']['image_title'];
	    $display .= "\t<img src='$pLoc' title='$pAlt' alt='$pAlt' class='{$jd_vars['blogpost-img']}' />\n";
	}

	$display .= <<<_HTML
	<h1 class='{$jd_vars['blogpost-title']}'><a href='{$postLink}'>{$postTitle}</a></h1>
	<ul class='{$jd_vars['blogpost-byline']}'>
		<li class='{$jd_vars['blogpost-date']}' title='$postTime'><span>$postDate</span></li>
		<li class='{$jd_vars['blogpost-author']}'><a href='{$jd_vars['routesPath']}/$pAuthorArticles' title='More articles by this writer…'>$pAuthorName</a></li>
		<li class='{$jd_vars['blogpost-author-url']}'><a href='$pAuthorUrl' title='Go to author’s website…'>($pAuthorUrl)</a></li>
	</ul>
	<div class='{$jd_vars['blogpost-content']}'>
		{$post['JDBlogPost']['post_content']}
	</div>
	<ul class='{$jd_vars['blogpost-list-category']}'>

_HTML;

	foreach ($post['Categories'] as $category)
	{
		$display .= "\t\t<li class='{$jd_vars['blogpost-list-category-item']}'><a href='{$jd_vars['routesPath']}/{$category['Category']['slug']}'>{$category['Category']['name']}</a></li>\n";
	}

	foreach ($post['Tags'] as $category)
	{
		$display .=  "\t\t<li class='{$jd_vars['blogpost-list-category-tag']}'><a href='{$jd_vars['routesPath']}/{$category['Category']['slug']}'>{$category['Category']['name']}</a></li>\n";
	}

	$display .= "\t</ul>\n";

	echo $display;

?>
