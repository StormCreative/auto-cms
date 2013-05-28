requirejs.config({
    paths: {
        Backbone: '../utils/backbone',
        jquery: '../utils/jquery'
    },
    shim: {
        'Backbone': {
            deps: ['../utils/lodash', 'jquery'], // load dependencies
            exports: 'Backbone' // use the global 'Backbone' as the module value
        }
    }
});

if ( window.File && window.FileReader && window.FileList && window.Blob ) {
    
    require(['../views/ImageUpload'], function(ImageUpload) {
        var imageupload = new ImageUpload ();
    });
    
}
else {
    require([ 'uploader' ]);
}

require( ['../views/Wysiwyg'], function (Wysiwyg) {
    var wysiwyg = new Wysiwyg();
});