<?php

class about_model extends activerecord
{
	public function __Construct ()
	{
		parent::__Construct ();

		
		

		//Set the validation from the fields, at the moment they will all be not_empty so we have something to test
		$this->has_one = "";
        $this->has_many = array();

		$this->validates = array(array( "not_empty", "name" ),array( "not_empty", "email" ),array( "not_empty", "phone" ),array( "not_empty", "postcode" ));
	}
}

?>