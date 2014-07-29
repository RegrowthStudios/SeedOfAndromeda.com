<?php
    $i = 0;
    $images = array();
    foreach ( glob ( "assets/images/screenshots/*.jpg" ) as $image ) {
	    if (substr_count ( $image, "_thumb_" ) == 0) {
            $images[$i] = $image;
		    $i++;
	    }
    }
?>
                                        
<div id="screenshot-viewer" class="row clearfix">
    <div class="header"><h1>Screenshots</h1></div>
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
                for ( $j = 0; $j < $i; $j++ ) {
                    $img = $images[$j];
            ?>
            <div class="media-wrapper card-wrapper" style="display: none;">
                <a href="<?php echo $img ?>" id="screenshotlink" data-lightbox="screenshot" title="screenshot">
                    <div class="card-background" style="background-image: url('<?php echo substr ( $img, 0, strlen ( $img ) - 4 ) ?>_thumb_781x398.jpg');"></div>
                </a>
            </div>
            <?php
                }
            ?>
        </div>
    </div>
</div>

<div id="screenshots" class="row clearfix">
    <div class="divider"></div>
                                                
    <?php
        for( $k = 0; $k < $i; $k++ ) {
            $img = $images[$k];
    ?>
    <div class="col quad-col-1">
        <img class="img small-wide screenshot" src="<?php echo substr ( $img, 0, strlen ( $img ) - 4 ) ?>_thumb_213x128.jpg" />
    </div>
    <?php
        }
    ?>
</div>