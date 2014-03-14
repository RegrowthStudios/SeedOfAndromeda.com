<?php
session_start();

$username = $_POST['username'];
$password = md5($_POST['password']);

if($username == "benjamin" && $password == "7921230445f43e82d14b553a768214db"){ $_SESSION['user'] = $username; }

if (isset($_SESSION['user']) && $_SESSION['user'] != '')
{
header("location: /master/master_devlog.php"); exit;
}
else
{
header("location: /master/"); exit;
}
?>