<?php
try {
	$connection = new PDO ( "mysql:host=soamaindb.db.11993160.hostedresource.com;dbname=soamaindb", "soamaindb", "Soa@pass123" );
	$connection->setAttribute ( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	$connection->exec ( "SET NAMES utf8" );
} catch ( PDOException $e ) {
}
?>