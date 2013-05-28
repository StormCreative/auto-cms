<?php

class base_build
{
    protected $_tablename;
    protected $_db_name;
    protected $_desc;

    /**
     * Describes the current table and saves the schema as a property
     *
     * @access protected
     */
    protected function describe ()
    {
        $describe = mysql_query ( "DESCRIBE `" . $this->_db_name . "_" . $this->_tablename . "`" );

        while ( $row = mysql_fetch_array ( $describe ) ) {
            $this->_desc[] = $row;
        }
    }

    /**
     * Method to add a new column to a table
     * This will only run if the column is in the migration object but not currently in the database structure
     *
     * @param string $column
     *
     * @access protected
     */
    protected function add_column ( $column )
    {
        $attr = $this->_schema[ $column ];

        if ( mysql_query ( "ALTER TABLE `" . $this->_db_name . "_" . $this->_tablename . "` ADD " . $column . " " . $attr['type'] . " " . ( !!$attr['limit'] ? "(" . $attr['limit'] . ")" : "" ) . "" ) )
            display ( $this->_tablename . " - " . $column . " was added successfully\n" );
    }

    /**
     * If the column is currently in the table structure but not in the migration object we need to remove that column from the table
     *
     * @param string $column
     *
     * @access protected
     */
    protected function drop_column ( $column )
    {
        if ( mysql_query ( "ALTER TABLE `" . $this->_db_name . "_" . $this->_tablename . "` DROP " . $column ) )
            display ( $this->_tablename . " - " . $column . " was removed from the table structure\n" );
    }

    public function alter ()
    {
        $this->describe ();

        //This loop is to check the schema array against the database structure
        foreach ($this->_schema as $key => $value) {
            if ( !check_col_exists ( $key, $this->_desc ) )
                $this->add_column ( $key );

            else
                display ( $this->_tablename . " - " . $key . " is already a column\n" );
        }

        //This loop is to check the database structure against the schema array
        foreach ($this->_desc as $key => $value) {
            if ( !check_col_exists ( $value[ 'Field' ], $this->_schema ) ) {
                $this->drop_column ( $value[ 'Field' ] );
            }
        }

        display ( "\n" );
    }
}
