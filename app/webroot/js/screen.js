
function layoutHandler(){	
	var styleLink = $(".width-size");	
	if (window.innerWidth > 800){
		/*styleLink.setAttribute("href", "mobile.css")*/
		$(".width-size").attr('width','1053;');		
	}		
}

window.onresize = layoutHandler();

