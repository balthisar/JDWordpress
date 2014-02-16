<?php
	foreach ($posts as $post)
	{
        $post_loc = "http://" . $_SERVER['HTTP_HOST'] . $post['JDBlogPost']['post_permalink_cake'];
		$post_lastmod = $post['JDBlogPost']['post_modified_gmt_sitemap'];

		$result[] = "<url>";
		$result[] = "\t<loc>$post_loc</loc>";
		$result[] = "\t<lastmod>$post_lastmod</lastmod>";
		$result[] = "\t<changefreq>$post_changefreq</changefreq>";
		$result[] = "\t<priority>$post_priority</priority>";
		$result[] = "</url>\n";
	}
	echo implode("\n", $result);
?>
