<?php
    $mashape_key = "abMdiba0hHmsha90MmDv77mL1jYkp1dYqwmjsnqh5foXJ9FMoU";
    set_time_limit(35);
    $turl = "https://jere".$_GET['api'].".p.mashape.com/?prot=".$_GET['prot'];
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
    );
?>