<?php

include_once('Monitoring/includes/ro.inc');
include_once('Monitoring/www/www.inc');

if(empty($_GET['node'])) {
	$www->giveRedirect('/nodes/search.php');
	exit(0);
}

list($r_details, $error) = $mon->listNodes(array('node' => $_GET['node']));

if(count($r_details) != 1) {
	$www->giveRedirect('/nodes/search.php?node=' . urlencode($_GET['node']));
	exit(0);
}

$details = reset($r_details);

include('top.inc');
?>

<br>
<button id="buttonModifyNode">Modify</button>&nbsp;|
<button id="buttonAddServiceNode">Add Service</button>

<br>

<table class="table_box" cellpadding="3" cellspacing="5">
 <tr>
  <td colspan="3"><b>Node:</b>&nbsp;&nbsp;<a href="https://nodes.com/nodes/details.php?node=<?php echo $details['node']; ?>" target="_blank" class="ext_link"><?php echo $details['node']; ?></a></td>
 </tr>
 <tr>
  <td><b>Enabled:</b>&nbsp;&nbsp;<span id="spanNodeEnabled"><?php echo $details['enabled']; ?></span></td>
  <td><b>Notifications:</b>&nbsp;&nbsp;<span id="spanNodeNotifications"><?php echo $details['notifications']; ?></span></td>
  <td><b>Interval:</b>&nbsp;&nbsp;<span id="spanNodeInterval"><?php echo $details['check_interval']; ?></span> Minutes</td>
 </tr>
</table>

<br>

<h3>Services <img class="clickable" id="imgAddServiceNode" src="/img/add.png"></h3>
<div id="divListServices"></div>

<h3>Inherited Services</h3>
<div id="divListInherited"></div>

<?php $history->showDataTableDiv(); ?>

<?php include('Monitoring/www/addmodify_node_service_form.inc'); ?>
<?php include('Monitoring/www/addmodify_nodegroup_node_override_form.inc'); ?>

<div id="divModifyNode">
 <div class="hd">Modify <?php echo $details['node']; ?></div>
 <div class="bd">
<form name="formModifyNode" method="POST" action="/api/w/v2/modify_node.php">
<input type="hidden" name="node" value="<?php echo $details['node']; ?>">
<table width="99%">
 <tr>
  <td><label for="enabled">Enabled:</label></td>
  <td>
<select name="enabled">
 <option value=""></option>
 <option value="0">0</option>
 <option value="1">1</option>
</select>
  </td>
 </tr>
 <tr>
  <td><label for="notifications">Notifications:</label></td>
  <td>
<select name="notifications">
 <option value=""></option>
 <option value="0">0</option>
 <option value="1">1</option>
</select>
  </td>
 </tr>
 <tr>
  <td><label for="check_interval">Interval:</label></td>
  <td><input type="text" name="check_interval" size="6"> Minutes</td>
 </tr>
</table> 
</form>
 </div>
</div>

</body>
</html>
<script type="text/javascript">
YAHOO.util.Event.addListener(window, "load", function() {
	var sHistoryUrl = '/api/r/v2/list_node_history.php?node=<?php echo $details['node']; ?>&';
<?php
include('Ops/includes/history_js.inc');
?>
	var myServicesColumnDefs = [
		{key:"services.service_id", field:'["services.service_id"]', label:"ID", sortable:true, resizeable:true},
		{key:"services.service_name", field:'["services.service_name"]', label:"Service", sortable:true, resizeable:true},
		{key:"service_nodes.priority", field:'["service_nodes.priority"]', label:"Priority", sortable:true, resizeable:true, formatter:formatServiceNode},
		{key:"service_nodes.enabled", field:'["service_nodes.enabled"]', label:"Enabled", sortable:true, resizeable:true},
		{key:"service_nodes.notifications", field:'["service_nodes.notifications"]', label:"Notifications", sortable:true, resizeable:true},
		{key:"service_nodes.check_interval", field:'["service_nodes.check_interval"]', label:"Interval", sortable:true, resizeable:true, formatter:formatServiceNode},
		{key:"service_nodes.check_attempts", field:'["service_nodes.check_attempts"]', label:"Attempts", sortable:true, resizeable:true, formatter:formatServiceNode},
		{key:"service_nodes.args", field:'["service_nodes.args"]', label:"Args", sortable:true, resizeable:true, formatter:formatServiceNode},
		{key:"service_nodes.warning_threshold", field:'["service_nodes.warning_threshold"]', label:"Warning", sortable:true, resizeable:true, formatter:formatServiceNode},
		{key:"service_nodes.critical_threshold", field:'["service_nodes.critical_threshold"]', label:"Critical", sortable:true, resizeable:true, formatter:formatServiceNode},
		{key:"delete_service", label:"", className:"center", formatter:YAHOO.BG.formatDeleteIcon}
	];

	var sServicesUrl = '/api/r/v2/list_services_nodes.php?format=json&node=<?php echo $details['node']; ?>&';

	var myServicesDataSource = new YAHOO.util.DataSource(sServicesUrl);
	myServicesDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
	myServicesDataSource.responseSchema = {
		resultsList: "records",
		fields: [
			{key:'["nodes.check_interval"]', parser:"number"},
			{key:'["service_nodes.args"]'},
			{key:'["service_nodes.check_attempts"]', parser:"number"},
			{key:'["service_nodes.check_interval"]', parser:"number"},
			{key:'["service_nodes.critical_threshold"]'},
			{key:'["service_nodes.enabled"]'},
			{key:'["service_nodes.notifications"]'},
			{key:'["service_nodes.priority"]', parser:"number"},
			{key:'["service_nodes.warning_threshold"]'},
			{key:'["services.args"]'},
			{key:'["services.check_attempts"]', parser:"number"},
			{key:'["services.check_interval"]', parser:"number"},
			{key:'["services.critical_threshold"]'},
			{key:'["services.enabled"]'},
			{key:'["services.notifications"]'},
			{key:'["services.priority"]', parser:"number"},
			{key:'["services.service_id"]', parser:"number"},
			{key:'["services.service_name"]'},
			{key:'["services.warning_threshold"]'}
		],
		metaFields: {
			totalRecords: "totalRecords"
		}
	};

	var myServicesConfigs = YAHOO.BG.datatableConfigs('services.service_name', 'asc', 25);

	myServicesDataTable = new YAHOO.widget.DataTable("divListServices", myServicesColumnDefs,
			myServicesDataSource, myServicesConfigs);

	myServicesDataTable.handleDataReturnPayload = function(oRequest, oResponse, oPayload) {
		oPayload.totalRecords = oResponse.meta.totalRecords;
		return oPayload;
	};

	myServicesDataTable.subscribe("rowMouseoverEvent", myServicesDataTable.onEventHighlightRow);
	myServicesDataTable.subscribe("rowMouseoutEvent", myServicesDataTable.onEventUnhighlightRow);

	myAddServiceNode = function(o) {
		myAddModifyServiceNodeDialog.setHeader('Add Service');
	 	document.formAddModifyServiceNode.node.value = '<?php echo $details['node']; ?>';
		document.formAddModifyServiceNode.node.readOnly = true;
		document.formAddModifyServiceNode.service_id.readOnly = false;
		myAddModifyServiceNodeDialog.show();

		myAddModifyServiceNodeDialog.callback.success = function(o) {
			var output;
			try {
				output = YAHOO.lang.JSON.parse(o.responseText);
			} catch(e) {
				YAHOO.BG.dialogOnSuccess(o, myAddModifyServiceNodeDialog.form);
				return true;
			}

			YAHOO.BG.dialogOnSuccess(o, myAddModifyServiceNodeDialog.form);

			if(typeof(output.status) != "undefined") {
				if(output.status == "200" && typeof(output.details) != "undefined") {
					YAHOO.BG.dialogOnSuccess(o, myAddModifyServiceNodeDialog.form);
					myAddModifyServiceNodeDialog.form.reset();
					myAddModifyServiceNodeDialog.hide();
					YAHOO.BG.refreshDataTable(myServicesDataTable);
					YAHOO.BG.refreshDataTable(myHistoryDataTable);
				}
			}
		};
	};

<?php include('Monitoring/www/addmodify_node_service_js.inc'); ?>

	YAHOO.util.Event.addListener("buttonAddServiceNode", "click", myAddServiceNode);
	YAHOO.util.Event.addListener("imgAddServiceNode", "click", myAddServiceNode);

	myServicesDataTable.subscribe("cellClickEvent", function(oArgs) {
		var target = oArgs.target;
		var record = this.getRecord(target);
		var service = record.getData('["services.service_id"]');
		var serviceName = record.getData('["services.service_name"]');

		if(this.getColumn(target).getKey() == "delete_service") {
			YAHOO.BG.handleYesNoYes = function() {
				var postData = 'delete=yes&node=<?php echo $details['node']; ?>&service_id=' + encodeURIComponent(service);
				var sUrl = '/api/w/v2/delete_service_node.php?format=json';

				var callback = {
					success: function(o) {
						YAHOO.BG.dialogOnSuccess(o);
						YAHOO.BG.refreshDataTable(myServicesDataTable);
						YAHOO.BG.refreshDataTable(myHistoryDataTable);
					},
					failure: YAHOO.BG.dialogOnFailure
				};

				YAHOO.BG.showLoading("show");
				YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, postData);
			};

			YAHOO.BG.yesNoDialog.setBody('Remove ' + serviceName + '?');
			YAHOO.BG.yesNoDialog.show();
			return;
		}

		document.formAddModifyServiceNode.node.value = '<?php echo $details['node']; ?>';
		document.formAddModifyServiceNode.node.readOnly = true;
		document.formAddModifyServiceNode.service_id.value = service;
		document.formAddModifyServiceNode.service_id.readOnly = true;

		document.formAddModifyServiceNode.args.value = record.getData('["service_nodes.args"]');
		document.formAddModifyServiceNode.critical_threshold.value = record.getData('["service_nodes.critical_threshold"]');
		document.formAddModifyServiceNode.check_attempts.value = record.getData('["service_nodes.check_attempts"]');
		document.formAddModifyServiceNode.check_interval.value = record.getData('["service_nodes.check_interval"]');
		document.formAddModifyServiceNode.enabled.value = record.getData('["service_nodes.enabled"]');
		document.formAddModifyServiceNode.notifications.value = record.getData('["service_nodes.notifications"]');
		document.formAddModifyServiceNode.priority.value = record.getData('["service_nodes.priority"]');
		document.formAddModifyServiceNode.warning_threshold.value = record.getData('["service_nodes.warning_threshold"]');

		myAddModifyServiceNodeDialog.setHeader('Modify ' + serviceName);
		myAddModifyServiceNodeDialog.show();

		myAddModifyServiceNodeDialog.callback.success = function(o) {
			YAHOO.BG.dialogOnSuccess(o, myAddModifyServiceNodeDialog.form);
			myAddModifyServiceNodeDialog.form.reset();
			myAddModifyServiceNodeDialog.hide();
			YAHOO.BG.refreshDataTable(myServicesDataTable);
			YAHOO.BG.refreshDataTable(myHistoryDataTable);
		};
	});

	var myInheritedColumnDefs = [
		{key:"services.service_id", field:'["services.service_id"]', label:"ID", sortable:true, resizeable:true},
		{key:"nodegroups.nodegroup", field:'["nodegroups.nodegroup"]', label:"Nodegroup", sortable:true, resizeable:true},
		{key:"services.service_name", field:'["services.service_name"]', label:"Service", sortable:true, resizeable:true},
		{key:"service_nodegroups.priority", field:'["service_nodegroups.priority"]', label:"Priority", sortable:true, resizeable:true, formatter:formatServiceNodegroupNode},
		{key:"service_nodegroups.enabled", field:'["service_nodegroups.enabled"]', label:"Enabled", sortable:true, resizeable:true, formatter:formatServiceNodegroupNode},
		{key:"service_nodegroups.notifications", field:'["service_nodegroups.notifications"]', label:"Notifications", sortable:true, resizeable:true, formatter:formatServiceNodegroupNode},
		{key:"service_nodegroups.check_interval", field:'["service_nodegroups.check_interval"]', label:"Interval", sortable:true, resizeable:true, formatter:formatServiceNodegroupNode},
		{key:"service_nodegroups.check_attempts", field:'["service_nodegroups.check_attempts"]', label:"Attempts", sortable:true, resizeable:true, formatter:formatServiceNodegroupNode},
		{key:"service_nodegroups.args", field:'["service_nodegroups.args"]', label:"Args", sortable:true, resizeable:true, formatter:formatServiceNodegroupNode},
		{key:"service_nodegroups.warning_threshold", field:'["service_nodegroups.warning_threshold"]', label:"Warning", sortable:true, resizeable:true, formatter:formatServiceNodegroupNode},
		{key:"service_nodegroups.critical_threshold", field:'["service_nodegroups.critical_threshold"]', label:"Critical", sortable:true, resizeable:true, formatter:formatServiceNodegroupNode}
	];

	var sInheritedUrl = '/api/r/v2/list_inherited_services.php?format=json&node=<?php echo $details['node']; ?>&';

	var myInheritedDataSource = new YAHOO.util.DataSource(sInheritedUrl);
	myInheritedDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
	myInheritedDataSource.responseSchema = {
		resultsList: "records",
		fields: [
			{key:'["nodegroups.nodegroup"]'},
			{key:'["nodes.check_interval"]', parser:"number"},
			{key:'["service_nodegroup_nodes.enabled"]'},
			{key:'["service_nodegroup_nodes.notifications"]'},
			{key:'["service_nodegroups.args"]'},
			{key:'["service_nodegroups.check_attempts"]', parser:"number"},
			{key:'["service_nodegroups.check_interval"]', parser:"number"},
			{key:'["service_nodegroups.critical_threshold"]'},
			{key:'["service_nodegroups.enabled"]'},
			{key:'["service_nodegroups.notifications"]'},
			{key:'["service_nodegroups.priority"]', parser:"number"},
			{key:'["service_nodegroups.warning_threshold"]'},
			{key:'["services.args"]'},
			{key:'["services.check_attempts"]', parser:"number"},
			{key:'["services.check_interval"]', parser:"number"},
			{key:'["services.critical_threshold"]'},
			{key:'["services.enabled"]'},
			{key:'["services.notifications"]'},
			{key:'["services.priority"]', parser:"number"},
			{key:'["services.service_id"]', parser:"number"},
			{key:'["services.service_name"]'},
			{key:'["services.warning_threshold"]'}
		],
		metaFields: {
			totalRecords: "totalRecords"
		}
	};

	var myInheritedConfigs = YAHOO.BG.datatableConfigs('services.service_name', 'asc', 25);

	myInheritedDataTable = new YAHOO.widget.DataTable("divListInherited", myInheritedColumnDefs,
			myInheritedDataSource, myInheritedConfigs);

	myInheritedDataTable.handleDataReturnPayload = function(oRequest, oResponse, oPayload) {
		oPayload.totalRecords = oResponse.meta.totalRecords;
		return oPayload;
	};

	myInheritedDataTable.subscribe("rowMouseoverEvent", myInheritedDataTable.onEventHighlightRow);
	myInheritedDataTable.subscribe("rowMouseoutEvent", myInheritedDataTable.onEventUnhighlightRow);

<?php include('Monitoring/www/addmodify_nodegroup_node_override_js.inc'); ?>

	myInheritedDataTable.subscribe("cellClickEvent", function(oArgs) {
		var target = oArgs.target;
		var record = this.getRecord(target);

		var enabled = record.getData('["service_nodegroup_nodes.enabled"]');
		var nodegroup = record.getData('["nodegroups.nodegroup"]');
		var notifications = record.getData('["service_nodegroup_nodes.notifications"]');
		var service = record.getData('["services.service_id"]');

		document.formAddModifyNodegroupNodeOverride.node.value = '<?php echo $details['node']; ?>';
		document.formAddModifyNodegroupNodeOverride.node.readOnly = true;
		document.formAddModifyNodegroupNodeOverride.nodegroup.value = nodegroup;
		document.formAddModifyNodegroupNodeOverride.service_id.value = service;

		document.formAddModifyNodegroupNodeOverride.enabled.value = enabled;
		document.formAddModifyNodegroupNodeOverride.notifications.value = notifications;

		myAddModifyNodegroupNodeOverrideDialog.setHeader('Override ' + nodegroup);
		myAddModifyNodegroupNodeOverrideDialog.show();

		myAddModifyNodegroupNodeOverrideDialog.callback.success = function(o) {
			YAHOO.BG.dialogOnSuccess(o, myAddModifyNodegroupNodeOverrideDialog.form);
			myAddModifyNodegroupNodeOverrideDialog.form.reset();
			myAddModifyNodegroupNodeOverrideDialog.hide();
			YAHOO.BG.refreshDataTable(myInheritedDataTable);
		};
	});

	var handleModifyNodeCancel = function() {
		myModifyNodeDialog.form.reset();
		this.cancel();
	};

	var handleModifyNodeSubmit = function() {
		YAHOO.BG.showLoading('show');
		this.submit();
	};

	var myModifyNodeButtons = [
		{ text:"Submit", handler:handleModifyNodeSubmit, isDefault:true },
		{ text:"Cancel", handler:handleModifyNodeCancel }
	];

	var myModifyNodeDialog = new YAHOO.widget.Dialog("divModifyNode",
		{
			fixedcenter: true,
			hideaftersubmit: false,
			visible: false,
			width: "400px",
			zIndex: 100
		}
	);
	myModifyNodeDialog.cfg.queueProperty("buttons", myModifyNodeButtons);
	myModifyNodeDialog.render();

	myModifyNodeDialog.validate = function() {
		return true;
	};

	myModifyNodeDialog.callback.failure = YAHOO.BG.dialogOnFailure;

	myModifyNodeDialog.callback.success = function(o) {
		var output;
		try {
			output = YAHOO.lang.JSON.parse(o.responseText);
		} catch(e) {
			YAHOO.BG.dialogOnSuccess(o, myModifyNodeDialog.form);
			return true;
		}

		YAHOO.BG.dialogOnSuccess(o, myModifyNodeDialog.form);

		if(typeof(output.status) != "undefined") {
			if(output.status == "200" && typeof(output.details) != "undefined") {
				YAHOO.BG.dialogOnSuccess(o, myModifyNodeDialog.form);
				myModifyNodeDialog.form.reset();
				YAHOO.BG.refreshDataTable(myServicesDataTable);
				YAHOO.BG.refreshDataTable(myInheritedDataTable);
				YAHOO.BG.refreshDataTable(myHistoryDataTable);
				document.getElementById('spanNodeEnabled').innerHTML = output.details.enabled;
				document.getElementById('spanNodeNotifications').innerHTML = output.details.notifications;
				document.getElementById('spanNodeInterval').innerHTML = output.details.check_interval;
				myModifyNodeDialog.hide();
			}
		}
	};

	YAHOO.util.Event.addListener("buttonModifyNode", "click", function(o) {
		document.formModifyNode.enabled.value = document.getElementById('spanNodeEnabled').innerHTML;
		document.formModifyNode.notifications.value = document.getElementById('spanNodeNotifications').innerHTML;
		document.formModifyNode.check_interval.value = document.getElementById('spanNodeInterval').innerHTML;
		myModifyNodeDialog.show();
	});
});
</script>
