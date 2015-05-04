function layoutHandler() {
	if (window.innerWidth > 1080) {
	    var variableWidth = window.innerWidth - 300; 
	    $(".width-size").attr("style","width:"+variableWidth+"px");		
	}

	if ($(window).scrollTop() > 50) {
		$("#header-program").addClass("fix-program-header");
		$(".ttc-actions").addClass("fix-action-button")
	} else {
		$("#header-program").removeClass("fix-program-header");
		$(".ttc-actions").removeClass("fix-action-button")
	}


	/*if (window.innerHeight > 340) {
	    var variableHeight = window.innerHeight - 200; 
	    $(".height-size").attr("style","height:"+variableHeight+"px");		
	}
	if (window.innerHeight > 340) {
	    var displayVariableHeight = window.innerHeight - 300; 
	    $(".display-height-size").attr("style","height:"+displayVariableHeight+"px");		
	}*/
}

$(function(){

	$(window).load(function() {
	    layoutHandler();
	});
	$(window).scroll(function(){
	    layoutHandler();
	});
	$(window).resize(function(){
		layoutHandler();
	});
});
