<?php

error_reporting ( 1 );
ini_set ( 'display_errors', 'on' );

define( "DR", getcwd() );

//We need to use a autoloader because im not going to include everything like a chump
//Apart from the autoloader itself of course!
include 'core/loader/autoloader.php';
include 'core/settings/database.php';
include 'core/settings/site.php';
include 'core/config/config.php';

$loader = new autoloader ();
$loader->autoload ( $classname );

abstract class base_admin
{
    protected $_query;
    protected $_operations;

    protected $_admin_path;

    /**
     * From now on we will be using the database classes we already have
     * This is because the mysql_* functions are shit
     */
    public function __Construct ()
    {
        //Set a new path for the admin folder as a property
        $this->_admin_path = PATH . "_admin/";

        $this->check_functions ();

        //Instantiate the query and operations objects
        $this->_query = new query ();
        $this->_operations = new operations ();
    }

    /**
     * Method to check if the functions file has been included and it includes it if false
     * This is done to ensure if this script is run without a previous script no fatal errors are returned
     *
     * @access private
     */
    private function check_functions ()
    {
        if ( !function_exists ( 'get_input' ) )
            include 'cmd/functions.php';
    }
}
