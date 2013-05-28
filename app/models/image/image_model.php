<?php

class image_model extends activerecord
{
    public function __Construct ()
    {
        parent::__Construct ();

        //Set the validation from the fields, at the moment they will all be not_empty so we have something to test
        $this->validates = array();
        $this->has_many = array();
        $this->has_one = "";
    }
}
