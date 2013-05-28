define(['Backbone'], function(){

    return Backbone.Model.extend({
        
        initialize: function() {

            //Connect to the facebook JavaScript SDK
            this.load_sdk();

            //Set the app ID as a property
            this.app_id = window.app_id;
        },

        /**
         * Function to load facebooks JavaScript SDK
         */
        load_sdk: function () {

            window.fbAsyncInit = function() {
                FB.init({
                  appId      : window.app_id,        // App ID
                  status     : true,                     // check login status
                  cookie     : true,                     // enable cookies to allow the server to access the session
                  xfbml      : true                      // parse page for xfbml or html5 social plugins like login button below
                });

                // Put additional init code here
              };

            (function(d){
                 var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
                 if (d.getElementById(id)) {return;}
                 js = d.createElement('script'); js.id = id; js.async = true;
                 js.src = "//connect.facebook.net/en_US/all.js";
                 ref.parentNode.insertBefore(js, ref);
            }(document));
        },

        get_user_id: function () {

            return $( '.js-user-info' ).attr( 'fb-id' );
        },

        /**
         * We need to get the information of the person they are hooking up with
         * The data might need to be added to the meta tag and the users profile will need to be added to the publish hookup
         *
         * Will probably need to query the database for their facebook ID ( if they have one ) and then call their information from the facebook api
         */
        get_user_info: function () {

            //Call the facebook api to get the users information
            $.ajax({ url: 'http://graph.facebook.com/' + this.get_user_id(),
                     dataType: 'JSON',
                     async: false,
                     success: _.bind (function ( data ) {
                        this.set_user_info( data );
                     }, this)
            });
        },

        set_user_info: function ( data ) {
            this.user_info = data;
        },

        /**
         * The function below actions the hookup
         * This should be called last
         * 
         * It might be worth constructing this dynamically depending on the number of different types of hookups relationships
         */
        publish_hookup: function () {

            this.get_user_info();

            FB.api(
                'me/holiday-hookup:hookup',
                'post',
                {
                  profile: this.user_info.link
                },
                function(response) {
                  // handle the response
                  console.log ( response );
                }
            );
        },

        publish_post: function() {

            this.get_user_info();
            
            FB.api(
                'me/_holidayhookup:post',
                'post',
                {
                    gifts_occasion: {type: '123'}
                },
                    function(response) {
                         console.log ( response );
                // handle the response
                }
            );
            
            
        }

    });
});
