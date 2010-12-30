<?php

include('Monitoring/includes/ro.inc');

$details = array();
$errors = array();
$search = array_merge($_GET, $_POST);

list($details['service'], $errors['service']) = $history->getHistory('service_nodegroup_history', $search);
list($details['nodegroup'], $errors['nodegroup']) = $history->getHistory('nodegroup_history', $search);
list($details['node'], $errors['node']) = $history->getHistory('service_nodegroup_node_history', $search);

$data = array_merge($details['service'], $details['nodegroup'], $details['node']);

if($errors['service'] === false && $errors['nodegroup'] === false && $errors['node'] === false) {
	print($ops->formatOutput($data));
} else {
	print($ops->formatOutput($data, '500', implode("\n", $errors)));
}
?>
