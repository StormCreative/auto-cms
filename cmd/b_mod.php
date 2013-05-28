<?php
include 'base.php';

class b_mod extends base
{
    public $_name;
    private $_model;

    public function get_name ()
    {
        display ( "Model Name: " );
        $this->_name = get_input ();
    }

    public function form ()
    {
        $this->_model = '<?php

class ' . $this->_name . ' extends active_record
{

}';

    }

    public function save ()
    {
        mkdir ( '../app/models/' . $this->_name . '/' );
        file_put_contents ( '../app/models/' . $this->_name . '/' . $this->_name . '_model.php', $this->_model );
    }
}

$mod = new b_mod ();

$mod->get_name ();
$mod->form ();
$mod->save ();

$tablename = $mod->_name;
include 'dba.php';
