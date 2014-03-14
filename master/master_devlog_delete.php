<?php
session_start();
session_regenerate_id();
if (isset($_SESSION['user']) && $_SESSION['user'] != '' && $_SESSION['user'] == 'benjamin'){ $user = $_SESSION['user']; } 
else { header("Location: /master/"); exit; }

include './db_connect.php'; 

$id = $_GET['id'];

mysql_query("DELETE FROM devlog WHERE id = '$id'");

header("Location: /master/master_devlog.php");
?>