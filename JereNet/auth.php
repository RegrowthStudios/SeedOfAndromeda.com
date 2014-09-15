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
        //This needs to make the following call 
        
        //https://seedofandromeda.com/JereNet/api/
        //Call 1
        // tosess=
        //
        
        
        
        //Post-Data:
        //  JerX_Sess= from call 1
        //  prot=prtauth
        //  prt= //TODO GET NICE COMBO!
        //  key= //SAME LOL
        //  met=ownu
        //  username=<username>
        //  
        
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