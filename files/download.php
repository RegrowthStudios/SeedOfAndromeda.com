<?php
// Kill all the caches, not neccessary but just to make sure :)
header ( "Last-Modified: " . gmdate ( "D, d M Y H:i:s" ) . " GMT" );
header ( "Cache-Control: no-store, no-cache, must-revalidate" ); // HTTP/1.1
header ( "Cache-Control: post-check=0, pre-check=0", false );
header ( "Pragma: no-cache" ); // HTTP/1.0
header ( "Expires: Sat, 26 Jul 1997 05:00:00 GMT" ); // Date in the past
                                                     // Log downloads here...

try {
	$connection = new PDO ( "mysql:host=localhost;dbname=test", "root", "" );
	$connection->setAttribute ( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	$connection->exec ( "SET NAMES utf8" );
	
	$query = $connection->prepare ( "INSERT INTO dlcounts (filename) VALUES(?) ON DUPLICATE KEY UPDATE count = count + 1" );
	$query->execute ( array (
			$_REQUEST ['file'] 
	) );
} catch ( PDOException $e ) {
}

// Redirect to the actual file, need to use cloudflare header etc later...
header ( "Location: /files/" . $_REQUEST ['file'] . "?" . $_SERVER ['REMOTE_ADDR'] );
echo "<html><head><title>File ready</title></head>
		<body>Your file is ready for download. If your browser does not redirect you,
		please click <a href=\"/files/" . $_REQUEST ['file'] . "?" . $_SERVER ['REMOTE_ADDR'] . "\">here</a>.</body></html>";