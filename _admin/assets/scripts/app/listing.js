requirejs.config({
    paths: {
        Backbone: '../utils/backbone',
        jquery: '../utils/jquery',
        jqueryui: '../utils/jqueryui'
    },
    shim: {
        'Backbone': {
            deps: ['../utils/lodash', 'jquery', 'jqueryui'], // load dependencies
            exports: 'Backbone' // use the global 'Backbone' as the module value
        }
    }
});

require(['../views/Listing','settings', 'sortable', 'mobilenav'], function ( Listing ) {
    var listing = new Listing ();
});