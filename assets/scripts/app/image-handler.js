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
    console.log ( 'Non-uploadify uploader loaded' );
    require(['../views/ImageUpload'], function(ImageUpload) {
        var imageupload = new ImageUpload ();
    });
}
else {
    console.log ( 'Uploadify loaded' );
    require([ 'uploader' ]);
}