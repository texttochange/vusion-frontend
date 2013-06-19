
function layoutHandler() {
	if (window.innerWidth > 1080) {
	    var variableWidth = window.innerWidth - 300; 
	    $(".width-size").attr("style","width:"+variableWidth+"px");		
	}
	if (window.innerHeight > 340) {
	    var variableHeight = window.innerHeight - 300; 
	    $(".height-size").attr("style","height:"+variableHeight+"px");		
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
