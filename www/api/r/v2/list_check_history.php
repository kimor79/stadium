<?php

include('Monitoring/includes/ro.inc');

$params = array();
$search = array_merge($_GET, $_POST);
foreach($ops->default_api_params as $param) {
	if(array_key_exists($param, $search)) {
		$params[$param] = $search[$param];
	}

	unset($search[$param]);
}

list($data, $count, $error) = $mon->listCheckHistory($search, $params);

if($error === false) {
	print($ops->formatOutput($data, '200', NULL, $count));
} else {
	print($ops->formatOutput($data, '500', $error, $count));
}

?>
