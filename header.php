<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $pagetitle ?></title>

<link rel="shortcut icon" type="image/x-icon"
	href="/Assets/images/favicon.ico" />

<link rel="stylesheet" href="/styles/soa.css?ver=18" type="text/css" />

<link rel="stylesheet" href="/styles/Normalise.css" type="text/css" />

<link rel="stylesheet"
	href="/Assets/Fonts/the_league_of_orbitron/Orbitron.css"
	type="text/css" />

<link href='http://fonts.googleapis.com/css?family=Electrolize'
	rel='stylesheet' type='text/css' />

<meta name="keywords"
	content="seed of andromeda, SoA, Windows, PC, Mac, Linux, Voxel, Voxel-Based, Voxel Game, Voxel-Based Game, indie game, independent game, independently developed, video game, pc game, creation, survival, chunks, blocks, sci fi" />

<meta name="description"
	content="Seed Of Andromeda is a Voxel based Sandbox RPG in a futuristic setting. The player will crash land on a fully round voxel planet and will be forced to survive hostile creatures. As the player progresses through the game, they will be able to form settlements, develop technology, and eventually escape the rock they are stranded on!" />

<meta name="og:title" content="Seed of Andromeda" />

<meta name="og:description"
	content="Seed Of Andromeda is a Voxel based Sandbox RPG in a futuristic setting. The player will crash land on a fully round voxel planet and will be forced to survive hostile creatures. As the player progresses through the game, they will be able to form settlements, develop technology, and eventually escape the rock they are stranded on!" />

<meta name="og:site_name" content="seedofandromeda" />

<meta name="og:type" content="game" />

<meta name="og:url" content="http://www.seedofandromeda.com/" />

<meta name="og:image"
	content="http://www.seedofandromeda.com/Assets/images/HeaderOld.png" />
<script
	src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"
	type="text/javascript"></script>

<script
	src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"
	type="text/javascript"></script>
<script src="/scripts/lightbox-2.6.js"></script>
<link href="/styles/lightbox.css" rel="stylesheet" />
<script src="/scripts/soa.js?ver=7"></script>
<script src="/scripts/jquery.sticky.js"></script>
<script>
$(document).ready(function(){
    $("#nav-bar").sticky({topSpacing:0});
  });
</script>
<?php

$pagestyle = "styles/pages/" . str_replace ( ".php", ".css", $pagename );
if (file_exists ( $pagestyle )) {
	echo '<link rel="stylesheet" href="/' . $pagestyle . '?ver=13" type="text/css" />';
}
$pagestyle = "scripts/pages/" . str_replace ( ".php", ".js", $pagename );
if (file_exists ( $pagestyle )) {
	echo '<script src="/' . $pagestyle . '?ver=3"></script>';
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


</head>



<body>

	<div id="main">


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
							href="#"> <?php } else { ?> > <a href="/community/modifications/"> <?php } ?> Mods</a></li>

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

					<form method='post' action='<?php echo XenForo_Link::buildPublicLink('canonical:login'); ?>'>

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
						<input type="hidden" name="_xfToken" value="<?php isset($visitor['csrf_token_page']) ? $visitor['csrf_token_page'] : "";?>"> <input
							type='submit' value='submit' onClick='prepare_login();'
							class="left" />

					</form>

					<div class='register'>
						<a href="<?php echo XenForo_Link::buildPublicLink('canonical:register'); ?>">or Register Now!</a>
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
