formatServiceNodegroup = function (elCell, oRecord, oColumn, oData) {
	var aKey = oColumn.key.split('.', 2);

	var nodegroupRecord = oRecord.getData('["nodegroups.' + aKey[1] + '"]');
	if(nodegroupRecord) {
		elCell.style.fontWeight = 'bold';
		elCell.innerHTML = nodegroupRecord;
		return;
	}

	if(!oData) {
		var serviceRecord = oRecord.getData('["services.' + aKey[1] + '"]');
		if(serviceRecord) {
			elCell.style.fontStyle = 'italic';
			elCell.innerHTML = serviceRecord;
			return;
		}
	}

	elCell.innerHTML = oData;
};

formatServiceNode = function (elCell, oRecord, oColumn, oData) {
	var aKey = oColumn.key.split('.', 2);

	var nodeRecord = oRecord.getData('["nodes.' + aKey[1] + '"]');
	if(nodeRecord) {
		elCell.style.fontWeight = 'bold';
		elCell.innerHTML = nodeRecord;
		return;
	}

	if(!oData) {
		var serviceRecord = oRecord.getData('["services.' + aKey[1] + '"]');
		if(serviceRecord) {
			elCell.style.fontStyle = 'italic';
			elCell.innerHTML = serviceRecord;
			return;
		}
	}

	elCell.innerHTML = oData;
};

formatServiceNodegroupNode = function (elCell, oRecord, oColumn, oData) {
	var aKey = oColumn.key.split('.', 2);

	var nodeRecord = oRecord.getData('["nodes.' + aKey[1] + '"]');
	if(nodeRecord) {
		elCell.style.fontWeight = 'bold';
		elCell.innerHTML = nodeRecord;
		return;
	}

	var snodeRecord = oRecord.getData('["service_nodegroup_nodes.' + aKey[1] + '"]');
	if(snodeRecord) {
		elCell.style.fontWeight = 'bold';
		elCell.innerHTML = snodeRecord;
		return;
	}

	if(!oData) {
		var serviceRecord = oRecord.getData('["services.' + aKey[1] + '"]');
		if(serviceRecord) {
			elCell.style.fontStyle = 'italic';
			elCell.innerHTML = serviceRecord;
			return;
		}
	}

	elCell.innerHTML = oData;
};

formatCurrentNode = function (elCell, oRecord, oColumn, oData) {
	elCell.innerHTML = oData;

	switch(oRecord.getData('["nodes.priority"]')) {
		case 1:
			elCell.style.backgroundColor = 'red';
			break;
		case 2:
			elCell.style.backgroundColor = 'orange';
			break;
		case 3:
			elCell.style.backgroundColor = 'yellow';
			break;
	}
};

formatCurrentPriority = function (elCell, oRecord, oColumn, oData) {
	var ngnService = oRecord.getData('["service_nodegroup_nodes.service_id"]');
	var ngService = oRecord.getData('["service_nodegroups.service_id"]');
	var nService = oRecord.getData('["service_nodes.service_id"]');

	var aKey = oColumn.key.split('.', 2);
	var value;

	if(ngService && !nService) {
	// This is from a nodegroup mapping
		value = oRecord.getData('["service_nodegroups.' + aKey[1] + '"]');
		if(value) {
			elCell.innerHTML = value;
			return;
		}
	}

	if(nService && !ngService) {
	// This is from a node mapping
		value = oRecord.getData('["service_nodes.' + aKey[1] + '"]');
		if(value) {
			elCell.innerHTML = value;
			return;
		}
	}

	if(ngnService && oRecord.getData('["service_nodegroup_node.enabled"]') != 1) {
	// This is from a node mapping overriding a nodegroup mapping
		value = oRecord.getData('["service_nodes.' + aKey[1] + '"]');
		if(value) {
			elCell.innerHTML = value;
			return;
		}
	}

	elCell.innerHTML = oData;
};

formatCurrentRow = function (elTr, oRecord) {
	if(oRecord.getData('["acknowledgements.c_time"]')) {
		YAHOO.util.Dom.addClass(elTr, 'acknowledged');
	}

	return true;
};

formatCurrentIcons = function (elCell, oRecord, oColumn, oData) {
	if(oRecord.getData('["acknowledgements.c_time"]')) {
		elCell.innerHTML = oRecord.getData('["acknowledgements.user"]');
		elCell.style.textDecoration = 'none';
	} else {
		elCell.innerHTML = '<a href="#ack"><img class="img" src="/img/accept.png" alt="Acknowledge"></a>';
	}

	var type = oRecord.getData('["check_commands.type"]');
	if(type && type == 'generic_manual') {
		elCell.innerHTML += ' <a href="#clear"><img class="img" src="/img/cancel.png" alt="Clear"></a>';
	}
};

formatNoCurrentIcons = function (elCell, oRecord, oColumn, oData) {
	if(oRecord.getData('["acknowledgements.c_time"]')) {
		elCell.innerHTML = oRecord.getData('["acknowledgements.user"]');
		elCell.style.textDecoration = 'none';
	}
};

formatEntity = function (elCell, oRecord, oColumn, oData) {
	if(oData == 'Multiple') {
		var count = oRecord.getData('multiple.entity').length;
		elCell.innerHTML = '<b>' + oData + ' (' + count + ')</b>';
	} else {
		elCell.innerHTML = oData;
	}
};

formatMonitor = function (elCell, oRecord, oColumn, oData) {
	if(oData == 'Multiple') {
		var count = oRecord.getData('multiple.monitor').length;
		elCell.innerHTML = '<b>' + oData + ' (' + count + ')</b>';
	} else {
		elCell.innerHTML = oData;
	}
};

formatServiceName = function (elCell, oRecord, oColumn, oData) {
	if(oData == 'Multiple') {
		var count = oRecord.getData('multiple.service_name').length;
		elCell.innerHTML = '<b>' + oData + ' (' + count + ')</b>';
	} else {
		elCell.innerHTML = oData;
	}
};

formatState = function (elCell, oRecord, oColumn, oData) {
	if(isNaN(oData)) {
		elCell.innerHTML = 'UNKNOWN';
		return;
	}

	switch(oData) {
		case 0:
			elCell.innerHTML = 'OK';
			break;
		case 1:
			elCell.innerHTML = 'WARNING';
			break;
		case 2:
			elCell.innerHTML = 'CRITICAL';
			break;
		default:
			elCell.innerHTML = 'UNKNOWN';
	}
};

formatWiki = function (elCell, oRecord, oColumn, oData) {
	if(!oData) {
		return;
	}

	elCell.innerHTML = '<a href="https://wiki.com/' + oData + '" class="ext_link" target="_blank">' + oData + '</a>';
};
