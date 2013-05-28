define(['Backbone'], function(){

    /**
     * Event Monitoring
     * Only trigger event if mouse is moving otherwise go to sleep!
     */

    return Backbone.Model.extend({
        
        initialize: function() {

            // Setting timestamp to be zero
            // Within the server side we check for zero and convert to 'now'
            this.timestamp = "0";
            this.noerror = true;

        },


        start: function() {

            // Only poll if there is active mouse movement
            // will be turned off for mobile though
            // as mobile does not call for longer responses and life
            if($(document).mousemove()) {

                this.connect();
            }

        },
        
        /**
         * Connect function makes the call to server side
         * instantiates itself to constantly continue AJAX long polling
         */
        connect: function() {
            
            // Rebind to be locally accessible
            _this = this;

            $.ajax({

                type: "POST",
                url: window.site_path+"home/ajax",
                data: { 
                    timestamp: _this.timestamp 
                }

            }).done( _.bind(function( data ) {
                
                if( data != 'false' ) {
                    
                    var data = $.parseJSON(data);

                    _this.timestamp = data[(data.length-1)].create_date;

                    $('.js-hookups').show();
                    $('.js-hookups').html(data.length);
                    
                }

                _this.handle(data);
                _this.noerror = true;

            })).complete( _.bind(function(data) {
                
                if( !_this.noerror) { 
                   setTimeout(function(){ _this.connect() }, 5000); 
                } else {
                    _this.connect();
                }

                _this.noerror = false;

            }));

        },

        handle: function(data) {

            console.log(data);
            console.log(123)
        }


    });
});
