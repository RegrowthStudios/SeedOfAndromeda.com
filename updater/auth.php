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
				die("Invalid parameters");
			}else{
				$login = $sdk->validateLogin($_REQUEST['username'], $_REQUEST['password'], true, true);
				if(!is_int($login)){
				die("FAIL\nLogin failed");
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
echo "OK\n".$user['user_id']."\n".$user['username']."\n".$user['email']."\n".$user['custom_title']."\n";
}