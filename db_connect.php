<?php
try {
	$connection = new PDO ( "mysql:host=localhost;dbname=soamaindb", "soamaindb", "Soa@pass123" );
	$connection->setAttribute ( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	$connection->exec ( "SET NAMES utf8" );
} catch ( PDOException $e ) {
}
?>