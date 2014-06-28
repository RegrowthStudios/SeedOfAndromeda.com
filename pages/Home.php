<div class="double-col">

    <a href="/Assets/images/Screenshots/Mountains.jpg"
        data-lightbox="images" title="Mountains" class="clear right image">
        <img
            src="/Assets/images/Screenshots/Mountains_thumb_125x100.jpg"
            class="clear right image" /></a>

    <h3>Try The Game</h3>

    <br />
    The pre-alpha version of the game can be downloaded <a
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
	biome and block editors!
    <br />
    <br />
    <a href="/thegame"
        style="float: right;">Read more here!</a>

</div>

<div class="tri-double-col">

    <h3>Featured Video</h3>
    <div id="featured_video"></div>
    <script type="text/javascript">
        function showVideo(response) {
            if (response.data && response.data.items) {
                var items = response.data.items;
                if (items.length > 0) {
                    var item = items[0];
                    var videoid = "https://www.youtube.com/embed/" + item.id + "?wmode=transparent";
                    console.log("Latest ID: '" + videoid + "'");
                    var video = "<iframe width='610' height='314' src='" + videoid + "' frameborder='0' allowfullscreen></iframe>";
                    $('#featured_video').html(video);
                }
            }
        }
    </script>
    <script type="text/javascript"
        src="https://gdata.youtube.com/feeds/api/users/UCMlW2qG20hcFYo06rcit4CQ/uploads?max-results=1&orderby=published&v=2&alt=jsonc&callback=showVideo"></script>


</div>

<div id="latest-dev-activity" class="double-col">
    <h3>Latest Dev Activity</h3>
    <div>
        <?php
        function comparePostTimes($a, $b)
        {
            if ($a["post_date"] == $b["post_date"]) {
                return 0;
            }
            return ($a["post_date"] < $b["post_date"]) ? 1 : -1;
        }
        
        function getThreadFromList($tid,$threads){
            foreach($threads as $thread){
                if($thread["thread_id"] == $tid){
                    return $thread;
                }
            }
            return array();
        }
        
        $pids = array();
        $tids = array();
        
        //Add developer user IDs here:
        $devIDs = array(1);
        //List of ignored forum IDs:
        $ignoreForums = array();
        
        
        foreach($devIDs as $devID){
            $results = XenForo_Search_SourceHandler_Abstract::getDefaultSourceHandler()->executeSearchByUserId(
                $devID, 0, 10
            );
            //$results = $this->getModelFromCache('XenForo_Model_Search')->getSearchResultsForDisplay($results);
            if ($results)
            {
                //var_dump($results);
                foreach($results as $result){
                    switch($result[0]){
                        case "thread":
                            if(!in_array($result[1],$tids)){
                                $tids[] = $result[1];
                            }
                            break;
                        case "post":
                            if(!in_array($result[1],$pids)){
                                $pids[] = $result[1];
                            }
                            break;
                    }
                }
            }
        }
        
        
        
        $posts = XenForo_Model::create('XenForo_Model_Post')->getPostsByIds($pids);
        usort($posts, "comparePostTimes");
        //var_dump($posts);
        
        foreach($posts as $post){
            if(!in_array($post["thread_id"],$tids)){
                $tids[] = $post["thread_id"];
            }
        }
        
        $threads = $sdk->getThreadsByIds($tids);
        //var_dump($threads);
        
        $i = 0;
        
        foreach($posts as $post){
            if($i >= 5){
                break; //Limit to 5 posts
            }
            $thread = getThreadFromList($post["thread_id"],$threads);
            if(in_array($thread['node_id'], $ignoreForums)){
                continue;
            }
            $user = $sdk->getUser($post["user_id"]);
            echo '<a href="'.XenForo_Link::buildPublicLink('canonical:members', $user).'">'. $user["username"] . ' ('.$user["custom_title"].')</a> '.($thread["first_post_id"] == $post["post_id"] ? "posted" : "replied to").' a thread <a href="'.XenForo_Link::buildPublicLink('canonical:threads', $thread).'">'.$thread["title"].'</a> - '.XenForo_Locale::dateTime($post["post_date"]);
            $message = XenForo_Helper_String::wholeWordTrim($post['message'], 150); //Strip the message to 150 chars
            $message = XenForo_Helper_String::bbCodeStrip($message); //Strip bbcode
            echo "<p>".$message."</p>";
            //var_dump($post);
            $i++;
        }
        ?>
    </div>
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
                <h3 style="color: red; text-shadow: 0px 0px 10px rgba(255, 0, 0, 1);">Please enable JavaScript to see Dev News content!
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
        }	
        ?>
    </div>
</div>
