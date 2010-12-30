<?php

include_once('Ops/crypt.class');
$crypt = new OpsCrypt();

$mysql_host = 'localhost';
$mysql_db = 'monitoring';
$mysql_user = 'monapirwuser';
$mysql_password = 'foobar';

include_once('Monitoring/includes/main.inc');
?>
