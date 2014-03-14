<?php
session_start();
session_regenerate_id();
if (isset($_SESSION['user']) && $_SESSION['user'] != '' && $_SESSION['user'] == 'benjamin'){ $user = $_SESSION['user']; } 
else { header("Location: /master/"); exit; }

include './db_connect.php';

?>
<html>
<head>
<title>Seed of Andromeda Master Admin</title>
<link rel="shortcut icon" type="image/x-icon" href="/images/favicon.ico">
</head>
<body style="margin: 0px; overflow-x: hidden;" id="body" height="100%">
<table bgcolor="#6b6766" cellpadding="0" cellspacing="0" width="100%" height="100%">
<TR>
<TD valign="top" align="center">
	<table bgcolor="white" cellpadding="0" cellspacing="0" height="100%" width="956">
    <TR>
    <TD width="15"></TD>
    <TD width="926" valign="top">
    
    	<table cellpadding="0" cellspacing="0" width="926" height="100%" style="empty-cells: show; border-collapse: collapse; margin-left: 15px; margin-right: 15px;">
        <TR>
        <TD style="border-bottom: dashed 1px black;">
        	
            <table cellpadding="0" cellspacing="0"width="100%" style="margin-top: 15px; margin-bottom: 10px;">
            <TR>
        	<TD valign="top"><img src="/images/logo.png"></TD>
        	<TD align="right" valign="bottom"><font style="font-size: 14px; font-family: arial;">MASTER ADMINISTRATION</font></TD>
        	</TR>
        	</table>
            
       	</TD>
        </TR>
        <TR>
        <TD style="padding-top: 5px; padding-bottom: 20px; border-bottom: 1px solid black;">
        
        	<table cellpadding="0" cellspacing="0" width="100%">
            <TR>
            <TD align="right"><a href="/master/master_logout.php" style="font-size: 12px; font-weight: bold; font-family: arial; color: #084482; text-decoration: none;">Log Out</a></font></TD>
            </TR>
            </table>
        
        </TD>
        </TR>
        <TR>
        <TD height="100%">
        
        	<table cellpadding="0" cellspacing="0" height="100%">
            <TR>
            <TD valign="top" style="border-right: 1px solid black;">
            
            	<table cellpadding="0" cellspacing="0" width="122">
				<TR>
                <TD onClick="window.location.href='/master/master_devlog.php'" style="cursor: pointer; cursor: hand; border-bottom: 1px solid #666666; padding-left: 5px;" height="30" width="100%"><font style="font-size: 12px; font-family: arial;">Developer Log</font></TD>
                </TR>
				<TR>
                <TD onClick="window.location.href='/master/master_screenshots.php'" style="cursor: pointer; cursor: hand; border-bottom: 1px solid #666666; padding-left: 5px;" height="30" width="100%"><font style="font-size: 12px; font-family: arial;">Screenshots</font></TD>
                </TR>
				<TR>
                <TD onClick="window.location.href='/master/master_videos.php'" style="cursor: pointer; cursor: hand; border-bottom: 1px solid #666666; padding-left: 5px;" height="30" width="100%"><font style="font-size: 12px; font-family: arial;">Videos</font></TD>
                </TR>
				<TR>
                <TD onClick="window.location.href='/master/master_newsletter.php'" style="font-weight: bold; cursor: pointer; cursor: hand; border-bottom: 1px solid #666666; padding-left: 5px;" height="30" width="100%"><font style="font-size: 12px; font-family: arial;">Newsletter</font></TD>
                </TR>
				<TR>
                <TD onClick="window.location.href='/master/master_about.php'" style="cursor: pointer; cursor: hand; border-bottom: 1px solid #666666; padding-left: 5px;" height="30" width="100%"><font style="font-size: 12px; font-family: arial;">About</font></TD>
                </TR>
                </table>
            
            </TD>
            <TD width="804" height="100%" valign="top">
            
            	<table cellpadding="0" cellspacing="0" width="804" bgcolor="#f5f5f5" height="100%" valign="top">
                <TR>
                <TD valign="top" style="padding: 15px;">
					<table border="1" width="100%">
					<TR>
					<TD>EMAIL</TD>
					<TD>FIRST NAME</TD>
					<TD>LAST NAME</TD>
					<TD>DATE</TD>
					</TR>
					<?php
					$result = mysql_query("SELECT * FROM newsletter_subscribers ORDER BY date_created DESC");
					while($row = mysql_fetch_array($result))
					{
					echo '<TR>
					<TD>'.$row['email'].'</TD>
					<TD>'.$row['first_name'].'</TD>
					<TD>'.$row['last_name'].'</TD>
					<TD>'.$row['date_created'].'</TD>
					</TR>';
					}
					?>
					</table>
                </TD>
                </TR>
                <TR>
                <TD height="100%"></TD>
                </TR>
                </table>
            
            </TD>
            </TR>
            </table>
        
        </TD>
        </TR>
        </table>
    
    </TD>
    <TD width="15"></TD>
    </TR>
    </table>
    
</TD>
</TR>
</table>
</body>