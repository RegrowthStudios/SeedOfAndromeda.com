<?php

    //This simplifies Post requests to mashape by removing the need for directly sending a key in the header.
    //This script does not require a lot of worka and doesnt really do anything, and is therefore released as public domain


    //Settings
    $mashape_key = "<your key here>";

    //Code
    set_time_limit(35); //Just in case we need this setting; adding 5 seconds as ping tolerance
    $turl = "https://jere".$_GET['api'].".p.mashape.com/?prot=".$_GET['prot']; //Forming the target url

    echo file_get_contents(
        $turl,
        false,
        stream_context_create(
            array( 'http' => array(
                'header' =>  'X-Mashape-Authorization: '.$mashape_key ,
                'method' => 'post',
                'content' => http_build_query($_POST),
                ),
            )
        )
    );//building and executing query and returning the string.
?>