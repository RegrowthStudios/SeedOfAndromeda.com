<?php
define ( 'IN_MYBB', 1 );

require_once 'Forums/global.php';

require_once 'class.MyBBIntegrator.php';

$MyBBI = new MyBBIntegrator ( $mybb, $db, $cache, $plugins, $lang, $config );

$pagetitle = "SoA - ";
$pagename = "";
if (! isset ( $_REQUEST ['page'] )) {
	$_REQUEST ['page'] = "index";
}
$cleanpageid = preg_replace ( "/[^A-Za-z0-9_]/", '', str_replace ( ".php", "", strtolower ( $_REQUEST ['page'] ) ) );
$pageurl = $cleanpageid;
switch ($cleanpageid) {
	case "index" :
		$pagetitle = "Seed of Andromeda";
		$pagename = "Home.php";
		$pageurl = "";
		break;
	case "thegame" :
		$pagetitle .= "The Game";
		$pagename = "The Game.php";
		break;
	case "theteam" :
		$pagetitle .= "The Team";
		$pagename = "The Team.php";
		break;
	case "screenshots" :
		$pagetitle .= "Image Media";
		$pagename = "Screenshots.php";
		break;
	case "videos" :
		$pagetitle .= "Video Media";
		$pagename = "Videos.php";
		break;
	// case "mods" :
	// $pagetitle = "Mods";
	// $pagename = "";
	// break;
	case "irc" :
		$pagetitle .= "IRC";
		$pagename = "IRC.php";
		break;
	// case "store" :
	// $pagetitle = "Store";
	// $pagename = "";
	// break;
	case "blogs" :
	case "devlog" :
		$pagetitle .= "Blogs";
		$pagename = "Blogs.php";
		break;
	case "downloads" :
		$pagetitle .= "Downloads";
		$pagename = "Downloads.php";
		break;
	// case "reddit" :
	// $pagetitle .= "Reddit";
	// $pagename = "";
	// break;
	case "blog" :
		$pagetitle .= "Blog";
		$pagename = "Blog.php";
		break;
	case "underconstruction" :
		$pagetitle = "Under Construction";
		$pagename = "Under Construction.php";
		break;
	case "login" :
		$pagetitle = "Log in";
		$pagename = "Login.php";
		break;
}

$page_exists = $pagename == "" ? false : file_exists ( "pages/" . $pagename );

if ($pageurl != "" && $_REQUEST ['page'] != $pageurl && $page_exists) {
	header ( "Location: /" . $pageurl );
	echo '<html><body><a href="/' . $pageurl . '/>Page moved</a></body></html>';
	exit ();
}
if (! $page_exists) {
	header ( "HTTP/1.0 404 Not Found" );
	$pagetitle .= "Not Found";
}
if (! isset ( $_REQUEST ['notemplate'] )) {
	include ("header.php");
}

if ($page_exists) {
	include ("pages/" . $pagename);
} else {
	include ("pages/404.php");
}

if (! isset ( $_REQUEST ['notemplate'] )) {
	include ("footer.php");
}