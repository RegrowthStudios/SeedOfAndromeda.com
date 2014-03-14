<?php
session_start();
session_regenerate_id();
if (isset($_SESSION['user']) && $_SESSION['user'] != '' && $_SESSION['user'] == 'benjamin'){ $user = $_SESSION['user']; } 
else { header("Location: /master/"); exit; }

include './db_connect.php';

$result = mysql_query("SELECT * FROM pages WHERE page = 'screenshots'");
while($row = mysql_fetch_array($result))
{
$content = $row['content'];
}

?>
<html>
<head>
<title>Seed of Andromeda Master Admin</title>
<link rel="shortcut icon" type="image/x-icon" href="/images/favicon.ico">
<script src="//tinymce.cachefly.net/4.0/tinymce.min.js"></script>
<script>
        tinymce.init({selector:'textarea'
		,plugins:["advlist autolink lists link image charmap print preview hr anchor pagebreak",
        "searchreplace wordcount visualblocks visualchars code fullscreen",
        "insertdatetime media nonbreaking save table contextmenu directionality",
        "emoticons template paste textcolor"],relative_urls: false});

</script>
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
                <TD onClick="window.location.href='/master/master_screenshots.php'" style="font-weight: bold; cursor: pointer; cursor: hand; border-bottom: 1px solid #666666; padding-left: 5px;" height="30" width="100%"><font style="font-size: 12px; font-family: arial;">Screenshots</font></TD>
                </TR>
				<TR>
                <TD onClick="window.location.href='/master/master_videos.php'" style="cursor: pointer; cursor: hand; border-bottom: 1px solid #666666; padding-left: 5px;" height="30" width="100%"><font style="font-size: 12px; font-family: arial;">Videos</font></TD>
                </TR>
				<TR>
                <TD onClick="window.location.href='/master/master_newsletter.php'" style="cursor: pointer; cursor: hand; border-bottom: 1px solid #666666; padding-left: 5px;" height="30" width="100%"><font style="font-size: 12px; font-family: arial;">Newsletter</font></TD>
                </TR>
				<TR>
                <TD onClick="window.location.href='/master/master_about.php'" style="cursor: pointer; cursor: hand; border-bottom: 1px solid #666666; padding-left: 5px;" height="30" width="100%"><font style="font-size: 12px; font-family: arial;">About</font></TD>
                </TR>
                </table>
            
            </TD>
            <TD width="804" height="100%" valign="top">
            
            	<table cellpadding="0" cellspacing="0" width="804" bgcolor="#f5f5f5" height="100%" valign="top">
                <TR><form action="/master/master_content_function.php" method="post">
                <TD valign="top" style="padding: 15px;">
				<textarea id="content" name="content" style="height: 300px;"><?php echo $content; ?></textarea>
				<input type="hidden" value="screenshots" name="page">
				<BR>    
				<input type="submit" value="Save">
				</form>
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