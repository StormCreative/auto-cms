define(['jquery', '../utils/hogan'], function($, hogan){

    var renderer = {};

    renderer.html = function( template, section,  params ) {

        _this = this;
        $.ajax({
            url: window.site_path + 'assets/tmpls/'+template+'.tmpl',
            dataType: 'html',
            success: _.bind ( function (tmp) {

                template = hogan.compile(tmp);
                template = template.render( params );

                section.html(template)

            }, this)
        });

    };

    return renderer;
 
});