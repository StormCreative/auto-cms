define(['../../../../assets/scripts/utils/api-caller', 'Backbone'], function(api){
    
    return Backbone.View.extend({

        initialize: function(){
            /**
             * To make it a bit easier for us I am going to append the relavant html for the button and list on page load because this
             * uploader and the uploadify have different html layouts
             *
             * All that is needed is a div with a class of .js-upload-container
             */
            this.append_upload_area();

            this.site_path = window.site_path.replace( "_admin", "admin" );

            //Define some properties
            this.all_images = [];
            this.upload_count = !!window.image_count ? window.image_count : 0;
            this.total_images_allowed = !!window.number_of_images ? window.number_of_images : 99;
            this.image_info;

            //Need a separate count for the ID of the container because using the same count as the upload_count causes duplicate classes on div
            this.container_count = this.upload_count;

            this.image;
            this.image_container = $( '.js-images' );
            this.new_width;
            this.new_height;

            //Define some properties for the document upload
            this.document_name;
            this.document_upload_count = !!window.document_count ? window.document_count : 0;
            this.total_documents_allowed = 1;
            this.document_info;
            this.document_container = $( '.js-documents' );
        },
        
        //The view element itself
        el: $('body'),
        
        // Selectors are scoped to the parent element
        events: {
            //Event type .element-name : function-name
            'change .js-image-upload'     : 'get_image',
            'click  .js-remove-image'     : 'remove_image',
            'click  .js-delete-image'     : 'delete_image',
            'change .js-document-upload'  : 'get_document',
            'click  .js-documents-delete' : 'remove_document',
            'click  .js-delete-upload'    : 'delete_upload'
        },

        append_upload_area: function () {

            $( '.js-upload-container' ).append( '<p class="js-error"><p>' +
                                                '<div class="js-image-upload-container">' +
                                                    '<input type="hidden" name="normal_uploader" value="1" />' + 
                                                    '<input type="file" class="js-image-upload" name="image[]" multiple />' +
                                                    '<span class="action">Upload Image</span>' +
                                                '</div>' +
                                                '<div class="js-images"></div>' );

            //We also need to set one up if the page allows document uploads
            $( '.js-uploads-container' ).append ( '<p class="js-document-error"><p>' +
                                                  '<div class="js-document-upload-container">' +
                                                      '<input type="hidden" name="normal_uploader" value="1" />' + 
                                                      '<input type="file" class="js-document-upload" name="uploads[]" />' +
                                                      '<span class="action">Upload File</span>' +
                                                  '</div>' +
                                                  '<div class="js-documents"></div>' );
        },

        get_image: function ( e ) {

            var all_files = $( e )[0].target.files,
                total_images = all_files.length;

            for ( i = 0; i < total_images; i++ ) {

                var reader = new FileReader();

                reader.onload = _.bind(function(file) {
                    this.set_image(file);              
                }, this);

                reader.readAsDataURL(all_files[i]);

            }
        },

        set_image: function ( data ) {

            this.image_info = data;
            var validation = this.validation();

            if ( validation[0] ) {

                this.image = new Image();
                this.image.src = data.target.result;

                this.get_new_dimensions();
            }
            else {
                //If the validation fails we need to take the file value from the input so it wont upload if the user presses submit
                var input = $('.js-image-upload')[this.upload_count-window.image_count];
                $( input ).val('');

                //We need to display the error to the user
                $( ".js-error" ).text( validation[1] );
            }
        },

        get_new_dimensions: function () {

            //Add 1 to the upload count
            this.upload_count++;
            this.container_count++;

            //We need to append the new image first otherwise getting the height and width of the image is really buggy
            $(this.image).addClass( 'js-image-' + this.upload_count );

            //For some reason it wouldnt let me append a div with the image in it
            //So I had to append the div, then append the image into the div
            this.image_container.append( '<div class="container_' + this.container_count + '"></div>' );
            $( '.container_' + this.container_count ).append( this.image );
            
            var img_width = $( '.js-image-' + this.upload_count ).width(),
                img_height = $( '.js-image-' + this.upload_count ).height();

            //Need to do a if statement here depending on the dimensions of the image
            if ( img_width > img_height ) {

                //Get the ratio
                var ratio = img_height / img_width;
                this.new_width = '100';
                this.new_height = Math.floor(this.new_width * ratio);
            }
            else if ( img_width < img_height ) {

                 //Get the ratio
                var ratio = img_width / img_height;
                this.new_height = '100';
                this.new_width = Math.floor(this.new_height * ratio);
            }
            else if ( img_width == img_height ) {
                //Set a new width and height for the thumbnail
                this.new_width = '100';
                this.new_height = '100';
            }

            this.append_new_image();
            this.fiddle_input ();
        },

        /**
         * Change the height and width of the new image
         * Also need to call the function that adds a delete button that is associated with the image
         */
        append_new_image: function () {
             $( '.js-image-' + this.upload_count ).css('width', this.new_width + 'px').css('height', this.new_height + 'px');
             this.append_delete_button();
        },

        append_delete_button: function () {
            $( '.container_' + this.container_count ).append( '<p class="js-remove-image" data-image-number="' + this.container_count + '" style="cursor: pointer;">Delete</p>' );
        },

        /**
         * In order to send muliiple files to the server I am going to hide the old file button and append a new one so
         * the $_FILES array in PHP should pick up all the files
         */
        fiddle_input: function () {

            var input_file_buttons = $( '.js-image-upload' ),
                input_file_container = $( '.js-image-upload-container' );

            for ( i = 0; i < input_file_buttons.length; i++ ) {

                if ( $(input_file_buttons[i]).css( 'display' ) != 'none' ) {
                    $(input_file_buttons[i]).css( 'display', 'none' );
                }
            }

            //Append a new input onto the container so the user still only see one upload button
            input_file_container.append ( '<input type="file" class="js-image-upload" name="image[]" value="Upload a photo" />' );
        },

        remove_image: function ( e ) {

            var target = e.target,
                image_no = $( target ).attr( 'data-image-number' ),
                input_buttons = $( '.js-image-upload' );

            $( input_buttons[image_no-1] ).remove();
            $( '.container_' + image_no ).remove();

            //Take 1 away from the total uploaded property
            this.upload_count--;

        },

        /**
         * Method to hold all the validation stuff
         * Needs access to the data that the FileReader object supplies
         */
        validation: function ( e ) {

            //Define some max values
            //Memory size in bits
            var max_size = '5242880',
                allowed_extensions = [ 'jpg', 'jpeg', 'png', 'gif' ];

            //Get the extension of th uploaded file
            //This isnt as easy because it is bundled in to a long string
            var extension_string = this.image_info.target.result,
                split = extension_string.split( ';' ),
                split_again = split[0].split( ':' ),
                final_split = split_again[1].split( '/' ),
                extension = final_split[ 1 ];

            if ( this.image_info.total > max_size ) {
                return [ false, 'The image is too large ( 5mb max )' ];
            }
            else if ( $.inArray( extension, allowed_extensions ) == -1 ) {
                return [ false, 'Wrong file type' ];
            }
            else if ( this.upload_count >= this.total_images_allowed ) {
                return [ false, 'Your are only allowed a total of "' + this.total_images_allowed + '" images' ];
            }

            return [ true ];
        },

        delete_image: function ( e ) {

            var target = e.target;
                imagename = $( target ).attr( 'data-imagename' ),
                image_id = $( target ).attr( 'data-id' );

            $.ajax({ url: this.site_path + 'AJAX_delete/normal_delete',
                     data: { imagename: imagename },
                     type: 'POST',
                     dataType: 'JSON',
                     success: _.bind(function ( data ) {

                         if ( data[ 'status' ] == 200 )
                         {
                            //In order to make this fit into the admin frame work I will need to unset all the files inputs
                            $( '.js-hidden-name' ).val( '' );

                            var file_input = $( '.js-image-upload' ),
                                count = file_input.length;

                            for ( i = 0; i < count; i++ ) {
                                $( file_input[ i ] ).val( '' );
                            }

                            $( '.js-saved-image' ).remove();
                            $( '.image_' + image_id ).remove();

                            this.upload_count--;

                         }
                         else if ( data[ 'status' ] == 400 )
                            $( ".js-error" ).text( data[ 'msg' ] );

                     }, this )
            });
        },

        /**
         *
         * DOCUMENTS 
         *
         */
         get_document: function ( e ) {

            var doc_name = $(e.target).val().split( "\\" ),
                doc_split = doc_name[ 2 ].split( '.' );

            this.document_ext = doc_split[ 1 ];
            this.document_name = doc_name[ 2 ];

            var file_info = $(e)[0].target.files[0],
                reader = new FileReader();

            reader.onload = _.bind(function(file) {
                this.set_document( file );              
            }, this);

            reader.readAsDataURL(file_info);

         },

         set_document: function ( data ) {

            this.document_info = data;

            var validation = this.document_validation(),
                input_file_container = $( '.js-document-upload-container' ),
                input_file_buttons = $( '.js-document-upload' );

            if ( validation[0] ) {

                //Loop through and hide all the file upload buttons
                for ( i = 0; i < input_file_buttons.length; i++ ) {

                    if ( $(input_file_buttons[i]).css( 'display' ) != 'none' ) {
                        $(input_file_buttons[i]).css( 'display', 'none' );
                    }
                }

                //Add the document name to the DOM
                //We get the document name from the input field
                //From here we need to append the document name and a delete button 
                this.document_container.append( '<div>' +
                                                    '<input type="text" name="upload_name[user]" value="' + this.document_name + '" />' +
                                                    '<input type="hidden" name="upload_name[actual]" value="' + this.document_name + '" />' +
                                                    ' - <button type="button" class="js-documents-delete">X Remove</button>' +
                                                '</div>' );

                //Append a new input onto the container so the user still only see one upload button
                input_file_container.append ( '<input type="file" class="js-document-upload" name="uploads[]" value="" />' );

                //Increment the upload count by one
                this.document_upload_count++;
            }
            else {
                //If the validation fails we need to take the file value from the input so it wont upload if the user presses submit
                var input = $( '.js-document-upload' )[ this.upload_count-window.document_count ];
                $( input ).val( '' );

                //We need to display the error to the user
                $( ".js-document-error" ).text( validation[1] );
            }

         },

         document_validation: function () {

            //Define some max values
            //Memory size in bits
            var max_size = '5242880',
                not_allowed_extensions = [ 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx' ];

            if ( this.document_info.total > max_size ) {
                return [ false, 'The document is too large ( 5mb max )' ];
            }
            else if ( $.inArray( this.document_ext, not_allowed_extensions ) == -1 ) {
                return [ false, 'Wrong file type' ];
            }
            else if ( this.document_upload_count >= this.total_images_allowed ) {
                return [ false, 'Your are only allowed a total of "' + this.total_documents_allowed + '" document(s)' ];
            }

            return [ true ];
         },

         remove_document: function ( e ) {

            var target = e.target,
                display_container = $(target).parent().parent(),
                file_buttons = $( '.js-document-upload' );
            
            //Remember to remove both the div with the document name and the input so we dont submit more than we want
            $( display_container ).remove();
            $( file_buttons[ this.document_upload_count - 1 ] ).remove();

            e.preventDefault();
         },

         /**
          * This method is to delete an already uploaded file
          * To remove a file from the DOM before its uploaded use the remove_document method
          */
         delete_upload: function ( e ) {

            var target = e.target,
                upload_name = $( target ).attr( 'data-upload-name' ),
                upload_id = $( target ).attr( 'data-id' );

            //Send the upload id so the row in the uploads table can be deleted and the upload name so the file can be unset
            $.ajax({ url: this.site_path + 'AJAX_delete/normal_upload',
                     data: { id: upload_id, name: upload_name },
                     type: 'POST',
                     dataType: 'JSON',
                     success: function ( data ) {

                         if ( data[ 'status' ] == 200 ) {
                             $( target ).parent().remove();
                         }
                         else if ( data[ 'status' ] == 400 ) {
                            $( '.js-document-error' ).text( 'Upload could not be deleted at this time.' );
                         }

                     }
            });
         }
    });
});