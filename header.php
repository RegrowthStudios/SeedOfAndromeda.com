<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo $pagetitle ?></title>
	<!-- Force latest IE version. Must be before any other tags in head except title. -->
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="shortcut icon" type="image/x-icon" href="/assets/images/favicon.ico" />
    <link rel="stylesheet" href="/styles/normalise.css" type="text/css" />
    <link rel="stylesheet" href="/styles/soa.min.css?ver=11" type="text/css" />
    <link rel="stylesheet" href="/assets/fonts/the_league_of_orbitron/Orbitron.css" type="text/css" />
    <link href='https://fonts.googleapis.com/css?family=Electrolize' rel='stylesheet' type='text/css' />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="UTF-8">
    <meta name="keywords"
	    content="seed of andromeda, SoA, Windows, PC, Mac, Linux, Voxel, Voxel-Based, Voxel Game, Voxel-Based Game, indie game, independent game, independently developed, video game, pc game, creation, survival, chunks, blocks, sci fi" />
    <meta name="description"
	    content="Seed Of Andromeda is a Voxel based Sandbox RPG in a futuristic setting. The player will crash land on a fully round voxel planet and will be forced to survive hostile creatures. As the player progresses through the game, they will be able to form settlements, develop technology, and eventually escape the rock they are stranded on!" />
    <meta name="og:title" 
        content="Seed of Andromeda" />
    <meta name="og:description"
	    content="Seed Of Andromeda is a Voxel based Sandbox RPG in a futuristic setting. The player will crash land on a fully round voxel planet and will be forced to survive hostile creatures. As the player progresses through the game, they will be able to form settlements, develop technology, and eventually escape the rock they are stranded on!" />
    <meta name="og:site_name" 
        content="seedofandromeda" />
    <meta name="og:type" 
        content="game" />
    <meta name="og:url" 
        content="https://www.seedofandromeda.com/" />
    <meta name="og:image"
	    content="https://www.seedofandromeda.com/assets/images/soa_icon.png" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js" type="text/javascript"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js" type="text/javascript"></script>
    <script src="/scripts/spin.min.js" type="text/javascript"></script>
    <script src="/scripts/lightbox-2.6.js"></script>
    <link href="/styles/lightbox.css" rel="stylesheet" />
    <script src="/scripts/soa.js?=4"></script>
    <script src="/scripts/jquery.sticky.js"></script>
    <script>
        $(document).ready(function () {
            $("#nav-bar").sticky({ topSpacing: 0 });
        });
    </script>
    <?php
    $pagestyle = "styles/pages/" . str_replace ( ".php", ".min.css", $pagename );
    if (strcmp($pagename, "admin.php") == 0) {
        if (isset($_REQUEST['blogs'])) {
            $pagestyle = "styles/pages/admin/blogs.min.css";
        } else if (isset($_REQUEST['downloads'])) {
            $pagestyle = "styles/pages/admin/downloads.min.css";
        } else if (isset($_REQUEST['images'])) {
            $pagestyle = "styles/pages/admin/images.min.css";
        } else if (isset($_REQUEST['videos'])) {
            $pagestyle = "styles/pages/admin/videos.min.css";
        }
    }    
    if (file_exists ( $pagestyle )) {
	    echo '<link rel="stylesheet" href="/' . $pagestyle . '?ver=15" type="text/css" />';
    }
    $pagestyle = "scripts/pages/" . str_replace ( ".php", ".js", $pagename );
    if (strcmp($pagename, "admin.php") == 0) {
        if (isset($_REQUEST['blogs'])) {
            $pagestyle = "scripts/pages/admin/blogs.js";
        } else if (isset($_REQUEST['downloads'])) {
            $pagestyle = "scripts/pages/admin/downloads.js";
        } else if (isset($_REQUEST['images'])) {
            $pagestyle = "scripts/pages/admin/images.js";
        } else if (isset($_REQUEST['videos'])) {
            $pagestyle = "scripts/pages/admin/videos.js";
        }
    }   
    if (file_exists ( $pagestyle )) {
	    echo '<script src="/' . $pagestyle . '?ver=14"></script>';
    }
    ?>
    <script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

      ga('create', 'UA-46451794-1', 'seedofandromeda.com');
      ga('send', 'pageview');

    </script>
</head>
<body>
    <div id="main">

        <a href="/">
            <div id="header"></div>
        </a>

        <div id="content-wrapper">

            <div id="nav-bar">
                <ul id="navigation">
                    <li class="home"><a href="/">Home</a></li>
                    <li class="about"><a href="#">About</a>
                        <div class="dropdown-wrapper">
                            <ul class="dropdown">
                                <li><a href="/the-game">The Game</a></li>
                                <li><a href="/the-team">The Team</a></li>
                            </ul>
                        </div>
                    </li>
                    <li class="media"><a href="#">Media</a>
                        <div class="dropdown-wrapper">
                            <ul class="dropdown">
                                <li><a href="/downloads">Downloads</a></li>
                                <li><a href="/images">Images</a></li>
                                <li><a href="/videos">Videos</a></li>
                            </ul>
                        </div>
                    </li>
                    <li class="community"><a href="#">Community</a>
                        <div class="dropdown-wrapper">
                            <ul class="dropdown">
                                <li><a href="/community/">Forums</a></li>
                                <li><a href="/community/modifications/">Mods</a></li>
                                <li><a href="/irc">IRC</a></li>
                                <li><a href="http://www.reddit.com/r/seedofandromeda/" target="_blank">Reddit</a></li>
                            </ul>
                        </div>
                    </li>
                    <li class="store"><a href="/under-construction">Store</a></li>
                    <li class="blogs"><a href="/blogs">Dev Blogs</a></li>
                </ul>
                
                <div id="account-bar">
                <?php
                    if(!$loggedIn)
                    {
                ?>
                    <img class="account-image" src="/assets/images/DefaultUser_NoSignIn_ProfImg.png" />
                    <div class="account-overview">
                        <span class="account-name">Not Logged In</span>
                    </div>
                    <div class="account-log-in-wrapper">
                        <ul class="account-log-in">
                            <form method='post' action='<?php echo XenForo_Link::buildPublicLink('canonical:login'); ?>'>
                                <div class="input"> 
						            <span>Username:</span> <input type='text'
							            style="padding-right: 0;" id="username" name='login'
							            placeholder="Username" />
                                </div>
                                <div class="input"> 
						            <span>Password:</span> <input type='password'
							            style="padding-right: 0;" id='password' name='password'
							            placeholder="Password" />
                                </div>
                                <div class="input">
                                    <span>Remember Me:</span> <div class="checkbox"> <input id="remember" 
								        type='checkbox' style="padding-right: 0;" name='remember' />
							            <label for="remember"></label>
                                    </div>
                                </div>
						        <input type="hidden" name="cookie_check" value="1"> <input
							        type="hidden" name="redirect" value="/<?php echo $pageurl;?>">
						        <div class="account-log-in-submit"><input type="hidden" name="_xfToken" value="<?php isset($visitor['csrf_token_page']) ? $visitor['csrf_token_page'] : "";?>"> <input
							        type='submit' value='submit' onClick='prepare_login();'
							        class="left" /><span class="account-log-in-register">&nbsp;or <a href="<?php echo XenForo_Link::buildPublicLink("canonical:register");?>">register</a>.</span></div>
					        </form>
                        </ul>
                    </div>
                <?php
                    }
                    else
                    {
                ?>
                    <img class="account-image" src="/community/avatar.php?userid=<?php echo $userinfo['user_id']; ?>&size=s" />
                    <div class="account-overview">
                        <span class="account-name"><?php echo $userinfo['username']; ?></span>
                        <span class="account-alerts"><?php echo $userinfo['alerts_unread']+$userinfo['conversations_unread']; ?></span>
                    </div>
                    <div class="account-options-wrapper">
                        <ul class="account-options">
                            <li><a href="<?php echo XenForo_Link::buildPublicLink("canonical:account/alerts");?>">Alerts (<?php echo $userinfo['alerts_unread']; ?>)</a></li>
                            <li><a href="<?php echo XenForo_Link::buildPublicLink("canonical:conversations");?>">Inbox (<?php echo $userinfo['conversations_unread']; ?>)</a></li>
                            <li><a href="<?php echo XenForo_Link::buildPublicLink("canonical:account");?>">My Account</a></li>
                            <li><a class="logout" href="<?php echo XenForo_Link::buildPublicLink("canonical:logout", $userinfo, array('_xfToken' => $visitor['csrf_token_page'], 'redirect' => '/'.$pageurl));?>">Log Out</a></li>
                        </ul>
                    </div>
                <?php
                    }
                ?>
                </div>
            </div>

            <div id="content-outer">
                <div class="content-border content-border-0" style="width: 1053px;">
                    <div class="content-border content-border-1" style="width: 1051px;">
                        <div class="content-border content-border-2" style="width: 1049px;">
                            <div class="content-border content-border-3" style="width: 1047px;">
                                <div class="content-border content-border-2" style="width: 1045px;">
                                    <div class="content-border content-border-1" style="width: 1043px;">
                                        <div class="content-border content-border-0" style="width: 1041px;">