requirejs.config({
    paths: {
        Backbone: '../utils/backbone',
        jquery: '../utils/jquery',
        jqueryui: '../utils/jqueryui'
    },
    shim: {
        'Backbone': {
            deps: ['../utils/lodash', 'jquery'], // load dependencies
            exports: 'Backbone' // use the global 'Backbone' as the module value
        }
    }
});

require( ['../views/Wysiwyg','settings', 'mobilenav'], function (Wysiwyg) {
    var wysiwyg = new Wysiwyg();
});

if ( window.File && window.FileReader && window.FileList && window.Blob ) {
    require(['../views/ImageUpload'], function(ImageUpload) {
        var imageupload = new ImageUpload ();
    });
}
else
    require([ 'uploader' ]);