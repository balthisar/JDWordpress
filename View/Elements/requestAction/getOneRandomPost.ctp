<?php
	echo "\n<div class='{$jd_vars['blogpost-article-excerpt']} {$jd_vars['blogpost-random']}'>";
	echo $this->element('content-post-compact', $dataForView, ['plugin' => 'JDWordpress'] );
	echo "\n</div>\n";
?>
