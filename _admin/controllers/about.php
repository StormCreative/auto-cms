<?php

class about extends application_controller
{
	private $_about;
	private $_admin_helper;

	public function __Construct ()
	{
		parent::__Construct ();

		$this->_about = new about_model();
		$this->_admin_helper = new Admin_helper();
		
		$this->forms->setActionTable ( 'about' );
	}

	public function edit ( $id = "" )
	{
		$this->_about->attributes[ 'id' ] = !!$id ? $id : $_POST['about']['id'];

		if ( post_set() )
		{
			
			

			if ( !$this->_about->save( $_POST[ 'about' ] ) ) {
				$feedback = organise_feedback ( $this->_about->errors, TRUE );
			}
			else
				$feedback = organise_feedback ( $this->forms->getSuccessMessage () );
		}

		$this->_about->find( $this->_about->attributes[ 'id' ] );

		// Sets the form values by the properties set through the Find method in active record
		$this->mergeTags ( $this->_about->attributes );
		$this->addTag ( "gallery_items", $this->_admin_helper->get_images( $this->_about->attributes[ 'gallery' ] ) );
		
		$this->addTag ( 'image', $this->_about->image );
		$this->addTag ( 'feedback', $feedback );

		$this->addStyle ( 'edit' );
		$this->addStyle ( 'button' );

		$this->setScript( 'main' );
	}
}

?>