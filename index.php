<?php
define ( 'IN_MYBB', 1 );

require_once 'Forums/global.php';

require_once 'class.MyBBIntegrator.php';

$MyBBI = new MyBBIntegrator ( $mybb, $db, $cache, $plugins, $lang, $config );

$pagetitle = "SoA - ";
$pagename = "";
$pageurl = "";
if (! isset ( $_REQUEST ['page'] )) {
	$_REQUEST ['page'] = "index";
}
$cleanpageid = str_replace ( ".php", "", preg_replace ( '/\s+/', '', strtolower ( $_REQUEST ['page'] ) ) );
switch ($cleanpageid) {
	case "index" :
		$pagetitle = "Seed of Andromeda";
		$pagename = "Home.php";
		$pageurl = "";
		break;
	case "thegame" :
		$pagetitle .= "The Game";
		$pagename = "The Game.php";
		$pageurl = "TheGame";
		break;
	case "theteam" :
		$pagetitle .= "The Team";
		$pagename = "The Team.php";
		$pageurl = "TheTeam";
		break;
	case "screenshots" :
		$pagetitle .= "Image Media";
		$pagename = "Screenshots.php";
		$pageurl = "Screenshots";
		break;
	case "videos" :
		$pagetitle .= "Video Media";
		$pagename = "Videos.php";
		$pageurl = "Videos";
		break;
	// case "mods" :
	// $pagetitle = "Mods";
	// $pagename = "";
	// break;
	case "irc" :
		$pagetitle .= "IRC";
		$pagename = "IRC.php";
		$pageurl = "IRC";
		break;
	// case "store" :
	// $pagetitle = "Store";
	// $pagename = "";
	// break;
	case "blogs" :
	case "devlog" :
		$pagetitle .= "Blogs";
		$pagename = "Blogs.php";
		$pageurl = "Blogs";
		break;
	case "downloads" :
		$pagetitle .= "Downloads";
		$pagename = "Downloads.php";
		$pageurl = "Downloads";
		break;
	// case "reddit" :
	// $pagetitle .= "Reddit";
	// $pagename = "";
	// break;
	case "blog" :
		$pagetitle .= "Blog";
		$pagename = "Blog.php";
		$pageurl = "Blog";
		break;
	case "wip" :
		$pagetitle = "Under Construction";
		$pagename = "Under Construction.php";
		$pageurl = "UnderConstruction";
		break;
}

if ($pageurl != "" && $_REQUEST ['page'] != $pageurl) {
	header ( "Location: /" . $pageurl );
	echo '<html><body><a href="/' . $pageurl . '/>Page moved</a></body></html>';
	exit ();
}

include ("header.php");

if (file_exists ( "pages/" . $pagename )) {
	include ("pages/" . $pagename);
} else {
	header ( "HTTP/1.0 404 Not Found" );
	include ("pages/404.php");
}

include ("footer.php");