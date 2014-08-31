<?php
    $featuredQuery = $connection->prepare ( "SELECT * FROM videos WHERE category = ? AND published = ?" );
    $featuredQuery->execute ( array (
        "FEATURED",
        1
    ) );
    $featuredFetched = array();
	while ( $row = $featuredQuery->fetch () ) {
        $featuredFetched[] = $row;
    }
    $streamQuery = $connection->prepare ( "SELECT * FROM videos WHERE category = ? AND published = ?" );
    $streamQuery->execute ( array (
        "STREAM",
        1
    ) );
    $streamFetched = array();
	while ( $row = $streamQuery->fetch () ) {
        $streamFetched[] = $row;
    }
?>
<div id="video-viewer" class="row clearfix">
    <div class="header"><h1>Videos</h1></div>
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
            <?php
                $fetched = array_merge ( $featuredFetched, $streamFetched );
                foreach ( $fetched as $vid ) {
                    echo '<div class="media-wrapper card-wrapper" style="display: none;"><div class="video-title"><h2 class="indent-large">' . $vid["title"] . '</h2></div><div class="card-background" style="background-image: url(\'' . $vid["thumb_url"] . '\');" data-id="' . $vid["vid_id"] . '"></div><div class="video-play"></div></div>';
                }
            ?>
        </div>
    </div>
</div>

<?php 
    if ( count ( $featuredFetched ) > 0 ) {
?>
<div id="featured-videos" class="row clearfix">
    <div class="header"><h1>Featured Videos</h1></div>
    <?php
        foreach ( $featuredFetched as $vid ) {
            echo '<div class="col tri-col-1"><img src="' . $vid["thumb_url"] . '" data-id="' . $vid["vid_id"] . '" title="' . $vid["title"] . '" class="img medium-wide video" /></div>';
        }
    ?>
</div>
<?php
    }
?>

<?php 
    if ( count ( $streamFetched ) > 0 ) {
?>
<div id="stream-videos" class="row clearfix">
    <div class="header"><h1>Stream Videos</h1></div>
    <?php
        foreach ( $streamFetched as $vid ) {
            echo '<div class="col tri-col-1"><img src="' . $vid["thumb_url"] . '" data-id="' . $vid["vid_id"] . '" title="' . $vid["title"] . '" class="img medium-wide video" /></div>';
        }
    ?>
</div>
<?php
    }
?>

<div id="all-videos" class="row clearfix">
    <div class="header"><h1>All Videos</h1></div>        
</div>