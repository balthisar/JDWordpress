<?php
	foreach ($posts as $post)
	{
		$cats = [];
		foreach ($post['Categories'] as $category)
		{
			$cats[] = $category['Category']['name'];
		}
		foreach ($post['Tags'] as $category)
		{
			$cats[] = 'tag:' . $category['Category']['name'];
		}

		echo  $this->Rss->item(array(),
										[	'title' => $post['JDBlogPost']['post_title'],
											'author' => "{$post['JDBlogAuthor']['user_email']} ({$post['JDBlogAuthor']['display_name']})",
											'link' => $post['JDBlogPost']['post_permalink_cake'],
											'guid' => array('url' => $post['JDBlogPost']['post_permalink_cake'], 'isPermaLink' => 'true'),
											'description' => h(strip_tags($post['JDBlogPost']['post_excerpt'])),
											'content:encoded' =>  [ 'content' => h(strip_tags($post['JDBlogPost']['post_content'])), 'namespace' => [ 'prefix' => 'content', 'url' => 'http://purl.org/rss/1.0/modules/content/']],
											'comments' => $post['JDBlogPost']['post_permalink_cake'],
											'pubDate' => $post['JDBlogPost']['post_date_formatted'] . " " . $post['JDBlogPost']['post_time_formatted'],
											'category' => $cats,
										] );
	}
?>
