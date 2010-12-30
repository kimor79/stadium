<?php

include('Monitoring/includes/rw.inc');

$do_delete = 0;
$key_opts = array();
$missing = array();
$new_details = array();
$nodegroup_nodes = array();
$old_details = array();
$query;
$query_opts = array();
$service_details = array();
$s_message = '';

if(empty($_POST['service_id']) || !ctype_digit($_POST['service_id'])) {
	$missing[] = 'Missing/Invalid service ID';
}

if($ops->isBlank($_POST['nodegroup'])) {
	$missing[] = 'Missing nodegroup';
}

if($ops->isBlank($_POST['node'])) {
	$missing[] = 'Missing node';
}

if(!empty($missing)) {
	print($ops->formatWriteOutput('400', implode("\n", $missing)));
	exit(0);
}

list($nodegroup_nodes, $junk) = $mon->listNodegroupNodes(array(
	'node' => $_POST['node'],
	'nodegroup' => $_POST['nodegroup'],
));

if(count($nodegroup_nodes) < 1) {
	print($ops->formatWriteOutput('400', 'Node is not part of this nodegroup'));
	exit(0);
}

list($service_details, $junk) = $mon->listServicesNodegroups(array(
	'nodegroup' => $_POST['nodegroup'],
	'service_id' => $_POST['service_id'],
));

if(count($service_details) < 1) {
	print($ops->formatWriteOutput('400', 'Service is not configured on this nodegroup'));
	exit(0);
}

$key_opts[] = sprintf("`node` = '%s'", mysql_real_escape_string($_POST['node']));
$key_opts[] = sprintf("`nodegroup` = '%s'", mysql_real_escape_string($_POST['nodegroup']));
$key_opts[] = sprintf("`service_id` = '%d'", mysql_real_escape_string($_POST['service_id']));

if(isset($_POST['enabled'])) {
	if($ops->isBlank($_POST['enabled'])) {
		$do_delete++;
		$query_opts[] = '`enabled` = DEFAULT';
	} else {
		if($ops->isYesNo($_POST['enabled'], true) == false) {
			$query_opts[] = '`enabled` = 0';
		} else {
			$query_opts[] = '`enabled` = 1';
		}
	}
}

if(isset($_POST['notifications'])) {
	if($ops->isBlank($_POST['notifications'])) {
		$do_delete++;
		$query_opts[] = '`notifications` = DEFAULT';
	} else {
		if($ops->isYesNo($_POST['notifications'], true) == false) {
			$query_opts[] = '`notifications` = 0';
		} else {
			$query_opts[] = '`notifications` = 1';
		}
	}
}

list($old_details, $junk) = $mon->listNodegroupNodeOverrides(array(
	'node' => $_POST['node'],
	'nodegroup' => $_POST['nodegroup'],
	'service_id' => $_POST['service_id'],
));

if($do_delete == 2) {
	$query = 'DELETE FROM `service_nodegroup_nodes` WHERE ';
	$query .= implode(' AND ', $key_opts);
	$s_message = 'deleted';
} else {
	$post_query = implode(', ', $query_opts);
	$query_opts = array_merge($query_opts, $key_opts);
	$query = 'INSERT INTO `service_nodegroup_nodes` SET ';
	$query .= implode(', ', $query_opts);
	$query .= ' ON DUPLICATE KEY UPDATE ';
	$query .= $post_query;
	$s_message = 'updated';
}

$result = do_mysql_query($query);

if($result[0] !== true) {
	print($ops->formatWriteOutput('500', $result[1]));
	exit(0);
}

list($new_details, $junk) = $mon->listNodegroupNodeOverrides(array(
	'node' => $_POST['node'],
	'nodegroup' => $_POST['nodegroup'],
	'service_id' => $_POST['service_id'],
));

if(!empty($old_details)) {
	$old_details = reset($old_details);
}

if(!empty($new_details)) {
	$new_details = reset($new_details);
}

$h_error = $history->updateHistory(array(
	'history_table' => 'service_nodegroup_node_history',
	'keys' => array(
		'node' => $_POST['node'],
		'nodegroup' => $_POST['nodegroup'],
		'service_id' => $_POST['service_id'],
	),
	'old_data' => $old_details,
	'new_data' => $new_details,
	'source_tables' => array('service_nodegroup_nodes'),
));

if(!empty($h_error)) {
	$s_message .= ' but unable to add history: ' . $h_error;
}

$message = sprintf("%s/%s %s %s", $_POST['nodegroup'], $_POST['node'], $_POST['service_id'], $s_message);
print($ops->formatWriteOutput('200', $message, $new_details));
?>
