<?php

include('Monitoring/includes/rw.inc');

$details = array();
$nodegroup_details = array();
$query = 'DELETE FROM `service_nodegroups` WHERE ';
$query_opts = array();
$service_details = array();

if(!$ops->isYesNo($_POST['delete'], false)) {
	print($ops->formatWriteOutput('400', 'delete=yes also needs to be passed to this api'));
	exit(0);
}

if(isset($_POST['service_id']) && ctype_digit((string)$_POST['service_id'])) {
	list($service_details, $junk) = $mon->listServices(array('service_id' => $_POST['service_id']));
	$service_details = reset($service_details);
}

if(empty($service_details)) {
	print($ops->formatWriteOutput('400', 'Missing/Unknown service ID'));
	exit(0);
}

if(!$ops->isBlank($_POST['nodegroup'])) {
	list($nodegroup_details, $junk) = $mon->listNodegroups(array('nodegroup' => $_POST['nodegroup']));
	$nodegroup_details = reset($nodegroup_details);
}

if(empty($nodegroup_details)) {
	print($ops->formatWriteOutput('400', 'Missing/Unknown nodegroup'));
	exit(0);
}

list($details, $junk) = $mon->listServicesNodegroups(array(
	'service_id' => $service_details['service_id'],
	'nodegroup' => $nodegroup_details['nodegroup'],
));

if(empty($details)) {
	print($ops->formatWriteOutput('400', 'Service not configured for that nodegroup'));
	exit(0);
}

$query_opts[] = sprintf("`service_id` = '%d'", $service_details['service_id']);
$query_opts[] = sprintf("`nodegroup` = '%s'", $nodegroup_details['nodegroup']);

$query .= implode(' AND ', $query_opts);
$query .= $post_query;
$result = do_mysql_query($query);

if($result[0] !== true) {
	print($ops->formatWriteOutput('500', $result[1]));
	exit(0);
}

if(!empty($details)) {
	$details = reset($details);
}

$h_error = $history->updateHistory(array(
	'history_table' => 'service_nodegroup_history',
	'keys' => array('service_id' => $service_details['service_id'], 'nodegroup' => $nodegroup_details['nodegroup']),
	'old_data' => $details,
	'new_data' => array(),
	'source_tables' => array('service_nodegroups'),
));

if(!empty($h_error)) {
	$s_message .= ' but unable to add history: ' . $h_error;
}

$message = sprintf("%s removed from %s", $nodegroup_details['nodegroup'], $service_details['service_name']);
print($ops->formatWriteOutput('200', $message));
?>
