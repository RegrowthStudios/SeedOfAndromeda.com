<?php

    define('IN_MYBB', 1);
	
	require_once 'Forums/global.php';
	require_once 'class.MyBBIntegrator.php';
	
	$MyBBI = new MyBBIntegrator($mybb, $db, $cache, $plugins, $lang, $config); 
	
	if($_GET['logoutkey'] == null)
	{
		header("Location: /SoA%20Site/Logout.php?logoutkey=".$MyBBI->mybb->user['logoutkey']);
	}
	
	if($MyBBI->logout())
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
			header("Location: /".$prev."?logouterror=failed");
		}
		else
		{
			header("Location: /?logouterror=failed");
		}
		
	}
	
?>