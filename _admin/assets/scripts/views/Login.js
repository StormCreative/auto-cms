define(['../../../../assets/scripts/utils/api-caller', '../utils/hogan', 'Backbone'], function(api, hogan){
    
    return Backbone.View.extend({

        initialize: function () {

        	//Grab some elements we will need
            this.username = $( '.js-username' );
            this.password = $( '.js-password' );
            this.error = $( '.js-error' );

            //Some properties
            this.site_path = window.site_path.replace( "_admin", "admin" );
        },

        el: $( 'body' ),

        events: {
            //Event type .element-name : function-name
            'submit .js-login-form'                : 'process',
            'submit .js-forgotten-password'        : 'forgotten_password',
            'click .js-forgotten-password-button'  : 'forgotten_password_toggle'
        },

        forgotten_password_toggle: function () {

            $('.forgotten-form').slideToggle('normal');
        },

        process: function ( e ) {
            
            var api_caller = api,
                username = this.username.val(),
                password = this.password.val();

            if ( this.validation_not_null( [ username, password ] ) == true ) {

                $.ajax({ url: this.site_path + 'AJAX_login',
                         data: { username: username, password: password },
                         type: 'POST',
                         dataType: 'JSON',
                         success: _.bind(function ( data ) {

                            if ( data[0] == 'ok' )
                                window.location = this.site_path + 'dashboard';
                            else
                                this.error.text( 'Wrong log in details' ).show();

                         }, this)
                });

                /**
                 * For some reason the api caller doesnt work
                 * Might be a sub directory thing
                //Call the api to see if the user is in the database
                api_caller.table = 'access';
                //apic.binds = { email: username, password: password };
                //apic.where = "email = :email AND password = :password";
                
                api_caller.action = function ( data ) {
                    console.log ( data );
                }

                api_caller.call( 'all' );
                **/
            }
            else {
                this.error.text( 'Fill in your details' ).show();
            }

            e.preventDefault();
        },

        validation_not_null: function ( data ) {

            var count = data.length,
                pass = true;

            for ( i = 0; i < count; i++ ) {

                if ( !data[ i ] )
                    pass = false;
            }

            return pass;
        },

        validation_email_format: function ( email ) {

            var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

            if ( !filter.test( email ) )
                return false;
            else
                return true;
        },

        forgotten_password: function ( e ) { 

            var email = $( '.js-forgot-password-email' ).val();

            if ( this.validation_not_null( [ email ] ) == true ) {

                if ( this.validation_email_format( email ) == true )
                {
                    $.ajax({ url: this.site_path + 'AJAX_login/forgotten_password',
                             data: { email: email },
                             type: 'POST',
                             dataType: 'JSON',
                             success: _.bind(function ( data ) {

                                if ( data[ 'status' ] == 200 )
                                    this.error.text( data[ 'msg' ] ).removeClass( 'error_message' ).addClass( 'success_message' ).show();

                                else 
                                    this.error.text( data[ 'msg' ] ).show();

                             }, this)
                    });
                }
                else
                    this.error.text( 'Your email is in the wrong format' ).show();
            }
            else
                this.error.text( 'Your email is needed to send you a new password' ).show();

            e.preventDefault();
        }
        
    });
});