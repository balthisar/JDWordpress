<?php
	$result[] = "\n<ul class='{$jd_vars['toc-Recent']} {$jd_vars['toc-Recent-prefix-slug']}$slug'>";
	foreach ($posts as $post)
	{
		$result[] = "\t<li><a href='{$post['JDBlogPost']['post_permalink_cake']}'>{$post['JDBlogPost']['post_title']}</a></li>";
	}
	$result[] = "</ul>\n";
	echo implode("\n", $result);
?>

