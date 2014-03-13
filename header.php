    	<?php

			define('IN_MYBB', 1);
			
			require_once 'Forums/global.php';
			require_once 'class.MyBBIntegrator.php';
			
			$MyBBI = new MyBBIntegrator($mybb, $db, $cache, $plugins, $lang, $config); 
		
            $loginerr = (string) $_GET['loginerror'];
            if($loginerr != null) {
                echo"<script type='text/javascript'>
                         window.onload = function() { alert('Invalid User Name or Password!'); }
                     </script>";   
            }
			
            $logouterr = (string) $_GET['logouterror'];
            if($logouterr != null) {
                echo"<script type='text/javascript'>
                         window.onload = function() { alert('Log Out Failed!'); }
                     </script>";   
            }
			
        ?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

    <head>
    
        <title>
		<?php 
			if($page_id == null)
			{
				echo "Seed of Andromeda";
			}
			else
			{
				switch($page_id)
				{
					case 1:
						echo "Seed of Andromeda";
						break;
					case 2:
						echo "SoA - The Game";
						break;
					case 3:
						echo "SoA - The Team";
						break;
					case 4:
						echo "SoA - Image Media";
						break;
					case 5:
						echo "SoA - Video Media";
						break;
					case 6:
						echo "SoA - Mods";
						break;
					case 7:
						echo "SoA - IRC";
						break;
					case 8:
						echo "SoA - Store";
						break;
					case 9:
						echo "SoA - Blogs";
						break;
					case 12:
						echo "SoA - Forums";
						break;
					case 13:
						echo "SoA - Downloads";
						break;
					case 14:
						echo "SoA - Reddit";
						break;
					case 16:
						echo "SoA - Blog";
						break;
					case 999:
						echo "Under Construction";
						break;
					default:
						echo "Seed of Andromeda";
						break;
				}
			}
			
			?>
		</title>
		<link rel="shortcut icon" type="image/x-icon" href="Assets/images/favicon.ico" />
        <link rel="stylesheet" href="soa.css" type="text/css" />
        <link rel="stylesheet" href="Normalise.css" type="text/css" />
        <link rel="stylesheet" href="Assets/Fonts/the_league_of_orbitron/Orbitron.css" type="text/css" />
        <link href='http://fonts.googleapis.com/css?family=Electrolize' rel='stylesheet' type='text/css'>
		<?php
			if($page_id == 4)
			{
				?>
					<link rel="stylesheet" href="Screenshots.css" type="text/css" />
				<?php
			}
			else if($page_id == 3)
			{
				?>
					<link rel="stylesheet" href="TheTeam.css" type="text/css" />
				<?php
			}
			else if($page_id == 5)
			{
				?>
					<link rel="stylesheet" href="Videos.css" type="text/css" />
				<?php
			}
		?>
        
        <meta name="keywords" content="seed of andromeda, SoA, Windows, PC, Mac, Linux, Voxel, Voxel-Based, Voxel Game, Voxel-Based Game, indie game, independent game, independently developed, video game, pc game, creation, survival, chunks, blocks, sci fi">
        <meta name="description" content="Seed Of Andromeda is a Voxel based Sandbox RPG in a futuristic setting. The player will crash land on a fully round voxel planet and will be forced to survive hostile creatures. As the player progresses through the game, they will be able to form settlements, develop technology, and eventually escape the rock they are stranded on!">
        <meta property="og:title" content="Seed of Andromeda">
        <meta property="og:description" content="Seed Of Andromeda is a Voxel based Sandbox RPG in a futuristic setting. The player will crash land on a fully round voxel planet and will be forced to survive hostile creatures. As the player progresses through the game, they will be able to form settlements, develop technology, and eventually escape the rock they are stranded on!">
        <meta name="og:site_name" content="seedofandromeda">
        <meta name="og:type" content="game">
        <meta name="og:url" content="http://www.seedofandromeda.com/">
        <meta name="og:image" content="http://www.seedofandromeda.com/Assets/images/HeaderOld.png">
        
    </head>

    <body>
        <div id="main">
        
            <div id="social-bar">
                <!-- Really Needs Optimising! -->
                <!--<a href="https://twitter.com/ChillstepCoder" target="_blank" title="Ben Arnold's Twitter'">
                    <div id="social-button" class="twitter">
                        <img src="Assets/images/SocialBackgroundTwitter.png" class="star" />
                        <img src="Assets/images/SocialTwitterRing_Top_Left.png" class="top-left" />
                        <img src="Assets/images/SocialTwitterRing_Top_Right.png" class="top-right" />
                        <img src="Assets/images/SocialTwitterRing_Bottom_Right.png" class="bottom-right" />
                        <img src="Assets/images/SocialTwitterRing_Bottom_Left.png" class="bottom-left" />
                        <img src="Assets/images/SocialTwitterRing_Top_Left_Hover.png" class="top-left-hover" />
                        <img src="Assets/images/SocialTwitterRing_Top_Right_Hover.png" class="top-right-hover" />
                        <img src="Assets/images/SocialTwitterRing_Bottom_Right_Hover.png" class="bottom-right-hover" />
                        <img src="Assets/images/SocialTwitterRing_Bottom_Left_Hover.png" class="bottom-left-hover" />
                    </div>
                </a>
                <a href="http://www.youtube.com/user/DubstepCoder" target="_blank" title="Ben Arnold's YouTube Channel'">
                    <div id="social-button" class="youtube">
                        <img src="Assets/images/SocialBackgroundYouTube.png" class="star" />
                        <img src="Assets/images/SocialYouTubeRing_Top_Left.png" class="top-left" />
                        <img src="Assets/images/SocialYouTubeRing_Top_Right.png" class="top-right" />
                        <img src="Assets/images/SocialYouTubeRing_Bottom_Right.png" class="bottom-right" />
                        <img src="Assets/images/SocialYouTubeRing_Bottom_Left.png" class="bottom-left" />
                        <img src="Assets/images/SocialYouTubeRing_Top_Left_Hover.png" class="top-left-hover" />
                        <img src="Assets/images/SocialYouTubeRing_Top_Right_Hover.png" class="top-right-hover" />
                        <img src="Assets/images/SocialYouTubeRing_Bottom_Right_Hover.png" class="bottom-right-hover" />
                        <img src="Assets/images/SocialYouTubeRing_Bottom_Left_Hover.png" class="bottom-left-hover" />
                    </div>
                </a>
            </div>
            <div id="social-bar" class="right">
                <a href="https://www.facebook.com/SeedOfAndromedaUnofficial?ref=stream" target="_blank" title="Official SoA Facebook Page'">
                    <div id="social-button" class="facebook">
                        <img src="Assets/images/SocialBackgroundFacebook.png" class="star" />
                        <img src="Assets/images/SocialFacebookRing_Top_Left.png" class="top-left" />
                        <img src="Assets/images/SocialFacebookRing_Top_Right.png" class="top-right" />
                        <img src="Assets/images/SocialFacebookRing_Bottom_Right.png" class="bottom-right" />
                        <img src="Assets/images/SocialFacebookRing_Bottom_Left.png" class="bottom-left" />
                        <img src="Assets/images/SocialFacebookRing_Top_Left_Hover.png" class="top-left-hover" />
                        <img src="Assets/images/SocialFacebookRing_Top_Right_Hover.png" class="top-right-hover" />
                        <img src="Assets/images/SocialFacebookRing_Bottom_Right_Hover.png" class="bottom-right-hover" />
                        <img src="Assets/images/SocialFacebookRing_Bottom_Left_Hover.png" class="bottom-left-hover" />
                    </div>
                </a>
                <a href="http://www.indiedb.com/games/seed-of-andromeda" target="_blank" title="SoA IndieDB Page'">
                    <div id="social-button" class="indiedb">
                        <img src="Assets/images/SocialBackgroundIndieDB.png" class="star" />
                        <img src="Assets/images/SocialIndieDBRing_Top_Left.png" class="top-left" />
                        <img src="Assets/images/SocialIndieDBRing_Top_Right.png" class="top-right" />
                        <img src="Assets/images/SocialIndieDBRing_Bottom_Right.png" class="bottom-right" />
                        <img src="Assets/images/SocialIndieDBRing_Bottom_Left.png" class="bottom-left" />
                        <img src="Assets/images/SocialIndieDBRing_Top_Left_Hover.png" class="top-left-hover" />
                        <img src="Assets/images/SocialIndieDBRing_Top_Right_Hover.png" class="top-right-hover" />
                        <img src="Assets/images/SocialIndieDBRing_Bottom_Right_Hover.png" class="bottom-right-hover" />
                        <img src="Assets/images/SocialIndieDBRing_Bottom_Left_Hover.png" class="bottom-left-hover" />
                    </div>
                </a>-->
            </div>
            <div id="header"></div>
            <div id="nav-bar">
                <ul id="navigation">
                    <li <?php if($page_id != null && $page_id == 1){ ?> class='active'> <a href="#"> <?php } else { ?> > <a href="http://www.seedofandromeda.com/index.php"> <?php } ?> Home</a></li>
                    <li>
                        <a href="#">About</a>
                        <ul class="dropdown">
                            <li <?php if($page_id != null && $page_id == 2){ ?> class='active' > <a href="#"> <?php } else { ?> > <a href="http://www.seedofandromeda.com/The%20Game.php"> <?php } ?> The Game</a></li>
                            <li <?php if($page_id != null && $page_id == 3){ ?> class='active' > <a href="#"> <?php } else { ?> > <a href="http://www.seedofandromeda.com/The%20Team.php"> <?php } ?> The Team</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="#">Media</a>
                        <ul class="dropdown">
                            <li <?php if($page_id != null && $page_id == 13){ ?> class='active' > <a href="#"> <?php } else { ?> > <a href="http://www.seedofandromeda.com/Downloads.php"> <?php } ?> Downloads</a></li>
                            <li <?php if($page_id != null && $page_id == 4){ ?> class='active' > <a href="#"> <?php } else { ?> > <a href="http://www.seedofandromeda.com/Screenshots.php"> <?php } ?> Screenshots</a></li>
                            <li <?php if($page_id != null && $page_id == 5){ ?> class='active' > <a href="#"> <?php } else { ?> > <a href="http://www.seedofandromeda.com/Videos.php"> <?php } ?> Videos</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="#">Community</a>
                        <ul class="dropdown">
                            <li <?php if($page_id != null && $page_id == 12){ ?> class='active' > <a href="#"> <?php } else { ?> > <a href="http://www.seedofandromeda.com/Forums/"> <?php } ?> Forums</a></li>
                            <li <?php if($page_id != null && $page_id == 6){ ?> class='active' > <a href="#"> <?php } else { ?> > <a href="http://www.seedofandromeda.com/Under%20Construction.php"> <?php } ?> Mods</a></li>
                            <li <?php if($page_id != null && $page_id == 7){ ?> class='active' > <a href="#"> <?php } else { ?> > <a href="http://www.seedofandromeda.com/IRC.php"> <?php } ?> IRC</a></li>
                            <li <?php if($page_id != null && $page_id == 14){ ?> class='active' > <a href="#"> <?php } else { ?> > <a href="http://www.reddit.com/r/seedofandromeda/" target="_blank"> <?php } ?> Reddit</a></li>
                        </ul>
                    </li>
                    <li <?php if($page_id != null && $page_id == 8){ ?> class='active' > <a href="#"> <?php } else { ?> > <a href="http://www.seedofandromeda.com/Under%20Construction.php"> <?php } ?> Store</a></li>
                    <li <?php if($page_id != null && $page_id == 9){ ?> class='active'> <a href="#"> <?php } else { ?> > <a href="http://www.seedofandromeda.com/Blogs.php"> <?php } ?> Dev Blogs</a></li>
                </ul>
    			<?php
				    if (!($MyBBI->isLoggedIn()))
                    { 
                ?> 
				<div id='accountBar' class='loggedOut'>
                    <img src='Assets/images/DefaultUser_NoSignIn_ProfImg.png' />
                    <div class='accountsName'>Not Logged In</div>
                    <div class='accountLog'>
                        <div id='break' class='five'></div>
                         <form method='post' action='/Login_Function.php'>
                            <span>Username:</span> <input type='text' style="padding-right: 0;" id="username" name='username' placeholder="Username" />
							<br /><div id='break' class='five'></div>
                            <span>Password:</span> <input type='password' style="padding-right: 0;" id='password' name='password' placeholder="Password" />
							<br />
                            <span>Remember Me:</span> <input type='checkbox' style="padding-right: 0;" name='remember' />
							<br />
                            <input type='submit' name='submit' value='submit' onClick='prepare_login();' class="left" />
                        </form>
                        <div class='register'><a href="http://seedofandromeda.com/Forums/member.php?action=register">or Register Now!</a></div>
                    </div>
                </div>
				<?php 
                    } 
                    else 
                    { 
                ?>
                <div id='accountBar'>
                    <img src='Assets/images/DefaultUser_ProfImg.png' />
                    <div class='accountsName'><?php echo $MyBBI->mybb->user['username']; ?></div>
                    <div class='accountAlerts'>99+</div>
                    <ul class='accountOptions'>
                        <li><a href='#'>My Profile</a></li>
                        <li><a href='#'>Inbox</a></li>
                        <li><a class="logout" href='/Logout.php'>Log Out</a></li>
                    </ul>
                </div>
                <?php 
                } 
                ?>
            </div>