<?php
	require_once ("db_connect.php");
    
    $IMAGES_PER_PAGE = 24;
    
    if (! isset ( $connection )) {
        echo "";
    } else {
        if ( isset ( $_REQUEST['pid'] )) {
            $i = 0;
            $images = array();
            $di = new RecursiveDirectoryIterator("assets/images/screenshots/",RecursiveDirectoryIterator::SKIP_DOTS);
            $it = new RecursiveIteratorIterator($di);
            foreach($it as $image)
            {
                if( pathinfo($image,PATHINFO_EXTENSION) == "jpg" || pathinfo($image,PATHINFO_EXTENSION) == "png" || pathinfo($image,PATHINFO_EXTENSION) == "gif" ) {
                    $time = filemtime($image);
                    if (array_key_exists ($time, $images)) {
                        while (array_key_exists ($time, $images)) {
                            $time++;
                        }
                    }
                    $images[$time] = $image;
                }
            }
            ksort ( $images );
            $sortedImages = array_values ( $images );
            
            $j = $IMAGES_PER_PAGE * ( $_REQUEST['pid'] - 1 );
            $end = $IMAGES_PER_PAGE + $j;
            $imgs = array();
            for ( $j; $j < $end; $j++ ) {
                if ( $j >= sizeof ( $sortedImages )) {
                    break;
                }
                array_push ( $imgs, $sortedImages[$j] );
            }
            echo json_encode ($imgs);
        }
    }
?>