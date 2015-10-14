require(['jquery'], function() {

	function layoutHandler() {
		if (window.innerWidth > 1080) {
		    var variableWidth = window.innerWidth - 300; 
		    $(".width-size").attr("style","width:"+variableWidth+"px");		
		}
		if (window.innerHeight > 340) {
		    var variableHeight = window.innerHeight - 200; 
		    $(".height-size").attr("style","height:"+variableHeight+"px");		
		}
		if (window.innerHeight > 340) {
		    var displayVariableHeight = window.innerHeight - 300; 
		    $(".display-height-size").attr("style","height:"+displayVariableHeight+"px");		
		}
	}

	//Run at loading
	layoutHandler();

	//Run at window resize / load
	$(function(){
		$(window).load(function() {
		        layoutHandler();
		});
		$(window).resize(function(){
				layoutHandler();
		});
	});

});