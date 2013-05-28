<?php

error_reporting ( 1 );
ini_set ( 'display_errors', 'on' );

abstract class base
{
    protected $_db_name;
    protected $_db_user;
    protected $_db_pass;
    protected $_db_host;

    protected $_directory;

    public function __Construct ()
    {
        include 'core/settings/site.php';
        $this->_directory = $settings['PROJECT'];

        $this->check_functions ();
        $this->check_database ();
    }

    /**
     * Method to check if the functions file has been included and it includes it if false
     * This is done to ensure if this script is run without a previous script no fatal errors are returned
     *
     * @access private
     */
    protected function check_functions ()
    {
        if ( !function_exists ( 'get_input' ) )
            include 'cmd/functions.php';
    }

    /**
     * Method to check if the database settings have been included
     *
     * @access private
     */
    protected function check_database ()
    {
        if (!$settings) {
            include 'core/settings/database.php';

            $this->_db_name = $settings['DB_NAME'];
            $this->_db_user = $settings['DB_USER'];
            $this->_db_pass = $settings['DB_PASS'];
            $this->_db_host = $settings['DB_HOST'];
        }
    }

    /**
     * Method to connect to mysql
     * Also checks if the requested database exists, if not it creates it
     *
     * @access public
     */
    public function connect_mysql ()
    {
        mysql_connect ( $this->_db_host, $this->_db_user, $this->_db_pass ) or die ( mysql_error () );
        mysql_query ( 'CREATE DATABASE IF NOT EXISTS `' . $this->_db_name . '`');

        mysql_select_db ( $this->_db_name );
    }
}
