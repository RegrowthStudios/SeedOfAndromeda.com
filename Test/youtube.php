<?php
    // set feed URL
    $feedURL = 'https://gdata.youtube.com/feeds/api/videos?author=UCMlW2qG20hcFYo06rcit4CQ&max-results=48&order=date';
   
    // read feed into SimpleXML object
    $sxml = simplexml_load_file($feedURL);
    
    $i = 0;
    // iterate over entries in feed
    foreach ($sxml->entry as $entry) {
        $i++;
        // get nodes in media: namespace for media information
        $media = $entry->children('http://search.yahoo.com/mrss/');
     
        // get video player URL
        $attrs = $media->group->player->attributes();
        $watch = $attrs['url'];
        $vars;
        parse_str( parse_url( $watch, PHP_URL_QUERY ), $vars );
        $id = $vars['v'];
        
        $check = $i % 3;
        
        if($check == 1) {
?>
        <div class="video-col tri-col-1 empty">
            <img src="http://img.youtube.com/vi/<?php echo $id ?>/mqdefault.jpg" data-id="<?php echo $id ?>" class="video" />
        </div>
<?php
        } else if($check == 2) {
?>
        <div class="video-col tri-col-2 empty">
            <img src="http://img.youtube.com/vi/<?php echo $id ?>/mqdefault.jpg" data-id="<?php echo $id ?>" class="video" />
        </div>
<?php
        } else {
?>
        <div class="video-col tri-col-3 empty">
            <img src="http://img.youtube.com/vi/<?php echo $id ?>/mqdefault.jpg" data-id="<?php echo $id ?>" class="video" />
        </div>
<?php
        }
?>
        </div>
<?php        
    }
?>