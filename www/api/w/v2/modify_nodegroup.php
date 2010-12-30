<?php

include('Monitoring/includes/rw.inc');

$details = array();
$query;
$query_opts = array();
$s_message = '';
$service_id = 0;

if($ops->isBlank($_POST['nodegroup'])) {
	print($ops->formatWriteOutput('400', 'Missing nodegroup'));
	exit(0);
}

list($details, $junk) = $mon->listNodegroups(array('nodegroup' => $_POST['nodegroup']));
$details = reset($details);

if(empty($details)) {
	print($ops->formatWriteOutput('400', 'No such nodegroup'));
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

$query .= 'UPDATE `nodegroups` SET ' . implode(',', $query_opts);
$query .= ' WHERE `nodegroup` = ' . sprintf("'%s'", $details['nodegroup']);
$result = do_mysql_query($query);

if($result[0] !== true) {
	print($ops->formatWriteOutput('500', $result[1]));
	exit(0);
}

list($new_details, $error) = $mon->listNodegroups(array('nodegroup' => $details['nodegroup']));
$new_details = reset($new_details);

$h_error = $history->updateHistory('nodegroup_history', 'nodegroup', $details['nodegroup'], $details, $new_details, array('nodegroups'));

if($h_error !== false) {
	$s_message .= ' but unable to add history: ' . $h_error;
}

$message = sprintf("%s updated", $new_details['nodegroup']);
print($ops->formatWriteOutput('200', $message, $new_details));
?>
