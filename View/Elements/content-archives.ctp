<?php
	$t = function ($d) { return str_repeat("\t", $d); };

	$display[] = $jd_vars['elementComments'] ? "\n\t<!-- rendered by content-archives.ctp -->" : '';
	$display[] = $t(1) . "<ul class='{$jd_vars['archives-outer']}'>";

    foreach ($archivePosts as $year => $months)
    {
        $display[] = $t(2) . "<li class='{$jd_vars['archives-year']}'>";
        $display[] = $t(3) . "<span>$year</span>";
        $display[] = $t(3) . "<ul>";

        foreach ($months as $month => $posts)
        {
            $display[] = $t(4) . "<li class='{$jd_vars['archives-month']}'>";
            $display[] = $t(5) . "<span>" . date("F", mktime(0, 0, 0, $month, 10)) . "</span>";
            $display[] = $t(5) . "<ul>";

            foreach ($posts as $post)
            {
        		$display[] = $t(6) . "<li><a href='{$post['JDBlogPost']['post_permalink_cake']}'>{$post['JDBlogPost']['post_title']}</a></li>";
            }

            $display[] = $t(5) . "</ul>";
            $display[] = $t(4) . "</li>";

        }

            $display[] = $t(3) . "</ul>";
            $display[] = $t(2) . "</li>";

    }
    $display[] = $t(1) . "</ul>\n";
	echo implode("\n", $display);
?>
