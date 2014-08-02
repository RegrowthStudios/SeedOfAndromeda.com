<?php
    if ( isset ( $_POST["draft"] ) && isset ( $_POST["id"] )) {
        require_once("db_connect.php");
        $query = $connection->prepare ( "UPDATE blog_posts SET draft = ?, draftIsLatest = ? WHERE id = ?" );
        $query->execute ( array (
            $_POST["draft"],
            1,
            strip_tags ( $_POST["id"] )
        ) );
        echo 1;
    } else {
        echo 0;
    }
?>