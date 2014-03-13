<?php include './soa_user.php'; ?>
<!DOCTYPE HTML>
<html lang="en">

  <head>
  
    <title>SoA - Register</title>
    <link rel="stylesheet" type="text/css" href="soa.css" />
    <link rel="shortcut icon" type="image/x-icon" href="Assets/images/favicon.ico" />
    <link rel="stylesheet" href="Assets/Fonts/the_league_of_orbitron/Orbitron.css" type="text/css" />
    <link href='http://fonts.googleapis.com/css?family=Electrolize' rel='stylesheet' type='text/css'>
    
  </head>
  
  <body>
  
    <div id="main">
        <?php include $_SERVER[DOCUMENT_ROOT].'/header.php'; ?>
        <div class="topimg"></div>
        <div id="content-outer">
            <div class="double-col">
                <div class="form-outer form-double-column">
                    <h3>Create Account</h3>
                    <br />
                    <form name="create" action="/Register_Function.php" method="post">
                    <table>
                        <tr>
                            <td><h4>First Name*</h4></td>
                            <td><input type="text" maxlength="40" id="reg_first_name" name="first_name" placeholder="First Name" /></td>
                        </tr>
                        <tr>
                            <td><h4>Last Name*</h4></td>
                            <td><input type="text" maxlength="40" id="reg_last_name" name="last_name" placeholder="Last Name" /></td>
                        </tr>
                        <tr>
                            <td><h4>Email Address*</h4></td>
                            <td><input type="text" maxlength="100" id="reg_email" name="email" placeholder="Email" /></td>
                        </tr>
                        <tr>
                            <td><h4>User Name*</h4></td>
                            <td><input type="text" maxlength="40" id="reg_username" name="username" placeholder="User Name" /></td>
                        </tr>
                        <tr>
                            <td><h4>Password*</h4></td>
                            <td><input type="password" maxlength="40" id="reg_password" name="password" placeholder="Password" /></td>
                        </tr>
                        <tr>
                            <td><h4>Confirm Password*</h4></td>
                            <td><input type="password" maxlength="40" id="reg_confirm_password" name="confirm_password" placeholder="Password" /></form></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td><input style="float: right;" type="button" onClick="validate_create();" value="Create Account"/></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php include $_SERVER[DOCUMENT_ROOT].'/footer.php'; ?>
      
  </body>
  
</html>