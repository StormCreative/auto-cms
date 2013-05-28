define(['jquery'], function($){

    var api = {

        // Set a string of a where clause
        where : "",

        // Send the binds to cross reference the where clause
        // ( This is for PDO prepared statemnts to prevent SQL Injection )
        binds : {},

        // The table/model looking to get data for
        table : "",
        
        // Set the posting type ( GET / POST )
        post_type : 'POST',

        // Data can be externally set to send to be saved
        data: {},

        // Response data to set.
        response : '',

        // Action is externally overwritten
        action: null,

    };

    api.reset = function () {
        
        api.where = "";
        api.binds = {};
        api.table = "";
        api.post_type = 'POST';
        api.data = {};
        api.response = '';
        api.action = null;

        return api;
    }

    /**
     * Arranges the where information set gloabbly
     * to be passed into the data object. 
     */
    api.arrange_where = function() {

        if( !!this.where ) {
            this.data.where = this.where;
        } 

        if( !!this.binds ) {
            this.data.binds = this.binds;
        }
        
    }


    /**
     * Actions the API via ajax call
     */
    api.call = function( action, async ) {

        var async = !!typeof async == 'undefined' ? true : false;

        this.arrange_where();
        
        if( action.match(/(destroy)/i) ) {

            this.data.auth = true;
        } 

        // Scope in the objects self to set within the AJAX
        _this = this;

        $.ajax({

            type: this.post_type,
            url: window.site_path + "api/" + this.table + "/" + action,
            data: this.data,
            async: async

        }).done(function( data ) {

            data = $.parseJSON(data);

            // Check if the global action property is defined
            if( typeof _this.action !== 'undefined' && _this.action != null ) {

                // Call the user defined function
                _this.action(data);
                
            } else {
                // Set the response - at the moment it's not actually accessible outside....
                _this.response = data;
                return this;
            }

        });
    }

    return api;

    /* Quick example usage */
    /*
    var apis = api;
        apis.table = 'posts';

        
        apis.data.location = 'Essex';
        apis.data.content = 'Bit of info';
        apis.data.dateto = 'Date to';
        apis.data.datefrom = 'Date from';
        apis.data.users_id = '1';
        
        apis.action = function(data) {
            //console.log(data)
        }
        
       

       apis.call('all')
       */

});