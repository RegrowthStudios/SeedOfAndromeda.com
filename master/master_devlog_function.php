<?php
session_start();
session_regenerate_id();
if (isset($_SESSION['user']) && $_SESSION['user'] != '' && $_SESSION['user'] == 'benjamin'){ $user = $_SESSION['user']; } 
else { header("Location: /master/"); exit; }

include './db_connect.php'; 

$id = mysql_real_escape_string($_POST['id']);
$content = $_POST['content'];
$title = $_POST['title'];
$date_created = date("Y-m-d h:i:s");

if($id != "")
{
mysql_query("UPDATE devlog SET title = '$title', content = '$content' WHERE id = '$id'");
}
else
{
mysql_query("INSERT INTO devlog (title,date_created,content) VALUES('$title','$date_created','$content')");	
}
header("Location: /master/master_devlog.php");
?>