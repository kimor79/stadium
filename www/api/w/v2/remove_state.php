<?php

include('Monitoring/includes/rw.inc');

$missing = array();
$monitor = isset($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : $_SERVER['REMOTE_ADDR'];
$node;
$query_opts = array();

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

if(!$ops->isBlank($_POST['monitor'])) {
	$monitor = (get_magic_quotes_gpc()) ? stripslashes($_POST['monitor']) : $_POST['monitor'];
	$monitor = mysql_real_escape_string($monitor);
}

$node = (get_magic_quotes_gpc()) ? stripslashes($_POST['node']) : $_POST['node'];
$node = mysql_real_escape_string($node);
$query_opts[] = sprintf("`node` = '%s'", $node);

$query_opts[] = sprintf("`monitor` = '%s'", $monitor);
$query_opts[] = sprintf("`service_id` = '%d'", $_POST['service_id']);

$query = 'DELETE FROM `current_states` WHERE ';
$query .= implode(' AND ', $query_opts);

$result = do_mysql_query($query);

if($result[0] !== true) {
	print($ops->formatWriteOutput('500', $result[1]));
	exit(0);
}

print($ops->formatWriteOutput('200', 'Check removed'));
?>
