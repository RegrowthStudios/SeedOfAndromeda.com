<?php
/*
 * This file is for the SoA Updater. Here is the file format as understood by the launcher:
 *  Integer, SoA Version
 *  String, SoA zip URL
 *  Integer, SoAUpdater version
 *  String SoAUpdater download URL
 */
// Kill all the caches, not neccessary but just to make sure :)
header ( "Last-Modified: " . gmdate ( "D, d M Y H:i:s" ) . " GMT" );
header ( "Cache-Control: no-store, no-cache, must-revalidate" ); // HTTP/1.1
header ( "Cache-Control: post-check=0, pre-check=0", false );
header ( "Pragma: no-cache" ); // HTTP/1.0
header ( "Expires: Sat, 26 Jul 1997 05:00:00 GMT" ); // Date in the past

header ( "Content-type: text/plain" );

// if (isset ( $_REQUEST ['version'] )) {
// 	// Conditions on version number can be used in the future
// }
?>
100
http://www.soatest.local/files/game/SoA_firetest0.zip
2
http://www.soatest.local/files/updater/SOAUpdater.exe