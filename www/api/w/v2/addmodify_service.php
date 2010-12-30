<?php

include('Monitoring/includes/rw.inc');

$details = array();
$query;
$query_opts = array();
$s_message = '';
$service_id = 0;

if(isset($_POST['service_id']) && ctype_digit((string)$_POST['service_id'])) {
	list($details, $junk) = $mon->listServices(array('service_id' => $_POST['service_id']));
	$details = reset($details);
}

if(empty($details)) {
	if($ops->isBlank($_POST['service_name'])) {
		print($ops->formatWriteOutput('400', 'Missing service name'));
		exit(0);
	}

	if($ops->isBlank($_POST['check_command'])) {
		print($ops->formatWriteOutput('400', 'Missing check_command'));
		exit(0);
	}

	$query = 'INSERT INTO';
	$s_message = 'added';
} else {
	if($ops->isYesNo($_POST['create_only'], false)) {
		print($ops->formatWriteOutput('400', 'Service already exists'));
		exit(0);
	}

	$post_query = sprintf(" WHERE `service_id` = '%d'", $details['service_id']);
	$query = 'UPDATE';
	$service_id = $details['service_id'];
	$s_message = 'updated';
}

if(!$ops->isBlank($_POST['service_name'])) {
	$service_name = (get_magic_quotes_gpc()) ? stripslashes($_POST['service_name']) : $_POST['service_name'];
	$service_name = mysql_real_escape_string($service_name);
	$query_opts[] = sprintf("`service_name` = '%s'", $service_name);
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

if(isset($_POST['check_command'])) {
	list($check_commands, $error) = $mon->listCheckCommands(array('check_command' => $_POST['check_command']));
	if(count($check_commands) == 1) {
		$check_command = mysql_real_escape_string($_POST['check_command']);
		$query_opts[] = sprintf("`check_command` = '%s'", $check_command);
	} else {
		print($ops->formatWriteOutput('400', 'Invalid check command'));
		exit(0);
	}
}

if(isset($_POST['description'])) {
	if($ops->isBlank($_POST['description'])) {
		$query_opts[] = '`description` = DEFAULT';
	} else {
		$description = (get_magic_quotes_gpc()) ? stripslashes($_POST['description']) : $_POST['description'];
		$description = mysql_real_escape_string($description);
		$query_opts[] = sprintf("`description` = '%s'", $description);
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

if(isset($_POST['wiki'])) {
	if(!$ops->isBlank($_POST['wiki'])) {
		$wiki = (get_magic_quotes_gpc()) ? stripslashes($_POST['wiki']) : $_POST['wiki'];
		$wiki = mysql_real_escape_string($wiki);
		$query_opts[] = sprintf("`wiki` = '%s'", $wiki);
	} else {
		print($ops->formatWriteOutput('400', 'Wiki cannot be empty'));
		exit(0);
	}
}

$query .= ' `services` SET ' . implode(',', $query_opts);
$query .= $post_query;
$result = do_mysql_query($query);

if($result[0] !== true) {
	print($ops->formatWriteOutput('500', $result[1]));
	exit(0);
}

if(empty($service_id)) {
	$service_id = mysql_insert_id();
}

list($new_details, $error) = $mon->listServices(array('service_id' => $service_id));
$new_details = reset($new_details);

$h_error = $history->updateHistory('service_history', 'service_id', $service_id, $details, $new_details, array('services', 'check_commands'));

if($h_error !== false) {
	$s_message .= ' but unable to add history: ' . $h_error;
}

$message = sprintf("%s %s", $new_details['service_name'], $s_message);
print($ops->formatWriteOutput('200', $message, $new_details));
?>
