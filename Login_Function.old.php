<?php 

    session_start();
	
    require "password_compat/lib/password.php";
	
    include $_SERVER[DOCUMENT_ROOT].'/db_connect.php';
    
    
    $username = mysql_real_escape_string($_POST['username']);
    $password = "";
    $newpassword = "";
    $needsupdating = false;
    $username_check = "none";
	
	$update_check = (int) mysql_fetch_array(mysql_query("SELECT updated_pass FROM soa_users WHERE username = '$username'"))['updated_pass'];
	
    if($update_check == 1)
    {
	
		$hash = mysql_fetch_array(mysql_query("SELECT password FROM soa_users WHERE username = '$username'"))['password'];
		$password = $_POST['password'];
		if (password_verify($password, $hash)) 
		{
			$username_check = $username;
		}
		
    } 
	else 
	{
      
		$password = md5($_POST['password']."@#$%^&*&$");
		$newpassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
		$needsupdating = true;
		$result = mysql_query("SELECT username FROM soa_users WHERE username = '$username' AND password = '$password'");
		while($row = mysql_fetch_array($result))
		{
			$username_check = $row['username'];
		}
		
    }
    
    if($username_check == "none")
    {
        $prev = (string) $_GET['prev'];
        if($prev != null) {
            header("Location: /".$prev."?loginerror=invalid"); 
        } else {
            header("Location: /?loginerror=invalid");
        }
		mysql_close($con);
        exit;
    }
    else 
    { 
		
        if($needsupdating)
        {
		
            $debug = mysql_query("
				UPDATE soa_users 
				SET password='$newpassword', updated_pass='1' 
				WHERE username='$username';
			");
			
        }
		
        $_SESSION['soa_user2'] = $username_check;
        $prev = (string) $_GET['prev'];
        if($prev != null) {
            header("Location: /".$prev);  
        } else {
            header("Location: /");
        }
		mysql_close($con);
        exit;
    }
    session_write_close();
	
?>