<?php

include('Monitoring/includes/ro.inc');

$details = array();
$errors = array();
$search = array_merge($_GET, $_POST);

list($details['service'], $errors['service']) = $history->getHistory('service_node_history', $search);
list($details['node'], $errors['node']) = $history->getHistory('node_history', $search);
list($details['nodegroup'], $errors['nodegroup']) = $history->getHistory('service_nodegroup_node_history', $search);

$data = array_merge($details['service'], $details['node'], $details['nodegroup']);

if($errors['service'] === false && $errors['node'] === false && $errors['nodegroup'] === false) {
	print($ops->formatOutput($data));
} else {
	print($ops->formatOutput($data, '500', implode("\n", $errors)));
}
?>
