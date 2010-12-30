<?php

include('Monitoring/includes/ro.inc');

$search = array_merge($_GET, $_POST);
foreach($ops->default_api_params as $param) {
	unset($search[$param]);
}

list($data, $error) = $mon->listInheritedServices($search);

if($error === false) {
	print($ops->formatOutput($data));
} else {
	print($ops->formatOutput($data, '500', $error));
}
?>
