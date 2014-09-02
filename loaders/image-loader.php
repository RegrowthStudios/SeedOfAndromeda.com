<?php
	require_once ("../db_connect.php");
    
    $IMAGES_PER_PAGE = 24;
    
    if (! isset ( $connection )) {
        echo "";
    } else {
        if ( isset ( $_REQUEST['pid'] )) {
            $category = "GAMEPLAY";
            if ( isset ( $_REQUEST['category'] )) {
                $category = $_REQUEST['category'];
            }
            $images = array();
            $query = $connection->prepare ( "SELECT * FROM images WHERE published = ? AND category = ? ORDER BY id DESC" ); //LIMIT " . ( $_REQUEST['pid'] * $IMAGES_PER_PAGE )
            $query->execute( array (
                1,
                $category
            ) );
            $fetched = $query->fetchAll();
            foreach ( $fetched as $img ) {
                if ( file_exists ( ".." . $img["img_url"] ) ) {
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
            echo json_encode ( $imgs );
        } else if ( isset ( $_REQUEST['getTotalPages'] )) {
            $query = $connection->prepare( "SELECT * FROM images WHERE published = ?" );
            $query->execute( array ( 
                1
            ) );
            echo json_encode ( ceil ( $query->rowCount() / $IMAGES_PER_PAGE ) );
        }
    }
?>