<?php
error_reporting(0);
$con = mysql_connect("soamaindb.db.11993160.hostedresource.com","soamaindb","Soa@pass123") or die('<!DOCTYPE HTML><html><head><title>SeedOfAndromeda.com</title><link rel="stylesheet" type="text/css" href="/common.css"><link rel="shortcut icon" type="image/x-icon" href="/images/favicon.ico"></head><body><table style="height: 100%; width: 100%;"><TR><TD align="center" valign="middle" style="font-size: 16px;"><img src="/images/logo.png"><BR>SeedofAndromeda.com is currently undergoing scheduled maintenance.<BR><BR>We\'ll be back in a few minutes.</TD></table></body></html>');
mysql_select_db("soamaindb", $con); 
?>