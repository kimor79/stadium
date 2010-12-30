<?php

include('Monitoring/includes/rw.inc');

$details = array();
$query;
$query_opts = array();
$s_message = '';
$service_id = 0;

if($ops->isBlank($_POST['node'])) {
	print($ops->formatWriteOutput('400', 'Missing node'));
	exit(0);
}

list($details, $junk) = $mon->listNodes(array('node' => $_POST['node']));
$details = reset($details);

if(empty($details)) {
	print($ops->formatWriteOutput('400', 'No such node'));
	exit(0);
}

if($ops->isBlank($_POST['enabled'])) {
	$query_opts[] = '`enabled` = DEFAULT';
} else {
	if($ops->isYesNo($_POST['enabled'], true) == false) {
		$query_opts[] = '`enabled` = 0';
	} else {
		$query_opts[] = '`enabled` = 1';
	}
}

if($ops->isBlank($_POST['notifications'])) {
	$query_opts[] = '`notifications` = DEFAULT';
} else {
	if($ops->isYesNo($_POST['notifications'], true) == false) {
		$query_opts[] = '`notifications` = 0';
	} else {
		$query_opts[] = '`notifications` = 1';
	}
}

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

$query .= 'UPDATE `nodes` SET ' . implode(',', $query_opts);
$query .= ' WHERE `node` = ' . sprintf("'%s'", $details['node']);
$result = do_mysql_query($query);

if($result[0] !== true) {
	print($ops->formatWriteOutput('500', $result[1]));
	exit(0);
}

list($new_details, $error) = $mon->listNodes(array('node' => $details['node']));
$new_details = reset($new_details);

$h_error = $history->updateHistory('node_history', 'node', $details['node'], $details, $new_details, array('nodes'));

if($h_error !== false) {
	$s_message .= ' but unable to add history: ' . $h_error;
}

$message = sprintf("%s updated", $new_details['node']);
print($ops->formatWriteOutput('200', $message, $new_details));
?>
