<?php
include('top.inc');
include('Monitoring/includes/ro.inc');
?>

<br>
<button id="buttonAddService">New Service</button>
<br><br>

<div id="divSearchServices">
 <div class="hd"></div>
 <div class="bd">
<form name="formSearchServices" method="GET" action="">
<table>
 <tr><td>

<table>
 <tr><td><label for="service_id">ID:</label></td><td colspan="3"><input type="text" name="service_id" size="5" value="<?php echo $_GET['service_id']; ?>"></td></tr>
 <tr><td><label for="service_name">Service:</label></td><td colspan="3"><input type="text" name="service_name" size="30" value="<?php echo $_GET['service_name']; ?>"></td></tr>
 <tr>
  <td><label for="enabled">Enabled:</label></td>
  <td>
<select name="enabled">
 <option value="any">any</option>
 <option value="1"<?php if(isset($_GET['enabled']) && $_GET['enabled'] == 1) echo ' selected'; ?>>1</option>
 <option value="0"<?php if(isset($_GET['enabled']) && $_GET['enabled'] == 0) echo ' selected'; ?>>0</option>
</select>
  </td>
  <td><label for="notifications">Notifications:</label></td>
  <td>
<select name="notifications">
 <option value="any">any</option>
 <option value="1"<?php if(isset($_GET['notifications']) && $_GET['notifications'] == 1) echo ' selected'; ?>>1</option>
 <option value="0"<?php if(isset($_GET['notifications']) && $_GET['notifications'] == 0) echo ' selected'; ?>>0</option>
</select>
  </td>
 </tr>
 <tr><td><label for="priority">Priority:</label></td><td colspan="3"><input type="text" name="priority" size="5" value="<?php echo $_GET['priority']; ?>"></td></tr>
 <tr><td><label for="check_attempts">Attempts:</label></td><td colspan="3"><input type="text" name="check_attempts" size="5" value="<?php echo $_GET['check_attempts']; ?>"></td></tr>
</table>

</td><td>

<table>
 <tr><td><label for="check_command">Command:</label></td><td><input type="text" name="check_command" size="30" value="<?php echo $_GET['check_command']; ?>"></td></tr>
 <tr><td><label for="type">Type:</label></td><td><input type="text" name="type" size="30" value="<?php echo $_GET['type']; ?>"></td></tr>
 <tr><td><label for="wiki">Wiki:</label></td><td><input type="text" name="wiki" size="30" value="<?php echo $_GET['wiki']; ?>"></td></tr>
 <tr><td><label for="check_interval">Interval:</label></td><td><input type="text" name="check_interval" size="5" value="<?php echo $_GET['check_interval']; ?>"> Minutes</td></tr>
</table>

</td></tr>
<tr><td>

<table>
 <tr><td><label for="warning_threshold">Warning:</label></td><td><input type="text" name="warning_threshold" size="30" value="<?php echo $_GET['warning_threshold']; ?>"></td></tr>
 <tr><td><label for="critical_threshold">Critical:</label></td><td><input type="text" name="critical_threshold" size="30" value="<?php echo $_GET['critical_threshold']; ?>"></td></tr>
</table>

</td><td>

<table>
 <tr><td><label for="args">Args:</label></td><td><input type="text" name="args" size="30" value="<?php echo $_GET['args']; ?>"></td></tr>
 <tr><td><label for="description">Description:</label></td><td><input type="text" name="description" size="30" value="<?php echo $_GET['description']; ?>"></td></tr>
</table>

</td></tr>
</table>
</form>
 </div>
 <div class="ft"></div>
</div>

<br>

<div id="divListServices"></div>

<?php include('addmodify_service_form.inc'); ?>

</body>
</html>
<script type="text/javascript">
YAHOO.util.Event.addListener(window, "load", function() {
	var handleSubmit = function() {
		var searchRequest = '';
		var data = myDialog.getData();

		if(data.service_id != '') {
			searchRequest += '&service_id=' + data.service_id;
		}

		if(data.service_name != '') {
			searchRequest += '&service_name=' + encodeURIComponent(data.service_name);
		}

		if(data.priority != '') {
			searchRequest += '&priority=' + encodeURIComponent(data.priority);
		}

		if(data.enabled != 'any') {
			searchRequest += '&enabled=' + encodeURIComponent(data.enabled);
		}

		if(data.notifications != 'any') {
			searchRequest += '&notifications=' + encodeURIComponent(data.notifications);
		}

		if(data.check_command != '') {
			searchRequest += '&check_command=' + encodeURIComponent(data.check_command);
		}

		if(data.type != '') {
			searchRequest += '&type=' + encodeURIComponent(data.type);
		}

		if(data.wiki != '') {
			searchRequest += '&wiki=' + encodeURIComponent(data.wiki);
		}

		if(data.check_interval != '') {
			searchRequest += '&check_interval=' + encodeURIComponent(data.check_interval);
		}

		if(data.check_attempts != '') {
			searchRequest += '&check_attempts=' + encodeURIComponent(data.check_attempts);
		}

		if(data.warning_threshold != '') {
			searchRequest += '&warning_threshold=' + encodeURIComponent(data.warning_threshold);
		}

		if(data.critical_threshold != '') {
			searchRequest += '&critical_threshold=' + encodeURIComponent(data.critical_threshold);
		}

		if(data.args != '') {
			searchRequest += '&args=' + encodeURIComponent(data.args);
		}

		if(data.description != '') {
			searchRequest += '&description=' + encodeURIComponent(data.description);
		}

		window.location = '?' + searchRequest;
	};

	var myButtons = [
		{ text:"Search", handler:handleSubmit, isDefault:true }
	];

	var myDialog = new YAHOO.widget.Dialog("divSearchServices", {
		close: false,
		draggable: false,
		fixedcenter: false,
		hideaftersubmit: false,
		underlay: "none",
		visible: true,
		width: "650px",
		zIndex: 0
	});

	myDialog.cfg.queueProperty("buttons", myButtons);
	myDialog.render();

	var myEnterDialog = new YAHOO.util.KeyListener("divSearchServices", { keys:13 }, { fn:handleSubmit });
	myEnterDialog.enable();

<?php
$api_url_requests = array();

if(isset($_GET['service_id']) && ctype_digit($_GET['service_id'])) {
	$api_url_requests[] = sprintf("service_id=%s", $_GET['service_id']);
}

if(isset($_GET['service_name'])) {
	$api_url_requests[] = sprintf("service_name=%s", urlencode('%' . $_GET['service_name'] . '%'));
}

if(isset($_GET['enabled']) && ctype_digit($_GET['enabled'])) {
	$api_url_requests[] = sprintf("enabled=%s", $_GET['enabled']);
}

if(isset($_GET['notifications']) && ctype_digit($_GET['notifications'])) {
	$api_url_requests[] = sprintf("notifications=%s", $_GET['notifications']);
}

if(isset($_GET['check_command'])) {
	$api_url_requests[] = sprintf("check_command=%s", urlencode('%' . $_GET['check_command'] . '%'));
}

if(isset($_GET['type'])) {
	$api_url_requests[] = sprintf("type=%s", urlencode('%' . $_GET['type'] . '%'));
}

if(isset($_GET['wiki'])) {
	$api_url_requests[] = sprintf("wiki=%s", urlencode('%' . $_GET['wiki'] . '%'));
}

if(isset($_GET['check_interval'])) {
	$api_url_requests[] = sprintf("check_interval=%s", $_GET['check_interval']);
}

if(isset($_GET['check_attempts'])) {
	$api_url_requests[] = sprintf("check_attempts=%s", $_GET['check_attempts']);
}

if(isset($_GET['warning_threshold'])) {
	$api_url_requests[] = sprintf("warning_threshold=%s", urlencode('%' . $_GET['warning_threshold'] . '%'));
}

if(isset($_GET['critical_threshold'])) {
	$api_url_requests[] = sprintf("critical_threshold=%s", urlencode('%' . $_GET['critical_threshold'] . '%'));
}

if(isset($_GET['args'])) {
	$api_url_requests[] = sprintf("args=%s", urlencode('%' . $_GET['args'] . '%'));
}

if(isset($_GET['description'])) {
	$api_url_requests[] = sprintf("description=%s", urlencode('%' . $_GET['description'] . '%'));
}

?>
	var myColumnDefs = [
		{key:"service_id", label:"ID", sortable:true, resizeable:true},
		{key:"service_name", label:"Name", sortable:true, resizeable:true},
		{key:"type", label:"Type", sortable:true, resizeable:true},
		{key:"priority", label:"Priority", sortable:true, resizeable:true},
		{key:"check_interval", label:"Interval", sortable:true, resizeable:true},
		{key:"wiki", label:"Wiki", sortable:true, resizeable:true},
		{key:"description", label:"Description", sortable:true, resizeable:true, formatter:YAHOO.BG.formatLongString}
	];

	var sUrl = '/api/r/v2/list_services.php?format=json&<?php echo implode('&', $api_url_requests); ?>&';

	var myDataSource = new YAHOO.util.DataSource(sUrl);
	myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
	myDataSource.responseSchema = {
		resultsList: "records",
		fields: [
			{key:"check_interval"},
			{key:"description"},
			{key:"priority"},
			{key:"service_id", parser:"number"},
			{key:"service_name"},
			{key:"type"},
			{key:"wiki"}
		],
		metaFields: {
			totalRecords: "totalRecords"
		}
	};

	var myConfigs = YAHOO.BG.datatableConfigs('service_name', 'asc', 100);

	myDataTable = new YAHOO.widget.DataTable("divListServices", myColumnDefs, myDataSource, myConfigs);

	myDataTable.handleDataReturnPayload = function(oRequest, oResponse, oPayload) {
		oPayload.totalRecords = oResponse.meta.totalRecords;
		return oPayload;
	};

	myDataTable.subscribe("rowMouseoverEvent", myDataTable.onEventHighlightRow);
	myDataTable.subscribe("rowMouseoutEvent", myDataTable.onEventUnhighlightRow);

	myDataTable.subscribe("rowClickEvent", function(oArgs) {
		var target = oArgs.target;
		var record = this.getRecord(target);
		var service = record.getData('service_id');

		window.location = '/services/details.php?service_id=' + service;
	});

<?php include('addmodify_service_js.inc'); ?>
});
</script>
