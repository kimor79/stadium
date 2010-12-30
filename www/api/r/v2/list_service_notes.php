<?php

include('Monitoring/includes/ro.inc');

$ops->use_sticky_sort = true;

$search = array_merge($_GET, $_POST);

list($data, $error) = $notes->getNotes('service_notes', $search);

if($error === false) {
	print($ops->formatOutput($data));
} else {
	print($ops->formatOutput($data, '500', $error));
}
?>
