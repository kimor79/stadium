<?php

include('Monitoring/includes/rw.inc');

$check_query;
$check_result;
$current_opts = array();
$current_query;
$current_result;
$message = 'No message given';
$missing = array();
$monitor = isset($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : $_SERVER['REMOTE_ADDR'];
$node;
$query_opts = array();
$service_id = 0;

if($ops->isBlank($_POST['node'])) {
	$missing[] = 'Missing node';
}

if($ops->isBlank($_POST['service_id'])) {
	$missing[] = 'Missing service_id';
} else {
	list($services, $junk) = $mon->listServices(array('service_id' => $_POST['service_id']));
	if(empty($services)) {
		$missing[] = 'No such service';
	} else {
		if(count($services) != 1) {
			$missing[] = 'Service not unique';
		}
	}
}

if(!isset($_POST['state'])) {
	$missing[] = 'Missing state';
} else {
	if(!ctype_digit($_POST['state'])) {
		$missing[] = 'State must be a number';
	}
}

if(!empty($missing)) {
	print($ops->formatWriteOutput('400', implode("\n", $missing)));
	exit(0);
}

if(!$ops->isBlank($_POST['entity'])) {
	$entity = (get_magic_quotes_gpc()) ? stripslashes($_POST['entity']) : $_POST['entity'];
	$entity = mysql_real_escape_string($entity);
	$query_opts[] = sprintf("`entity` = '%s'", $entity);
	$current_opts[] = sprintf("`entity` = '%s'", $entity);
}

if(!$ops->isBlank($_POST['message'])) {
	$message = (get_magic_quotes_gpc()) ? stripslashes($_POST['message']) : $_POST['message'];
	$message = mysql_real_escape_string($message);
}

if(!$ops->isBlank($_POST['monitor'])) {
	$monitor = (get_magic_quotes_gpc()) ? stripslashes($_POST['monitor']) : $_POST['monitor'];
	$monitor = mysql_real_escape_string($monitor);
}

$node = (get_magic_quotes_gpc()) ? stripslashes($_POST['node']) : $_POST['node'];
$node = mysql_real_escape_string($node);
$query_opts[] = sprintf("`node` = '%s'", $node);

$query_opts[] = sprintf("`c_time` = '%d'", time());
$query_opts[] = sprintf("`message` = '%s'", $message);
$query_opts[] = sprintf("`monitor` = '%s'", $monitor);
$query_opts[] = sprintf("`service_id` = '%d'", $_POST['service_id']);
$query_opts[] = sprintf("`state` = '%d'", $_POST['state']);

$current_opts[] = sprintf("`monitor` = '%s'", $monitor);
$current_opts[] = sprintf("`node` = '%s'", $node);
$current_opts[] = sprintf("`service_id` = '%d'", $_POST['service_id']);

if($_POST['state'] < 1) {
	$current_query = 'DELETE FROM `current_states` WHERE ';
	$current_query .= implode(' AND ', $current_opts);
} else {
	$current_query = 'INSERT INTO `current_states` SET ';
	$current_query .= implode(',', $query_opts);
	$current_query .= ' ON DUPLICATE KEY UPDATE ';

	$current_dupes = array();
	$current_dupes[] = '`check_count` = `check_count` + 1';
	$current_dupes[] = '`previous_state` = `state`';
	$current_dupes[] = sprintf("`m_time` = '%d'", time());
	$current_dupes[] = sprintf("`message` = '%s'", $message);
	$current_dupes[] = sprintf("`state` = '%d'", $_POST['state']);

	$current_query .= implode(',', $current_dupes);
}

$current_result = do_mysql_query($current_query);

if($current_result[0] !== true) {
	print($ops->formatWriteOutput('500', $current_result[1]));
	exit(0);
}

$check_query = 'INSERT INTO `check_history` SET ' . implode(',', $query_opts);
$check_result = do_mysql_query($check_query);

if($check_result[0] !== true) {
	print($ops->formatWriteOutput('500', $check_result[1]));
	exit(0);
}

print($ops->formatWriteOutput('200', 'Check added'));
?>
