
function layoutHandler() {
	if (window.innerWidth > 1080) {
	    var variableWidth = window.innerWidth - 300; 
	    $(".width-size").attr("style","width:"+variableWidth+"px");		
	}		
}

$(function(){
		$(window).load(function() {
		        layoutHandler();
		});
		$(window).resize(function(){
				layoutHandler();
		});
});
