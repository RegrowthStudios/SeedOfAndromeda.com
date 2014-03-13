<?php

    define('IN_MYBB', 1);
	
	require_once 'Forums/global.php';
	require_once 'class.MyBBIntegrator.php';
	
	$MyBBI = new MyBBIntegrator($mybb, $db, $cache, $plugins, $lang, $config); 
	
	$posted_username = $_POST['username'];
    $posted_password = $_POST['password'];
	
	$prev = (string) $_GET['prev'];
	
	if($MyBBI->login($posted_username, $posted_password))
	{
	
		if($prev != null)
		{
			header("Location: /".$prev);
		}
		else
		{
			header("Location: /");
		}
		
	}
	else 
	{
		
		if($prev != null)
		{
			header("Location: /".$prev."?loginerror=invalid"); 
		}
		else
		{
			header("Location: /?loginerror=invalid");
		}
		
	}
	
?>