function layoutHandler() {

	if ($(window).scrollTop() > 50) {
		$("#header-program").addClass("fix-program-header");
		$("#header-content").addClass("fix-content-header");
	} else {
		$("#header-program").removeClass("fix-program-header");
		$("#header-content").removeClass("fix-content-header");
	}

	if ($(window).scrollTop() > 60) {
		$("#navigation-menu").addClass("fix-navigation-menu");
	} else {
		$("#navigation-menu").removeClass("fix-navigation-menu");
	}
}

function headerContentHeightAdjust() {
	var height = $('#header-content').height()
	$('#header-content-box').attr('style', 'min-height:' + height + 'px');
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

	headerContentHeightAdjust();
	$('#header-content').bind('heightChange', headerContentHeightAdjust);
});