<?php
	echo "<?xml version='1.0' encoding='UTF-8'?>";

	$documentData = [
		'xmlns:content' => "http://purl.org/rss/1.0/modules/content/",
		'xmlns:atom' => "http://www.w3.org/2005/Atom",
					];

	$channelData = [
		'title'			=> 'balthisar.com blog',
		'link'			=> $channelSrc['link'],
		'atom:link'		=> $channelSrc['atom:link'],
		'description'	=> $channelSrc['description'],
		'language'		=> 'en-US',
		'category'		=> $channelSrc['category'],
		'copyright'		=> '©' . strftime("%Y") . ' by Jim Derry and balthisar.com',
		'docs'			=> $channelSrc['docs'],
		'generator'		=> $channelSrc['generator'],
		'lastBuildDate'	=> $this->Rss->time(time()),
		'managingEditor'=> 'balthisar@balthisar.com (Jim Derry)',
		'image'			=> [	'link'			=> Router::url('/', true),
								'title'			=> 'balthisar.com blog',
								'url'			=> Router::url('/apple-touch-icon-144x144-precomposed.png', true),
								'description'	=> 'balthisar.com logo icon',
								'height'		=> '144',
								'width'			=> '144',
							],
		];

	$channel = $this->Rss->channel(array(), $channelData, $content_for_layout);
	echo $this->Rss->document($documentData, $channel);
?>
