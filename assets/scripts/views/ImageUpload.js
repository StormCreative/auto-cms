define(['../utils/api-caller', 'Backbone'], function(api){
    
    return Backbone.View.extend({

        initialize: function(){

            //Define some properties
            this.all_images = [];
            this.upload_count = window.image_count;
            this.total_images_allowed = 4;
            this.image_info;

            this.image;
            this.image_container = $( '.js-images' );
            this.new_width;
            this.new_height;
        },
        
        //The view element itself
        el: $('body'),
        
        // Selectors are scoped to the parent element
        events: {
            //Event type .element-name : function-name
            'change .js-image-upload' : 'get_image',
            'click .js-remove-image'  : 'remove_image',
            'click .js-delete-image'  : 'delete_image'
        },

        get_image: function ( e ) {

            var file_info = $(e)[0].target.files[0],
                reader = new FileReader();

            reader.onload = _.bind(function(file) {
                this.set_image(file);              
            }, this);

            reader.readAsDataURL(file_info);
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

            //We need to append the new image first otherwise getting the height and width of the image is really buggy
            $(this.image).addClass( 'js-image-' + this.upload_count );

            //For some reason it wouldnt let me append a div with the image in it
            //So I had to append the div, then append the image into the div
            this.image_container.append( '<div class="container_' + this.upload_count + '"></div>' );
            $( '.container_' + this.upload_count ).append( this.image );
            
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
            $( '.container_' + this.upload_count ).append( '<p class="js-remove-image" data-image-number="' + this.upload_count + '" style="cursor: pointer;">X Delete</p>' );
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
            input_file_container.append ( '<input type="file" class="js-image-upload" name="user_images[]" value="Upload a photo" />' );
        },

        remove_image: function ( e ) {

            var target = e.target,
                image_no = $( target ).attr( 'data-image-number' ),
                input_buttons = $( '.js-image-upload' );

            //Remove the input button
            $( input_buttons[image_no-1] ).remove();
            $( '.container_' + image_no ).remove();

            //Take 1 away from the total uploaded property
            this.upload_count--;

        },

        /**
         * Method to hold all the validation stuff
         * Needs access to the data that the FileReader object supplies
         *
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
            else if ( this.upload_count >= this.total_images_allowed )
            {
                return [ false, 'Your are onlky allowed a total of 4 images' ];
            }

            return [ true ];
        },

        delete_image: function ( e ) {

            var target = e.target;
                image_id = $( target ).attr( 'data-image-id' );

            var api_caller = api;

            api_caller.action = function(data) {
                if ( data['status'] == 200 ) {
                    $(target).parent().remove();
                }
            }

            api_caller.call( 'user_images/destroy/'+image_id );
        }
    });
});
