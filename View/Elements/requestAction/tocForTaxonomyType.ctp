<?php
	if ($rssFlag)
	{
		$slug = 'rss_slug';
		$class = $jd_vars['prefix-listTaxonomy'] . $type;
	}
	else
	{
		$slug = 'cake_slug';
		$class = $jd_vars['prefix-listTaxonomyRSS'] . $type;
	}

	$result[] = "\n<ul class='$class'>";

	foreach ($terms as $record)
	{
		$result[] = "\t<li><a href='{$record['Terms'][$slug]}'>{$record['Terms']['name']}</a></li>";
	}

	$result[] = "</ul>\n";
	echo implode( "\n", $result);
?>
