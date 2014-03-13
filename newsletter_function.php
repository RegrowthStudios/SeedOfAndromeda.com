<?php include './soa_user.php'; 

$first_name = mysql_real_escape_string($_POST['first_name']);
$last_name = mysql_real_escape_string($_POST['last_name']);
$email = mysql_real_escape_string($_POST['email']);

$result = mysql_query("SELECT email FROM newsletter_subscribers WHERE email = '$email'");
while($row = mysql_fetch_array($result))
{
$soa_email_check = $row['email'];
}

if($soa_email_check == $email){ $email_exists = true; }

$date_created = date("Y-m-d");

if($soa_email_check != true)
{
mysql_query("INSERT INTO newsletter_subscribers (email,first_name,last_name,date_created) VALUES('$email','$first_name','$last_name','$date_created')");	
$success = true;
}
else
{
$success = false;	
}
?>
<!DOCTYPE HTML>
<html lang="en">
    <head>
    
            <title>SoA - Newsletter</title>
        	<link rel="shortcut icon" type="image/x-icon" href="Assets/images/favicon.ico" />
            <link rel="stylesheet" href="soa.css" type="text/css" />
            <link rel="stylesheet" href="Normalise.css" type="text/css" />
            <link rel="stylesheet" href="Screenshots.css" type="text/css" />
            <link rel="stylesheet" href="Assets/Fonts/the_league_of_orbitron/Orbitron.css" type="text/css" />
            <link href='http://fonts.googleapis.com/css?family=Electrolize' rel='stylesheet' type='text/css'>
    
            <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js" type="text/javascript"></script>
            <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js" type="text/javascript"></script>
            <script src="soa.js"></script>
    
    </head>
    <body>
        <div id="main">
        	<?php include $_SERVER[DOCUMENT_ROOT].'/header.php'; ?>
            <div class="topimg"></div>
            <div id="content-outer">
                <div class="double-col">
            		<h3><?php if($success == true){ echo 'Thank you for signing up!'; } else { echo "An Error Occurred"; } ?></h3>
            		<br />
            		<?php 
            		    if($success == true)
            		    { 
                    ?>
            		        <a href="/" class="account_link">Go to the Homepage</a>
                    <?php
            		    } 
            		    else
                		{ 
                    ?>
            		    <span class="account_link">The email address you entered already exists in our records.</span> <a href="/" class="account_link">Go Back</a>; 
                    <?php
            		    } 
                    ?>
                </div>
        	</div>
            <div class="bottomimg"></div>
        	<?php include $_SERVER[DOCUMENT_ROOT].'/footer.php'; ?>
        </div>
    </body>
</html>