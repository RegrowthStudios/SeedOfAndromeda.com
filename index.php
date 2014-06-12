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
$cleanpageid = preg_replace ( "/[^\/A-Za-z0-9_\-]/", '', str_replace ( ".php", "", strtolower ( $_REQUEST ['page'] ) ) );
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
	case "underconstruction" :
		$pagetitle = "Under Construction";
		$pagename = "Under Construction.php";
		break;
	case "maintenance" :
		$pagetitle = "Maintenance";
		$pagename = "Maintenance.php";
		break;
	case "login" :
		$pagetitle = "Log in";
		$pagename = "Login.php";
		break;
	// Blogs:
	case "blog" :
	case "blogs/creating-a-region-file-system-for-a-voxel-game" :
		$pagetitle = "Creating a Region File System for a Voxel Game";
		$pagename = "blogs/BenA_1.php";
		$pageurl = "blogs/creating-a-region-file-system-for-a-voxel-game";
		break;
	case "blogs/designing-the-world-character" :
		$pagetitle = "Designing the World Character";
		$pagename = "blogs/Anthony_1.php";
		break;
	case "blogs/crafting-research-and-intergroup-cooperation-volume-one-part-one" :
		$pagetitle = "Crafting, Research and Intergroup Cooperation - Volume I.I";
		$pagename = "blogs/Matthew_1.php";
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
function echo_disqus() {
	global $loggedIn, $userinfo, $visitor, $pagetitle, $cleanpageid, $pageurl;
	
	echo '<div id="disqus_thread" class="double-col empty"></div><script type="text/javascript">
    var disqus_shortname = "seedofandromeda";
    var disqus_identifier = "' . $cleanpageid . '";
    var disqus_title = "' . $pagetitle . '";
    var disqus_url = "http://www.seedofandromeda.com/' . $pageurl . '";';
	if ($loggedIn && false) { // Disable SSO until Disqus creates a new SSO domain for SoA site
		$data = array (
				"id" => $userinfo ['user_id'],
				"username" => $userinfo ['username'],
				"email" => $userinfo ['email'] 
		);
		$message = base64_encode ( json_encode ( $data ) );
		$timestamp = time ();
		$hmac = dsq_hmacsha1 ( $message . ' ' . $timestamp, DISQUS_SECRET_KEY );
		echo '
		var disqus_config = function() {
		    this.page.remote_auth_s3 = "' . $message . ' ' . $hmac . ' ' . $timestamp . '";
		    this.page.api_key = "' . DISQUS_PUBLIC_KEY . '";
		}';
	}
	?>
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
<a href="http://disqus.com" class="dsq-brlink">comments powered by <span
	class="logo-disqus">Disqus</span></a>
<?php
}