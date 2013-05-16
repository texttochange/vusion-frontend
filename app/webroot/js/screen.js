
function layoutHandler(){	
		alert("hi");
	if (window.innerWidth > 800){
		/*styleLink.setAttribute("href", "mobile.css")*/
		$(".width-size").attr('width','1053;');		
	}		
}

$(function(){
		layoutHandler($(this));
		$(window).resize(function(){
				layoutHandler($(this));
		});
});
/*window.onresize = layoutHandler();*/

