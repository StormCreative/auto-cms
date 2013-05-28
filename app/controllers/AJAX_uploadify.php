<?php

class AJAX_uploadify
{	
	private $_uploadify;

	private $_image_path;
	private $_name;

	public function __Construct ( $uploadify = TRUE )
	{
		$this->_uploadify = $uploadify;
		$this->_image_path = PATH . '_admin/assets/uploads/';

		$this->save();
	}

	public function index () {}

	/**
	 * Method to save a upload to the server
	 * This will be used by both Uploadify and the normal uploader
	 *
	 *
	 */
	public function save ()
	{
		$options = $this->allowed( $_POST[ 'type' ] );

		if ( !!$_FILES ) {

			$true_tmp_name = $this->get_true_data( 'tmp_name' );
			$true_name = $this->get_true_data( 'name' );

			$filename = $this->get_random_name ( $true_name );

			if ( in_array ( $this->get_ext ( $filename ), $options[ 'file_type' ] ) )
			{
				if ( 1 == 2 )
				{
					$im = new Imagick ( $tempFile );
					$im->setImageCompressionQuality ( 100 );
					$im->cropThumbnailImage ( 500, 350 );
					$im->writeImage ( $options[ 'dest' ] . $filename );
				}
				else
				{
					$si = new simple_image ();
					$si->load ( $true_tmp_name );
					$si->resize_crop ( 500, 350 );
					$si->save ( $options[ 'dest' ] . $filename );
				}
				
				$this->handle_return( $filename, $_POST[ 'type' ], $true_name );
			}
		}
	}

	/**
	 * Method to return the allowed file types and the destination the file should be saved
	 * This is really only used if the user is using uploadify
	 *
	 * @param string $type ( Default image )
	 *
	 * @return array containing file_type / dest
	 *
	 * @access private
	 */
	private function allowed ( $type = 'image' )
	{
		if ( $type == 'image' )
		{
			$dest = $this->_image_path . 'images/';
			$file_types = array ( 'jpg', 'jpeg', 'gif', 'png' );
		}
		elseif ( $type == 'document' )
		{
			$dest = $this->_image_path . 'documents/';
			$file_types = array ( 'pdf', 'doc', 'docx', 'ppt', 'pptx', 'xlsx', 'xls' );
		}
		else
		{
			$dest = $this->_image_path . 'images/';
			$file_types = array ( 'jpg', 'jpeg', 'gif', 'png' );
		}
			

		return array( 'file_type' => $file_types,
					  'dest' => $dest );
	}

	/**
	 * Method to grab some data from the $_FILES array
	 *
	 * Uploadify does a annoying thing where it reformats the $_FILES array so the values will not be in the same place for both uploaders
	 *
	 * @return string $data
	 *
	 * @access private
	 */
	private function get_true_data ( $type )
	{
		if ( $this->_uploadify == TRUE ) {
			$data = $_FILES[ 'image' ][ $type ];
		}
		else {
			$data = $_FILES[ 'image' ][ $type ][0];
		}

		return $data;
	}

	/**
	 * Method to change how the data is returned depending on what method the user is using
	 * If its uploadify the data needs to the died, otherwise it needs to be returned
	 *
	 * @param string / array $data
	 *
	 *
	 * @access private
	 */
	private function handle_return ( $filename, $type = "", $name = "" )
	{
		if ( !!$type && !!$name && $this->_uploadify == TRUE ) {
			die ( json_encode( array( 'filename' => $filename, 'type' => $type, 'name' => $name ) ) );
		}
		else {
			$this->_name = $filename;
		}
	}

	/**
	 * Generate a random name for the image
	 * This will also need to take into account the image extension
	 *
	 * @param string $name
	 *
	 * @return string $new_name
	 *
	 * @access private
	 */
	private function get_random_name ( $name = "" )
	{
		return random_string ( 10 ) . '.' . $this->get_ext ( $name );
	}

	/**
	 * Get the extension of a file from its name
	 *
	 * @param string $filename
	 *
	 * @return string $ext
	 *
	 * @access private
	 */
	private function get_ext ( $filename = "" )
	{
		$file_parts = pathinfo ( $filename );
		return $file_parts[ 'extension' ];
	}

	/**
	 * Returns the image name
	 *
	 * @return string $this->_name
	 *
	 * @access public
	 */
	public function get_name ()
	{
		return $this->_name;
	}
	
	public function delete ()
	{
		if ( !!$_POST['imagename'] && !!$_POST['type'] )
		{
			//If the thing deleted was a document delete the row from the database
			if ( !!$_POST[ 'id' ] && $_POST[ 'type' ] == 'document' )
			{
				$downloads = new downloads_model ();
				$downloads->delete ( array ( $_POST[ 'id' ] ) );
			}
			elseif ( !!$_POST[ 'id' ] && $_POST[ 'type' ] == 'image' )
			{
				$images = new image_model ();
				$images->delete ( array ( $_POST[ 'id' ] ) );
			}
			
			//This is to delete a gallery image
			//I put this here because image upload / delete works on AJAX and I wasn't to sure of a way to get this into the controller with shuffling a lot of stuff around
			if ( !!$_POST['gallery'] )
				$this->delete_gallery ( $_POST['gallery'] );
			
			$target = PATH . '_admin/assets/uploads/' . $_POST['type'] . 's';
			//Unset the file
			if ( unlink ( $_SERVER[ 'DOCUMENT_ROOT' ] . $target . '/' . $_POST['imagename'] ) )
				die ( json_encode ( array ( 'deleted' ) ) );
		}
		else
			die ( json_encode ( array ( 'not deleted' ) ) );
	}
}
