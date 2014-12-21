<?php
    $mashape_key = "FwI8MLOFEkmshdRiqzPcvlmlniGOp1dztj9jsnW7ook1MVSfVT";
    set_time_limit(35);
    $turl = "https://jere".$_GET['api'].".p.mashape.com/?prot=".$_GET['prot'];
    if ( count($_POST) > 0 ) {
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
    } else {
        echo file_get_contents(
            $turl,
            false,
            stream_context_create(
                array( 'http' => array(
                    'header' =>  'X-Mashape-Authorization: '.$mashape_key ,
                    'method' => 'get'
                    ),
                )
            )
        );
    }
?>