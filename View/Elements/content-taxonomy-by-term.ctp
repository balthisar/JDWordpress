<?php
	$t = function ($d) { return str_repeat("\t", $d); };

	$display[] = $jd_vars['elementComments'] ? "\n\t<!-- rendered by content-taxonomy-by-term.ctp -->" : "";
	$display[] = $t(1) . "<ul class='{$jd_vars['taxonomy-by-term-outer']} {$jd_vars['taxonomy-by-term-prefix']}{$tocType}'>";
	foreach ( $wpTocPosts as $record )
		{
			$display[] = $t(2) . "<li class='{$jd_vars['taxonomy-by-term-term']} {$record['Terms']['slug']}'><a href='{$record['Terms']['cake_slug']}'>{$record['Terms']['name']}</a>";
			$display[] = $t(3) . "<div>";
			$display[] = $t(4) . "<ul>";
			foreach ( $record['Posts'] as $post )
			{
				$display[] = $t(5) . "<li class='{$jd_vars['taxonomy-by-term-title']}'><a href='{$post['JDBlogPost']['post_permalink_cake']}'>{$post['JDBlogPost']['post_title']}</a></li>";
			}
			$display[] = $t(4) . "</ul>";
			$display[] = $t(3) . "</div>";
			$display[] = $t(2) . "</li>";
		}
    $display[] = $t(1) . "</ul>\n";
	echo implode("\n", $display);
?>
