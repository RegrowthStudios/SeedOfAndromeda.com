<div class="double-col">	<h3 style="text-align: center; font-size: 1.6em;">Videos</h3>	<div class="vid-prev">		<img src="Assets/images/arrowLeft.png" />	</div>	<div id="video-frame-inner">		<iframe height="393" width="700" src="#" class="temp-iframe"			frameborder="0" allowfullscreen></iframe>		<iframe height="393" width="700" src="#" class="enlarged-iframe"			frameborder="0" allowfullscreen></iframe>	</div>	<div class="vid-next">		<img src="Assets/images/arrowRight.png" />	</div>	<br /></div><br /><?php

// set feed URL
$feedURL = 'https://gdata.youtube.com/feeds/api/videos?author=UCMlW2qG20hcFYo06rcit4CQ&max-results=48&orderby=published';

// read feed into SimpleXML object

$sxml = simplexml_load_file ( $feedURL );

$i = 0;

// iterate over entries in feed

foreach ( $sxml->entry as $entry ) {
	
	$i ++;
	
	// get nodes in media: namespace for media information
	
	$media = $entry->children ( 'http://search.yahoo.com/mrss/' );
	
	// get video player URL
	
	$attrs = $media->group->player->attributes ();
	
	$watch = $attrs ['url'];
	
	$vars;
	
	parse_str ( parse_url ( $watch, PHP_URL_QUERY ), $vars );
	
	$id = $vars ['v'];
	
	$check = $i % 3;
	
	if ($check == 1) {
		
		?><div class="video-col tri-col-1 empty">	<img src="https://img.youtube.com/vi/<?php echo $id ?>/mqdefault.jpg"		data-id="<?php echo $id ?>" class="video" />	<div class="video-play"></div></div><?php
	} else if ($check == 2) {
		
		?><div class="video-col tri-col-2 empty">	<img src="https://img.youtube.com/vi/<?php echo $id ?>/mqdefault.jpg"		data-id="<?php echo $id ?>" class="video" />	<div class="video-play"></div></div><?php
	} else {
		
		?><div class="video-col tri-col-3 empty">	<img src="https://img.youtube.com/vi/<?php echo $id ?>/mqdefault.jpg"		data-id="<?php echo $id ?>" class="video" />	<div class="video-play"></div></div><?php
	}
}

?>
