<?php
require_once("_index1.php");
if(isset($_GET['location']))
	$location = $_GET['location'];
else
	$location = "";
if(isset($_GET['offset']))
	$how_offset = $_GET['offset'];
else
	$how_offset = 0;
if(isset($_GET['show']))
	$how_many = $_GET['show'];
else
	$how_many = 10;

if ( (isset($_GET['logs']) && $_GET['logs'] == 'all') || !isset($_GET['logs']) ){
	$chat_location = substr(preg_replace("[^A-Za-z0-9]", "",$CURRENT_DIR),21);
	$MYSQL_CONNECT = mysql_connect($DB_PARAMATERS['db_host'],$DB_PARAMATERS['db_user'],$DB_PARAMATERS['db_pass'])or die("CAN NOT CONNECT");
	mysql_select_db($DB_PARAMATERS['db_name'], $MYSQL_CONNECT);
	$q2 = "SELECT * FROM `webchat_lines` ORDER BY `id` DESC LIMIT ".$how_offset.", ".$how_many;		
	$result = mysql_query($q2);
	if ($result){
		$chat_posts = mysql_num_rows($result);
		$i=0;
		while ($i < $chat_posts) {
			$chat_author = mysql_result($result,$i,"author");
			$chat_gravatar = mysql_result($result,$i,"gravatar");
			$chat_text = htmltrim(nl2br(mysql_result($result,$i,"text")));
			$chat_ts = mysql_result($result,$i,"ts");
			$i++;
			echo "<div class='log_item'><span class='log_ts'>".$chat_ts."</span>".$chat_text."</div>";
		}
	}
}else{
	$chat_location = $location ;
	$MYSQL_CONNECT = mysql_connect($DB_PARAMATERS['db_host'],$DB_PARAMATERS['db_user'],$DB_PARAMATERS['db_pass'])or die("CAN NOT CONNECT");
	mysql_select_db($DB_PARAMATERS['db_name'], $MYSQL_CONNECT);
	$q2 = "SELECT * FROM `webchat_lines` WHERE `gravatar` = '".$_GET['logs']."'ORDER BY `id` DESC LIMIT ".$how_offset.", ".$how_many;		
	$result = mysql_query($q2);
	if ($result){
		$chat_posts = mysql_num_rows($result);
		$i=0;
		while ($i < $chat_posts) {
			$chat_author = mysql_result($result,$i,"author");
			$chat_gravatar = mysql_result($result,$i,"gravatar");
			$chat_text = htmltrim(nl2br(mysql_result($result,$i,"text")));
			$chat_ts = mysql_result($result,$i,"ts");
			$i++;
			echo "<div class='log_item'><span class='log_ts'>".$chat_ts."</span>".$chat_text."</div>";
		}
	}
}
?>
