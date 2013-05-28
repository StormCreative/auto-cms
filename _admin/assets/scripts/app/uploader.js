/**
 * Function act as a handler for uploadify.
 * Takes a object of options and assigns them to the uploadify options. If a option is not passed in the default will be sent to th euploadify function.
 *
 * There now only needs to be one div on the page with a class of js-upload-container
 * 
 * @param array options ( the options to be changed in the uploadify set up )
 */
define(['jquery'], function($) {

	require(['../utils/uploadify/uploadify.min']);

	/**
	 * Add the necessary html to the page so the uploader will work
	 */
	var container = $( '.js-upload-container' ),
		type = container.attr( 'data-type' );

	if ( type == 'image' ) {

		container.append( '<div id="image-list-js"></div>' +
						  '<div class="file-upload-js images_upload">' +                  
							  '<div type="button" name="image_upload" data-type="image" id="image_upload"/>' +
						  '</div>' );
	}
	
	var uploads_container = $( '.js-uploads-container' ),
		uploads_type = uploads_container.attr( 'data-type' );

	if ( uploads_type == 'document' ) {

		uploads_container.append( '<div id="file-list-js"></div>' +
								  '<div class="file-upload-js documents-upload">' +                    
									   '<div type="button" name="file_upload" data-type="document" id="file_upload"/>' +
								  '</div>' );
	}
	
	var feedback = $('#feedback-js');
	var form = $('#upload-form-js');
	
	//Documents
	var file_list = $('#file-list-js');
	var file_upload = $('#file_upload');
	
	//Images
	var image_list = $('#image-list-js');
	var image_upload = $('#image_upload');
	var image_multi = !!window.image_multi ? true : false;
	
	$(".file-upload-js>div").each(function(e) {
		
		var settings = {};
		var id = '';
		var type = $(this).attr('data-type'),
			number_of_images;

		if ( typeof ( window.number_of_images ) == 'undefined' )
			var no_of_images = 1;

		if ( typeof ( window.ext ) == 'undefined' )
			var allowed_extensions = '';
		else
			var allowed_extensions = window.ext;
		
		if ( type == 'image' )
		{
			settings = { 'height' : 23, 'width' : 75, 'buttonText': 'Select Image', 'uploadLimit': no_of_images, 'formData' : { 'type' : 'image' } };
			id = 'image_upload';
		}
		else if ( type == 'document' )
		{
			settings = { 'height' : 23, 'width' : 100, 'buttonText': 'Select Document', 'fileTypeExts' : ( !!allowed_extensions ? ext : '*.pdf' ), 'formData' : { 'type' : 'document' } };
			id = 'file_upload';
		}
		
		$( function () {
		
		    $('#' + id).uploadify({                              
		    	'wmode'            : 'transparent',
		    	'buttonClass'      : settings.buttonClass || '',
		    	'buttonCursor'     : settings.buttonCursor || 'hand',
		    	'buttonText'       : '',
		    	'buttonImage'      : settings.buttonImage || '',
		    	'checkExists'      : settings.checkExists || '',
		    	'debug'            : settings.debug || false,
		    	'fileObjName'      : settings.fileObjName || 'image',
		    	'fileSizeLimit'    : settings.fileSizeLimit || '5mb',
		    	'fileTypeDesc'     : settings.fileTypeDesc || 'jpg, git or png',
		    	'fileTypeExts'     : settings.fileTypeExts || '*.jpg; *.jpeg; *.gif; *.png',
		    	'formData'         : settings.formData || { 'type' : 'image' },
		    	'height'           : settings.height || 50,
		    	'itemTemplate'     : settings.itemTemplate || '',
		        'method'           : settings.method || 'post',
		        'multi'            : settings.multi || true,
		        'overrideEvents'   : settings.overrideEvents || ['onSelectError', 'onDialogClose'],
		        'preventCaching'   : settings.preventCaching || true,
		        'progressData'     : settings.progressData || 'percentage',
		        'queueID'          : settings.queueID || false,
		        'queueSizeLimit'   : settings.queueSizeLimit || 100,
		        'removeCompleted'  : settings.removeCompleted || true,
		        'removeTimeout'    : settings.removeTimeout || 0,
		        'requeueErrors'    : settings.requeueErrors || false,
		        'successTimeout'   : settings.successTimeout || 30,
		        'swf'              : window.site_path + 'assets/scripts/utils/uploadify/uploadify.swf',
		        'uploader'         : '/pegisis/ajax_uploadify',
		        'uploadLimit'      : settings.uploadLimit || 999,
		        'width'            : settings.width || 120,
		        'z-index'		   : '1',
		        'onCancel'         : function ( data ) {
			        cancel ( data );
		        },
		        'onFallback'       : function () {
			        fallback ();
		        },
		        'onSelectError'    : function ( file, errorCode, errorMsg ) {
			        select_error ( file, errorCode, errorMsg );
		        },
		        'onUploadError'    : function ( file, errorCode, errorMsg, errorString ) {
		        	fail ( file, errorCode, errorMsg, errorString );
		        },
		        'onUploadSuccess'  : function ( file, data, response ) {
			        success ( file, data, response );
		        }
		    });
		
		});
	});
	
	/**
	 * Function to run if the user cancels a upload
	 * So far it just displays a message
	 */
	function cancel ( data ) {
		feedback.append ( '<p>' + data.name + ' was cancelled.</p>' );
	}
	
	/**
	 * Function to run if the users browser does not support flash
	 * So far it just displays a message with a link to download the latest version of flash
	 */
	function fallback ( data ) {
		feedback.html ( 'Your browser does not support Flash. You can go <a href="#" target="_blank">here</a> to download the latest version of Flash.' );
	}
	
	/**
	 * Function to run if the file fails to upload
	 * So far it just appends a message to the user in the feedback section
	 */
	function fail ( file, errorCode, errorMsg, errorString ) {
		feedback.append ( '<p>' + file.name + ' could not be uploaded.</p>' );
	}
	
	/**
	 * Function runs of the file was successfully uploaded
	 * It also creates a hidden field within the form for the name of the file so it can be inserted into the database
	 *
	 * If the file uploaded was a image then the image is displayed above the upload button
	 * If the file is a document the document name, a icon for the document type and a delete button is 
	 */
	function success ( file, data, response ) {
		var data = $.parseJSON ( data );
		
		if ( data.type == 'image' )
		{
			if ( image_multi == true )
			{
				image_list.append ( '<div id="' + data.filename + '">' +
					                '<span><img src="' + site_path + 'assets/Uploads/images/' + data.filename + '" /></span>' +
					                '<ol class="hoz btns">' +
					                	'<input type="hidden" name="multi-image[' + data.filename + '][id]" value="" />' +
					                	'<input type="hidden" name="multi-image[' + data.filename + '][imgname]" value="' + data.filename + '" />' +
					                    '<input type="button" class="btn del-image delete-image-js" data-id="" data-imagename="' + data.filename + '" data-gallery="" data-type="' + data.type + '" value="Delete" /></li>' +
					                '</ol>' +
					            '</div>' );
			}
			else
			{
				image_list.append ( '<p id="' + data.filename + '"><img src="' + window.site_path + 'assets/uploads/images/' + data.filename + '" title="' + file.name + '" /><input type="hidden" name="image" value="' + data.filename + '" /><input type="button" class="btn del-image delete-image-js" data-imagename="' + data.filename + '" data-type="' + data.type + '" value="Delete" /></p>' );
			}

			$('#imgname').val ( data.filename );
		}
		else if ( data.type == 'document' )
		{
			file_list.append ( '<p id="' + data.filename + '"><input type="text" name="downloads[' + data.filename + '][title]" class="input" id="' + data.filename + '" value="' + data.name + '" /> <input type="hidden" name="downloads[' + data.filename + '][name]" value="' + data.filename + '" /> <input type="button" class="btn del-image delete-image-js" data-imagename="' + data.filename + '" data-type="' + data.type + '" value="Delete" /><input type="hidden" name="document_name" value="' + data.filename + '" /></p>' );

		}
	}
	
	/**
	 * Function that triggers on a select error.
	 * This will trigger if:
	 *	- queue limit is exceeded
	 *	- file size exceeds limit
	 *	- the file has no size
	 *	- invalid file type
	 *
	 * This would be best suited as a message on the screen rather than a alert
	 */
	function select_error ( file, errorCode, errorMsg ) {
		feedback.append ( '<p>' + file.name + ' could not be uploaded. ' + errorMsg + '</p>' );
	}
	
	//For some reason the on click didnt work so I had to do it like this
	$(document).on('click', '.js-delete-image', function(e) {
		$( this ).parent().parent().remove();
	});

	$(document).on('click', '.js-delete-upload', function(e) {
		$( '.js-existing-upload-container' ).remove();
	});
	
	//Because the imagename is being returned with its extension we need to escape the full stop for jQuery
	function jq(myid) { 
	   return '#' + myid.replace(/(:|\.)/g,'\\$1');
	}
})