define(['chosen'], function() {
	console.log("function initialize");
		//function initializeDropdown() {
			$("#fixed-time").datetimepicker({
	            timeFormat: "hh:mm",
	            timeOnly: false,
	            dateFormat:"dd/mm/yy"
	        });
			$("#UnattachedMessageSend-to-match-conditions").chosen();

			$("#ProgramProgram").chosen();
		//}
});