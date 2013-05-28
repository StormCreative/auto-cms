<?php

class query_builder
{
    private $_db_name;
    private $_tablename;

    private $_query = '';

    public function __Construct ( $db_name, $tablename )
    {
        $this->_db_name = $db_name;
        $this->_tablename = $tablename;
    }

    /**
     * Method form the table of the migration
     * Adds a ID field by default
     *
     * @param string $name
     *
     * @access public
     */
    public function create_table ()
    {
        $this->_query .= 'CREATE TABLE `' . $this->_db_name . '_' . $this->_tablename . '` (
        `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY';
    }

    /**
     * Method to add a varchar field to the main query
     *
     * @param string $name
     * @param int    $len  ( default 255 )
     *
     * @access public
     */
    public function varchar ( $name, $len = 255 )
    {
        $this->_query .= ', `' . $name . '` VARCHAR(' . $len . ')';
    }

    /**
     * Method to add a int field to the main query
     *
     * @param string $name
     * @param int    $len  ( default 11 )
     *
     * @access public
     */
    public function int ( $name, $len = 11 )
    {
        $this->_query .= ', `' . $name . '` INT(' . $len . ')';
    }

    /**
     * Method to add a text field to the main query
     *
     * @param string $name
     *
     * @access public
     */
    public function text ( $name )
    {
        $this->_query .= ', `' . $name . '` TEXT';
    }

    /**
     * Method to add a date field to the main query
     *
     * @param string $name
     *
     * @access public
     */
    public function date ( $name )
    {
        $this->_query .= ', `' . $name . '` DATE';
    }

    /**
     * Method to add a timestamp field to the main query
     *
     * @param string $name
     *
     * @access public
     */
    public function timestamp ( $name )
    {
        $this->_query .= ', `' . $name . '` TIMESTAMP';
    }

    /**
     * Method to add a datetime field to the main query
     *
     * @param string $name
     *
     * @access public
     */
    public function datetime ( $name )
    {
        $this->_query .= ', `' . $name . '` DATETIME';
    }

    /**
     * Method to add a longtext field to the current query
     *
     * @param string $name
     *
     * @access public
     */
    public function longtext ( $name )
    {
        $this->_query .= ', `' . $name . '` LONGTEXT';
    }

    /**
     * Method to add a float field to the current query
     *
     * @param string $name
     *
     * @access public
     */
    public function float ( $name )
    {
        $this->_query .= ', `' . $name . '` FLOAT';
    }

    /**
     * Method to add a tinytext field to the current query
     *
     * @param string $home
     *
     * @access public
     */
    public function tinytext ( $name )
    {
        $this->_query .= ', `' . $name . '` TINYTEXT';
    }

    /**
     * Once the full query has been form we obviously need to run it
     *
     * @access public
     */
    public function run ()
    {
        //Remember to close off the query before you run it
        $this->_query .= ')';

        if ( mysql_query ( $this->_query ) ) {
            //Enter a record in the migrations table to show record the current migration
            $this->record_migration ();

            display ( "The table `" . $this->_tablename . "` has been migrated successfully.\n" );
        } else
            display ( mysql_error () );
    }

    /**
     * Method to record the migration ( tablename, datetime )
     *
     * @access public
     */
    public function record_migration ()
    {
        $query = 'INSERT INTO migrations ( `name`, `create_date` ) VALUES ( "' . $this->_db_name . '_' . $this->_tablename . '", now() )';
        if ( mysql_query ( $query ) )
            return TRUE;

        else
            display ( mysql_error () );
    }
}
