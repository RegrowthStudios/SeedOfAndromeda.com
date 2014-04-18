<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $pagetitle ?></title>

<link rel="shortcut icon" type="image/x-icon"
	href="/Assets/images/favicon.ico" />

<link rel="stylesheet" href="/styles/soa.css?ver=5" type="text/css" />

<link rel="stylesheet" href="/styles/Normalise.css" type="text/css" />

<link rel="stylesheet"
	href="/Assets/Fonts/the_league_of_orbitron/Orbitron.css"
	type="text/css" />

<link href='http://fonts.googleapis.com/css?family=Electrolize'
	rel='stylesheet' type='text/css'>

<meta name="keywords"
	content="seed of andromeda, SoA, Windows, PC, Mac, Linux, Voxel, Voxel-Based, Voxel Game, Voxel-Based Game, indie game, independent game, independently developed, video game, pc game, creation, survival, chunks, blocks, sci fi">

<meta name="description"
	content="Seed Of Andromeda is a Voxel based Sandbox RPG in a futuristic setting. The player will crash land on a fully round voxel planet and will be forced to survive hostile creatures. As the player progresses through the game, they will be able to form settlements, develop technology, and eventually escape the rock they are stranded on!">

<meta name="og:title" content="Seed of Andromeda">

<meta name="og:description"
	content="Seed Of Andromeda is a Voxel based Sandbox RPG in a futuristic setting. The player will crash land on a fully round voxel planet and will be forced to survive hostile creatures. As the player progresses through the game, they will be able to form settlements, develop technology, and eventually escape the rock they are stranded on!">

<meta name="og:site_name" content="seedofandromeda">

<meta name="og:type" content="game">

<meta name="og:url" content="http://www.seedofandromeda.com/">

<meta name="og:image"
	content="http://www.seedofandromeda.com/Assets/images/HeaderOld.png">
<script
	src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"
	type="text/javascript"></script>

<script
	src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"
	type="text/javascript"></script>
<script src="/scripts/lightbox-2.6.js"></script>
<link href="/styles/lightbox.css" rel="stylesheet" />
<script src="/scripts/soa.js?ver=4"></script><?php

if (isset($_REQUEST ['loginerror'])) {
	
	echo "<script type='text/javascript'>

                         window.onload = function() { alert('" . addslashes ( $_REQUEST ['loginerror'] ) . "'); }

                     </script>";
}
if (isset ( $_REQUEST ['logouterror'] )) {
	
	echo "<script type='text/javascript'>

                         window.onload = function() { alert('Log Out Failed!'); }

                     </script>";
}
$pagestyle = "styles/pages/" . str_replace ( ".php", ".css", $pagename );
if (file_exists ( $pagestyle )) {
	echo '<link rel="stylesheet" href="/' . $pagestyle . '" type="text/css" />';
}
$pagestyle = "scripts/pages/" . str_replace ( ".php", ".js", $pagename );
if (file_exists ( $pagestyle )) {
	echo '<script src="/' . $pagestyle . '"></script>';
}
?>
<!-- Google Analytics BEGIN-->

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-46451794-1', 'seedofandromeda.com');
  ga('send', 'pageview');

</script>

<!-- Google Analytics END-->



<!-- Downloads Tracking -->
<!-- Task completed by Cloud Flare script
<script type="text/javascript">
if (typeof jQuery != 'undefined') {
  jQuery(document).ready(function($) {
    var filetypes = /\.(zip|exe|dmg|pdf|doc.*|xls.*|ppt.*|mp3|txt|rar|wma|mov|avi|wmv|flv|wav)$/i;
    var baseHref = '';
    if (jQuery('base').attr('href') != undefined) baseHref = jQuery('base').attr('href');
 
    jQuery('a').on('click', function(event) {
      var el = jQuery(this);
      var track = true;
      var href = (typeof(el.attr('href')) != 'undefined' ) ? el.attr('href') :"";
      var isThisDomain = href.match(document.domain.split('.').reverse()[1] + '.' + document.domain.split('.').reverse()[0]);
      if (!href.match(/^javascript:/i)) {
        var elEv = []; elEv.value=0, elEv.non_i=false;
        if (href.match(/^mailto\:/i)) {
          elEv.category = "Email";
          elEv.action = "click";
          elEv.label = href.replace(/^mailto\:/i, '');
          elEv.loc = href;
        }
        else if (href.match(filetypes)) {
          var extension = (/[.]/.exec(href)) ? /[^.]+$/.exec(href) : undefined;
          elEv.category = "Download";
          elEv.action = "click-" + extension[0];
          elEv.label = href.replace(/ /g,"-");
          elEv.loc = baseHref + href;
        }
        else if (href.match(/^https?\:/i) && !isThisDomain) {
          elEv.category = "External";
          elEv.action = "click";
          elEv.label = href.replace(/^https?\:\/\//i, '');
          elEv.non_i = true;
          elEv.loc = href;
        }
        else if (href.match(/^tel\:/i)) {
          elEv.category = "Telephone";
          elEv.action = "click";
          elEv.label = href.replace(/^tel\:/i, '');
          elEv.loc = href;
        }
        else track = false;
 
        if (track) {
          _gaq.push(['_trackEvent', elEv.category.toLowerCase(), elEv.action.toLowerCase(), elEv.label.toLowerCase(), elEv.value, elEv.non_i]);
          if ( el.attr('target') == undefined || el.attr('target').toLowerCase() != '_blank') {
            setTimeout(function() { location.href = elEv.loc; }, 400);
            return false;
      }
    }
      }
    });
  });
}
</script>
-->
<!-- Downloads Tracking END -->
</head>



<body>

	<div id="main">



		<div id="social-bar">

			<!-- Really Needs Optimising! -->

			<!--<a href="https://twitter.com/ChillstepCoder" target="_blank" title="Ben Arnold's Twitter'">
                    <div id="social-button" class="twitter">
                        <img src="/Assets/images/SocialBackgroundTwitter.png" class="star" />
                        <img src="/Assets/images/SocialTwitterRing_Top_Left.png" class="top-left" />
                        <img src="/Assets/images/SocialTwitterRing_Top_Right.png" class="top-right" />
                        <img src="/Assets/images/SocialTwitterRing_Bottom_Right.png" class="bottom-right" />
                        <img src="/Assets/images/SocialTwitterRing_Bottom_Left.png" class="bottom-left" />
                        <img src="/Assets/images/SocialTwitterRing_Top_Left_Hover.png" class="top-left-hover" />
                        <img src="/Assets/images/SocialTwitterRing_Top_Right_Hover.png" class="top-right-hover" />
                        <img src="/Assets/images/SocialTwitterRing_Bottom_Right_Hover.png" class="bottom-right-hover" />
                        <img src="/Assets/images/SocialTwitterRing_Bottom_Left_Hover.png" class="bottom-left-hover" />
                    </div>
                </a>
                <a href="http://www.youtube.com/user/DubstepCoder" target="_blank" title="Ben Arnold's YouTube Channel'">
                    <div id="social-button" class="youtube">
                        <img src="/Assets/images/SocialBackgroundYouTube.png" class="star" />
                        <img src="/Assets/images/SocialYouTubeRing_Top_Left.png" class="top-left" />
                        <img src="/Assets/images/SocialYouTubeRing_Top_Right.png" class="top-right" />
                        <img src="/Assets/images/SocialYouTubeRing_Bottom_Right.png" class="bottom-right" />
                        <img src="/Assets/images/SocialYouTubeRing_Bottom_Left.png" class="bottom-left" />
                        <img src="/Assets/images/SocialYouTubeRing_Top_Left_Hover.png" class="top-left-hover" />
                        <img src="/Assets/images/SocialYouTubeRing_Top_Right_Hover.png" class="top-right-hover" />
                        <img src="/Assets/images/SocialYouTubeRing_Bottom_Right_Hover.png" class="bottom-right-hover" />
                        <img src="/Assets/images/SocialYouTubeRing_Bottom_Left_Hover.png" class="bottom-left-hover" />
                    </div>
                </a>
            </div>
            <div id="social-bar" class="right">
                <a href="https://www.facebook.com/SeedOfAndromedaUnofficial?ref=stream" target="_blank" title="Official SoA Facebook Page'">
                    <div id="social-button" class="facebook">
                        <img src="/Assets/images/SocialBackgroundFacebook.png" class="star" />
                        <img src="/Assets/images/SocialFacebookRing_Top_Left.png" class="top-left" />
                        <img src="/Assets/images/SocialFacebookRing_Top_Right.png" class="top-right" />
                        <img src="/Assets/images/SocialFacebookRing_Bottom_Right.png" class="bottom-right" />
                        <img src="/Assets/images/SocialFacebookRing_Bottom_Left.png" class="bottom-left" />
                        <img src="/Assets/images/SocialFacebookRing_Top_Left_Hover.png" class="top-left-hover" />
                        <img src="/Assets/images/SocialFacebookRing_Top_Right_Hover.png" class="top-right-hover" />
                        <img src="/Assets/images/SocialFacebookRing_Bottom_Right_Hover.png" class="bottom-right-hover" />
                        <img src="/Assets/images/SocialFacebookRing_Bottom_Left_Hover.png" class="bottom-left-hover" />
                    </div>
                </a>
                <a href="http://www.indiedb.com/games/seed-of-andromeda" target="_blank" title="SoA IndieDB Page'">
                    <div id="social-button" class="indiedb">
                        <img src="/Assets/images/SocialBackgroundIndieDB.png" class="star" />
                        <img src="/Assets/images/SocialIndieDBRing_Top_Left.png" class="top-left" />
                        <img src="/Assets/images/SocialIndieDBRing_Top_Right.png" class="top-right" />
                        <img src="/Assets/images/SocialIndieDBRing_Bottom_Right.png" class="bottom-right" />
                        <img src="/Assets/images/SocialIndieDBRing_Bottom_Left.png" class="bottom-left" />
                        <img src="/Assets/images/SocialIndieDBRing_Top_Left_Hover.png" class="top-left-hover" />
                        <img src="/Assets/images/SocialIndieDBRing_Top_Right_Hover.png" class="top-right-hover" />
                        <img src="/Assets/images/SocialIndieDBRing_Bottom_Right_Hover.png" class="bottom-right-hover" />
                        <img src="/Assets/images/SocialIndieDBRing_Bottom_Left_Hover.png" class="bottom-left-hover" />
                    </div>
                </a>-->

		</div>

		<a href="/"><div id="header"></div></a>

		<div id="nav-bar">

			<ul id="navigation">

				<li <?php if($cleanpageid == "index"){ ?> class='active'><a href="#"> <?php } else { ?> > <a
						href="/"> <?php } ?> Home</a></li>

				<li><a href="#">About</a>

					<ul class="dropdown">

						<li <?php if($cleanpageid == "thegame"){ ?> class='active'><a
							href="#"> <?php } else { ?> > <a href="/thegame"> <?php } ?> The Game</a></li>

						<li <?php if($cleanpageid == "theteam"){ ?> class='active'><a
							href="#"> <?php } else { ?> > <a href="/theteam"> <?php } ?> The Team</a></li>

					</ul></li>

				<li><a href="#">Media</a>

					<ul class="dropdown">

						<li <?php if($cleanpageid == "downloads"){ ?> class='active'><a
							href="#"> <?php } else { ?> > <a href="/downloads"> <?php } ?> Downloads</a></li>

						<li <?php if($cleanpageid == "screenshots"){ ?> class='active'><a
							href="#"> <?php } else { ?> > <a href="/screenshots"> <?php } ?> Screenshots</a></li>

						<li <?php if($cleanpageid == "videos"){ ?> class='active'><a
							href="#"> <?php } else { ?> > <a href="/videos"> <?php } ?> Videos</a></li>

					</ul></li>

				<li><a href="#">Community</a>

					<ul class="dropdown">

						<li <?php if($cleanpageid == "forums"){ ?> class='active'><a
							href="#"> <?php } else { ?> > <a href="/community/"> <?php } ?> Forums</a></li>

						<li <?php if($cleanpageid == "mods"){ ?> class='active'><a
							href="#"> <?php } else { ?> > <a href="/underconstruction"> <?php } ?> Mods</a></li>

						<li <?php if($cleanpageid == "irc"){ ?> class='active'><a href="#"> <?php } else { ?> > <a
								href="/irc"> <?php } ?> IRC</a></li>

						<li <?php if($cleanpageid == "reddit"){ ?> class='active'><a
							href="#"> <?php } else { ?> > <a
								href="http://www.reddit.com/r/seedofandromeda/" target="_blank"> <?php } ?> Reddit</a></li>

					</ul></li>

				<li <?php if($cleanpageid == "store"){ ?> class='active'><a href="#"> <?php } else { ?> > <a
						href="/underconstruction"> <?php } ?> Store</a></li>

				<li <?php if($cleanpageid == "blogs"){ ?> class='active'><a href="#"> <?php } else { ?> > <a
						href="/blogs"> <?php } ?> Dev Blogs</a></li>

			</ul>
    			<?php
							
							if (! $loggedIn) 

							{
								
								?> 
				<div id='accountBar' class='loggedOut'>

				<img src='/Assets/images/DefaultUser_NoSignIn_ProfImg.png' />

				<div class='accountsName'>Not Logged In</div>

				<div class='accountLog'>

					<div id='break' class='five'></div>

					<form method='post' action='/community/login/login'>

						<span>Username:</span> <input type='text'
							style="padding-right: 0;" id="username" name='login'
							placeholder="Username" /> <br />
						<div id='break' class='five'></div>

						<span>Password:</span> <input type='password'
							style="padding-right: 0;" id='password' name='password'
							placeholder="Password" /> <br /> <span>Remember Me:</span> <input
							type='checkbox' style="padding-right: 0;" name='remember' /> <br />
						<input type="hidden" name="cookie_check" value="1"> <input
							type="hidden" name="redirect" value="/<?php echo $pageurl;?>">
						<input type="hidden" name="_xfToken" value=""> <input
							type='submit' value='submit' onClick='prepare_login();'
							class="left" />

					</form>

					<div class='register'>
						<a href="/community/register/">or Register Now!</a>
					</div>

				</div>

			</div>
				<?php
							} 

							else 

							{
								
								?>
                <div id='accountBar'>

				<img
					src='/community/avatar.php?userid=<?php echo $userinfo['user_id']; ?>&size=s'
					height="35" width="35" />

				<div class='accountsName'><?php echo $userinfo['username']; ?></div>

				<div class='accountAlerts'><?php echo $userinfo['alerts_unread']+$userinfo['conversations_unread']; ?></div>

				<ul class='accountOptions'>

					<li><a
						href='<?php echo XenForo_Link::buildPublicLink("canonical:account/alerts");?>'>Alerts (<?php echo $userinfo['alerts_unread']; ?>)</a></li>
					<li><a
						href='<?php echo XenForo_Link::buildPublicLink("canonical:conversations");?>'>Inbox (<?php echo $userinfo['conversations_unread']; ?>)</a></li>

					<li><a
						href='<?php echo XenForo_Link::buildPublicLink("canonical:account");?>'>My
							Account</a></li>

					<li><a class="logout"
						href='<?php echo XenForo_Link::buildPublicLink("canonical:logout", $userinfo, array('_xfToken' => $visitor['csrf_token_page'], 'redirect' => '/'.$pageurl));?>'>Log
							Out</a></li>

				</ul>

			</div>
                <?php
							}
							
							?>
            </div>


		<div id="content-outer">
			<div class="content_border0" style="width:1053px;">
			<div class="content_border1" style="width:1051px;">
			<div class="content_border2" style="width:1049px;">
			<div class="content_border3" style="width:1047px;">
			<div class="content_border2" style="width:1045px;">
			<div class="content_border1" style="width:1043px;">
			<div class="final_content_border content_border0" style="width:1041px;">
