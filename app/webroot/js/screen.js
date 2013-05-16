
function layoutHandler(){		
	if (window.innerWidth > 800){		
		$(".width-size").attr("style","width:1053px");		
	}		
}

$(function(){
		layoutHandler($(this));
		$(window).resize(function(){
				layoutHandler($(this));
		});
});
/*window.onresize = layoutHandler();*/

