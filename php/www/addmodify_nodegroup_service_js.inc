	var handleAddModifyServiceNodegroupCancel = function() {
		myAddModifyServiceNodegroupDialog.form.reset();
		this.cancel();
	};

	var handleAddModifyServiceNodegroupSubmit = function() {
		YAHOO.BG.showLoading('show');
		this.submit();
	};

	var myAddModifyServiceNodegroupButtons = [
		{ text:"Submit", handler:handleAddModifyServiceNodegroupSubmit, isDefault:true },
		{ text:"Cancel", handler:handleAddModifyServiceNodegroupCancel }
	];

	var myAddModifyServiceNodegroupDialog = new YAHOO.widget.Dialog("divAddModifyServiceNodegroup",
		{
			fixedcenter: true,
			hideaftersubmit: false,
			visible: false,
			width: "400px",
			zIndex: 100
		}
	);
	myAddModifyServiceNodegroupDialog.cfg.queueProperty("buttons", myAddModifyServiceNodegroupButtons);
	myAddModifyServiceNodegroupDialog.render();

	myAddModifyServiceNodegroupDialog.validate = function() {
		return true;
	};

	myAddModifyServiceNodegroupDialog.callback.failure = YAHOO.BG.dialogOnFailure;
