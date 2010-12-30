	var handleAddModifyServiceCancel = function() {
		myAddModifyServiceDialog.form.reset();
		this.cancel();
	};

	var handleAddModifyServiceSubmit = function() {
		YAHOO.BG.showLoading('show');
		this.submit();
	};

	var myAddModifyServiceButtons = [
		{ text:"Submit", handler:handleAddModifyServiceSubmit, isDefault:true },
		{ text:"Cancel", handler:handleAddModifyServiceCancel }
	];

	var myAddModifyServiceDialog = new YAHOO.widget.Dialog("divAddModifyService",
		{
			fixedcenter: true,
			hideaftersubmit: false,
			visible: false,
			width: "400px",
			zIndex: 100
		}
	);
	myAddModifyServiceDialog.cfg.queueProperty("buttons", myAddModifyServiceButtons);
	myAddModifyServiceDialog.render();

	myAddModifyServiceDialog.validate = function() {
		return true;
	};

	myAddModifyServiceDialog.callback.failure = YAHOO.BG.dialogOnFailure;

	YAHOO.util.Event.addListener("buttonAddService", "click", function(o) {
		myAddModifyServiceDialog.setHeader('New service');
	 	document.formAddModifyService.service_id.value = 'NEW';
		myAddModifyServiceDialog.show();

		myAddModifyServiceDialog.callback.success = function(o) {
			var output;
			try {
				output = YAHOO.lang.JSON.parse(o.responseText);
			} catch(e) {
				YAHOO.BG.dialogOnSuccess(o, myAddModifyServiceDialog.form);
				return true;
			}

			YAHOO.BG.dialogOnSuccess(o, myAddModifyServiceDialog.form);

			if(typeof(output.status) != "undefined") {
				if(output.status == "200" && typeof(output.details) != "undefined") {
					myAddModifyServiceDialog.hide();
					window.location = '/services/details.php?service_id=' + encodeURIComponent(output.details.service_id);
				}
			}
		};
	});
