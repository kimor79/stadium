<?php

include_once('Monitoring/includes/ro.inc');
include_once('Monitoring/www/www.inc');

if(empty($_GET['service_id'])) {
	$www->giveRedirect('/services/search.php');
	exit(0);
}

list($r_details, $error) = $mon->listServices(array('service_id' => $_GET['service_id']));

if(count($r_details) != 1) {
	$www->giveRedirect('/services/search.php?service_id=' . urlencode($_GET['service_id']));
	exit(0);
}

$details = reset($r_details);

include('top.inc');
?>

<br>
<button id="buttonAddService">New Service</button>&nbsp;|
<button id="buttonModifyService">Modify</button>&nbsp;|
<?php $notes->showAddButton(); ?>&nbsp;|
<button id="buttonAddServiceNodegroup">Add Nodegroup</button>&nbsp;|
<button id="buttonAddServiceNode">Add Node</button>

<br>

<table class="table_box" cellpadding="3" cellspacing="5">
 <tr>
  <td><b>Name:</b>&nbsp;&nbsp;<span id="spanServiceName"><?php echo $details['service_name']; ?></span> (<?php echo $details['service_id']; ?>)</td>
  <td><b>Priority:</b>&nbsp;&nbsp;<span id="spanServicePriority"><?php echo $details['priority']; ?></span></td>
  <td><b>Wiki:</b>&nbsp;&nbsp;<a id="aServiceWiki" href="<?php echo $mon->wiki_link . $details['wiki']; ?>" target="_blank" class="ext_link"><span id="spanServiceWiki"><?php echo $details['wiki']; ?></span></a></td>
 </tr>
 <tr>
  <td><b>Enabled:</b>&nbsp;&nbsp;<span id="spanServiceEnabled"><?php echo $details['enabled']; ?></span></td>
  <td><b>Notifications:</b>&nbsp;&nbsp;<span id="spanServiceNotifications"><?php echo $details['notifications']; ?></span></td>
  <td>&nbsp;</td>
 </tr>
 <tr>
  <td><b>Interval:</b>&nbsp;&nbsp;<span id="spanServiceInterval"><?php echo $details['check_interval']; ?></span> Minutes</td>
  <td><b>Attempts:</b>&nbsp;&nbsp;<span id="spanServiceAttempts"><?php echo $details['check_attempts']; ?></span></td>
  <td>&nbsp;</td>
 </tr>
 <tr>
  <td><b>Warning:</b>&nbsp;&nbsp;<span id="spanServiceWarning"><?php echo $details['warning_threshold']; ?></span></td>
  <td><b>Critical:</b>&nbsp;&nbsp;<span id="spanServiceCritical"><?php echo $details['critical_threshold']; ?></span></td>
  <td><b>Command:</b>&nbsp;&nbsp;<span id="spanServiceCommand"><?php echo $details['check_command']; ?></span> (<span id="spanServiceType"><?php echo $details['type']; ?></span>)</td>
 </tr>
 <tr><td colspan="3"><b>Args:</b><br><span id="spanServiceArgs"><?php echo $details['args']; ?></span></td></tr>
 <tr><td colspan="3"><b>Description:</b><br><span id="spanServiceDescription"><?php echo $details['description']; ?></span></td></tr>
</table>

<br>

<?php $notes->showDataTableDiv(); ?>

<h3>Nodegroups <img class="clickable" id="imgAddServiceNodegroup" src="/img/add.png"></h3>
<div id="divListNodegroups"></div>

<h3>Nodes <img class="clickable" id="imgAddServiceNode" src="/img/add.png"></h3>
<div id="divListNodes"></div>

<?php $history->showDataTableDiv(); ?>

<?php $notes->showForm('/api/w/v2/addmodify_service_note.php', 'service_id', $details['service_id']); ?>

<?php include('addmodify_service_form.inc'); ?>
<?php include('Monitoring/www/addmodify_nodegroup_service_form.inc'); ?>
<?php include('Monitoring/www/addmodify_node_service_form.inc'); ?>

</body>
</html>
<script type="text/javascript">
YAHOO.util.Event.addListener(window, "load", function() {
	var sNotesUrl = '/api/r/v2/list_service_notes.php?service_id=<?php echo $details['service_id']; ?>&';
<?php
include('Ops/includes/notes_js.inc');
?>

	var sHistoryUrl = '/api/r/v2/list_service_history.php?service_id=<?php echo $details['service_id']; ?>&';
<?php
include('Ops/includes/history_js.inc');
?>
	var myNodegroupsColumnDefs = [
		{key:"nodegroups.nodegroup", field:'["nodegroups.nodegroup"]', label:"Nodegroup", sortable:true, resizeable:true},
		{key:"service_nodegroups.priority", field:'["service_nodegroups.priority"]', label:"Priority", sortable:true, resizeable:true, formatter:formatServiceNodegroup},
		{key:"service_nodegroups.enabled", field:'["service_nodegroups.enabled"]', label:"Enabled", sortable:true, resizeable:true},
		{key:"service_nodegroups.notifications", field:'["service_nodegroups.notifications"]', label:"Notifications", sortable:true, resizeable:true},
		{key:"service_nodegroups.check_interval", field:'["service_nodegroups.check_interval"]', label:"Interval", sortable:true, resizeable:true, formatter:formatServiceNodegroup},
		{key:"service_nodegroups.check_attempts", field:'["service_nodegroups.check_attempts"]', label:"Attempts", sortable:true, resizeable:true, formatter:formatServiceNodegroup},
		{key:"service_nodegroups.args", field:'["service_nodegroups.args"]', label:"Args", sortable:true, resizeable:true, formatter:formatServiceNodegroup},
		{key:"service_nodegroups.warning_threshold", field:'["service_nodegroups.warning_threshold"]', label:"Warning", sortable:true, resizeable:true, formatter:formatServiceNodegroup},
		{key:"service_nodegroups.critical_threshold", field:'["service_nodegroups.critical_threshold"]', label:"Critical", sortable:true, resizeable:true, formatter:formatServiceNodegroup},
		{key:"delete_nodegroup", label:"", className:"center", formatter:YAHOO.BG.formatDeleteIcon}
	];

	var sNodegroupsUrl = '/api/r/v2/list_services_nodegroups.php?format=json&service_id=<?php echo $details['service_id']; ?>&';

	var myNodegroupsDataSource = new YAHOO.util.DataSource(sNodegroupsUrl);
	myNodegroupsDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
	myNodegroupsDataSource.responseSchema = {
		resultsList: "records",
		fields: [
			{key:'["nodegroups.check_interval"]', parser:"number"},
			{key:'["nodegroups.nodegroup"]'},
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
			{key:'["services.warning_threshold"]'}
		],
		metaFields: {
			totalRecords: "totalRecords"
		}
	};

	var myNodegroupsConfigs = YAHOO.BG.datatableConfigs('nodegroups.nodegroup', 'asc', 25);

	myNodegroupsDataTable = new YAHOO.widget.DataTable("divListNodegroups", myNodegroupsColumnDefs,
			myNodegroupsDataSource, myNodegroupsConfigs);

	myNodegroupsDataTable.handleDataReturnPayload = function(oRequest, oResponse, oPayload) {
		oPayload.totalRecords = oResponse.meta.totalRecords;
		return oPayload;
	};

	myNodegroupsDataTable.subscribe("rowMouseoverEvent", myNodegroupsDataTable.onEventHighlightRow);
	myNodegroupsDataTable.subscribe("rowMouseoutEvent", myNodegroupsDataTable.onEventUnhighlightRow);

	myAddServiceNodegroup = function(o) {
		myAddModifyServiceNodegroupDialog.setHeader('Add Nodegroup');
	 	document.formAddModifyServiceNodegroup.service_id.value = '<?php echo $details['service_id']; ?>';
		document.formAddModifyServiceNodegroup.service_id.readOnly = true;
		document.formAddModifyServiceNodegroup.nodegroup.readOnly = false;
		myAddModifyServiceNodegroupDialog.show();

		myAddModifyServiceNodegroupDialog.callback.success = function(o) {
			var output;
			try {
				output = YAHOO.lang.JSON.parse(o.responseText);
			} catch(e) {
				YAHOO.BG.dialogOnSuccess(o, myAddModifyServiceNodegroupDialog.form);
				return true;
			}

			YAHOO.BG.dialogOnSuccess(o, myAddModifyServiceNodegroupDialog.form);

			if(typeof(output.status) != "undefined") {
				if(output.status == "200" && typeof(output.details) != "undefined") {
					YAHOO.BG.dialogOnSuccess(o, myAddModifyServiceNodegroupDialog.form);
					myAddModifyServiceNodegroupDialog.form.reset();
					myAddModifyServiceNodegroupDialog.hide();
					YAHOO.BG.refreshDataTable(myNodegroupsDataTable);
				}
			}
		};
	};

<?php include('Monitoring/www/addmodify_nodegroup_service_js.inc'); ?>

	YAHOO.util.Event.addListener("buttonAddServiceNodegroup", "click", myAddServiceNodegroup);
	YAHOO.util.Event.addListener("imgAddServiceNodegroup", "click", myAddServiceNodegroup);

	myNodegroupsDataTable.subscribe("cellClickEvent", function(oArgs) {
		var target = oArgs.target;
		var record = this.getRecord(target);
		var nodegroup = record.getData('["nodegroups.nodegroup"]');

		if(this.getColumn(target).getKey() == "delete_nodegroup") {
			YAHOO.BG.handleYesNoYes = function() {
				var postData = 'delete=yes&service_id=<?php echo $details['service_id']; ?>&nodegroup=' + encodeURIComponent(nodegroup);
				var sUrl = '/api/w/v2/delete_service_nodegroup.php?format=json';

				var callback = {
					success: function(o) {
						YAHOO.BG.dialogOnSuccess(o);
						YAHOO.BG.refreshDataTable(myNodegroupsDataTable);
					},
					failure: YAHOO.BG.dialogOnFailure
				};

				YAHOO.BG.showLoading("show");
				YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, postData);
			};

			YAHOO.BG.yesNoDialog.setBody('Remove ' + nodegroup + '?');
			YAHOO.BG.yesNoDialog.show();
			return;
		}

		document.formAddModifyServiceNodegroup.nodegroup.value = record.getData('["nodegroups.nodegroup"]');
		document.formAddModifyServiceNodegroup.nodegroup.readOnly = true;
		document.formAddModifyServiceNodegroup.service_id.value = '<?php echo $details['service_id']; ?>';
		document.formAddModifyServiceNodegroup.service_id.readOnly = true;

		document.formAddModifyServiceNodegroup.args.value = record.getData('["service_nodegroups.args"]');
		document.formAddModifyServiceNodegroup.critical_threshold.value = record.getData('["service_nodegroups.critical_threshold"]');
		document.formAddModifyServiceNodegroup.check_attempts.value = record.getData('["service_nodegroups.check_attempts"]');
		document.formAddModifyServiceNodegroup.check_interval.value = record.getData('["service_nodegroups.check_interval"]');
		document.formAddModifyServiceNodegroup.enabled.value = record.getData('["service_nodegroups.enabled"]');
		document.formAddModifyServiceNodegroup.notifications.value = record.getData('["service_nodegroups.notifications"]');
		document.formAddModifyServiceNodegroup.priority.value = record.getData('["service_nodegroups.priority"]');
		document.formAddModifyServiceNodegroup.warning_threshold.value = record.getData('["service_nodegroups.warning_threshold"]');

		myAddModifyServiceNodegroupDialog.setHeader('Modify ' + nodegroup);
		myAddModifyServiceNodegroupDialog.show();

		myAddModifyServiceNodegroupDialog.callback.success = function(o) {
			YAHOO.BG.dialogOnSuccess(o, myAddModifyServiceNodegroupDialog.form);
			myAddModifyServiceNodegroupDialog.form.reset();
			myAddModifyServiceNodegroupDialog.hide();
			YAHOO.BG.refreshDataTable(myNodegroupsDataTable);
		};
	});

	var myNodesColumnDefs = [
		{key:"nodes.node", field:'["nodes.node"]', label:"Node", sortable:true, resizeable:true},
		{key:"service_nodes.priority", field:'["service_nodes.priority"]', label:"Priority", sortable:true, resizeable:true, formatter:formatServiceNode},
		{key:"service_nodes.enabled", field:'["service_nodes.enabled"]', label:"Enabled", sortable:true, resizeable:true},
		{key:"service_nodes.notifications", field:'["service_nodes.notifications"]', label:"Notifications", sortable:true, resizeable:true},
		{key:"service_nodes.check_interval", field:'["service_nodes.check_interval"]', label:"Interval", sortable:true, resizeable:true, formatter:formatServiceNode},
		{key:"service_nodes.check_attempts", field:'["service_nodes.check_attempts"]', label:"Attempts", sortable:true, resizeable:true, formatter:formatServiceNode},
		{key:"service_nodes.args", field:'["service_nodes.args"]', label:"Args", sortable:true, resizeable:true, formatter:formatServiceNode},
		{key:"service_nodes.warning_threshold", field:'["service_nodes.warning_threshold"]', label:"Warning", sortable:true, resizeable:true, formatter:formatServiceNode},
		{key:"service_nodes.critical_threshold", field:'["service_nodes.critical_threshold"]', label:"Critical", sortable:true, resizeable:true, formatter:formatServiceNode},
		{key:"delete_node", label:"", className:"center", formatter:YAHOO.BG.formatDeleteIcon}
	];

	var sNodesUrl = '/api/r/v2/list_services_nodes.php?format=json&service_id=<?php echo $details['service_id']; ?>&';

	var myNodesDataSource = new YAHOO.util.DataSource(sNodesUrl);
	myNodesDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
	myNodesDataSource.responseSchema = {
		resultsList: "records",
		fields: [
			{key:'["nodes.check_interval"]', parser:"number"},
			{key:'["nodes.node"]'},
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
			{key:'["services.warning_threshold"]'}
		],
		metaFields: {
			totalRecords: "totalRecords"
		}
	};

	var myNodesConfigs = YAHOO.BG.datatableConfigs('nodes.node', 'asc', 25);

	myNodesDataTable = new YAHOO.widget.DataTable("divListNodes", myNodesColumnDefs,
			myNodesDataSource, myNodesConfigs);

	myNodesDataTable.handleDataReturnPayload = function(oRequest, oResponse, oPayload) {
		oPayload.totalRecords = oResponse.meta.totalRecords;
		return oPayload;
	};

	myNodesDataTable.subscribe("rowMouseoverEvent", myNodesDataTable.onEventHighlightRow);
	myNodesDataTable.subscribe("rowMouseoutEvent", myNodesDataTable.onEventUnhighlightRow);

<?php include('Monitoring/www/addmodify_node_service_js.inc'); ?>

	myAddServiceNode = function(o) {
		myAddModifyServiceNodeDialog.setHeader('Add Node');
	 	document.formAddModifyServiceNode.service_id.value = '<?php echo $details['service_id']; ?>';
		document.formAddModifyServiceNode.service_id.readOnly = true;
		document.formAddModifyServiceNode.node.readOnly = false;
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
					myAddModifyServiceNodeDialog.form.reset();
					myAddModifyServiceNodeDialog.hide();
					YAHOO.BG.refreshDataTable(myNodesDataTable);
				}
			}
		};
	};

	YAHOO.util.Event.addListener("buttonAddServiceNode", "click", myAddServiceNode);
	YAHOO.util.Event.addListener("imgAddServiceNode", "click", myAddServiceNode);


	myNodesDataTable.subscribe("cellClickEvent", function(oArgs) {
		var target = oArgs.target;
		var record = this.getRecord(target);
		var node = record.getData('["nodes.node"]');

		if(this.getColumn(target).getKey() == "delete_node") {
			YAHOO.BG.handleYesNoYes = function() {
				var postData = 'delete=yes&service_id=<?php echo $details['service_id']; ?>&node=' + encodeURIComponent(node);
				var sUrl = '/api/w/v2/delete_service_node.php?format=json';

				var callback = {
					success: function(o) {
						YAHOO.BG.dialogOnSuccess(o);
						YAHOO.BG.refreshDataTable(myNodesDataTable);
					},
					failure: YAHOO.BG.dialogOnFailure
				};

				YAHOO.BG.showLoading("show");
				YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, postData);
			};

			YAHOO.BG.yesNoDialog.setBody('Remove ' + node + '?');
			YAHOO.BG.yesNoDialog.show();
			return;
		}

		document.formAddModifyServiceNode.node.value = record.getData('["nodes.node"]');
		document.formAddModifyServiceNode.node.readOnly = true;
		document.formAddModifyServiceNode.service_id.value = '<?php echo $details['service_id']; ?>';
		document.formAddModifyServiceNode.service_id.readOnly = true;

		document.formAddModifyServiceNode.args.value = record.getData('["service_nodes.args"]');
		document.formAddModifyServiceNode.critical_threshold.value = record.getData('["service_nodes.critical_threshold"]');
		document.formAddModifyServiceNode.check_attempts.value = record.getData('["service_nodes.check_attempts"]');
		document.formAddModifyServiceNode.check_interval.value = record.getData('["service_nodes.check_interval"]');
		document.formAddModifyServiceNode.enabled.value = record.getData('["service_nodes.enabled"]');
		document.formAddModifyServiceNode.notifications.value = record.getData('["service_nodes.notifications"]');
		document.formAddModifyServiceNode.priority.value = record.getData('["service_nodes.priority"]');
		document.formAddModifyServiceNode.warning_threshold.value = record.getData('["service_nodes.warning_threshold"]');

		myAddModifyServiceNodeDialog.setHeader('Modify ' + node);
		myAddModifyServiceNodeDialog.show();

		myAddModifyServiceNodeDialog.callback.success = function(o) {
			YAHOO.BG.dialogOnSuccess(o, myAddModifyServiceNodeDialog.form);
			myAddModifyServiceNodeDialog.form.reset();
			myAddModifyServiceNodeDialog.hide();
			YAHOO.BG.refreshDataTable(myNodesDataTable);
		};
	});

<?php include('addmodify_service_js.inc'); ?>

	YAHOO.util.Event.addListener("buttonModifyService", "click", function(o) {
		myAddModifyServiceDialog.setHeader('Modify service');
		myAddModifyServiceDialog.form.reset();

		document.formAddModifyService.service_id.value = '<?php echo $details['service_id']; ?>';
		document.formAddModifyService.service_name.value = document.getElementById('spanServiceName').innerHTML;
		document.formAddModifyService.priority.value = document.getElementById('spanServicePriority').innerHTML;
		document.formAddModifyService.wiki.value = document.getElementById('spanServiceWiki').innerHTML;
		document.formAddModifyService.enabled.value = document.getElementById('spanServiceEnabled').innerHTML;
		document.formAddModifyService.notifications.value = document.getElementById('spanServiceNotifications').innerHTML;
		document.formAddModifyService.check_interval.value = document.getElementById('spanServiceInterval').innerHTML;
		document.formAddModifyService.check_attempts.value = document.getElementById('spanServiceAttempts').innerHTML;
		document.formAddModifyService.check_command.value = document.getElementById('spanServiceCommand').innerHTML;
		document.formAddModifyService.warning_threshold.value = document.getElementById('spanServiceWarning').innerHTML;
		document.formAddModifyService.critical_threshold.value = document.getElementById('spanServiceCritical').innerHTML;
		document.formAddModifyService.args.value = document.getElementById('spanServiceArgs').innerHTML;
		document.formAddModifyService.description.value = document.getElementById('spanServiceDescription').innerHTML;

		myAddModifyServiceDialog.show();

		myAddModifyServiceDialog.callback.success = function(o) {
			var output;
			try {
				output = YAHOO.lang.JSON.parse(o.responseText);
			} catch(e) {
				YAHOO.BG.dialogOnSuccess(o, myAddModifyServiceDialog.form);
				return true;
			}

			if(typeof(output.status) != "undefined") {
				if(output.status == "200" && typeof(output.details) != "undefined") {
					YAHOO.BG.refreshDataTable(myHistoryDataTable);
					myAddModifyServiceDialog.form.reset();
					myAddModifyServiceDialog.hide();
					YAHOO.BG.updateStatusDiv('lightgreen', output.message);
					document.getElementById('spanServiceName').innerHTML = output.details.service_name;
					document.getElementById('spanServicePriority').innerHTML = output.details.priority;
					document.getElementById('spanServiceWiki').innerHTML = output.details.wiki;
					document.getElementById('aServiceWiki').href = '<?php echo $mon->wiki_link; ?>' + output.details.wiki;
					document.getElementById('spanServiceEnabled').innerHTML = output.details.enabled;
					document.getElementById('spanServiceNotifications').innerHTML = output.details.notifications;
					document.getElementById('spanServiceInterval').innerHTML = output.details.check_interval;
					document.getElementById('spanServiceAttempts').innerHTML = output.details.check_attempts;
					document.getElementById('spanServiceCommand').innerHTML = output.details.check_command;
					document.getElementById('spanServiceType').innerHTML = output.details.type;
					document.getElementById('spanServiceWarning').innerHTML = output.details.warning_threshold;
					document.getElementById('spanServiceCritical').innerHTML = output.details.critical_threshold;
					document.getElementById('spanServiceArgs').innerHTML = output.details.args;
					document.getElementById('spanServiceDescription').innerHTML = output.details.description;
				}
			}

			YAHOO.BG.dialogOnSuccess(o, myAddModifyServiceDialog.form);
		};
	});
});
</script>
