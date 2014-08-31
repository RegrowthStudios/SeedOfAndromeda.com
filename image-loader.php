<?php
	require_once ("db_connect.php");
    
    $IMAGES_PER_PAGE = 24;
    
    if (! isset ( $connection )) {
        echo "";
    } else {
        if ( isset ( $_REQUEST['pid'] )) {
            $images = array();
            $query = $connection->prepare ( "SELECT * FROM screenshots WHERE published = ? ORDER BY id DESC" ); //LIMIT " . ( $_REQUEST['pid'] * $IMAGES_PER_PAGE )
            $query->execute( array (
                1
            ) );
            $fetched = $query->fetchAll();
            foreach ( $fetched as $img ) {
                if ( file_exists ( "." . $img["img_url"] ) ) {
                    $images[] = array (
                        "title" => $img["title"],
                        "description" => $img["description"],
                        "url" => $img["img_url"],
                        "category" => $img["category"],
                    );
                }
            }
            $i = $IMAGES_PER_PAGE * ( $_REQUEST['pid'] - 1 );
            $end = $i + $IMAGES_PER_PAGE;
            $imgs = array();
            for ( $i; $i < $end; $i++ ) {
                if ( $i >= sizeof ( $images )) {
                    break;
                }
                array_push ( $imgs, $images[$i] );
            }
            echo json_encode ($imgs);
        }
    }
?>