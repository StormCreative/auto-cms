define(['backbone'], function(){

    return Backbone.View.extend({
        initialize: function(){
        	//This is the first thing that runs, so the constructor basically
            this.popup = $('.weather-popup');
        },
        
        //The view element itself
        el: $('#container'),
        
        // Selectors are scoped to the parent element
        events: {
        	//Event type .element-name : function-name
            'click .element-name': 'function-name'
        },

        //A list of functions goes here
        
        //If there is any AJAX functionality it needs to be binded to the current object using _.bind and this as the callback
        
        
    });
});