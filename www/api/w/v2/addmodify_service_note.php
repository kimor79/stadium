<?php

include('Monitoring/includes/rw.inc');

$message = '';
$note = '';
$service_id = 0;
$sticky = 0;

if(!$ops->isBlank($_POST['note'])) {
	$note = (get_magic_quotes_gpc()) ? stripslashes($_POST['note']) : $_POST['note'];
} else {
	print($ops->formatWriteOutput('400', 'Missing note'));
	exit(0);
}

if(isset($_POST['service_id']) && ctype_digit((string)$_POST['service_id'])) {
	list($s_details, $s_error) = $mon->listServices(array('service_id' => $_POST['service_id']));

	if($s_error !== false) {
		print($ops->formatWriteOutput('500', $s_error));
		exit(0);
	}

	$s_details = reset($s_details);

	if(empty($s_details['service_id'])) {
		print($ops->formatWriteOutput('400', 'No such service_id'));
		exit(0);
	}

	$service_id = $s_details['service_id'];
} else {
	print($ops->formatWriteOutput('400', 'Missing/invalid service_id'));
	exit(0);
}

if($ops->isYesNo($_POST['sticky'], false)) {
	$sticky = 1;
}

list($note_id, $error) = $notes->addNote('service_notes', 'service_id', $service_id, $note, $sticky);

if($error !== false) {
	print($ops->formatWriteOutput('500', $error));
	exit(0);
}

list($n_details, $n_error) = $notes->getNotes('service_notes', array('note_id' => $note_id));
$details = reset($n_details);

$message = 'Note ' . $note_id . ' added';

if($n_error !== false) {
	$message = $n_error;
}

print($ops->formatWriteOutput('200', $message, $details));
?>
