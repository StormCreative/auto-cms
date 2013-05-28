define(['../utils/hogan', '../utils/wysiwyg/parser_rules/advanced', '../utils/wysiwyg/dist/wysihtml5-0.4.0pre.min', 'Backbone'], function(hogan){
    
    return Backbone.View.extend({

        initialize: function () {

        	//Apply the wysiwyg to each of the textareas that have the class of js-wysiwyg
        	this.apply_wysiwyg();
        },

        el: $( 'body' ),

        apply_wysiwyg: function () {

        	var textarea = $( '.js-wysiwyg' ),
        		count = textarea.length;

        	for ( i = 0; i < count; i++ ) {

        		var area = $( textarea[ i ] );

        		area.attr( 'id', 'textarea-' + i );

				$.ajax({ url: window.site_path + 'assets/templates/wysiwyg.txt',
						 dataType: 'html',
						 async: false,
						 success: function ( tmp ) {
						 	this.template = hogan.compile(tmp);
		                    this.template = this.template.render( { id: i } );
		                    area.before(this.template);
						 }
				});

        		var editor = new wysihtml5.Editor("textarea-" + i, { // id of textarea element
												  		toolbar:      "wysihtml5-toolbar-" + i, // id of toolbar element
												  		parserRules:  wysihtml5ParserRules, // defined in parser rules set
												  		// Array (or single string) of stylesheet urls to be loaded in the editor's iframe
    											  		stylesheets: window.site_path + 'assets/styles/edit.css',
                                                        useLineBreaks: false
				});

        	}
        }
    });

});