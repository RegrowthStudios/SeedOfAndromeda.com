<?php
if (isset ( $connection )) {
	if (isset ( $blogpost )) {
		$author = $sdk->getUser ( $blogpost ["author"] );
        if($blogpost["prioritisescreenshots"]) {
		?>
<div id="blog-image-slider" class="row clearfix" style="display:none;">
    <div class="divider"></div>
    <div class="col double-col-2">
        <div class="media-slider-frame card-slider-frame">
            <div class="media-slider-control media-slider-control-left card-slider-control">
                <img class="media-slider-control-img" src="/assets/images/arrowLeft.png" />
            </div>
            <div class="media-slider-control media-slider-control-right card-slider-control">
                <img class="media-slider-control-img" src="/assets/images/arrowRight.png" />
            </div>
            <div class="media-slider-js-warning card-slider-js-warning">
                <h3 class="warning">Please enable JavaScript to see Dev News content!</h3>
            </div>
        </div>
    </div>
</div>
<?php
        }
?>
<div id="single-blog" class="row clearfix">
    <div class="header"><h1><?php echo XenForo_Helper_String::wholeWordTrim( strip_tags ( $blogpost["title"] ), 60) ?></h1></div>
    <div class="double-col-2">
        <div class="text">
	        <div id="blog-post" class="clearfix">
		        <div><?php echo $blogpost["post_body"];?></div>
	            <span id="blog-post-footer">
                    <?php
                        if(! $blogpost["removesignoff"]) {
                            if($blogpost["anonymous"]) {
                                echo "Seed of Andromeda Team";
                            } else {
                    ?>
			            <a href="<?php echo XenForo_Link::buildPublicLink('canonical:members', $author); ?>"><?php echo $author["username"]." - ".$author["custom_title"];?></a>
                    <?php 
                            }
                        }
                    ?>
	            </span>
	        </div>
        </div>
    </div>
</div>
<?php
        if(!$blogpost["prioritisescreenshots"] && !$blogpost["hidescreenshots"]) {
?>
<div id="blog-image-slider" class="row clearfix" style="display:none;">
    <div class="divider"></div>
    <div class="col double-col-2">
        <div class="media-slider-frame card-slider-frame">
            <div class="media-slider-control media-slider-control-left card-slider-control">
                <img class="media-slider-control-img" src="/assets/images/arrowLeft.png" />
            </div>
            <div class="media-slider-control media-slider-control-right card-slider-control">
                <img class="media-slider-control-img" src="/assets/images/arrowRight.png" />
            </div>
            <div class="media-slider-js-warning card-slider-js-warning">
                <h3 class="warning">Please enable JavaScript to see Dev News content!</h3>
            </div>
        </div>
    </div>
</div>
<?php
        }
?>
<div id="blog-comments" class="row clearfix">     
    <div class="divider"></div>  
    <div class="col double-col-2"> 
        <div class="text">
<?php
		if (! $blogpost ["disablecomments"]) {
			echo_disqus ( $blogpost ["title"], $pageurl, "blogs-" . $blogpost ["id"] );
		}
?>
        </div>
    </div>
</div>                                        
<?php
	} else {
		$query = $connection->prepare ( "SELECT * FROM blog_posts WHERE published = ? ORDER BY id DESC" );
		$query->execute ( array (
				1 
		) );
		
		while ( $row = $query->fetch () ) {
			$postlink = gen_postlink ( $row );
			echo '
            <div class="row clearfix">
                <div class="header"><h1><a href="/blogs/' . $postlink . '">' . XenForo_Helper_String::wholeWordTrim( strip_tags ( $row ["title"] ), 60) . '</a></h1></div>
                <div class="double-col-2">
                    <div class="text">
	                    <div id="blog-post" class="clearfix">
		                    <div>' . XenForo_Helper_String::wholeWordTrim( strip_tags ( $row ["post_brief"] ), 1400) . '</div>
	                        <span id="blog-post-footer">
                                <a
				                    href="/blogs/' . $postlink . '">Read
				                    More...</a> ';
			                    if (! $row ["disablecomments"]) {
				                    echo '<small> - (<a
				                    href="/blogs/' . $postlink . '#disqus_thread" data-disqus-identifier="blogs-' . $row ["id"] . '">Comments</a>)
			                    </small>';
			                    }
                                echo '
	                        </span>
	                    </div>
                    </div>
                </div>
            </div>
            ';
            }
		?>

<script type="text/javascript">
    var disqus_shortname = 'seedofandromeda';
 
    (function () {
    var s = document.createElement('script'); s.async = true;
    s.type = 'text/javascript';
    s.src = 'https://' + disqus_shortname + '.disqus.com/count.js';
    (document.getElementsByTagName('HEAD')[0] || document.getElementsByTagName('BODY')[0]).appendChild(s);
    }());
    </script>

<?php
	}
} else {
	echo '<div class="double-col empty"><h3 style="color: red; text-shadow: 0px 0px 10px rgba(255, 0, 0, 1);">No database connection!</h3></div>';
}

?>