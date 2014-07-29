<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo $pagetitle ?></title>
    <link rel="shortcut icon" type="image/x-icon" href="/Assets/images/favicon.ico" />
    <link rel="stylesheet" href="/styles/normalise.css" type="text/css" />
    <link rel="stylesheet" href="/styles/soa.min.css" type="text/css" />
    <link rel="stylesheet" href="/Assets/Fonts/the_league_of_orbitron/Orbitron.css" type="text/css" />
    <link href='http://fonts.googleapis.com/css?family=Electrolize' rel='stylesheet' type='text/css' />
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js" type="text/javascript"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js" type="text/javascript"></script>
    <script src="/scripts/lightbox-2.6.js"></script>
    <link href="/styles/lightbox.css" rel="stylesheet" />
    <script src="/scripts/soa.js"></script>
    <script src="/scripts/jquery.sticky.js"></script>
    <script>
        $(document).ready(function () {
            $("#nav-bar").sticky({ topSpacing: 0 });
        });
    </script>
    <?php
    $pagestyle = "styles/pages/" . str_replace ( ".php", ".min.css", $pagename );
    if (file_exists ( $pagestyle )) {
	    echo '<link rel="stylesheet" href="/' . $pagestyle . '?ver=13" type="text/css" />';
    }
    $pagestyle = "scripts/pages/" . str_replace ( ".php", ".js", $pagename );
    if (file_exists ( $pagestyle )) {
	    echo '<script src="/' . $pagestyle . '?ver=3"></script>';
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
                                <li><a href="/screenshots">Screenshots</a></li>
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
                    <img class="account-image" src="/community/avatar.php?userid=1&size=s" />
                    <div class="account-overview">
                        <span class="account-name">PsychoticLeprechaun</span>
                        <span class="account-alerts">0</span>
                    </div>
                    <div class="account-options-wrapper">
                        <ul class="account-options">
                            <li><a href="http://www.seedofandromeda.com/community/account/alerts">Alerts (0)</a></li>
                            <li><a href="http://www.seedofandromeda.com/community/conversations/">Inbox (0)</a></li>
                            <li><a href="http://www.seedofandromeda.com/community/account/">My Account</a></li>
                            <li><a class="logout" href="http://www.seedofandromeda.com/community/logout/?_xfToken=1%2C1403566850%2C111dbf3c78501b758fde404460ba519719eee3a4&redirect=%2F">Log Out</a></li>
                        </ul>
                    </div>
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