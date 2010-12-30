<?php
include('Monitoring/includes/ro.inc');
include('top.inc');

$start_time = date('Y/m/d H:i:s', time() - 86400); // Default to -24 hours
if(!$ops->isBlank($_GET['start_time'])) {
	$start_time = $_GET['start_time'];
}

?>

<br>

<table>
 <tr><td>

<div id="divSearchHistory">
 <div class="hd"></div>
 <div class="bd">
<form name="formSearchHistory" method="GET" action="">
<table>
 <tr><td><label for="node">Node:<label></td><td><input type="text" name="node" size="30" value="<?php echo $_GET['node']; ?>"></td></tr>
 <tr><td><label for="service_id">Service ID:<label></td><td><input type="text" name="service_id" size="5" value="<?php echo $_GET['service_id']; ?>"></td></tr>
 <tr><td><label for="service_name">Service:<label></td><td><input type="text" name="service_name" size="30" value="<?php echo $_GET['service_name']; ?>"></td></tr>
 <tr><td><label for="entity">Entity:<label></td><td><input type="text" name="entity" size="30" value="<?php echo $_GET['entity']; ?>"></td></tr>
 <tr><td><label for="monitor">Monitor:<label></td><td><input type="text" name="monitor" size="30" value="<?php echo $_GET['monitor']; ?>"></td></tr>
 <tr>
  <td><label for="state">State:<label></td>
  <td>
<select name="state">
 <option value=""></option>
<?php
foreach($mon->states as $id => $name) {
	echo '<option ';
	if(isset($_GET['state']) && $_GET['state'] == $id) {
		echo 'selected ';
	}
	printf("value=\"%s\">%s</option>", $id, $name);
}
?>
</select>
  </td>
 </tr>
 <tr><td><label for="start_time">Start Time:<label></td><td><input type="text" name="start_time" size="20" value="<?php echo $start_time; ?>">&nbsp;<button id="buttonStartTime" onclick="return(false);"><img src="/img/calbtn.gif" class="img"></button></td></tr>
 <tr><td><label for="finish_time">Finish Time:<label></td><td><input type="text" name="finish_time" size="20" value="<?php echo $_GET['finish_time']; ?>">&nbsp;<button id="buttonFinishTime" onclick="return(false);"><img src="/img/calbtn.gif" class="img"></button></td></tr>
</table>
</form>
 </div>
 <div class="ft"></div>
</div>

 </td><td id="tdCalendar"></td></tr>
</table>

<br>

<div id="divListChecks"></div>
<div id="divPopupCalendar"></div>

</body>
</html>
<script type="text/javascript">
YAHOO.util.Event.addListener(window, "load", function() {
	var handleSubmit = function() {
		var searchRequest = '';
		var data = myDialog.getData();

		if(data.service_id != '') {
			searchRequest += '&service_id=' + encodeURIComponent(data.service_id);
		}

		if(data.node != '') {
			searchRequest += '&node=' + encodeURIComponent(data.node);
		}

		if(data.entity != '') {
			searchRequest += '&entity=' + encodeURIComponent(data.entity);
		}

		if(data.monitor != '') {
			searchRequest += '&monitor=' + encodeURIComponent(data.monitor);
		}

		if(data.service_name != '') {
			searchRequest += '&service_name=' + encodeURIComponent(data.service_name);
		}

		if(data.state != '') {
			searchRequest += '&state=' + encodeURIComponent(data.state);
		}

		if(data.start_time != '') {
			searchRequest += '&start_time=' + encodeURIComponent(data.start_time);
		}

		if(data.finish_time != '') {
			searchRequest += '&finish_time=' + encodeURIComponent(data.finish_time);
		}

		window.location = '?' + searchRequest;
	};

	var myButtons = [
		{ text:"Search", handler:handleSubmit, isDefault:true }
	];

	var myDialog = new YAHOO.widget.Dialog("divSearchHistory", {
		close: false,
		draggable: false,
		fixedcenter: false,
		hideaftersubmit: false,
		underlay: "none",
		visible: true,
		width: "350px"
	});

	myDialog.cfg.queueProperty("buttons", myButtons);
	myDialog.render();

	var myEnterDialog = new YAHOO.util.KeyListener("divSearchHistory", { keys:13 }, { fn:handleSubmit });
	myEnterDialog.enable();

<?php
$api_url_requests = array(
	sprintf("q_start_time=%d", strtotime($start_time)),
);

if(isset($_GET['service_id'])) {
	$api_url_requests[] = sprintf("service_id=%s", urlencode($_GET['service_id']));
}

if(isset($_GET['node'])) {
	$api_url_requests[] = sprintf("node=%s", urlencode('%' . $_GET['node'] . '%'));
}

if(isset($_GET['entity'])) {
	$api_url_requests[] = sprintf("entity=%s", urlencode('%' . $_GET['entity'] . '%'));
}

if(isset($_GET['monitor'])) {
	$api_url_requests[] = sprintf("monitor=%s", urlencode('%' . $_GET['monitor'] . '%'));
}

if(isset($_GET['service_name'])) {
	$api_url_requests[] = sprintf("service_name=%s", urlencode('%' . $_GET['service_name'] . '%'));
}

if(isset($_GET['state'])) {
	$api_url_requests[] = sprintf("state=%s", urlencode('%' . $_GET['state'] . '%'));
}

if(isset($_GET['finish_time'])) {
	$api_url_requests[] = sprintf("q_finish_time=%d", strtotime($_GET['finish_time']));
}

?>

	var myColumnDefs = [
		{key:"check_history.c_time", field:'["check_history.c_time"]', label:"Date", sortable:true, resizeable:true, formatter:YAHOO.widget.DataTable.formatDate},
		{key:"check_history.service_id", field:'["check_history.service_id"]', label:"ID", sortable:true, resizeable:true},
		{key:"check_history.node", field:'["check_history.node"]', label:"Node", sortable:true, resizeable:true},
		{key:"services.service_name", field:'["services.service_name"]', label:"Service", sortable:true, resizeable:true},
		{key:"check_history.entity", field:'["check_history.entity"]', label:"Entity", sortable:true, resizeable:true},
		{key:"check_history.state", field:'["check_history.state"]', label:"State", sortable:true, resizeable:true, formatter:formatState},
		{key:"check_history.message", field:'["check_history.message"]', label:"Message", sortable:true, resizeable:true, formatter:YAHOO.BG.formatLongString},
		{key:"check_history.monitor", field:'["check_history.monitor"]', label:"Monitor", sortable:true, resizeable:true}
	];

	var sUrl = '/api/r/v2/list_check_history.php?format=json&<?php echo implode('&', $api_url_requests); ?>&';

	var myDataSource = new YAHOO.util.DataSource(sUrl);
	myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
	myDataSource.responseSchema = {
		resultsList: "records",
		fields: [
			{key:'["check_history.c_time"]', parser:YAHOO.util.DataSource.parseDate},
			{key:'["check_history.entity"]'},
			{key:'["check_history.message"]'},
			{key:'["check_history.monitor"]'},
			{key:'["check_history.node"]'},
			{key:'["check_history.service_id"]', parser:"number"},
			{key:'["check_history.state"]', parser:"number"},
			{key:'["services.service_name"]'}
		],
		metaFields: {
			totalRecords: "totalRecords"
		}
	};

	var myConfigs = YAHOO.BG.datatableConfigs('check_history.c_time', 'desc', 100);

	myDataTable = new YAHOO.widget.DataTable("divListChecks", myColumnDefs, myDataSource, myConfigs);

	myDataTable.handleDataReturnPayload = function(oRequest, oResponse, oPayload) {
		oPayload.totalRecords = oResponse.meta.totalRecords;
		return oPayload;
	};

	var myToolTip = new YAHOO.widget.Tooltip("myTooltip");

	myDataTable.on('cellMouseoverEvent', function (oArgs) {
		var target = oArgs.target;
		var column = this.getColumn(target);

		switch(column.key) {
			case 'check_history.message':
				var record = this.getRecord(target);
				var data = record.getData('["' + column.key + '"]');

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

	var startCalInit = true;
	var myStartCalendar;

	function popupStartCalendarHandler(type, oArgs, obj) {
		var dates = oArgs[0];
		var date = dates[0];

		var year = date[0], month = date[1], day = date[2];

		if(month < 10) {
			month = '0' + month;
		}

		if(day < 10) {
			day = '0' + day;
		}

		document.formSearchHistory.start_time.value = year + '-' + month + '-' + day + ' 00:00:00';
		myStartCalendar.hide();
	};

	YAHOO.util.Event.addListener("buttonStartTime", "click", function(o) {
		if(startCalInit) {
			startCalInit = false;
			myStartCalendar = new YAHOO.widget.Calendar('popupCal', 'divPopupCalendar', {
				close: true,
				maxdate: '<?php echo date('n/j/Y'); ?>'
			});

			myStartCalendar.selectEvent.subscribe(popupStartCalendarHandler, myStartCalendar, true);
		}

		myStartCalendar.cfg.setProperty('title', 'Start Date');

		var xy = YAHOO.util.Dom.getXY(document.getElementById('tdCalendar'));
		myStartCalendar.render();
		myStartCalendar.show();
		YAHOO.util.Dom.setXY('divPopupCalendar', xy, false);
	});

	var finishCalInit = true;
	var myFinishCalendar;

	function popupFinishCalendarHandler(type, oArgs, obj) {
		var dates = oArgs[0];
		var date = dates[0];

		var year = date[0], month = date[1], day = date[2];

		if(month < 10) {
			month = '0' + month;
		}

		if(day < 10) {
			day = '0' + day;
		}

		document.formSearchHistory.finish_time.value = year + '-' + month + '-' + day + ' 11:59:59';
		myFinishCalendar.hide();
	};

	YAHOO.util.Event.addListener("buttonFinishTime", "click", function(o) {
		if(finishCalInit) {
			finishCalInit = false;
			myFinishCalendar = new YAHOO.widget.Calendar('popupCal', 'divPopupCalendar', {
				close: true,
				maxdate: '<?php echo date('n/j/Y'); ?>'
			});

			myFinishCalendar.selectEvent.subscribe(popupFinishCalendarHandler, myFinishCalendar, true);
		}

		myFinishCalendar.cfg.setProperty('title', 'Finish Date');

		var xy = YAHOO.util.Dom.getXY(document.getElementById('tdCalendar'));
		myFinishCalendar.render();
		myFinishCalendar.show();
		YAHOO.util.Dom.setXY('divPopupCalendar', xy, false);
	});
});
</script>
