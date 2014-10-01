<div id="try-the-game" class="row clearfix">
    <div class="header"><h1>Try The Game!</h1></div>
    <div class="col double-col-2">
        <h4>The pre-alpha version of the game can be downloaded <a href="/downloads">here</a>!</h4>
    </div>
</div>
                                            
<div id="about-the-game" class="row clearfix">
    <div class="header"><h1>About The Game</h1></div>
    <div class="col double-col-2">
        <div class="text">
                Seed of Andromeda is a voxel based sandbox RPG. Set in the near future,
                the player crash lands on a planet with a harsh environment. In the
                desire to have a way to return to their mission, the player may be able
                to build up technologically and regain space flight, with the help of
                other survivors! The game focusses on modability and customisation,
                many tools will come packaged with the game, including world, tree,
                biome and block editors!
                <a href="/the-game" class="right read-more">Read more here!</a>
            <div id="featured-video"></div>
            <script type="text/javascript">
                function showVideo(response) {
                    if (response.items) {
                        var items = response.items;
                        if (items.length > 0) {
                            var item = items[0];
                            var videoid = "https://www.youtube.com/embed/" + item.snippet.resourceId.videoId + "?wmode=transparent";
                            var video = "<iframe width='610' height='314' src='" + videoid + "' frameborder='0' allowfullscreen></iframe>";
                            $('#featured-video').html(video);
                        }
                    }
                }
            </script>
            <script type="text/javascript" src="https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&maxResults=1&playlistId=UUMlW2qG20hcFYo06rcit4CQ&key=AIzaSyDDvpdu4_LQ0T07p8siXC2-pCUQXmi6tLA&callback=showVideo"></script>
        </div>
    </div>
</div>

<div id="dev-news" class="row clearfix">
    <div class="header"><h1>Latest Dev News</h1></div>
    <div class="col double-col-2">
        <?php
        
        if (isset ( $connection )) {
            $query = $connection->prepare ( "SELECT * FROM blog_posts WHERE published = ? AND devnews = ? ORDER BY id DESC LIMIT 5" );
            $query->execute ( array (
                    1,
                    1
            ) );
        ?>
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
            <?php
            while ( $row = $query->fetch () ) {
                $postlink = gen_postlink ( $row );
                $disp = "";
                echo '
                <div class="media-wrapper card-wrapper" style="display: none;">
                    <a href="/blogs/' . $postlink . '">
                        <div class="card-text">
                            <div class="card-header indent-xx-large">
                                <h2>' . $row ["title"] . '</h2>
                            </div>
                            <div class="card-summary indent-xxx-large"><strong>
                                <p>' . $row ["dev_news_body"] . '</p>
                            </strong></div>
                        </div>
                        <div class="card-background" style="background-image: url(\'' . $row ["dev_news_background"] . '\');"></div>
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

<div class="row clearfix">
    <div class="header"><h1>Latest Dev Activity</h1></div>
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
    $devIDs = array( 1, 5, 9, 11, 16, 20, 21, 43, 57, 136, 475, 488 );
    //List of ignored forum IDs:
    $ignoreForums = array( 15, 16, 17, 20, 21, 23, 30 );
        
    foreach($devIDs as $devID){
        $results = XenForo_Search_SourceHandler_Abstract::getDefaultSourceHandler()->executeSearchByUserId(
            $devID, 0, 18
        ); //18 to ensure 9 usable results.
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
        if($i >= 9){
            break; //Limit to 9 posts
        }
        $thread = getThreadFromList($post["thread_id"],$threads);
        if(in_array($thread['node_id'], $ignoreForums)){
            continue;
        }
        $user = $sdk->getUser($post["user_id"]);
        //var_dump($user);
        $message = XenForo_Helper_String::bbCodeStrip( $post['message'], true ); //Strip bbcode
        $message = XenForo_Helper_String::wholeWordTrim($message, 100); //Strip the message to 100 chars
        $threadTitle = XenForo_Helper_String::wholeWordTrim($thread["title"], 35);
        echo '
            <div class="col tri-col-1">
                <div class="dev-activity-card text">
                    <div class="dev-activity-dev">
                        <img class="dev-activity-avatar img xx-small" src="/community/avatar.php?userid=' . $user["user_id"] . '&size=s" />
                        <div class="dev-activity-dev-info">
                            <div class="dev-activity-dev-name"><a href="'.XenForo_Link::buildPublicLink('canonical:members', $user).'">'. $user["username"] . '</a></div>
                            <div class="dev-activity-dev-title"><em>'.$user["custom_title"].'</em></div>
                            <div class="content-corner-top-right content-corner"></div>
                            <div class="content-corner-bottom-right content-corner"></div>
                        </div>
                    </div>
                    <div class="dev-activity-content clearfix">
                        <div class="dev-activity-action"><em>' . ($thread["first_post_id"] == $post["post_id"] ? "Started" : "Replied to") . ' <a href="' . XenForo_Link::buildPublicLink('canonical:threads', $thread) . ($thread["first_post_id"] != $post["post_id"] ? "#post-" . $post["post_id"] : "") . '">' . $threadTitle . '</a></em></div>
                        <div class="dev-activity-message-wrapper">
                            <div class="dev-activity-message">
                                <span class="indent-small">' . $message . '</span>
                                <div class="content-corner-top-right content-corner"></div>
                                <div class="content-corner-bottom-right content-corner"></div>
                                <div class="content-corner-top-left content-corner"></div>
                                <div class="content-corner-bottom-left content-corner"></div>
                            </div>
                        </div>
                        <em class="dev-activity-time">' . XenForo_Locale::dateTime($post["post_date"]) . '</em>
                    </div>
                </div>
            </div>
        ';
        //var_dump($post);
        $i++;
    }
    ?>
</div>