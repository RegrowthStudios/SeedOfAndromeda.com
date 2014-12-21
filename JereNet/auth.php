<?php
require_once ('../community/XenForoSDK.php');
$sdk = new XenForoSDK ();

if ( !isset ( $_GET["u"] ) || !isset ( $_GET["p"] ) ) {
    echo 0;
} else {
    if ( $sdk->validateLogin($_GET["u"], $_GET["p"]) === true ) {
        $r = echo_userdata();
        if ($r == -1) {
            // Fail /w Email
            echo 1;
        } else {
            echo $r;
        }
    } else {
        echo 2;
    }
}

function echo_userdata(){
    $sessKey = file_get_contents( 'https://seedofandromeda.com/JereNet/api/?api=net&prot=tosess' );
    
    $post = array (
        "JerX_Sess" => $sessKey,
        "prt" => "SOAC",
        "key" => "f7fc2a9f7d22138",
        "met" => "ownu",
        "username" => $_GET["u"]
    );
    
    $err = file_get_contents(
        'https://seedofandromeda.com/JereNet/api/?api=net&prot=prtauth',
        false,
        stream_context_create(
            array( 'http' => array(
                'method' => 'post',
                'content' => $post,
                ),
            )
        )
    );
    
    // E:0 = Username available & logged in
    // E:1 = API Key Incorrect
    // E:2 = API Key Incorrect Permissions
    // E:3 = Username already exists
    
    if ($err == "E:0") {
        return $sessKey;
    } else if ($err == "E:3") {
        $post = array (
            "JerX_Sess" => $sessKey,
            "prt" => "SOAC",
            "key" => "f7fc2a9f7d22138",
            "met" => "ownu",
            "username" => ( $_GET["u"] . "_SoA" )
        );
        $err = file_get_contents(
            'https://seedofandromeda.com/JereNet/api/?api=net&prot=prtauth',
            false,
            stream_context_create(
                array( 'http' => array(
                    'method' => 'post',
                    'content' => $post,
                    ),
                )
            )
        );
        if ($err == "E:0") {
            return $sessKey;
        } else {
            return -1;
        }
    } else {
        return -1;        
    }
}