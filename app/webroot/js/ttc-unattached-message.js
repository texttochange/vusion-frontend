require(['chosen', 'jquery-ui-timepicker', 'ttc-utils', 'counter'], function(vusion) {

	function changeSendToType() {
		switch ($(this).val()) {
        case "match":
            $("select[name*=\"send-to-match-conditions\"]").attr("disabled",false).trigger("chosen:updated");
            $("select[name*=\"send-to-match-operator\"]").attr("disabled",false);
            $("input[name*=\"file\"]").attr("disabled",true);
            break;
        case "all":
            $("select[name*=\"send-to-match-conditions\"]").attr("disabled", true).val("").trigger("chosen:updated");
            $("select[name*=\"send-to-match-operator\"]").attr("disabled",true);
            $("input[name*=\"file\"]").attr("disabled",true);
            break;
        case "phone":
            $("select[name*=\"send-to-match-conditions\"]").attr("disabled", true).val("").trigger("chosen:updated");
            $("select[name*=\"send-to-match-operator\"]").attr("disabled",true);
            $("input[name*=\"file\"]").attr("disabled", false);
        }
	}

	function changeTypeSchedule() {
		if ($(this).val() == "fixed-time" ) {
        	$("#fixed-time").attr("disabled",false);
        } else {
        	$("#fixed-time").attr("disabled","disabled");
        	$("#fixed-time").val("");
        }
	}

	//INITIALIZATION OF THE PAGE ELEMENT FOR ADD AND EDIT ONLY
	$("#fixed-time").datetimepicker({
        timeFormat: "hh:mm",
        timeOnly: false,
        dateFormat:"dd/mm/yy"
    });
    addContentFormHelp();
	addCounter();
	$("#UnattachedMessageSend-to-match-conditions").chosen();
	$("input[name*='send-to-type']").on("change", changeSendToType);
	$("input[name*='type-schedule']").on("change", changeTypeSchedule);

});