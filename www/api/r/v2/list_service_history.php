<?php

include('Monitoring/includes/ro.inc');

$search = array_merge($_GET, $_POST);

list($data, $error) = $history->getHistory('service_history', $search);

if($error === false) {
	print($ops->formatOutput($data));
} else {
	print($ops->formatOutput($data, '500', $error));
}
?>
