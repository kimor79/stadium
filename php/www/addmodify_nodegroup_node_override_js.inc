	var handleAddModifyNodegroupNodeOverrideCancel = function() {
		myAddModifyNodegroupNodeOverrideDialog.form.reset();
		this.cancel();
	};

	var handleAddModifyNodegroupNodeOverrideSubmit = function() {
		YAHOO.BG.showLoading('show');
		this.submit();
	};

	var myAddModifyNodegroupNodeOverrideButtons = [
		{ text:"Submit", handler:handleAddModifyNodegroupNodeOverrideSubmit, isDefault:true },
		{ text:"Cancel", handler:handleAddModifyNodegroupNodeOverrideCancel }
	];

	var myAddModifyNodegroupNodeOverrideDialog = new YAHOO.widget.Dialog("divAddModifyNodegroupNodeOverride",
		{
			fixedcenter: true,
			hideaftersubmit: false,
			visible: false,
			width: "400px",
			zIndex: 100
		}
	);
	myAddModifyNodegroupNodeOverrideDialog.cfg.queueProperty("buttons", myAddModifyNodegroupNodeOverrideButtons);
	myAddModifyNodegroupNodeOverrideDialog.render();

	myAddModifyNodegroupNodeOverrideDialog.validate = function() {
		return true;
	};

	myAddModifyNodegroupNodeOverrideDialog.callback.failure = YAHOO.BG.dialogOnFailure;
