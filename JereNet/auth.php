<?php
require_once ('../community/XenForoSDK.php');
$sdk = new XenForoSDK ();

$loggedIn = $sdk->isLoggedIn ();
$userinfo = $sdk->getUser ();
$visitor = $sdk->getVisitor ();

if(isset($_REQUEST['action'])){
	switch($_REQUEST['action']){
		case "checklogin":
			if(!$loggedIn){
				die("FAIL\nNot logged in");
			}else{
				echo_userdata($userinfo);
			}
		break;
		case "login":
			if(!isset($_REQUEST['username']) || !isset($_REQUEST['password'])){
				die("SYNTAX");
			}else{
				$login = $sdk->validateLogin($_REQUEST['username'], $_REQUEST['password'], true, true);
				if(!is_int($login)){
				die("FAIL");
				}else{
				$user = $sdk->getUser ($login);
				echo_userdata($user);
				}
			}
		break;
	}
}else{
?>
debug form<br>
<form action="?action=login" method="post">
<input type="text" name="username"/>
<input type="password" name="password"/>
<input type="submit"/>
</form>
<?php
}

function echo_userdata($user){
echo file_get_contents(
        //This needs to make the following call s
        
        //https://seedofandromeda.com/JereNet/api/?api=net&prot=tosess
        //Call 1
        
        
        
        //https://seedofandromeda.com/JereNet/api/?api=net&prot=prtauth
        // post data:
        //JerX_Sess= [result from call 1]
        //prt=SOAC
        //key=f7fc2a9f7d22138
        //met=ownu
        //username=<username>

        'https://seedofandromeda.com/JereNet/api/',
        false,
        stream_context_create(
            array( 'http' => array(
                'method' => 'post',
                'content' => http_build_query($_POST),
                ),
            )
        )
    );
}