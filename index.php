<?php
define ( 'DISQUS_SECRET_KEY', 'lGNdUi1TnZVLH5etw6GhXOwdgE6G3heDoFOe4CVaMEv7mhbJhFEzdQh1yGiUhKV1' );
define ( 'DISQUS_PUBLIC_KEY', 'w0LT1xGs1NTQyvu8WkxCxl83bLEFydFMK6bPMwrF6rU1b1NsevdxkonJySK3AkhH' );
require_once ('community/XenForoSDK.php');
$sdk = new XenForoSDK ();

$loggedIn = $sdk->isLoggedIn ();
$userinfo = $sdk->getUser ();
$visitor = $sdk->getVisitor ();

$pagetitle = "SoA - ";
$pagename = "";
if (! isset ( $_REQUEST ['page'] )) {
	$_REQUEST ['page'] = "index";
}
$cleanpageid = clean_pageid ( $_REQUEST ['page'] );
$pageurl = $cleanpageid;
if (startsWith ( $cleanpageid, "blogs/" )) {
	$pageurl = "blogs/" . substr ( $cleanpageid, 6 );
	$cleanpageid = "blogs/";
} else if (endsWith ( $cleanpageid, "/" )) {
	$cleanpageid = substr ( $cleanpageid, 0, strlen ( $cleanpageid ) - 1 );
}
switch ($cleanpageid) {
    case "index" :
		require_once ("db_connect.php");
		$pagetitle = "Seed of Andromeda";
		$pagename = "home.php";
		$pageurl = "";
		break;
	case "the-game" :
		$pagetitle .= "The Game";
		$pagename = "the-game.php";
		break;
	case "the-team" :
		$pagetitle .= "The Team";
		$pagename = "the-team.php";
		break;
	case "screenshots" :
		$pagetitle .= "Image Media";
		$pagename = "screenshots.php";
		break;
	case "videos" :
		$pagetitle .= "Video Media";
		$pagename = "videos.php";
		break;
	// case "mods" :
	// $pagetitle = "Mods";
	// $pagename = "";
	// break;
	case "irc" :
		$pagetitle .= "IRC";
		$pagename = "irc.php";
		break;
	// case "store" :
	// $pagetitle = "Store";
	// $pagename = "";
	// break;
	case "blogs/" :
		require_once ("db_connect.php");
		$pagename = "blogs.php";
		$cleanpageid = $pageurl;
		if (isset ( $connection )) {
			$currentblogpostlink = substr ( $pageurl, 6 );
			$arr = explode ( "-", $currentblogpostlink, 1 );
			if (count ( $arr ) == 0) {
				$pagename = "";
			} else {
				$postid = preg_replace ( "/[^0-9]/", '', $arr [0] );
				if ($postid == "") {
					$pagename = "";
				} else {
					$query = $connection->prepare ( "SELECT * FROM blog_posts WHERE id = ? AND published = 1" );
					$query->execute ( array (
							$postid 
					) );
					$blogpost = $query->fetch ();
					if (! $blogpost) {
						$pagename = "";
					} else {
						$newpostlink = gen_postlink ( $blogpost );
						if ($currentblogpostlink != $newpostlink) {
							$pageurl = "blogs/" . $newpostlink;
						}
						$pagetitle .= $blogpost ["title"];
						unset ( $query );
					}
				}
			}
		} else {
			$pagetitle .= "Maintenance";
			$pagename = "maintenance.php";
		}
		
		// var_dump(get_defined_vars());
		// die();
		break;
	case "devlog" :
	case "blogs" :
		require_once ("db_connect.php");
		$pagetitle .= "Blogs";
		$pagename = "blogs.php";
		break;
	case "downloads" :
		require_once ("db_connect.php");
		$pagetitle .= "Downloads";
		$pagename = "downloads.php";
		break;
	// case "reddit" :
	// $pagetitle .= "Reddit";
	// $pagename = "";
	// break;
	case "under-construction" :
		$pagetitle .= "Under Construction";
		$pagename = "under-construction.php";
		break;
	case "maintenance" :
		$pagetitle .= "Maintenance";
		$pagename = "maintenance.php";
		break;
	case "login" :
		$pagetitle .= "Log in";
		$pagename = "login.php";
		break;
	case "blog-admin":
		$pagetitle .= "Blog Admin";
		$pagename = "blog-admin.php";
		require_once ("db_connect.php");
		break;
	case "downloads-admin":
		$pagetitle .= "Download Admin";
		$pagename = "downloads-admin.php";
		require_once ("db_connect.php");
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
function clean_pageid($pageid) {
	return preg_replace ( "/[^\/A-Za-z0-9_\-]/", '', str_replace ( ".php", "", strtolower ( $pageid ) ) );
}
function gen_postlink($row) {
	return $row ["id"] . '-' . clean_pageid ( str_replace ( " ", "-", $row ["title"] ) );
}
function dsq_hmacsha1($data, $key) {
	$blocksize = 64;
	$hashfunc = 'sha1';
	if (strlen ( $key ) > $blocksize)
		$key = pack ( 'H*', $hashfunc ( $key ) );
	$key = str_pad ( $key, $blocksize, chr ( 0x00 ) );
	$ipad = str_repeat ( chr ( 0x36 ), $blocksize );
	$opad = str_repeat ( chr ( 0x5c ), $blocksize );
	$hmac = pack ( 'H*', $hashfunc ( ($key ^ $opad) . pack ( 'H*', $hashfunc ( ($key ^ $ipad) . $data ) ) ) );
	return bin2hex ( $hmac );
}
function echo_disqus($title = "", $url = "", $id = "") {
	global $loggedIn, $userinfo, $visitor, $pagetitle, $cleanpageid, $pageurl;
	
	if($_SERVER['HTTP_HOST'] == "www.soatest.local"){
		return;
	}
	
	if ($title == "") {
		$title = $pagetitle;
	}
	if ($url == "") {
		$url = $pageurl;
	}
	if ($id == "") {
		$id = $cleanpageid;
	}
	
	echo '<div id="disqus_thread" class="double-col empty"></div><script type="text/javascript">
    var disqus_shortname = "seedofandromeda";
    var disqus_identifier = "' . $id . '";
    var disqus_title = "' . $title . '";
    var disqus_url = "http://www.seedofandromeda.com/' . $url . '";
    		
		var disqus_config = function() {
    		
    this.sso = {
          name:   "SoA Forum Login",
          button:  "http://www.seedofandromeda.com/Assets/images/disquslogin_new.png",
          url:        "' . XenForo_Link::buildPublicLink ( "canonical:login", $userinfo, array (
			'redirect' => '/closewindow.php' 
	) ) . '",
          logout:  "' . XenForo_Link::buildPublicLink ( "canonical:logout", $userinfo, array (
			'_xfToken' => $visitor ['csrf_token_page'],
			'redirect' => '/' . $pageurl . '#disqus_thread' 
	) ) . '",
          width:   "800",
          height:  "600"
    };
    		';
	$data = array ();
	if ($loggedIn) {
		$data = array (
				"id" => "soa-" . $userinfo ['user_id'],
				"username" => $userinfo ['username'],
				"email" => $userinfo ['email'],
				"avatar" => "http://www.seedofandromeda.com/community/avatar.php?userid=" . $userinfo ['user_id'] . "&size=l",
				"url" => XenForo_Link::buildPublicLink ( 'canonical:members', $userinfo ) 
		);
	}
	$message = base64_encode ( json_encode ( $data ) );
	$timestamp = time ();
	$hmac = dsq_hmacsha1 ( $message . ' ' . $timestamp, DISQUS_SECRET_KEY );
	
	echo "
		    this.page.remote_auth_s3 = '" . $message . " " . $hmac . " " . $timestamp . "';
		    this.page.api_key = '" . DISQUS_PUBLIC_KEY . "';
		";
	?>
	};
    (function() {
        var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
        dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
        (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
    })();
</script>
<noscript>
	Please enable JavaScript to view the <a
		href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a>
</noscript>
<?php
}
function startsWith($haystack, $needle) {
	return $needle === "" || strpos ( $haystack, $needle ) === 0;
}
function endsWith($haystack, $needle) {
	return $needle === "" || substr ( $haystack, - strlen ( $needle ) ) === $needle;
}