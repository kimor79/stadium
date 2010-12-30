<?php

$project = 'mon';
$title = 'Monitoring';
include('gOps/head.inc');

include('gMonitoring/www/www.inc');

$tabs = array(
	'search.php' => 'Search',
	'details.php' => 'Details',
	'history.php' => 'History',
);

?>

<body class="yui-skin-sam">

<?php

include('gMonitoring/www/body_top.inc');
$www->showSubTabs($tabs, '/nodegroups', 'search.php');

?>
