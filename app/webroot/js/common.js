require.config({
    baseUrl: '/js/',
    paths: {
        'jquery': 'jquery-1.10.2.min',
        'jquery-ui': 'jqueryui/js/jquery-ui-1.10.3.custom.min',
        'jquery-ui-timepicker': 'jqueryui/js/jquery-ui-timepicker-addon',
        'jquery-handsontable': 'jquery.handsontable-0.9.18.full',
        'jquery-validate': 'jquery.validate-1.9.0',
        'superfish': 'superfish-1.7.4/superfish.min',
        'supersubs': 'superfish-1.7.4/supersubs',
        'hoverintent': 'superfish-1.7.4/hoverIntent',
        'moment': 'moment',
        'chosen': 'chosen-1.0.jquery.min',
        'xregexp': 'xregexp-2.0.0/xregexp-all',
        'form2js': 'form2js/form2js',
        'form2js-utils': 'form2js/js2form.utils',
        'dform': 'dform/dform',
        'dform-ext': 'dform/dform.extensions',
        'dform-sub': 'dform/dform.subscribers',
        'dform-conv': 'dform/dform.converters',
        'jstree': 'jstree.min',
        'unattached-message': 'ttc-unattached-message',
        'responsive-utils': 'ttc-responsive-utils',
        'nav-menu': 'ttc-nav-menu',
        'vusion': 'ttc-vusion',
        'ttc-utils': 'ttc-utils',
        'dynamic-form-structure': 'ttc-dynamic-form-structure',
        'generic-program': 'ttc-generic-program',
        'counter': 'counter',
        'table': 'ttc-table',
        'simulator': 'ttc-simulator',
        'screen': 'ttc-screen',
        'd3': 'd3-3.5.6.min',
        'nvd3': 'nvd3/nv.d3',
        'graph-nvd3': 'ttc-graph-nvd3',
        'lodash': 'lodash',
        'twix': 'twix.min',
    },
    shim: {
    	'jquery-ui': {
	        deps: [ 'jquery' ],
	    },
	    'chosen': {
	        deps: [ 'jquery' ],
	    },
        'jquery-handsontable': {
            deps: [ 'jquery'],
        },
	    'jquery-ui-timepicker': {
	        deps: [ 'jquery-ui'],
	    },
        'superfish': {
            deps: ['jquery'],
        },
        'supersubs': {
            deps: ['jquery'],
        },
        'hoverintent': {
            deps: ['jquery'],
        },
        'dform-ext': {
            deps: ['dform', 'jquery']
        },
        'dform-sub': {
            deps: ['dform', 'jquery']
        },
        'dform-conv': {
            deps: ['dform', 'jquery']
        },
        'jquery-validate': {
            deps: ['jquery']
        },
        'form2js-utils': {
            deps: ['form2js']
        },
        'jstree': {
            deps: ['jquery']
        },
        'screen': {
            deps: ['jquery']
        },
        'graph-nvd3': {
            deps: ['nvd3', 'lodash', 'moment', 'twix']
        },
        'nvd3': {
            deps: ['d3'],
        },
        'twix': {
            deps: ['moment'],
        },
        'vusion': {       //tobe moved in source file
            deps: ['jquery', 'ttc-utils', 'screen'],
        },
        'ttc-utils': {     //tobe moved in source file
            deps: [
                'jquery', 
                'moment',
                'xregexp']
        },
        'table': {
            deps: ['jquery-handsontable', 'form2js-utils'],
        },
        'simulator': {
            deps: ['jquery', 'ttc-utils'],
        },
        'generic-program': {      //tobe moved in source file
            deps: [
            'dynamic-form-structure',
            'jquery',
            'jquery-validate',
            'jquery-ui-timepicker',
            'dform-ext',
            'dform-sub',
            'dform-conv', 
            'form2js',
            'form2js-utils',
            'ttc-utils',
            'counter',
            'xregexp'],
        }
    }
});
