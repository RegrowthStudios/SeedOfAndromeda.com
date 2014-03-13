<?php
session_start();
session_regenerate_id();
if (isset($_SESSION['user']) && $_SESSION['user'] != '' && $_SESSION['user'] == 'benjamin'){ $user = $_SESSION['user']; } 
else { header("Location: /master/"); exit; }

include './db_connect.php'; 

$page = $_POST['page'];
$content = $_POST['content'];

mysql_query("UPDATE pages SET content = '$content' WHERE page = '$page'");

header("Location: /master/master_".$page.".php");
?>