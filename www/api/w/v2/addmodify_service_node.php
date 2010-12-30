<?php

include('Monitoring/includes/rw.inc');

$details = array();
$node_details = array();
$query;
$query_opts = array();
$service_details = array();
$s_message = '';

if(isset($_POST['service_id']) && ctype_digit((string)$_POST['service_id'])) {
	list($service_details, $junk) = $mon->listServices(array('service_id' => $_POST['service_id']));
	$service_details = reset($service_details);
}

if(empty($service_details)) {
	print($ops->formatWriteOutput('400', 'Missing/Unknown service ID'));
	exit(0);
}

if(!$ops->isBlank($_POST['node'])) {
	list($node_details, $junk) = $mon->listNodes(array('node' => $_POST['node']));
	$node_details = reset($node_details);
}

if(empty($node_details)) {
	print($ops->formatWriteOutput('400', 'Missing/Unknown node'));
	exit(0);
}

list($details, $junk) = $mon->listServicesNodes(array(
	'service_id' => $service_details['service_id'],
	'node' => $node_details['node'],
));

if(count($details) != 1) {
	$query_opts[] = sprintf("`service_id` = '%d'", $service_details['service_id']);
	$query_opts[] = sprintf("`node` = '%s'", $node_details['node']);
	$query = 'INSERT INTO';
	$s_message = 'added';
} else {
	if($ops->isYesNo($_POST['create_only'], false)) {
		print($ops->formatWriteOutput('400', 'Service/Node already exists'));
		exit(0);
	}

	$post_query = sprintf(" WHERE `service_id` = '%d'", $service_details['service_id']);
	$post_query .= sprintf(" AND `node` = '%s'", $node_details['node']);
	$query = 'UPDATE';
	$s_message = 'updated';
}

if(isset($_POST['enabled'])) {
	if($ops->isYesNo($_POST['enabled'], true) == false) {
		$query_opts[] = '`enabled` = 0';
	} else {
		$query_opts[] = '`enabled` = 1';
	}
}

if(isset($_POST['notifications'])) {
	if($ops->isYesNo($_POST['notifications'], true) == false) {
		$query_opts[] = '`notifications` = 0';
	} else {
		$query_opts[] = '`notifications` = 1';
	}
}

if(isset($_POST['priority'])) {
	if($ops->isBlank($_POST['priority'])) {
		$query_opts[] = '`priority` = DEFAULT';
	} else {
		if(ctype_digit((string)$_POST['priority'])) {
			$query_opts[] = sprintf("`priority` = '%s'", $_POST['priority']);
		} else {
			print($ops->formatWriteOutput('400', 'Invalid priority'));
			exit(0);
		}
	}
}

if(isset($_POST['check_interval'])) {
	if($ops->isBlank($_POST['check_interval'])) {
		$query_opts[] = '`check_interval` = DEFAULT';
	} else {
		if(ctype_digit((string)$_POST['check_interval'])) {
			$query_opts[] = sprintf("`check_interval` = '%s'", $_POST['check_interval']);
		} else {
			print($ops->formatWriteOutput('400', 'Invalid check_interval'));
			exit(0);
		}
	}
}

if(isset($_POST['check_attempts'])) {
	if($ops->isBlank($_POST['check_attempts'])) {
		$query_opts[] = '`check_attempts` = DEFAULT';
	} else {
		if(ctype_digit((string)$_POST['check_attempts'])) {
			$query_opts[] = sprintf("`check_attempts` = '%s'", $_POST['check_attempts']);
		} else {
			print($ops->formatWriteOutput('400', 'Invalid check_attempts'));
			exit(0);
		}
	}
}

if(isset($_POST['args'])) {
	if($ops->isBlank($_POST['args'])) {
		$query_opts[] = '`args` = DEFAULT';
	} else {
		$args = (get_magic_quotes_gpc()) ? stripslashes($_POST['args']) : $_POST['args'];
		$args = mysql_real_escape_string($args);
		$query_opts[] = sprintf("`args` = '%s'", $args);
	}
}

if(isset($_POST['warning_threshold'])) {
	if($ops->isBlank($_POST['warning_threshold'])) {
		$query_opts[] = '`warning_threshold` = DEFAULT';
	} else {
		$warning_threshold = (get_magic_quotes_gpc()) ? stripslashes($_POST['warning_threshold']) : $_POST['warning_threshold'];
		$warning_threshold = mysql_real_escape_string($warning_threshold);
		$query_opts[] = sprintf("`warning_threshold` = '%s'", $warning_threshold);
	}
}

if(isset($_POST['critical_threshold'])) {
	if($ops->isBlank($_POST['critical_threshold'])) {
		$query_opts[] = '`critical_threshold` = DEFAULT';
	} else {
		$critical_threshold = (get_magic_quotes_gpc()) ? stripslashes($_POST['critical_threshold']) : $_POST['critical_threshold'];
		$critical_threshold = mysql_real_escape_string($critical_threshold);
		$query_opts[] = sprintf("`critical_threshold` = '%s'", $critical_threshold);
	}
}

$query .= ' `service_nodes` SET ' . implode(',', $query_opts);
$query .= $post_query;
$result = do_mysql_query($query);

if($result[0] !== true) {
	print($ops->formatWriteOutput('500', $result[1]));
	exit(0);
}

list($new_details, $junk) = $mon->listServicesNodes(array(
	'service_id' => $service_details['service_id'],
	'node' => $node_details['node'],
));
$new_details = reset($new_details);

if(!empty($details)) {
	$details = reset($details);
}

$h_error = $history->updateHistory(array(
	'history_table' => 'service_node_history',
	'keys' => array('service_id' => $service_details['service_id'], 'node' => $node_details['node']),
	'old_data' => $details,
	'new_data' => $new_details,
	'source_tables' => array('service_nodes'),
));

if(!empty($h_error)) {
	$s_message .= ' but unable to add history: ' . $h_error;
}

$message = sprintf("%s/%s %s", $new_details['services.service_name'], $new_details['nodes.node'], $s_message);
print($ops->formatWriteOutput('200', $message, $new_details));
?>
