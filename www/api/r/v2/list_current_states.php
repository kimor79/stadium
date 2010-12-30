<?php

include('Monitoring/includes/ro.inc');

$ops->use_custom_sort = 'current';
$search = array_merge($_GET, $_POST);

$acks = array();
$combine = $ops->isYesNo($search['combine'], true);
$current_states = array();
$data = array();
$details = array();
$errors = array();
$nodes_exclude = array();
$nodes_priority = array(
	'p1' => array(),
	'p2' => array(),
	'p3' => array(),
);
$show_acknowledgements = $ops->isYesNo($search['show_acknowledgements'], true);
$show_notifications = $ops->isYesNo($search['show_notifications'], false);

foreach($ops->default_api_params as $param) {
	unset($search[$param]);
}

//
// Nodes to exclude
//

list($not_active, $junk) = $mon->listNodegroupNodes(array('nodegroup' => 'monitoring.nodes.not_active'));
foreach($not_active as $value) {
	$nodes_exclude[] = $value['node'];
}

if(!$ops->isYesNo($search['show_maintenance'], false)) {
// By default we exclude nodes in maintenance. Setting show_maintenance
// to a true value skips this part.
	list($maintenance, $junk) = $mon->listNodegroupNodes(array('nodegroup' => 'monitoring.nodes.in_maintenance'));
	foreach($maintenance as $value) {
		$nodes_exclude[] = $value['node'];
	}
}

if(!$ops->isYesNo($search['show_not_production'], false)) {
// By default we exclude nodes not in production. Setting show_not_production
// to a true value skips this part.
	list($not_production, $junk) = $mon->listNodegroupNodes(array('nodegroup' => 'monitoring.nodes.not_production'));
	foreach($not_production as $value) {
		$nodes_exclude[] = $value['node'];
	}
}

//
// Misc settings

unset($search['combine']);
unset($search['show_acknowledgements']);
unset($search['show_maintenance']);
unset($search['show_not_production']);
unset($search['show_notifications']);

list($nodes_p1, $junk) = $mon->listNodegroupNodes(array('nodegroup' => 'monitoring.nodes.priority.1'));
foreach($nodes_p1 as $node) {
	$nodes_priority['p1'][] = $node['node'];
}

list($nodes_p2, $junk) = $mon->listNodegroupNodes(array('nodegroup' => 'monitoring.nodes.priority.2'));
foreach($nodes_p2 as $node) {
	$nodes_priority['p2'][] = $node['node'];
}

list($nodes_p3, $junk) = $mon->listNodegroupNodes(array('nodegroup' => 'monitoring.nodes.priority.3'));
foreach($nodes_p3 as $node) {
	$nodes_priority['p3'][] = $node['node'];
}

//
// Get current states
//

list($details['node'], $errors['node']) = $mon->listCurrentStatesNodes($search);
list($details['nodegroup'], $errors['nodegroup']) = $mon->listCurrentStatesNodegroupNodes($search);

$keys = array_merge(array_keys($details['node']), array_keys($details['nodegroup']));
$keys = array_unique($keys);

foreach($keys as $key) {
	$t_data = array();

	if(array_key_exists($key, $details['node'])) {
		$t_data = array_merge($t_data, $details['node'][$key]);
	}

	if(array_key_exists($key, $details['nodegroup'])) {
		$t_data = array_merge($t_data, $details['nodegroup'][$key]);
	}

	if($t_data['nodes.enabled'] != 1) {
		continue;
	}

	if(array_key_exists('nodegroups.enabled', $t_data) && $t_data['nodegroups.enabled'] != 1) {
		continue;
	}

	if(in_array($t_data['current_states.node'], $nodes_exclude)) {
		continue;
	}

	if(!$show_notifications) {
		if($t_data['nodes.notifications'] != 1) {
			continue;
		}

		if(array_key_exists('nodegroups.enabled', $t_data) && $t_data['nodegroups.notifications'] != 1) {
			continue;
		}
	}

	if(!is_null($t_data['acknowledgements.state']) && $t_data['current_states.state'] > $t_data['acknowledgements.state']) {
		foreach(get_column_names('acknowledgements') as $col) {
			$t_data['acknowledgements.' . $col] = NULL;
		}
	}

	if(!$show_acknowledgements) {
		if(!is_null($t_data['acknowledgements.service_id'])) {
			continue;
		}
	}

	$check_attempts = $t_data['services.check_attempts'];

	if(array_key_exists('service_nodegroups.check_attempts', $t_data) && !is_null($t_data['service_nodegroups.check_attempts'])) {
		$check_attempts = $t_data['service_nodes.check_attempts'];
	}

	if(array_key_exists('service_nodes.check_attempts', $t_data) && !is_null($t_data['service_nodes.check_attempts'])) {
		$check_attempts = $t_data['service_nodes.check_attempts'];
	}

	if($t_data['current_states.check_count'] < $check_attempts) {
		continue;
	}

	$t_key = $t_data['current_states.node'] . $t_data['current_states.service_id'] . $t_data['current_states.entity'] . $t_data['current_states.monitor'];

	$t_data['nodes.priority'] = NULL;
	if(in_array($t_data['current_states.node'], $nodes_priority['p1'])) {
		$t_data['nodes.priority'] = '1';
	} elseif(in_array($t_data['current_states.node'], $nodes_priority['p2'])) {
		$t_data['nodes.priority'] = '2';
	} elseif(in_array($t_data['current_states.node'], $nodes_priority['p3'])) {
		$t_data['nodes.priority'] = '3';
	}

	if(!$combine) {
		ksort($t_data);
		$data[$t_key] = $t_data;

		continue;
	}

	$t_key = $t_data['current_states.node'];

	if(array_key_exists('service_nodes.priority', $t_data) && $t_data['service_nodes.priority'] > $t_data['services.priority']) {
		$t_data['services.priority'] = $t_data['service_nodes.priority'];
	}

	if(empty($t_data['service_nodegroup_nodes.enabled'])) {
		if(array_key_exists('service_nodegroups.priority', $t_data) && $t_data['service_nodegroups.priority'] > $t_data['services.priority']) {
			$t_data['services.priority'] = $t_data['service_nodegroups.priority'];
		}
	}

	if(!empty($t_data['acknowledgements.service_id'])) {
		$acks[$t_key]++;
	}

	if(!array_key_exists($t_key, $data)) {
		$data[$t_key] = array(
			'acknowledgements.c_time' => $t_data['acknowledgements.c_time'],
			'acknowledgements.comment' => $t_data['acknowledgements.comment'],
			'acknowledgements.user' => $t_data['acknowledgements.user'],
			'current_states.c_time' => $t_data['current_states.c_time'],
			'current_states.check_count' => $t_data['current_states.check_count'],
			'current_states.entity' => $t_data['current_states.entity'],
			'current_states.m_time' => $t_data['current_states.m_time'],
			'current_states.message' => $t_data['current_states.message'],
			'current_states.monitor' => $t_data['current_states.monitor'],
			'current_states.node' => $t_data['current_states.node'],
			'current_states.service_id' => $t_data['current_states.service_id'],
			'current_states.state' => $t_data['current_states.state'],
			'nodes.check_interval' => $t_data['nodes.check_interval'],
			'nodes.enabled' => $t_data['nodes.enabled'],
			'nodes.node' => $t_data['nodes.node'],
			'nodes.notifications' => $t_data['nodes.notifications'],
			'nodes.priority' => $t_data['nodes.priority'],
			'services.priority' => $t_data['services.priority'],
			'services.service_name' => $t_data['services.service_name'],
			'services.wiki' => $t_data['services.wiki'],
		);

		continue;
	}

	$data[$t_key]['current_states.check_count'] += $t_data['current_states.check_count'];

	if($t_data['current_states.service_id'] != $data[$t_key]['current_states.service_id']) {
		if(!array_key_exists('multiple', $data[$t_key])) {
			$data[$t_key]['multiple'] = array();
		}

		if(!array_key_exists('service_id', $data[$t_key]['multiple'])) {
			$data[$t_key]['multiple']['service_id'] = array(
				$data[$t_key]['current_states.service_id'],
			);
		}

		if(!in_array($t_data['current_states.service_id'], $data[$t_key]['multiple']['service_id'])) {
			$data[$t_key]['multiple']['service_id'][] = $t_data['current_states.service_id'];
		}
		$data[$t_key]['current_states.service_id'] = '0';
	}

	if($t_data['services.service_name'] != $data[$t_key]['services.service_name']) {
		if(!array_key_exists('multiple', $data[$t_key])) {
			$data[$t_key]['multiple'] = array();
		}

		if(!array_key_exists('service_name', $data[$t_key]['multiple'])) {
			$data[$t_key]['multiple']['service_name'] = array(
				$data[$t_key]['services.service_name'],
			);
		}

		if(!in_array($t_data['services.service_name'], $data[$t_key]['multiple']['service_name'])) {
			$data[$t_key]['multiple']['service_name'][] = $t_data['services.service_name'];
		}
		$data[$t_key]['services.service_name'] = 'Multiple';
	}

	if($t_data['current_states.c_time'] < $data[$t_key]['current_states.c_time']) {
		$data[$t_key]['current_states.c_time'] = $t_data['current_states.c_time'];
	}

	if($t_data['current_states.m_time'] > $data[$t_key]['current_states.m_time']) {
		$data[$t_key]['current_states.m_time'] = $t_data['current_states.m_time'];
	}

	if($t_data['current_states.c_time'] > $data[$t_key]['current_states.m_time']) {
		$data[$t_key]['current_states.m_time'] = $t_data['current_states.c_time'];
	}

	if($t_data['current_states.state'] > $data[$t_key]['current_states.state']) {
		$data[$t_key]['current_states.state'] = $t_data['current_states.state'];
	}

	if($t_data['services.priority'] > $data[$t_key]['services.priority']) {
		$data[$t_key]['services.priority'] = $t_data['services.priority'];
	}

	if($t_data['current_states.monitor'] != $data[$t_key]['current_states.monitor']) {
		if(!array_key_exists('multiple', $data[$t_key])) {
			$data[$t_key]['multiple'] = array();
		}

		if(!array_key_exists('monitor', $data[$t_key]['multiple'])) {
			$data[$t_key]['multiple']['monitor'] = array(
				$data[$t_key]['current_states.monitor'],
			);
		}

		if(!in_array($t_data['current_states.monitor'], $data[$t_key]['multiple']['monitor'])) {
			$data[$t_key]['multiple']['monitor'][] = $t_data['current_states.monitor'];
		}
		$data[$t_key]['current_states.monitor'] = 'Multiple';
	}

	if($t_data['current_states.entity'] != $data[$t_key]['current_states.entity']) {
		if(!array_key_exists('multiple', $data[$t_key])) {
			$data[$t_key]['multiple'] = array();
		}

		if(!array_key_exists('entity', $data[$t_key]['multiple'])) {
			$data[$t_key]['multiple']['entity'] = array(
				$data[$t_key]['current_states.entity'],
			);
		}

		if(!in_array($t_data['current_states.entity'], $data[$t_key]['multiple']['entity'])) {
			$data[$t_key]['multiple']['entity'][] = $t_data['current_states.entity'];
		}
		$data[$t_key]['current_states.entity'] = 'Multiple';
	}

	if($t_data['current_states.message'] != $data[$t_key]['current_states.message']) {
		$data[$t_key]['current_states.message'] = 'Multiple';
	}

	if($t_data['services.wiki'] != $data[$t_key]['services.wiki']) {
		$data[$t_key]['services.wiki'] = NULL;
	}

	if($t_data['acknowledgements.service_id'] === 0 || $t_data['acknowledgements.service_id'] == $data[$t_key]['current_states.service_id']) {
		if($t_data['acknowledgements.c_time'] >= $data[$t_key]['current_states.c_time']) {
			$data[$t_key]['acknowledgements.c_time'] = $t_data['acknowledgements.c_time'];
			$data[$t_key]['acknowledgements.comment'] = $t_data['acknowledgements.comment'];
			$data[$t_key]['acknowledgements.user'] = $t_data['acknowledgements.user'];
		}
	} else {
		if(!is_null($t_data['acknowledgements.service_id'])) {
			if(count($data[$t_key]['multiple']['service_id']) == $acks[$t_key]) {
				if($t_data['acknowledgements.c_time'] >= $data[$t_key]['acknowledgements.c_time']) {
					$data[$t_key]['acknowledgements.c_time'] = $t_data['acknowledgements.c_time'];
				}

				$data[$t_key]['acknowledgements.comment'] = 'Multiple';
				$data[$t_key]['acknowledgements.user'] = 'Multiple';
			} else {
				$data[$t_key]['acknowledgements.c_time'] = NULL;
				$data[$t_key]['acknowledgements.comment'] = NULL;
				$data[$t_key]['acknowledgements.user'] = NULL;
			}
		} else {
			$data[$t_key]['acknowledgements.c_time'] = NULL;
			$data[$t_key]['acknowledgements.comment'] = NULL;
			$data[$t_key]['acknowledgements.user'] = NULL;
		}
	}
}

//
// Combine
//

//
// Get acks
//

$current_states = array_values($data);

if($errors['node'] === false && $errors['nodegroup'] === false) {
	print($ops->formatOutput($current_states));
} else {
	print($ops->formatOutput($current_states, '500', implode("\n", $errors)));
}
?>
