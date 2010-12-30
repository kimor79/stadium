<?php

include_once('Monitoring/includes/ro.inc');
include_once('Monitoring/www/www.inc');

if(empty($_GET['nodegroup'])) {
	$www->giveRedirect('/nodegroups/search.php');
	exit(0);
}

list($r_details, $error) = $mon->listNodegroups(array('nodegroup' => $_GET['nodegroup']));

if(count($r_details) != 1) {
	$www->giveRedirect('/nodegroups/search.php?nodegroup=' . urlencode($_GET['nodegroup']));
	exit(0);
}

$details = reset($r_details);

include('top.inc');
?>

<br>
<button id="buttonModifyNodegroup">Modify</button>&nbsp;|
<button id="buttonAddServiceNodegroup">Add Service</button>

<br>

<table class="table_box" cellpadding="3" cellspacing="5">
 <tr>
  <td colspan="3"><b>Nodegroup:</b>&nbsp;&nbsp;<a href="https://nodegroups.com/nodegroups/details.php?nodegroup=<?php echo $details['nodegroup']; ?>" target="_blank" class="ext_link"><?php echo $details['nodegroup']; ?></a></td>
 </tr>
 <tr>
  <td><b>Enabled:</b>&nbsp;&nbsp;<span id="spanNodegroupEnabled"><?php echo $details['enabled']; ?></span></td>
  <td><b>Notifications:</b>&nbsp;&nbsp;<span id="spanNodegroupNotifications"><?php echo $details['notifications']; ?></span></td>
  <td><b>Interval:</b>&nbsp;&nbsp;<span id="spanNodegroupInterval"><?php echo $details['check_interval']; ?></span> Minutes</td>
 </tr>
</table>

<br>

<h3>Services <img class="clickable" id="imgAddServiceNodegroup" src="/img/add.png"></h3>
<div id="divListServices"></div>

<h3>Node Overrides</h3>
<div id="divListNodes"></div>

<?php $history->showDataTableDiv(); ?>

<?php include('Monitoring/www/addmodify_nodegroup_service_form.inc'); ?>
<?php include('Monitoring/www/addmodify_nodegroup_node_override_form.inc'); ?>

<div id="divModifyNodegroup">
 <div class="hd">Modify <?php echo $details['nodegroup']; ?></div>
 <div class="bd">
<form name="formModifyNodegroup" method="POST" action="/api/w/v2/modify_nodegroup.php">
<input type="hidden" name="nodegroup" value="<?php echo $details['nodegroup']; ?>">
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
	var sHistoryUrl = '/api/r/v2/list_nodegroup_history.php?nodegroup=<?php echo $details['nodegroup']; ?>&';
<?php
include('Ops/includes/history_js.inc');
?>
	var myServicesColumnDefs = [
		{key:"services.service_id", field:'["services.service_id"]', label:"ID", sortable:true, resizeable:true},
		{key:"services.service_name", field:'["services.service_name"]', label:"Service", sortable:true, resizeable:true},
		{key:"service_nodegroups.priority", field:'["service_nodegroups.priority"]', label:"Priority", sortable:true, resizeable:true, formatter:formatServiceNodegroup},
		{key:"service_nodegroups.enabled", field:'["service_nodegroups.enabled"]', label:"Enabled", sortable:true, resizeable:true},
		{key:"service_nodegroups.notifications", field:'["service_nodegroups.notifications"]', label:"Notifications", sortable:true, resizeable:true},
		{key:"service_nodegroups.check_interval", field:'["service_nodegroups.check_interval"]', label:"Interval", sortable:true, resizeable:true, formatter:formatServiceNodegroup},
		{key:"service_nodegroups.check_attempts", field:'["service_nodegroups.check_attempts"]', label:"Attempts", sortable:true, resizeable:true, formatter:formatServiceNodegroup},
		{key:"service_nodegroups.args", field:'["service_nodegroups.args"]', label:"Args", sortable:true, resizeable:true, formatter:formatServiceNodegroup},
		{key:"service_nodegroups.warning_threshold", field:'["service_nodegroups.warning_threshold"]', label:"Warning", sortable:true, resizeable:true, formatter:formatServiceNodegroup},
		{key:"service_nodegroups.critical_threshold", field:'["service_nodegroups.critical_threshold"]', label:"Critical", sortable:true, resizeable:true, formatter:formatServiceNodegroup},
		{key:"delete_service", label:"", className:"center", formatter:YAHOO.BG.formatDeleteIcon}
	];

	var sServicesUrl = '/api/r/v2/list_services_nodegroups.php?format=json&nodegroup=<?php echo $details['nodegroup']; ?>&';

	var myServicesDataSource = new YAHOO.util.DataSource(sServicesUrl);
	myServicesDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
	myServicesDataSource.responseSchema = {
		resultsList: "records",
		fields: [
			{key:'["nodegroups.check_interval"]', parser:"number"},
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

	var myServicesConfigs = YAHOO.BG.datatableConfigs('services.service_name', 'asc', 25);

	myServicesDataTable = new YAHOO.widget.DataTable("divListServices", myServicesColumnDefs,
			myServicesDataSource, myServicesConfigs);

	myServicesDataTable.handleDataReturnPayload = function(oRequest, oResponse, oPayload) {
		oPayload.totalRecords = oResponse.meta.totalRecords;
		return oPayload;
	};

	myServicesDataTable.subscribe("rowMouseoverEvent", myServicesDataTable.onEventHighlightRow);
	myServicesDataTable.subscribe("rowMouseoutEvent", myServicesDataTable.onEventUnhighlightRow);

	myAddServiceNodegroup = function(o) {
		myAddModifyServiceNodegroupDialog.setHeader('Add Service');
	 	document.formAddModifyServiceNodegroup.nodegroup.value = '<?php echo $details['nodegroup']; ?>';
		document.formAddModifyServiceNodegroup.nodegroup.readOnly = true;
		document.formAddModifyServiceNodegroup.service_id.readOnly = false;
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
					YAHOO.BG.refreshDataTable(myServicesDataTable);
					YAHOO.BG.refreshDataTable(myHistoryDataTable);
				}
			}
		};
	};

<?php include('Monitoring/www/addmodify_nodegroup_service_js.inc'); ?>

	YAHOO.util.Event.addListener("buttonAddServiceNodegroup", "click", myAddServiceNodegroup);
	YAHOO.util.Event.addListener("imgAddServiceNodegroup", "click", myAddServiceNodegroup);

	myServicesDataTable.subscribe("cellClickEvent", function(oArgs) {
		var target = oArgs.target;
		var record = this.getRecord(target);
		var service = record.getData('["services.service_id"]');
		var serviceName = record.getData('["services.service_name"]');

		if(this.getColumn(target).getKey() == "delete_service") {
			YAHOO.BG.handleYesNoYes = function() {
				var postData = 'delete=yes&nodegroup=<?php echo $details['nodegroup']; ?>&service_id=' + encodeURIComponent(service);
				var sUrl = '/api/w/v2/delete_service_nodegroup.php?format=json';

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

		document.formAddModifyServiceNodegroup.nodegroup.value = '<?php echo $details['nodegroup']; ?>';
		document.formAddModifyServiceNodegroup.nodegroup.readOnly = true;
		document.formAddModifyServiceNodegroup.service_id.value = service;
		document.formAddModifyServiceNodegroup.service_id.readOnly = true;

		document.formAddModifyServiceNodegroup.args.value = record.getData('["service_nodegroups.args"]');
		document.formAddModifyServiceNodegroup.critical_threshold.value = record.getData('["service_nodegroups.critical_threshold"]');
		document.formAddModifyServiceNodegroup.check_attempts.value = record.getData('["service_nodegroups.check_attempts"]');
		document.formAddModifyServiceNodegroup.check_interval.value = record.getData('["service_nodegroups.check_interval"]');
		document.formAddModifyServiceNodegroup.enabled.value = record.getData('["service_nodegroups.enabled"]');
		document.formAddModifyServiceNodegroup.notifications.value = record.getData('["service_nodegroups.notifications"]');
		document.formAddModifyServiceNodegroup.priority.value = record.getData('["service_nodegroups.priority"]');
		document.formAddModifyServiceNodegroup.warning_threshold.value = record.getData('["service_nodegroups.warning_threshold"]');

		myAddModifyServiceNodegroupDialog.setHeader('Modify ' + serviceName);
		myAddModifyServiceNodegroupDialog.show();

		myAddModifyServiceNodegroupDialog.callback.success = function(o) {
			YAHOO.BG.dialogOnSuccess(o, myAddModifyServiceNodegroupDialog.form);
			myAddModifyServiceNodegroupDialog.form.reset();
			myAddModifyServiceNodegroupDialog.hide();
			YAHOO.BG.refreshDataTable(myServicesDataTable);
			YAHOO.BG.refreshDataTable(myHistoryDataTable);
		};
	});

	var myOverrideColumnDefs = [
		{key:"services.service_id", field:'["services.service_id"]', label:"ID", sortable:true, resizeable:true},
		{key:"service_nodegroup_nodes.node", field:'["service_nodegroup_nodes.node"]', label:"Node", sortable:true, resizeable:true},
		{key:"services.service_name", field:'["services.service_name"]', label:"Service", sortable:true, resizeable:true},
		{key:"service_nodegroup_nodes.enabled", field:'["service_nodegroup_nodes.enabled"]', label:"Enabled", sortable:true, resizeable:true},
		{key:"service_nodegroup_nodes.notifications", field:'["service_nodegroup_nodes.notifications"]', label:"Notifications", sortable:true, resizeable:true}
	];

	var sOverrideUrl = '/api/r/v2/list_nodegroup_node_overrides.php?format=json&nodegroup=<?php echo $details['nodegroup']; ?>&';

	var myOverrideDataSource = new YAHOO.util.DataSource(sOverrideUrl);
	myOverrideDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
	myOverrideDataSource.responseSchema = {
		resultsList: "records",
		fields: [
			{key:'["service_nodegroup_nodes.enabled"]'},
			{key:'["service_nodegroup_nodes.node"]'},
			{key:'["service_nodegroup_nodes.notifications"]'},
			{key:'["services.service_id"]', parser:"number"},
			{key:'["services.service_name"]'}
		],
		metaFields: {
			totalRecords: "totalRecords"
		}
	};

	var myOverrideConfigs = YAHOO.BG.datatableConfigs('services.service_name', 'asc', 25);

	myOverrideDataTable = new YAHOO.widget.DataTable("divListNodes", myOverrideColumnDefs,
			myOverrideDataSource, myOverrideConfigs);

	myOverrideDataTable.handleDataReturnPayload = function(oRequest, oResponse, oPayload) {
		oPayload.totalRecords = oResponse.meta.totalRecords;
		return oPayload;
	};

	myOverrideDataTable.subscribe("rowMouseoverEvent", myOverrideDataTable.onEventHighlightRow);
	myOverrideDataTable.subscribe("rowMouseoutEvent", myOverrideDataTable.onEventUnhighlightRow);

<?php include('Monitoring/www/addmodify_nodegroup_node_override_js.inc'); ?>

	myOverrideDataTable.subscribe("cellClickEvent", function(oArgs) {
		var target = oArgs.target;
		var record = this.getRecord(target);

		var enabled = record.getData('["service_nodegroup_nodes.enabled"]');
		var node = record.getData('["service_nodegroup_nodes.node"]');
		var notifications = record.getData('["service_nodegroup_nodes.notifications"]');
		var service = record.getData('["services.service_id"]');

		document.formAddModifyNodegroupNodeOverride.nodegroup.value = '<?php echo $details['nodegroup']; ?>';
		document.formAddModifyNodegroupNodeOverride.node.value = node;
		document.formAddModifyNodegroupNodeOverride.node.readOnly = true;
		document.formAddModifyNodegroupNodeOverride.service_id.value = service;

		document.formAddModifyNodegroupNodeOverride.enabled.value = enabled;
		document.formAddModifyNodegroupNodeOverride.notifications.value = notifications;

		myAddModifyNodegroupNodeOverrideDialog.setHeader('Override ' + node);
		myAddModifyNodegroupNodeOverrideDialog.show();

		myAddModifyNodegroupNodeOverrideDialog.callback.success = function(o) {
			YAHOO.BG.dialogOnSuccess(o, myAddModifyNodegroupNodeOverrideDialog.form);
			myAddModifyNodegroupNodeOverrideDialog.form.reset();
			myAddModifyNodegroupNodeOverrideDialog.hide();
			YAHOO.BG.refreshDataTable(myOverrideDataTable);
			YAHOO.BG.refreshDataTable(myHistoryDataTable);
		};
	});

	var handleModifyNodegroupCancel = function() {
		myModifyNodegroupDialog.form.reset();
		this.cancel();
	};

	var handleModifyNodegroupSubmit = function() {
		YAHOO.BG.showLoading('show');
		this.submit();
	};

	var myModifyNodegroupButtons = [
		{ text:"Submit", handler:handleModifyNodegroupSubmit, isDefault:true },
		{ text:"Cancel", handler:handleModifyNodegroupCancel }
	];

	var myModifyNodegroupDialog = new YAHOO.widget.Dialog("divModifyNodegroup",
		{
			fixedcenter: true,
			hideaftersubmit: false,
			visible: false,
			width: "400px",
			zIndex: 100
		}
	);
	myModifyNodegroupDialog.cfg.queueProperty("buttons", myModifyNodegroupButtons);
	myModifyNodegroupDialog.render();

	myModifyNodegroupDialog.validate = function() {
		return true;
	};

	myModifyNodegroupDialog.callback.failure = YAHOO.BG.dialogOnFailure;

	myModifyNodegroupDialog.callback.success = function(o) {
		var output;
		try {
			output = YAHOO.lang.JSON.parse(o.responseText);
		} catch(e) {
			YAHOO.BG.dialogOnSuccess(o, myModifyNodegroupDialog.form);
			return true;
		}

		YAHOO.BG.dialogOnSuccess(o, myModifyNodegroupDialog.form);

		if(typeof(output.status) != "undefined") {
			if(output.status == "200" && typeof(output.details) != "undefined") {
				YAHOO.BG.dialogOnSuccess(o, myModifyNodegroupDialog.form);
				myModifyNodegroupDialog.form.reset();
				YAHOO.BG.refreshDataTable(myServicesDataTable);
				YAHOO.BG.refreshDataTable(myHistoryDataTable);
				document.getElementById('spanNodegroupEnabled').innerHTML = output.details.enabled;
				document.getElementById('spanNodegroupNotifications').innerHTML = output.details.notifications;
				document.getElementById('spanNodegroupInterval').innerHTML = output.details.check_interval;
				myModifyNodegroupDialog.hide();
			}
		}
	};

	YAHOO.util.Event.addListener("buttonModifyNodegroup", "click", function(o) {
		document.formModifyNodegroup.enabled.value = document.getElementById('spanNodegroupEnabled').innerHTML;
		document.formModifyNodegroup.notifications.value = document.getElementById('spanNodegroupNotifications').innerHTML;
		document.formModifyNodegroup.check_interval.value = document.getElementById('spanNodegroupInterval').innerHTML;
		myModifyNodegroupDialog.show();
	});
});
</script>
