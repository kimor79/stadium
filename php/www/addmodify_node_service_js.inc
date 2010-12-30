	var handleAddModifyServiceNodeCancel = function() {
		myAddModifyServiceNodeDialog.form.reset();
		this.cancel();
	};

	var handleAddModifyServiceNodeSubmit = function() {
		YAHOO.BG.showLoading('show');
		this.submit();
	};

	var myAddModifyServiceNodeButtons = [
		{ text:"Submit", handler:handleAddModifyServiceNodeSubmit, isDefault:true },
		{ text:"Cancel", handler:handleAddModifyServiceNodeCancel }
	];

	var myAddModifyServiceNodeDialog = new YAHOO.widget.Dialog("divAddModifyServiceNode",
		{
			fixedcenter: true,
			hideaftersubmit: false,
			visible: false,
			width: "400px",
			zIndex: 100
		}
	);
	myAddModifyServiceNodeDialog.cfg.queueProperty("buttons", myAddModifyServiceNodeButtons);
	myAddModifyServiceNodeDialog.render();

	myAddModifyServiceNodeDialog.validate = function() {
		return true;
	};

	myAddModifyServiceNodeDialog.callback.failure = YAHOO.BG.dialogOnFailure;
