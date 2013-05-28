<?php
include 'base.php';

class b_con extends base
{
    private $_name;

    private $_methods = array ();

    private $_controller;

    public function get_name ()
    {
        display ( "Controller Name: " );
        $this->_name = get_input ();
    }

    public function get_methods ()
    {
        display ( "Methods: " );
        $this->_methods = explode ( ',', get_input () );
    }

    public function form ()
    {
        $controller = '<?php

class ' . $this->_name . ' extends ' . ( $this->_type == 'admin' ? 'application_controller implements genesis' : 'c_controller' ) . '
{
';
        $controller .= '
    public function index ()
    {
        $this->setView (\'' . $this->_name . '/' . $this->_name . '_index\');
    }';

        //Render methods
        foreach ($this->_methods as $method) {
            $controller .= '
    public function ' . $method . ' ()
    {

    }';
        }

        if ($this->_type == 'admin') {
            $controller .= '
    //These were automatically added because they are in the interface
    public function edit ( $id = "" ) {}

    public function add () {}';
        }

        $controller .= '
}';

        $this->_controller = $controller;
    }

    public function save ()
    {
        file_put_contents ( '../app/controllers/' .  $this->_name . '.php', $this->_controller );
    }

    /**
     * Method to loop through each of the methods and create a view for each method
     *
     * @access public
     */
    public function form_views ()
    {
        $view_index = '<h1>Index</h1>';

        mkdir ( '../app/views/templates/' . $this->_name );
        file_put_contents ( '../app/views/templates/' . $this->_name . '/' . $this->_name . '_index.php', $view_index );

        foreach ($this->_methods as $view) {
            $view_file = '<h1>' . $view . '</h1>';
            file_put_contents ( '../app/views/templates/' . $this->_name . '/' . $view . '.php', $view_file );
        }
    }

    /**
     * Method to create the shell application tests for each view
     * As a minimum these will contain
     * - setUp getting the relevant URL
     *
     * @access public
     */
    public function app_tests ()
    {
        mkdir ( '../tests/views/' . $this->_name . '/' );

        $app_test_index = '<?php
include '../../base_web_test_case.php';

class test_' . $this->_name . '_index extends base_web_test_case
{
    public function setUp ()
    {
        $this->get ( "http://localhost:8888' . $this->_directory . '' . $this->_name . '/" );
    }

    public function testHeader ()
    {
        $this->assertText ( \'index\' );
    }
}';
        file_put_contents ( '../tests/views/' . $this->_name . '/index_method.php', $app_test_index );

        foreach ($this->_methods as $model) {
            $app_test = '<?php
include '../../base_web_test_case.php';

class test_' . $this->_name . '_' . $model . ' extends base_web_test_case
{
    public function setUp ()
    {
        $this->get ( "http://localhost:8888' . $this->_directory . '' . $this->_name . '/' . $model . '" );
    }

    public function testHeader ()
    {
        $this->assertText ( \'' . $model . '\' );
    }

}';
            file_put_contents ( '../tests/views/' . $this->_name . '/' . $model . '.php', $app_test );
        }
    }

}

$con = new b_con ();

$con->get_name ();
$con->get_methods ();
$con->form ();

$con->save ();

$con->form_views ();
$con->app_tests ();
