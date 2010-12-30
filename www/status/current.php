<?php

include('top.inc');
include('Monitoring/includes/ro.inc');

?>

<br>

<div id="divCurrentSettings">
 <div class="hd"></div>
 <div class="bd">

<form name="formCurrentSettings" method="GET" action="">
<table>
 <tr><td colspan="2">
<label for="combine">Combine:</label><input type="checkbox" name="combine" <?php echo $ops->isYesNo($_GET['combine'], true) ? 'checked' : '' ; ?>>&nbsp;|
<label for="show_acknowledgements">Acknowledgements:</label><input type="checkbox" name="show_acknowledgements" <?php echo $ops->isYesNo($_GET['show_acknowledgements'], true) ? 'checked' : '' ; ?>>&nbsp;|
<label for="show_maintenance">Maintenance:</label><input type="checkbox" name="show_maintenance" <?php echo $ops->isYesNo($_GET['show_maintenance'], false) ? 'checked' : '' ; ?>>&nbsp;|
<label for="show_not_production">Not in Production:</label><input type="checkbox" name="show_not_production" <?php echo $ops->isYesNo($_GET['show_not_production'], false) ? 'checked' : '' ; ?>>
 </td></tr>
 <tr><td colspan="2">&nbsp;</td></tr>
 <tr>
<td><label for="service_id">Service ID:</label></td><td><input type="text" name="service_id" value="<?php echo $_GET['service_id']; ?>" size="5">&nbsp;
<label for="service_name">Service:</label>&nbsp;<input type="text" name="service_name" value="<?php echo $_GET['service_name']; ?>" size="30">
 </td></tr>
 <tr>
<td><label for="node">Node:</label></td><td><input type="text" name="node" value="<?php echo $_GET['node']; ?>" size="30">
 </td></tr>
</table>
</form>

 </div>
 <div class="ft"></div>
</div>

<br>
<span class="clickable_text" id="spanToggleRefresh">Refresh: <span id="spanStatusRefresh">on</span></span>&nbsp;| Last Refresh: <span id="spanStatusLastRefresh"></span>
<br><br>

<div id="divListCurrent"></div>

<div id="nodePanel">
 <div class="hd"></div>
 <div class="bd"><iframe id="iframeNode" frameborder="0" width="100%" height="100%" scrolling="yes" src=""></iframe></div>
</div>

<?php
if(isset($_SERVER['REMOTE_USER'])) {
?>
<div id="divAcknowledgeAlert">
 <div class="bd">
  <form method="POST" action="/api/w/v2/acknowledge_alert.php">
   <input type="hidden" name="service_id" value="">
   <input type="hidden" name="node" value="">
   <input type="hidden" name="entity" value="">
   <input type="hidden" name="state" value="">
  <table width="99%">
   <tr>
    <td><label for="comment">Comment</label></td>
    <td><textarea name="comment" rows="10" cols="35"></textarea></td>
   </tr>
  </table>
  </form>
 </div>
</div>
<?php
}
?>

</body>
</html>
<script type="text/javascript">
YAHOO.util.Event.addListener(window, "load", function() {
	myPanel = new YAHOO.widget.Panel("nodePanel", {
		close: true,
		draggable: true,
		fixedcenter: true,
		height: '450px',
		visible: false,
		width: '1100px'
	});

	myPanel.render();

	myPanel.hideEvent.subscribe(function() {
		document.getElementById('iframeNode').src = '';
	});

	handleSubmit = function() {
		var searchRequest = '';
		var data = myDialog.getData();

		if(!data.combine) {
			searchRequest += 'combine=no&';
		}

		if(!data.show_acknowledgements) {
			searchRequest += 'show_acknowledgements=no&';
		}

		if(data.show_maintenance) {
			searchRequest += 'show_maintenance=yes&';
		}

		if(data.show_not_production) {
			searchRequest += 'show_not_production=yes&';
		}

		if(data.service_id != '') {
			searchRequest += 'service_id=' + encodeURIComponent(data.service_id) + '&';
		}

		if(data.service_name != '') {
			searchRequest += 'service_name=' + encodeURIComponent(data.service_name) + '&';
		}

		if(data.node != '') {
			searchRequest += 'node=' + encodeURIComponent(data.node) + '&';
		}

		window.location = '?' + searchRequest;
	};

	var myButtons = [
		{ text:"Filter", handler:handleSubmit, isDefault:true }
	];

	var myDialog = new YAHOO.widget.Dialog("divCurrentSettings", {
		close: false,
		draggable: false,
		fixedcenter: false,
		hideaftersubmit: false,
		underlay: "none",
		visible: true,
		width: "550px"
	});

	myDialog.cfg.queueProperty("buttons", myButtons);
	myDialog.render();

	var myEnterDialog = new YAHOO.util.KeyListener("divCurrentSettings", { keys:13 }, { fn:handleSubmit });
	myEnterDialog.enable();

	var myColumnDefs = [
		{key:"current_states.c_time", field:'["current_states.c_time"]', label:"Date", sortable:true, resizeable:true, formatter:YAHOO.widget.DataTable.formatDate},
		{key:"current_states.node", field:'["current_states.node"]', label:"Node", sortable:true, resizeable:true, formatter:formatCurrentNode},
		{key:"services.service_name", field:'["services.service_name"]', label:"Service", sortable:true, resizeable:true, formatter:formatServiceName},
		{key:"current_states.entity", field:'["current_states.entity"]', label:"Entity", sortable:true, resizeable:true, formatter:formatEntity},
		{key:"services.priority", field:'["services.priority"]', label:"Priority", sortable:true, resizeable:true, formatter:formatCurrentPriority},
		{key:"current_states.state", field:'["current_states.state"]', label:"State", sortable:true, resizeable:true, formatter:formatState},
		{key:"current_states.message", field:'["current_states.message"]', label:"Message", sortable:true, resizeable:true, formatter:YAHOO.BG.formatLongString},
		{key:"current_states.monitor", field:'["current_states.monitor"]', label:"Monitor", sortable:true, resizeable:true, formatter:formatMonitor},
<?php
if(isset($_SERVER['REMOTE_USER'])) {
?>
		{key:"manage_icons", label:"", className:"center", formatter:formatCurrentIcons},
<?php
} else {
?>
		{key:"manage_icons", label:"", className:"center", formatter:formatNoCurrentIcons},
<?php
}
?>
		{key:"services.wiki", field:'["services.wiki"]', label:"Wiki", sortable:true, resizeable:true, formatter:formatWiki}
	];

<?php
$api_url_requests = array();

if(isset($_GET['combine'])) {
	$api_url_requests[] = sprintf("combine=%s", $_GET['combine']);
}

if(isset($_GET['show_acknowledgements'])) {
	$api_url_requests[] = sprintf("show_acknowledgements=%s", $_GET['show_acknowledgements']);
}

if(isset($_GET['show_maintenance'])) {
	$api_url_requests[] = sprintf("show_maintenance=%s", $_GET['show_maintenance']);
}

if(isset($_GET['show_not_production'])) {
	$api_url_requests[] = sprintf("show_not_production=%s", $_GET['show_not_production']);
}

if(isset($_GET['service_id'])) {
	$api_url_requests[] = sprintf("service_id=%d", $_GET['service_id']);
}

if(isset($_GET['service_name'])) {
	$api_url_requests[] = sprintf("service_name=%s", urlencode('%' . $_GET['service_name'] .'%'));
}

if(isset($_GET['node'])) {
	$api_url_requests[] = sprintf("node=%s", urlencode('%' . $_GET['node'] .'%'));
}

?>

	var sUrl = '/api/r/v2/list_current_states.php?format=json&<?php echo implode('&', $api_url_requests); ?>&';

	var myDataSource = new YAHOO.util.DataSource(sUrl);
	myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
	myDataSource.responseSchema = {
		resultsList: "records",
		fields: [
			{key:'["acknowledgements.c_time"]'},
			{key:'["acknowledgements.comment"]'},
			{key:'["acknowledgements.user"]'},
			{key:'["check_commands.type"]'},
			{key:'["current_states.c_time"]', parser:YAHOO.util.DataSource.parseDate},
			{key:'["current_states.entity"]'},
			{key:'["current_states.message"]'},
			{key:'["current_states.monitor"]'},
			{key:'["current_states.node"]'},
			{key:'["current_states.service_id"]', parser:"number"},
			{key:'["current_states.state"]', parser:"number"},
			{key:"multiple.entity"},
			{key:"multiple.monitor"},
			{key:"multiple.service_name"},
			{key:'["nodes.priority"]', parser:"number"},
			{key:'["service_nodegroup_nodes.enabled"]', parser:"number"},
			{key:'["service_nodegroup_nodes.service_id"]', parser:"number"},
			{key:'["service_nodegroups.enabled"]', parser:"number"},
			{key:'["service_nodegroups.priority"]', parser:"number"},
			{key:'["service_nodegroups.service_id"]', parser:"number"},
			{key:'["service_nodes.enabled"]', parser:"number"},
			{key:'["service_nodes.priority"]', parser:"number"},
			{key:'["service_nodes.service_id"]', parser:"number"},
			{key:'["services.priority"]', parser:"number"},
			{key:'["services.service_name"]'},
			{key:'["services.wiki"]'}
		],
		metaFields: {
			totalRecords: "totalRecords"
		}
	};

	var myConfigs = YAHOO.BG.datatableConfigs('services.priority', 'desc', 100);
	myConfigs.formatRow = formatCurrentRow;

	myDataTable = new YAHOO.widget.DataTable("divListCurrent", myColumnDefs, myDataSource, myConfigs);

	myDataTable.handleDataReturnPayload = function(oRequest, oResponse, oPayload) {
		oPayload.totalRecords = oResponse.meta.totalRecords;
		return oPayload;
	};

	myDataTable.subscribe("rowMouseoverEvent", myDataTable.onEventHighlightRow);
	myDataTable.subscribe("rowMouseoutEvent", myDataTable.onEventUnhighlightRow);

	myDataSource.subscribe("responseEvent", function(o) {
		document.getElementById("spanStatusLastRefresh").innerHTML = Date();
	});

	var myToolTip = new YAHOO.widget.Tooltip("myTooltip");

	myDataTable.on('cellMouseoverEvent', function (oArgs) {
		var target = oArgs.target;
		var column = this.getColumn(target);

		switch(column.key) {
			case 'current_states.message':
			case 'manage_icons':
				var data;
				var record = this.getRecord(target);

				if(column.key == 'manage_icons') {
					var tDate = record.getData('["acknowledgements.c_time"]');
					if(tDate) {
						var date = YAHOO.util.DataSource.parseDate(tDate);
						data = record.getData('["acknowledgements.user"]');
						data += ': ' + date.toLocaleString();
						data += "\n\n" + record.getData('["acknowledgements.comment"]');
					} else {
						break;
					}
				} else {
					data = record.getData('["' + column.key + '"]');
				}

				var xy = [parseInt(oArgs.event.clientX,10) + 10, parseInt(oArgs.event.clientY,10) + 10];

				myToolTip.setBody('<pre>' + data + '</pre>');
				myToolTip.cfg.setProperty('xy', xy);
				myToolTip.show();

				break;
			default:	
				myToolTip.hide();
		}
	});

	myDataTable.on('cellMouseoutevent', function (oArgs) {
		myToolTip.hide();
	});

	myRefresh = function() {
		oState = myDataTable.getState();
		var dir = (oState.sortedBy && oState.sortedBy.dir == 'yui-dt-asc') ? 'asc' : 'desc';

		var request = 'sort=' + oState.sortedBy.key + '&dir=' + dir
		request += '&startIndex=' + oState.pagination.recordOffset + '&results=' + oState.pagination.rowsPerPage;

		myDataTable.set('initialRequest', request);
		YAHOO.BG.refreshDataTable(myDataTable);
	};

<?php
if(isset($_SERVER['REMOTE_USER'])) {
?>
	var handleCancel = function() {
		this.cancel();
	};

	var handleSubmit = function() {
		this.submit();
	};

	var myButtons = [
		{ text:"Submit", handler:handleSubmit, isDefault:true },
		{ text:"Cancel", handler:handleCancel }
	];

	var myAckDialog = new YAHOO.widget.Dialog("divAcknowledgeAlert", {
		fixedcenter: true,
		visible: false,
		width: "400px",
		zIndex: 100
	});

	myAckDialog.cfg.queueProperty("buttons", myButtons);
	myAckDialog.render();

	myAckDialog.validate = function() {
		var comment = this.getData().comment;
		if(comment == '') {
			YAHOO.BG.updateStatusDiv('orange', 'Please enter a comment');
			return false;
		}

		if(comment.length < 3) {
			YAHOO.BG.updateStatusDiv('orange', 'Comment is too short');
			return false;
		}

		return true;
	};

	var showAckDialog = function(service_id, service_name, node, entity, state) {
		myAckDialog.setHeader('Acknowledge ' + service_name + ' on ' + node);
		myAckDialog.form.reset();
		myAckDialog.form.node.value = node;
		myAckDialog.form.service_id.value = service_id;
		myAckDialog.form.entity.value = entity;
		myAckDialog.form.state.value = state;
		myAckDialog.show();
		return false;
	};

	myAckDialog.callback.success = function(o) {
		YAHOO.BG.dialogOnSuccess(o, myAckDialog.form);
		myRefresh();
	};

	myAckDialog.callback.failure = YAHOO.BG.dialogOnFailure;

	myDataTable.subscribe("linkClickEvent", function(oArgs) {
		var record = this.getRecord(oArgs.target);

		if(oArgs.target.hash.substr(0, 1) != '#') {
			return false;
		}

		YAHOO.util.Event.stopEvent(oArgs.event);
		var entity = record.getData('["current_states.entity"]');
		var node = record.getData('["current_states.node"]');
		var service_id = record.getData('["current_states.service_id"]');
		var service_name = record.getData('["services.service_name"]');
		var state = record.getData('["current_states.state"]');

		switch(oArgs.target.hash.substr(1)) {
			case 'ack':
				showAckDialog(service_id, service_name, node, entity, state);
				return false;
		}

		return false;
	});
<?php
}
?>

	myDataTable.subscribe("rowClickEvent", function(oArgs) {
		var record = this.getRecord(oArgs.target);
		var node = record.getData('["current_states.node"]');

		document.getElementById('iframeNode').src = '/status/node_iframe.php?node=' + encodeURIComponent(node);
		myPanel.setHeader(node);
		myPanel.show();
	});

	document.getElementById("spanStatusLastRefresh").innerHTML = Date();
	var doRefresh = setInterval(myRefresh, 30000);
	YAHOO.util.Event.addListener("spanToggleRefresh", "click", function(o) {
		if(doRefresh == false) {
			doRefresh = setInterval(myRefresh, 30000);
			document.getElementById("spanStatusRefresh").innerHTML = 'on';
		} else {
			clearInterval(doRefresh);
			doRefresh = false;
			document.getElementById("spanStatusRefresh").innerHTML = 'off';
		}
	});
});
</script>
