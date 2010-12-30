<?php

include_once('Ops/includes/mysql.inc');
include_once('Ops/classes/history.class');
include_once('Ops/classes/notes.class');
include_once('Monitoring/classes/mon.class');
include_once('Monitoring/classes/opsapi_ext.class');

$history = new OpsHistory();
$notes = new OpsNotes();
$ops = new OpsApiMon();
$mon = new OpsMon();

if(!$mysql) {
	$mysql = mysql_connect($mysql_host, $mysql_user, $mysql_password);
	mysql_select_db($mysql_db);
}

?>
