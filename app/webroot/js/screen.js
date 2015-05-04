function layoutHandler() {
	if (window.innerWidth > 1080) {
	    var variableWidth = window.innerWidth - 300; 
	    $(".width-size").attr("style","width:"+variableWidth+"px");		
	}

	if ($(window).scrollTop() > 50) {
		$("#header-program").addClass("fix-program-header");
	} else {
		$("#header-program").removeClass("fix-program-header");
	}

	if ($(window).scrollTop() > 60) {
		$("#header-content").addClass("fix-content-header");
		$("#navigation-menu").addClass("fix-navigation-menu");
	} else {
		$("#header-content").removeClass("fix-content-header");
		$("#navigation-menu").removeClass("fix-navigation-menu");
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
