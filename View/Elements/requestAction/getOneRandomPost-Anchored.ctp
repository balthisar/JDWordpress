<?php
	echo "\n<a class='{$jd_vars['blogpost-enclosure-anchor']} {$jd_vars['blogpost-random']}' href='{$post['JDBlogPost']['post_permalink_cake']}'>\n";
	echo "<div class='{$jd_vars['blogpost-article-excerpt']} {$jd_vars['blogpost-random']}'>";
	echo $this->element('content-post-excerpt', $dataForView, ['plugin' => 'JDWordpress'] );
	echo "</div>\n";
	echo "</a>\n";
?>
