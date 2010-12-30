<?php

include('Monitoring/includes/rw.inc');

$missing = array();
$query_opts = array();

if($ops->isBlank($_POST['node']) && $ops->isBlank($_POST['service_id'])) {
	$missing[] = 'Missing node and/or service_id';
}

if(!$ops->isBlank($_POST['comment'])) {
	$comment = (get_magic_quotes_gpc()) ? stripslashes($_POST['comment']) : $_POST['comment'];
	$query_opts[] = sprintf("`comment` = '%s'", mysql_real_escape_string($comment));
} else {
	$missing[] = 'Missing comment';
}

if(isset($_POST['service_Id']) && !$ops->isBlank($_POST['service_id']) && !ctype_digit($_POST['service_id'])) {
	$missing[] = 'Invalid service_id';
}

if(isset($_POST['state']) && !$ops->isBlank($_POST['state']) && !array_key_exists($_POST['state'], $mon->states)) {
	$missing[] = 'Invalid state';
}

if(isset($_POST['node']) && !$ops->isBlank($_POST['node'])) {
	list($node_details, $junk) = $mon->listNodes(array('node' => $_POST['node']));
	if(count($node_details) != 1) {
		$missing[] = 'Unable to determine node';
	}
}

if(isset($_POST['service_id']) && !$ops->isBlank($_POST['service_id']) && $_POST['service_id'] != 0) {
	list($service_details, $junk) = $mon->listServices(array('service_id' => $_POST['service_id']));
	if(count($service_details) != 1) {
		$missing[] = 'Unable to determine service_id';
	}
}

if(!empty($missing)) {
	print($ops->formatWriteOutput('400', implode("\n", $missing)));
	exit(0);
}

if(isset($_POST['entity']) && !$ops->isBlank($_POST['entity'])) {
	$query_opts[] = sprintf("`entity` = '%s'", mysql_real_escape_string($_POST['entity']));
}

if(isset($_POST['state']) && !$ops->isBlank($_POST['state'])) {
	$query_opts[] = sprintf("`state` = '%d'", $_POST['state']);
}

if(isset($_POST['service_id']) && !$ops->isBlank($_POST['service_id'])) {
	$query_opts[] = sprintf("`service_id` = '%d'", $_POST['service_id']);
}

if(isset($_POST['node']) && !$ops->isBlank($_POST['node'])) {
	$query_opts[] = sprintf("`node` = '%s'", mysql_real_escape_string($_POST['node']));
}

$query_opts[] = sprintf("`c_time` = '%d'", time());
$query_opts[] = sprintf("`user` = '%s'", mysql_real_escape_string($_SERVER['REMOTE_USER']));

$query = 'INSERT INTO `acknowledgements` SET ' . implode(',', $query_opts);
$results = do_mysql_query($query);

if($results[0] !== true) {
	print($ops->formatWriteOutput('500', $results[1]));
	exit(0);
}

print($ops->formatWriteOutput('200', 'Alert acknowledged'));
?>
