<?php 
session_start();
session_regenerate_id();

if (isset($_SESSION['soa_user2'])){ $soa_user = $_SESSION['soa_user2']; } 
else
{ $soa_user = ""; }

include './db_connect.php';

$result = mysql_query("SELECT id,email,username FROM soa_users WHERE username = '$soa_user'");
while($row = mysql_fetch_array($result))
{
$soa_user_id = $row['id'];
$soa_email = $row['email'];
$soa_username = $row['username'];
}

if($soa_email == "" || $_SESSION['soa_user2'] == ""){ $soa_user = ""; }

?>