<?php
    @session_start();
    @session_destroy();        
    $prev = (string) $_GET['prev'];
    if($prev != null) {
        header("Location: /".$prev);     
    } else {
        header("Location: /");
    }
?>