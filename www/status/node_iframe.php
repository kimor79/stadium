<?php

$project = 'mon';
$title = 'Monitoring';
include('Ops/head.inc');

include('Monitoring/www/www.inc');

$details = array();
/*
 * This is a nice to have (Trac #5588) but at the moment
 * is too slow. So just include the link to NodesDB for now.
$curl_opts = array(
	CURLOPT_CAINFO => '/etc/ssl/cacert.pem',
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_SSL_VERIFYPEER => true,
	CURLOPT_URL => 'https://nodes.com/api/r/v1/list_nodes.php?format=json&include_sub_queries=no&node=' . $_GET['node'],
);

$ch = curl_init();
curl_setopt_array($ch, $curl_opts);

$j_data = curl_exec($ch);

if(!curl_errno($ch) && curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
	$data = json_decode($j_data, true);

	if(array_key_exists('status', $data) && $data['status'] == 200) {
		$details = reset($data['records']);
	}
}
*/

?>

<body class="yui-skin-sam">

<table class="table_box" cellpadding="1" cellspacing="3">
 <tr>
  <td><a href="https://nodes.com/nodes/details.php?node=<?php echo $_GET['node']; ?>" class="ext_link" target="_blank"><?php echo $_GET['node'];?></a>
<?php
if(!empty($details)) {
	echo '&nbsp|</td>';
	printf("<td><b>Status:</b> %s (<b>Site:</b> %s) |</td>", $details['node_status'], $details['site_status']);
	printf("<td><b>Maintenance:</b> %s (<b>Site:</b> %s) |</td>", $details['node_maintenance'], $details['site_maintenance']);
	printf("<td><b>Production:</b> %s (<b>Site:</b> %s) |</td>", $details['node_production'], $details['site_production']);
} else {
	echo '</td>';
}
?>
 </tr>
</table>

<br>
<span class="clickable_text" id="spanToggleRefresh">Refresh: <span id="spanStatusRefresh">on</span></span>&nbsp;| Last Refresh: <span id="spanStatusLastRefresh"></span>
<br><br>

<div id="divListCurrent"></div>

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
	YAHOO.BG.updateStatusDiv = function (sColor, sText) {
		var sDiv = parent.document.getElementById('tdStatusUpdate');

		sDiv.innerHTML = sText;
		sDiv.style.backgroundColor = sColor;
	};

	var myColumnDefs = [
		{key:"current_states.c_time", field:'["current_states.c_time"]', label:"Date", sortable:true, resizeable:true, formatter:YAHOO.widget.DataTable.formatDate},
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

	var sUrl = '/api/r/v2/list_current_states.php?format=json&combine=no&show_maintenance=yes&show_not_production=yes&node=<?php echo urlencode($_GET['node']); ?>&';

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

	var myConfigs = YAHOO.BG.datatableConfigs('services.priority', 'desc', 25);
	myConfigs.formatRow = formatCurrentRow;

	myDataTable = new YAHOO.widget.DataTable("divListCurrent", myColumnDefs, myDataSource, myConfigs);

	myDataTable.handleDataReturnPayload = function(oRequest, oResponse, oPayload) {
		oPayload.totalRecords = oResponse.meta.totalRecords;
		return oPayload;
	};

/*
	myDataTable.subscribe("rowMouseoverEvent", myDataTable.onEventHighlightRow);
	myDataTable.subscribe("rowMouseoutEvent", myDataTable.onEventUnhighlightRow);
*/

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
