<?php

class projects extends application_controller
{
	private $_projects;
	private $_admin_helper;

	public function __Construct ()
	{
		parent::__Construct ();

		$this->_projects = new projects_model();
		$this->_admin_helper = new Admin_helper();
		
		$this->forms->setActionTable ( 'projects' );
	}

	public function edit ( $id = "" )
	{
		$this->_projects->attributes[ 'id' ] = !!$id ? $id : $_POST['projects']['id'];

		if ( post_set() )
		{
			//Handle the image
					        if ( !!$_POST["multi-image"] || !!$_FILES )
					            $_POST[ "projects" ][ "gallery" ] = implode( ",", Image_helper::save_many( $_POST[ "multi-image" ] ) );
					        else
					            $_POST[ "projects" ][ "gallery" ] = NULL;
			

			if ( !$this->_projects->save( $_POST[ 'projects' ] ) ) {
				$feedback = organise_feedback ( $this->_projects->errors, TRUE );
			}
			else
				$feedback = organise_feedback ( $this->forms->getSuccessMessage () );
		}

		$this->_projects->find( $this->_projects->attributes[ 'id' ] );

		// Sets the form values by the properties set through the Find method in active record
		$this->mergeTags ( $this->_projects->attributes );
		$this->addTag ( "gallery_items", $this->_admin_helper->get_images( $this->_projects->attributes[ 'gallery' ] ) );
		
		$this->addTag ( 'image', $this->_projects->image );
		$this->addTag ( 'feedback', $feedback );

		$this->addStyle ( 'edit' );
		$this->addStyle ( 'button' );

		$this->setScript( 'main' );
	}
}

?>