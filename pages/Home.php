
<div class="double-col">

    <a href="/Assets/images/Screenshots/Mountains.jpg"
		data-lightbox="images" title="Mountains" class="clear right image"><img
		src="/Assets/images/Screenshots/Mountains_thumb_125x100.jpg"
		class="clear right image" /></a>

	<h3>Try The Game</h3>

	<br /> The pre-alpha version of the game can be downloaded <a
		href="/downloads">here</a>!

</div>

<br />

<div class="tri-col-3">

	<h3>About the Game</h3>

	Seed of Andromeda is a voxel based sandbox RPG. Set in the near future,
	the player crash lands on a planet with a harsh environment. In the
	desire to have a way to return to their mission, the player may be able
	to build up technologically and regain space flight, with the help of
	other survivors! The game focusses on modability and customisation,
	many tools will come packaged with the game, including world, tree,
	biome and block editors! <br /> <br /> <a href="/thegame"
		style="float: right;">Read more here!</a>

</div>

<div class="tri-double-col">

	<h3>Featured Video</h3>
	<div id="featured_video"></div>
	<script type="text/javascript">
    function showVideo(response) {
        if(response.data && response.data.items) {
            var items = response.data.items;
            if(items.length>0) {
                var item = items[0];
                var videoid = "https://www.youtube.com/embed/"+item.id+"?wmode=transparent";
                console.log("Latest ID: '"+videoid+"'");
                var video = "<iframe width='610' height='314' src='"+videoid+"' frameborder='0' allowfullscreen></iframe>"; 
                $('#featured_video').html(video);
            }
        }
    }
    </script>
	<script type="text/javascript"
		src="https://gdata.youtube.com/feeds/api/users/UCMlW2qG20hcFYo06rcit4CQ/uploads?max-results=1&orderby=published&v=2&alt=jsonc&callback=showVideo"></script>


</div>

<div id="latest-dev-news" class="double-col">
    
	<h3>Latest Dev News</h3>
    <div>
    <?php
	if (isset ( $connection )) {
		$query = $connection->prepare ( "SELECT * FROM blog_posts WHERE published = ? AND devnews = ? ORDER BY id DESC LIMIT 5" ); //LIMIT 1
		$query->execute ( array (
				1,
				1
		) );
        ?>
        <div id="dev-news-frame">
            <div class="dev-news-control dev-news-control-left">
		        <img src="/Assets/images/arrowLeft.png" />
            </div>
            <div class="dev-news-control dev-news-control-right">
                <img src="/Assets/images/arrowRight.png" />
            </div>
            <div id="dev-news-js-warning">
                <h3 style="color: red; text-shadow: 0px 0px 10px rgba(255, 0, 0, 1);">
                    Please enable JavaScript to see Dev News content!
                </h3>
             </div>
        <?php
		while ( $row = $query->fetch () ) {
			$postlink = gen_postlink ( $row );
            $background = $row ["dev_news_background"];
            $src_start = strpos('\'' . $background . '\'', 'src="') + 5;
            $src_end = strpos('\'' . $background . '\'', '"', $src_start);
            $backgroundurl = substr('\'' . $background . '\'', $src_start, $src_end - $src_start);
            $disp = "";
			echo '
                <div class="dev-news-wrapper" style="display:none;">
                    <a href="http://www.seedofandromeda.com/blogs/' . $postlink . '">
                        <div id="dev-news-text">
                            <div id="dev-news-header"> <h2>' . $row ["title"] . '</h2> </div>
                            <div id="dev-news-summary"> <strong>' . $row ["dev_news_body"] . '</strong> </div>
                        </div>
                        <div id="dev-news-background" style="background-image:url(\'' . $backgroundurl . '\');"></div>
                    </a>
                </div>
	        ';
        }
        ?>
        </div>
        <?php
        //<div id="blog-post-header">
        //    <p><a href="/blogs/' . $postlink . '">' . $row ["title"] . '</a></p>
        //</div>
        //<div id="blog-post-body">
        //    <p style="position: absolute;">' . substr ( preg_replace('/<iframe.*?\/iframe>/i','<p>Click read more to view this video!</p>', $row ["post_body"] ), 0, 2000 ) . ' ...</p>
        //</div>
        //<div id="blog-post-footer">
        //    <p>
        //        <a
        //            href="/blogs/' . $postlink . '">Read
        //            More...</a> ';
        //        if (! $row ["disablecomments"]) {
        //            echo '<small> - (<a
        //            href="http://www.seedofandromeda.com/blogs/' . $postlink . '#disqus_thread" data-disqus-identifier="blogs-' . $row ["id"] . '">Comments</a>)
        //        </small>';
        //        }
        
        //        echo '
        //    </p>
        //</div>
			}	
		?>

<!--        <script type="text/javascript">            var disqus_shortname = 'seedofandromeda';
            (function () {
                var s = document.createElement('script'); s.async = true;                s.type = 'text/javascript';                s.src = 'http://' + disqus_shortname + '.disqus.com/count.js';                (document.getElementsByTagName('HEAD')[0] || document.getElementsByTagName('BODY')[0]).appendChild(s);
            }());        </script>-->
    </div>	
</div>
