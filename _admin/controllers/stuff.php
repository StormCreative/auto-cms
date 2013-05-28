<?php

class stuff extends application_controller
{
	private $_stuff;
	private $_admin_helper;

	public function __Construct ()
	{
		parent::__Construct ();

		$this->_stuff = new stuff_model();
		$this->_admin_helper = new Admin_helper();
		
		$this->forms->setActionTable ( 'stuff' );
	}

	public function edit ( $id = "" )
	{
		$this->_stuff->attributes[ 'id' ] = !!$id ? $id : $_POST['stuff']['id'];

		if ( post_set() )
		{
			//Handle the image
					        if ( !!$_POST["multi-image"] || !!$_FILES )
					            $_POST[ "stuff" ][ "gallery" ] = implode( ",", Image_helper::save_many( $_POST[ "multi-image" ] ) );
					        else
					            $_POST[ "stuff" ][ "gallery" ] = NULL;
			

			if ( !$this->_stuff->save( $_POST[ 'stuff' ] ) ) {
				$feedback = organise_feedback ( $this->_stuff->errors, TRUE );
			}
			else
				$feedback = organise_feedback ( $this->forms->getSuccessMessage () );
		}

		$this->_stuff->find( $this->_stuff->attributes[ 'id' ] );

		// Sets the form values by the properties set through the Find method in active record
		$this->mergeTags ( $this->_stuff->attributes );
		$this->addTag ( "gallery_items", $this->_admin_helper->get_images( $this->_stuff->attributes[ 'gallery' ] ) );
		
		$this->addTag ( 'image', $this->_stuff->image );
		$this->addTag ( 'feedback', $feedback );

		$this->addStyle ( 'edit' );
		$this->addStyle ( 'button' );

		$this->setScript( 'main' );
	}
}

?>