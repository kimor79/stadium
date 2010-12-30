<?php

$project = 'mon';
$title = 'Monitoring';
include('Ops/head.inc');

include('Monitoring/www/www.inc');

$tabs = array(
	'search.php' => 'Search',
	'details.php' => 'Details',
	'history.php' => 'History',
);

?>

<body class="yui-skin-sam">

<?php

include('Monitoring/www/body_top.inc');
$www->showSubTabs($tabs, '/services', 'search.php');

?>
