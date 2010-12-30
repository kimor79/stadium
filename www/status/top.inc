<?php

$project = 'mon';
$title = 'Monitoring';
include('Ops/head.inc');

include('Monitoring/www/www.inc');

$tabs = array(
	'current.php' => 'Current',
	'history.php' => 'History',
);

?>

<body class="yui-skin-sam">

<?php

include('Monitoring/www/body_top.inc');
$www->showSubTabs($tabs, '/status', 'current.php');

?>
