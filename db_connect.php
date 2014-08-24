<?php
try {
	$connection = new PDO ( "mysql:host=127.0.0.1;dbname=seedofandromeda_soamaindb", "seedofandromeda", "qugehe9ev" );
	$connection->setAttribute ( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	$connection->exec ( "SET NAMES utf8" );
} catch ( PDOException $e ) {
}
?>