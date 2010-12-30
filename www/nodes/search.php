<?php
include('top.inc');
include('Monitoring/includes/ro.inc');
?>

<br>

<div id="divSearchNodes">
 <div class="hd"></div>
 <div class="bd">
<form name="formSearchNodes" method="GET" action="">
<table>
 <tr><td><label for="node">Node:</label></td><td colspan="3"><input type="text" name="node" size="30" value="<?php echo $_GET['node']; ?>"></td></tr>
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
</table>
</form>
 </div>
 <div class="ft"></div>
</div>

<br>

<div id="divListNodes"></div>

</body>
</html>
<script type="text/javascript">
YAHOO.util.Event.addListener(window, "load", function() {
	var handleSubmit = function() {
		var searchRequest = '';
		var data = myDialog.getData();

		if(data.node != '') {
			searchRequest += '&node=' + encodeURIComponent(data.node);
		}

		if(data.enabled != 'any') {
			searchRequest += '&enabled=' + encodeURIComponent(data.enabled);
		}

		if(data.notifications != 'any') {
			searchRequest += '&notifications=' + encodeURIComponent(data.notifications);
		}

		window.location = '?' + searchRequest;
	};

	var myButtons = [
		{ text:"Search", handler:handleSubmit, isDefault:true }
	];

	var myDialog = new YAHOO.widget.Dialog("divSearchNodes", {
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

	var myEnterDialog = new YAHOO.util.KeyListener("divSearchNodes", { keys:13 }, { fn:handleSubmit });
	myEnterDialog.enable();

<?php
$api_url_requests = array();

if(isset($_GET['node'])) {
	$api_url_requests[] = sprintf("node=%s", urlencode('%' . $_GET['node'] . '%'));
}

if(isset($_GET['enabled']) && ctype_digit($_GET['enabled'])) {
	$api_url_requests[] = sprintf("enabled=%s", $_GET['enabled']);
}

if(isset($_GET['notifications']) && ctype_digit($_GET['notifications'])) {
	$api_url_requests[] = sprintf("notifications=%s", $_GET['notifications']);
}

?>
	var myColumnDefs = [
		{key:"node", label:"Node", sortable:true, resizeable:true},
		{key:"services", label:"Services", sortable:true, resizeable:true},
		{key:"inherited", label:"Inherited", sortable:true, resizeable:true},
		{key:"enabled", label:"Enabled", sortable:true, resizeable:true},
		{key:"notifications", label:"Notifications", sortable:true, resizeable:true}
	];

	var sUrl = '/api/r/v2/list_nodes_service_count.php?format=json&<?php echo implode('&', $api_url_requests); ?>&';

	var myDataSource = new YAHOO.util.DataSource(sUrl);
	myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
	myDataSource.responseSchema = {
		resultsList: "records",
		fields: [
			{key:"enabled"},
			{key:"inherited", parser:"number"},
			{key:"node"},
			{key:"notifications"},
			{key:"services", parser:"number"}
		],
		metaFields: {
			totalRecords: "totalRecords"
		}
	};

	var myConfigs = YAHOO.BG.datatableConfigs('node', 'asc', 100);

	myDataTable = new YAHOO.widget.DataTable("divListNodes", myColumnDefs, myDataSource, myConfigs);

	myDataTable.handleDataReturnPayload = function(oRequest, oResponse, oPayload) {
		oPayload.totalRecords = oResponse.meta.totalRecords;
		return oPayload;
	};

	myDataTable.subscribe("rowMouseoverEvent", myDataTable.onEventHighlightRow);
	myDataTable.subscribe("rowMouseoutEvent", myDataTable.onEventUnhighlightRow);

	myDataTable.subscribe("rowClickEvent", function(oArgs) {
		var target = oArgs.target;
		var record = this.getRecord(target);
		var node = record.getData('node');

		window.location = '/nodes/details.php?node=' + node;
	});
});
</script>
