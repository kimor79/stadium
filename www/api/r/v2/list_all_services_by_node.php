<?php

include('Monitoring/includes/ro.inc');

$details = array();
$errors = array();
$nodegroups = array();
$nodes = array();
$search = array_merge($_GET, $_POST);

if(array_key_exists('node', $search)) {
	if(is_array($search['node'])) {
		$nodes = $search['node'];
	} else {
		$nodes[] = $search['node'];
	}
}

if(array_key_exists('nodegroup', $search)) {
	list($nodegroups, $error) = $mon->listNodegroupNodes(array(
		'nodegroup' => $search['nodegroup'],
	));

	if($error !== false) {
		$errors[] = $error;
	}

	foreach($nodegroups as $nodegroup) {
		$nodes[] = $nodegroup['node'];
	}

	if(empty($nodes)) {
		print($ops->formatOutput(array(), '400', 'No such nodegroup'));
		exit(0);
	}
}

if(empty($nodes)) {
	list($all_nodes, $error) = $mon->listNodes();
	foreach($all_nodes as $node) {
		$nodes[] = $node['node'];
	}

	if($error !== false) {
		$errors[] = $error;
	}
}

list($details['node'], $error) = $mon->listServicesNodes(array(
	'node' => $nodes,
));

if($error !== false) {
	$errors[] = $error;
}

list($details['inherited'], $error) = $mon->listInheritedServices(array(
	'node' => $nodes,
));

if($error !== false) {
	$errors[] = $error;
}

$data = array_merge($details['node'], $details['inherited']);

if(empty($errors)) {
	print($ops->formatOutput($data));
} else {
	print($ops->formatOutput($data, '500', implode("\n", $errors)));
}
?>
