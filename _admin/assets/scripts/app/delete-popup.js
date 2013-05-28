define(['jquery'], function($){

	var popUp = $('.js-delete-popup');
     
     popUp.click(function() {

        var deletion_box = $('.overlay');
        var close = $('.js-close');
        
        deletion_box.fadeIn('normal');

        close.click(function() {
            
            deletion_box.fadeOut('normal');
        
        });
    });
})