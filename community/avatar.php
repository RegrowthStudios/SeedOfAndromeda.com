<?php

//$startTime = microtime(true);
$fileDir = dirname(__FILE__);

require($fileDir . '/library/XenForo/Autoloader.php');
XenForo_Autoloader::getInstance()->setupAutoloader($fileDir . '/library');

XenForo_Application::initialize($fileDir . '/library', $fileDir);
XenForo_Application::set('page_start_time', $startTime);

####################



// GET DB
$db = XenForo_Application::get('db');

// ASSIGN GET PARAMETERS
$userid = (!empty($_GET['userid']) ? intval($_GET['userid']) : false);
$username = (!empty($_GET['username']) ? $_GET['username'] : false);
$size = (!empty($_GET['size']) ? $_GET['size'] : false);

// ENFORCE VALUES FOR SIZE, AND SET DEFAULT 'm'
$size = (in_array($size, array('s','m','l')) ? $size : 'm');



// GET USER BY USERID
if ($userid)
{
	$user = $db->fetchRow("
		SELECT *
		FROM xf_user
		WHERE user_id = ?
	", $userid);
}
// ELSE GET USER BY USERNAME
else if ($username)
{
	$user = $db->fetchRow("
		SELECT *
		FROM xf_user
		WHERE username = ?
	", $username);
}



// USER NOT FOUND, OUTPUT DEFAULT NO GENDER
if (empty($user['user_id']))
{
	$file = "./styles/default/xenforo/avatars/avatar_{$size}.png";
	$type = 'image/png';
	header('Content-Type:'.$type);
	header('Content-Length: ' . filesize($file));
	readfile($file);

	exit(0);
}



// GRAVATARS
if ($user['gravatar'])
{
	$avaurl = XenForo_Template_Helper_Core::getAvatarUrl($user, $size);

	$file = $avaurl;
	$filecontents = file_get_contents($file);
	$type = 'image/jpeg';
	header('Content-Type:'.$type);
	header('Content-Length: ' . strlen($filecontents));
	echo $filecontents;

	exit(0);
}



// CUSTOM AVATARS
if (!empty($user['avatar_date']))
{
	$avaurl = XenForo_Template_Helper_Core::getAvatarUrl($user, $size, 'custom');

	$file = './' . substr($avaurl, 0, strpos($avaurl, '?'));
	$type = 'image/jpeg';
	header('Content-Type:'.$type);
	header('Content-Length: ' . filesize($file));
	readfile($file);

	exit(0);
}



// DEFAULT AVATARS
if (true)
{
	$avaurl = XenForo_Template_Helper_Core::getAvatarUrl($user, $size, 'default');

	$file = './' . $avaurl;
	$type = 'image/png';
	header('Content-Type:'.$type);
	header('Content-Length: ' . filesize($file));
	readfile($file);

	exit(0);
}