<?php
    require_once ("../db_connect.php");
    
    $BLOGS_PER_PAGE = 5;
    
    if (! isset ( $connection )) {
        echo "";
    } else {
        if ( isset ( $_REQUEST['check'] )) {
            echo true;
        } else if ( isset ( $_REQUEST['pid'] )) {
            $category = "ALL";
            if ( isset ( $_REQUEST['category'] )) {
                $category = $_REQUEST['category'];
            }
            $blogs = array();
            $query;
            if ($category == "ALL") {
                $query = $connection->prepare ( "SELECT * FROM blog_posts WHERE published = ? ORDER BY updatetime DESC" );
                $query->execute( array (
                    1
                ) );
            } else {
                $query = $connection->prepare ( "SELECT * FROM blog_posts WHERE published = ? AND category = ? ORDER BY updatetime DESC" );
                $query->execute( array (
                    1,
                    $category
                ) );
            }
            $fetched = $query->fetchAll();
            foreach ( $fetched as $blog ) {
                $blogs[] = $blog;
            }
            $i = $BLOGS_PER_PAGE * ( $_REQUEST['pid'] - 1 );
            $end = $i + $BLOGS_PER_PAGE;
            $blgs = array();
            for ( $i; $i < $end; $i++ ) {
                if ( $i >= sizeof ( $blogs )) {
                    break;
                }
                array_push ( $blgs, $blogs[$i] );
            }
            echo json_encode ( $blgs );
        } else if ( isset ( $_REQUEST['getTotalPages'] )) {
            $query = $connection->prepare( "SELECT * FROM blog_posts WHERE published = ?" );
            $query->execute( array ( 
                1
            ) );
            echo json_encode ( ceil ( $query->rowCount() / $BLOGS_PER_PAGE ) );
        }
    }
?>