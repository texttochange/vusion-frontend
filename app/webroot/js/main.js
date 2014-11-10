require.config({
    paths : {
        //'jquery': 'jquery-1.10.2.min',
        //'jqueryui': 'jqueryui/js/jquery-ui-1.10.3.custom.min',
        'jqueryui-timepicker': 'jqueryui/js/jquery-ui-timepicker-addon',
        //'jqueryvalidate': 'jquery.validate-1.9.0',
        'datejs': 'datejs/date',
        'moment': 'moment',
        'chosen': 'chosen-1.0.jquery.min',
        'xregexp': 'xregexp-2.0.0/xregexp-all',
        'form2js': 'form2js/form2js',
        'form2js-utils': 'form2js/js2form.utils',
        'dform': 'dform/dform',
        'dform-ext': 'dform/dform.extensions',
        'dform-sub': 'dform/dform.subscribers',
        'dform-conv': 'dform/dform.converters',
        'initialize': 'ttc-initialize'
    },
    shim:{
    	'jquery-ui': {
	        deps: [ 'jquery' ],
	        exports: 'jQuery.ui'
	    },
	    'chosen': 'jquery',
	    /*'chosen': {
	        deps: [ 'jquery' ],
	        exports: 'jQuery.fn.chosen'
	    },*/
	    'jquery-ui-timepicker': {
	        deps: [ 'jquery' ],
	        //exports: 'jQuery.ui.fn.datetimepicker'
	    }
    }
});

require(
	[
		//'jquery',
		//'jqueryui',
		'jqueryui-timepicker',
		//'jqueryvalidate',
		'datejs',
		'moment',
		'chosen',
		'xregexp',
		'form2js',
		'form2js-utils',
		'initialize',
		//'dform',
		//'dform-ext',
		//'dform-sub',
		//'dform-conv'
	],
	function() {
		console.log("function called");
	}
);