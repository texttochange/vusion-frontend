
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
	    var variableHeight2 = window.innerHeight - 300; 
	    $(".dispaly-height-size").attr("style","height:"+variableHeight2+"px");		
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
