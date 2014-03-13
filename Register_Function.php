<?php include './soa_user.php';

	require "password_compat/lib/password.php";

	$first_name = mysql_real_escape_string($_POST['reg_first_name']);
	$last_name = mysql_real_escape_string($_POST['reg_last_name']);
	$email = mysql_real_escape_string($_POST['reg_email']);
	$username = mysql_real_escape_string($_POST['reg_username']);
	$password = password_hash(mysql_real_escape_string($_POST['reg_password']), PASSWORD_DEFAULT);
	
	$result = mysql_query("SELECT username,email FROM soa_users WHERE email = '$email' OR username = '$username'");
	while($row = mysql_fetch_array($result))
	{
		$soa_email_check = $row['email'];
		$soa_username_check = $row['username'];
	}

	if($soa_email_check == $email){ $email_exists = true; }
	if($soa_username_check == $username){ $username_exists = true; }

	$date_created = date("Y-m-d");

	if(!$email_exists && !$username_exists)
	{
		mysql_query("INSERT INTO soa_users (username,email,first_name,last_name,password,date_created,updated_pass) VALUES('$username','$email','$first_name','$last_name','$password','$date_created','1')");
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

    
        <title>SoA - Register</title>
		<link rel="shortcut icon" type="image/x-icon" href="Assets/images/favicon.ico" />
        <link rel="stylesheet" href="soa.css" type="text/css" />
        <link rel="stylesheet" href="Normalise.css" type="text/css" />
        <link rel="stylesheet" href="Assets/Fonts/the_league_of_orbitron/Orbitron.css" type="text/css" />
        <link href='http://fonts.googleapis.com/css?family=Electrolize' rel='stylesheet' type='text/css'>

  </head>

  <body>

    <div id="main">
        <?php $page_id = 999; include $_SERVER[DOCUMENT_ROOT].'/header.php'; ?>
        <div class="topimg"></div>
        <div id="content-outer">
            <div class="double-col">
                <h3><?php if($success == true){ ?> Your account has been successfully created! <?php } else { ?> An Error Occurred! <?php } ?></h3>
                <br />
                <?php 
                    if($success == true)
                    { 
                ?>
                    <h4 style="text-align:center;"><a href="/Login.php">Log in here</a></h4>
                <?php
                    } 
                    else
                    { 
                        if($email_exists)
                        { 
                ?>
                    <h4 style="text-align:center;">
                        The email address you entered is already in use!
                    </h4>
                <?php
                        }
                        if($username_exists)
                        { 
                ?>
                    <h4 style="text-align:center;">
                        The user name you entered is already in use!
                    </h4>
                <?php
                        }
						if($email_exists || $username_exists)
						{
				?>
                        <a href="/Register.php">Go Back</a>
				<?php
						}
                    } 
                ?>
            </div>
        </div>
        <div class="bottomimg"></div>
    </div>
    <?php include $_SERVER[DOCUMENT_ROOT].'/footer.php'; ?>

  </body>

</html>