<?php

define('IN_MYBB', 1);
	
require_once 'Forums/global.php';
require_once 'class.MyBBIntegrator.php';
	
$MyBBI = new MyBBIntegrator($mybb, $db, $cache, $plugins, $lang, $config);
header("Content-type: text/plain");
//Uncomment to test:
//var_dump($MyBBI->mybb->user);