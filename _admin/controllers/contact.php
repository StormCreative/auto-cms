<?php

class contact extends application_controller
{
	private $_contact;
	private $_admin_helper;

	public function __Construct ()
	{
		parent::__Construct ();

		$this->_contact = new contact_model();
		$this->_admin_helper = new Admin_helper();
		
		$this->forms->setActionTable ( 'contact' );
	}

	public function edit ( $id = "" )
	{
		$this->_contact->attributes[ 'id' ] = !!$id ? $id : $_POST['contact']['id'];

		if ( post_set() )
		{
			
			

			if ( !$this->_contact->save( $_POST[ 'contact' ] ) ) {
				$feedback = organise_feedback ( $this->_contact->errors, TRUE );
			}
			else
				$feedback = organise_feedback ( $this->forms->getSuccessMessage () );
		}

		$this->_contact->find( $this->_contact->attributes[ 'id' ] );

		// Sets the form values by the properties set through the Find method in active record
		$this->mergeTags ( $this->_contact->attributes );
		$this->addTag ( "gallery_items", $this->_admin_helper->get_images( $this->_contact->attributes[ 'gallery' ] ) );
		
		$this->addTag ( 'image', $this->_contact->image );
		$this->addTag ( 'feedback', $feedback );

		$this->addStyle ( 'edit' );
		$this->addStyle ( 'button' );

		$this->setScript( 'main' );
	}
}

?>