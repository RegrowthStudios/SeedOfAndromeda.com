<?php include './soa_user.php';

$content = mysql_real_escape_string(htmlentities($_POST['comment']));
$devlog_id = mysql_real_escape_string($_POST['devlog_id']);
$user_id = $soa_user_id;
$date_created = date("Y-m-d h:i:s");

mysql_query("INSERT INTO devlog_comments (devlog_id,user_id,date_created,content) VALUES('$devlog_id','$user_id','$date_created','$content')");	

header("Location: /devlog.php#log".$devlog_id);

?>