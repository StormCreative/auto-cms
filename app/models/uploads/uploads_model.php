<?php

class uploads_model extends activerecord
{
	public function __Construct ()
	{
		parent::__Construct();

		$this->has_one = "";
        $this->has_many = array();
	}
}
