require.config({
    baseUrl: '/js/',
    paths: {
        'jquery': 'jquery-1.10.2.min',
        'jquery-ui': 'jqueryui/js/jquery-ui-1.10.3.custom.min',
        'jquery-ui-timepicker': 'jqueryui/js/jquery-ui-timepicker-addon',
        //'jqueryvalidate': 'jquery.validate-1.9.0',
        'superfish': 'superfish-1.7.4/superfish.min',
        'supersubs': 'superfish-1.7.4/supersubs',
        'hoverintent': 'superfish-1.7.4/hoverIntent',
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
        'unattached-message': 'ttc-unattached-message',
        'responsive-utils': 'ttc-responsive-utils',
        'nav-menu': 'ttc-nav-menu',
        'vusion': 'ttc-vusion'
    },
    shim: {
    	'jquery-ui': {
	        deps: [ 'jquery' ],
	    //    exports: 'jQuery.ui'
	    },
	    //'chosen': ['jquery'],
	    'chosen': {
	        deps: [ 'jquery' ],
	    //    exports: 'jQuery.fn.chosen'
	    },
	    'jquery-ui-timepicker': {
	        deps: [ 'jquery-ui'],
//	        exports: 'jQuery.ui.fn.datetimepicker'
	    },
        'superfish': {
            deps: ['jquery'],
        },
        'supersubs': {
            deps: ['jquery'],
        },
        'hoverintent': {
            deps: ['jquery'],
        }
    }
});
