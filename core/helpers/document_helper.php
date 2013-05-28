<?php

class Document_helper
{
	/**
	 * Method to save a document to the database and move it onto the server
	 * 
	 *
	 * @access static public
	 */
	static public function save()
	{
		$uploads = new Uploads_model();

		//Need this to handle the two different uploaders
        if (!!$_POST[ 'downloads' ]) {
            $doc_name = $_POST[ 'downloads' ][ $_POST[ 'document_name' ] ][ 'name' ];
            $doc_title = $_POST[ 'downloads' ][ $_POST[ 'document_name' ] ][ 'title' ];
        } else {
            $doc_name = $_POST[ 'upload_name' ][ 'actual' ];
            $doc_title = $_POST[ 'upload_name' ][ 'user' ];
        }

        //Just incase the document title isnt set
        //This should only happen when the user is updating a record with a document already associated with it
        if ( !$doc_title )
            $doc_title = $_POST[ 'uploads' ][ 'title' ];

        //Just incase the document name isnt set
        //This should only happen when the user is updating a record with a document already associated with it
        if ( !$doc_name )
            $doc_name = $_POST[ 'uploads' ]['name'];

        $fileinfo = pathinfo( !!$doc_name ? $doc_name : $doc_title );

        //Prepare to save the upload
        //Generate a random name
        $new_name = random_string ( 10 ) . '.' . $fileinfo[ 'extension' ];

        //Move the file from the temporary location to the assets/upload/documents directory
        move_uploaded_file( $_FILES[ 'uploads' ][ 'tmp_name' ][ 0 ] , PATH . '_admin/assets/uploads/documents/' . $new_name );

        //Save the record and then return the ID of the row so it can be saved into the parent table
        if ( $uploads->save ( array ( 'title' => ( !!$doc_title ? $doc_title : $doc_name ), 'name' => $new_name ) ) )
            return $uploads->attributes[ 'id' ];
	}
}

?>