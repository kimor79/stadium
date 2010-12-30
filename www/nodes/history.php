<?php
include_once('Monitoring/includes/ro.inc');
include('top.inc');
?>

<br>

<div id="divSearchHistory">
 <div class="hd"></div>
 <div class="bd">
<form name="formSearchHistory" method="GET" action="">
<table>
 <tr><td><label for="node">Node:<label></td><td><input type="text" name="node" size="30" value="<?php echo $_GET['node']; ?>"></td></tr>
 <tr><td><label for="service_id">Service ID:<label></td><td><input type="text" name="service_id" size="5" value="<?php echo $_GET['service_id']; ?>"></td></tr>
 <tr><td><label for="user">User:<label></td><td><input type="text" name="user" size="30" value="<?php echo $_GET['user']; ?>"></td></tr>
 <tr><td><label for="field">Field:<label></td><td><input type="text" name="field" size="30" value="<?php echo $_GET['field']; ?>"></td></tr>
 <tr><td><label for="old_value">Old:<label></td><td><input type="text" name="old_value" size="30" value="<?php echo $_GET['old_value']; ?>"></td></tr>
 <tr><td><label for="new_value">New:<label></td><td><input type="text" name="new_value" size="30" value="<?php echo $_GET['new_value']; ?>"></td></tr>
</table>
</form>
 </div>
 <div class="ft"></div>
</div>

<?php $history->showDataTableDiv(); ?>

</body>
</html>
<script type="text/javascript">
YAHOO.util.Event.addListener(window, "load", function() {
	var handleCancel = function() {
		this.form.reset();
	};

	var handleSubmit = function() {
		var searchRequest = '';
		var data = myDialog.getData();

		if(data.node != '') {
			searchRequest += '&node=' + encodeURIComponent(data.node);
		}

		if(data.service_id != '') {
			searchRequest += '&service_id=' + encodeURIComponent(data.service_id);
		}

		if(data.user != '') {
			searchRequest += '&user=' + encodeURIComponent(data.user);
		}

		if(data.field != '') {
			searchRequest += '&field=' + encodeURIComponent(data.field);
		}

		if(data.old_value != '') {
			searchRequest += '&old_value=' + encodeURIComponent(data.old_value);
		}

		if(data.new_value != '') {
			searchRequest += '&new_value=' + encodeURIComponent(data.new_value);
		}

		window.location = '?' + searchRequest;
	};

	var myButtons = [
		{ text:"Search", handler:handleSubmit, isDefault:true },
		{ text:"Reset", handler:handleCancel }
	];

	var myDialog = new YAHOO.widget.Dialog("divSearchHistory", {
		close: false,
		draggable: false,
		fixedcenter: false,
		hideaftersubmit: false,
		underlay: "none",
		visible: true,
		width: "350px",
		zIndex: 0
	});

	myDialog.cfg.queueProperty("buttons", myButtons);
	myDialog.render();

	var myEnterDialog = new YAHOO.util.KeyListener("divSearchHistory", { keys:13 }, { fn:handleSubmit });
	myEnterDialog.enable();

<?php
$api_url_requests = array();

if(isset($_GET['service_id'])) {
	$api_url_requests[] = sprintf("service_id=%s", urlencode($_GET['service_id']));
}

if(isset($_GET['node'])) {
	$api_url_requests[] = sprintf("node=%s", urlencode('%' . $_GET['node'] . '%'));
}

if(isset($_GET['user'])) {
	$api_url_requests[] = sprintf("user=%s", urlencode('%' . $_GET['user'] . '%'));
}

if(isset($_GET['field'])) {
	$api_url_requests[] = sprintf("field=%s", urlencode('%' . $_GET['field'] . '%'));
}

if(isset($_GET['old_value'])) {
	$api_url_requests[] = sprintf("old_value=%s", urlencode('%' . $_GET['old_value'] . '%'));
}

if(isset($_GET['new_value'])) {
	$api_url_requests[] = sprintf("new_value=%s", urlencode('%' . $_GET['new_value'] . '%'));
}
?>

	var sHistoryUrl = '/api/r/v2/list_node_history.php?format=json&<?php echo implode('&', $api_url_requests); ?>&';
<?php
$my_history_js_options = array(
	'key' => 'node',
	'label' => 'Node',
	'rows_per_page' => 100,
);

include('Ops/includes/history_js.inc');
?>

});
</script>
