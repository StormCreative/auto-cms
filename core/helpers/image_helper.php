<?php

class Image_helper
{
	/**
	 * Method to save one image into the database and return the ID of the saved row
	 * 
	 * @param string $imagename default NULL
	 *
	 * @return int $id
	 *
	 * @access static public
	 */
	static public function save_one ( $imagename = "" )
	{
		$image = new Image_model();

		if ( !!$_POST[ 'normal_uploader' ] && !$imagename ) {
            $uploader = new Ajax_uploadify ( FALSE );
            $imagename = $uploader->get_name ();
        }

        if ( !!$imagename )
            $image->save ( array ( 'imgname' => $imagename ) );

        return $image->attributes[ 'id' ];
	}

	/**
	 * Method to save more than one image
	 * Loops through each image to save them, then added the ID to a array
	 * This needs to be called with self::multi_image_move otherwise the database rows will not have images associated with them
	 *
	 * @param array $images
	 *
	 * @return array $ids
	 *
	 * @access static public
	 */
	static public function save_many ( $images )
	{
		Image_helper::multi_image_move();

		if ( !$images ) {
			$images = $_POST[ 'multi-image' ];
		}

        $ids = array();

        foreach ($images as $image) {

            if ( !!$image[ 'imgname' ] )
            {
                $image_model = new image_model();
                $image_model->save( array( 'imgname' => $image[ 'imgname' ] ) );
                $ids[] = $image_model->attributes[ 'id' ];
            }
        }

        return $ids;
	}

	/**
	 * Method to move multiple images to our server
	 * Uses the exact same process as a single image but needs to remove the first element of the array so we dont get duplicates,
	 * then re arrange the array so we can loop through and save each image
	 *
	 * @param array $files
	 *
	 * @access static public
	 */
	static public function multi_image_move ()
	{
		$n = count( $_FILES[ 'image' ][ 'name' ] ) - 1;

        for ($i = 0; $i < $n; $i++) {
            $uploader = new AJAX_uploadify ( FALSE );
            $_POST['multi-image'][] = array ( 'imgname' => $uploader->get_name () );

            unset( $_FILES[ 'image' ][ 'name' ][ 0 ] );
            unset( $_FILES[ 'image' ][ 'type' ][ 0 ] );
            unset( $_FILES[ 'image' ][ 'tmp_name' ][ 0 ] );
            unset( $_FILES[ 'image' ][ 'error' ][ 0 ] );
            unset( $_FILES[ 'image' ][ 'size' ][ 0 ] );

            $_FILES[ 'image' ][ 'name' ] = array_values( $_FILES[ 'image' ][ 'name' ] );
            $_FILES[ 'image' ][ 'type' ] = array_values( $_FILES[ 'image' ][ 'type' ] );
            $_FILES[ 'image' ][ 'tmp_name' ] = array_values( $_FILES[ 'image' ][ 'tmp_name' ] );
            $_FILES[ 'image' ][ 'error' ] = array_values( $_FILES[ 'image' ][ 'error' ] );
            $_FILES[ 'image' ][ 'size' ] = array_values( $_FILES[ 'image' ][ 'size' ] );
        }
	}
}

?>