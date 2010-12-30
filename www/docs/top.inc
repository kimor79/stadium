<?php

$project = 'mon';
$title = 'Monitoring';
include('Ops/head.inc');

include('Monitoring/www/www.inc');

$tabs = array(
	'changelog.php' => 'Changelog',
);

?>

<body class="yui-skin-sam">

<?php

include('Monitoring/www/body_top.inc');
$www->showSubTabs($tabs, '/docs', 'changelog.php');

?>
