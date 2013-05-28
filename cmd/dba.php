<?php

if ( !function_exists ( "display" ) ) {
    include 'cmd/functions.php';
}
die ( 'hi' );

class dba extends base
{
    private $_decision;

    private $_tablename;
    private $_table_exists;

    private $_fields = array ();

    public function __Construct ( $tablename = "" )
    {
        die ( 'hello' );
        parent::__Construct ();

        if (!$this->_db_name) {
            display ( "Database Name: " );
            $this->_db_name = get_input ();
        }

        $this->connect_mysql ();

        if (!$tablename) {
            display ( "What do you want to do?\n" );

            $decisions = array ( 1 => "Create a new table",
                                 2 => "Drop a table",
                                 3 => "Delete rows by ID's",
                                 4 => "Insert row" );

            foreach ($decisions as $key => $value) {
                display ( "$key - $value\n" );
            }

            $this->_decision = get_input ();

            $this->tablename ();
        } else {
            $this->_tablename = $tablename;
            $this->_decision = 1;
        }
    }

    /**
     * Calls the relevant method based on the users decision
     *
     * @access public
     */
    public function decision ()
    {
        switch ($this->_decision) {
            case ( 1 ) :
                $this->create_table ();
            break;

            case ( 2 ) :
                $this->drop_table ();
            break;

            case ( 3 ) :
                $this->delete_by_ids ();
            break;

            case ( 4 ) :
                $this->insert_row ();
            break;
        }
    }

    /**
     * Asks the user for a tablename and checks if it exists
     * Applied the tablename to a property and a boolean exists value
     *
     * @access public
     */
    public function tablename ()
    {
        display ( "Enter tablename: " );
        $this->_tablename = get_input ();

        $this->check_table_exists ();
    }

    /**
     * Queries the database to check if the table exists
     * Applied the result to the table_exists property
     *
     * @access public
     */
    private function check_table_exists ()
    {
        $table_exists_query = mysql_query ( "SHOW TABLES LIKE '" . $this->_db_name . "_" . $this->_tablename . "'" );
        $exists_count = mysql_num_rows ( $table_exists_query );

        $this->_table_exists = ( $exists_count > 0 ? TRUE : FALSE );
    }

    /**
     * Lets the user enter as many fields as they want
     * Keeps asking the user for input until the user opts to continue
     *
     * @access private
     */
    private function get_fields ()
    {
        do {
            display ( "Enter a field ( name:type:length )\nor type 'n' to continue\n" );

            $input = get_input ();

            if ($input != 'n') {
                $x_input = explode ( ":", $input );
                $this->_fields[] = array ( 'name' => $x_input[0],
                                           'type' => $x_input[1],
                                           'length' => $x_input[2] );
            }
        } while ( $input != 'n' );
    }

    /**
     * Takes the fields property and organises it into a query if the table doesnt already exist
     * If the table does already exist a warning is displayed and the script is killed
     *
     * @access private
     */
    private function form_query_create ()
    {
        if ($this->_table_exists === TRUE) {
            display ( "The table '" . $this->_tablename . "' already exists. The script will now end.\n\n" );
            die (0);
        } else {
            $query = 'CREATE TABLE ' . $this->_db_name . '_' . $this->_tablename . ' (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,';

            foreach ($this->_fields as $field) {
                $query .= '`' . $field['name'] . '` ' . $field['type'] . '' . ( !!$field['length'] ? '(' . $field['length'] . ')' : '' ) . ',';
            }

            $query .= '`create_date` timestamp )';

            $this->create_schema ( $this->_tablename, $query );

            return $query;
        }
    }

    /**
     * Method to run a query that requires no data to be returned
     * So CRUD functionality
     *
     * @return TRUE ( on success )
     *
     * @access private
     */
    private function run_query ( $query )
    {
        if ( mysql_query ( $query ) )
            return TRUE;

        else
            die ( mysql_error () );
    }

    /**
     * Method to run a query that is expected to have some output to return
     *
     * @return $return
     *
     * @access private
     */
    private function run_query_output ( $output )
    {
        $query = mysql_query ( $output ) or die ( mysql_error () );

        $return = array ();

        while ( $row = mysql_fetch_assoc ( $query ) ) {
            $return[] = $row;
        }

        return $return;
    }

    /**
     * Method to descibe the current database table
     *
     * @return $fields
     *
     * @access private
     */
    private function describe ()
    {
        $data = $this->run_query_output ( "DESCRIBE " . $this->_db_name . '_' . $this->_tablename );

        $fields = array ();

        foreach ($data as $value) {
            $fields[ $value['Field'] ] = $value['Field'];
        }

        //Unset the ID and create_date fields because these have defaults
        unset ( $fields['id'] );
        unset ( $fields['create_date'] );

        return $fields;
    }

    /**
     * Calls the method to form the query then it runs it to create a new table
     *
     * @access public
     */
    public function create_table ()
    {
        $this->get_fields ();
        if ( $this->run_query ( $this->form_query_create () ) )
            display ( "The table '" . $this->_tablename . "' was created successfully\n\n" );

        else
            display ( "The table '" . $this->_tablename . "' could not be created at this time. Try again or check the script.\n\n" );
    }

    /**
     * Forms the query to drop a table if the table exists
     * If not displays a warning and kills the script
     *
     * @param private
     */
    private function form_query_drop ()
    {
        if ($this->_table_exists === FALSE) {
            display ( "You can't drop the table '" . $this->_tablename . "' because it doesn't exist. The script will now end.\n\n" );
            die (0);
        } else {
            $query = 'DROP TABLE ' . $this->_db_name . '_' . $this->_tablename;

            return $query;
        }
    }

    /**
     * If the table exists create the query and run it to delete the table
     *
     * @access public
     */
    public function drop_table ()
    {
        if ( $this->run_query ( $this->form_query_drop () ) )
            display ( "The table '" . $this->_tablename . "' was dropped successfully.\n\n" );

        else
            display ( "The table '" . $this->_tablename . "' could not be dropped. Try again or check the script.\n\n" );
    }

    /**
     * Receives a csv of ids, explodes them
     * Then loops through them to delete each row
     *
     * @param $ids
     *
     * @access private
     */
    private function form_query_delete_by_ids ( $ids )
    {
        if ($this->_table_exists === FALSE) {
            display ( "You can't delete rows from the table '" . $this->_tablename . "' because it doesn't exist. The script will now end.\n\n" );
            die (0);
        } else {
            $x_ids = explode ( ",", $ids );

            foreach ($x_ids as $id) {
                $this->run_query ( 'DELETE FROM `' . $this->_db_name . '_' . $this->_tablename . '` WHERE `id` = ' . $id );
            }
        }
    }

    /**
     * Method to delete a series of rows by their ID's
     *
     * @access private
     */
    private function delete_by_ids ()
    {
        display ( "Enter IDs...\n" );
        $ids = get_input ();

        $this->form_query_delete_by_ids ( $ids );
        display ( "Rows deleted successfully\n\n" );
    }

    /**
     * Method to insert a row into a table
     * Get the table schema so we know where to insert the data
     *
     * @access private
     */
    private function insert_row ()
    {
        if ($this->_table_exists === FALSE) {
            display ( "You insert a row into the table '" . $this->_tablename . "' because it doesn't exist. The script will now end.\n\n" );
        } else {
            $fields = $this->describe ();

            display ( "Values to insert ( " . implode ( ":", $fields ) . " )\n" );
            $data = explode ( ":", get_input () );

            if ( $this->run_query ( "INSERT INTO " . $this->_db_name . "_" . $this->_tablename . " ( " . implode ( ", ", $fields ) . " ) VALUES ( ' " . implode ( "', '", $data ) . "' )" ) )
                display ( "Insert successful\n\n" );

            else
                display ( "Insert unsuccessful\n\n" );
        }
    }

    /**
     * Method to save a migration class into the 'migrations' folder
     *
     * @access public
     */
    public function create_schema ()
    {
        //Form the schema array from the fields array because for some reason I cant just pop that into the file string
        $schema = 'array ( "id" => array ( "name" => "id",
                                           "type" => "int",
                                           "limit" => "11" ),
                            "create_date" => array ( "name" => "create_date",
                                                     "type" => "timestamp",
                                                     "limit" => "" ),
                  ';

        $i = 1;
        $count = count ( $this->_fields );

        foreach ($this->_fields as $field) {
            $comma = ( $i != $count ) ? ', ' : '';

            $schema .= '"' . $field[ 'name' ] . '" => array ( "name" => "' . $field[ 'name' ] . '",
                                                              "type" => "' . $field[ 'type' ] . '",
                                                              "limit" => "' . $field[ 'length' ] . '" )' . $comma . '
                                                              ';
            $i++;
        }

        $schema .= ' )';

        //Start the class
        $migration = '<?php

if ( class_exists ( "query_builder" ) != TRUE )
    include 'query_builder.php';

if ( class_exists ( "base_build" ) != TRUE )
    include 'base_build.php';

class build_' . $this->_tablename . ' extends base_build
{
    private $_builder;

    protected $_schema = '  . $schema . ';

    public function __Construct ( $db_name, $tablename )
    {
        $this->_tablename = $tablename;
        $this->_db_name = $db_name;

        $this->_build = new query_builder ( $db_name, "' . $this->_tablename . '" );
    }

    public function put ()
    {
        $this->_build->create_table ( "' . $this->_tablename . '" );
';
        foreach ($this->_fields as $field) {
            $migration .= '
        $this->_build->' . $field['type'] . ' ( "' . $field['name'] . '"' . ( !!$field['length'] ? ', "' . $field['length'] . '"' : '' ) . ' );';
        }

        $migration .= '
        $this->_build->timestamp ( "create_date" );';

    $migration .= '
        $this->_build->run ();
    }


    /**
     * Method to decide whether to create the whole table or to send it to the method so it can be altered
     *
     * @access public
     */
    public function desc ()
    {
        $table_exists = mysql_query ( "SHOW TABLES LIKE \'" . $this->_db_name . "_" . $this->_tablename . "\'" );

        if ( mysql_num_rows ( $table_exists ) == 0 )
            $this->put ();

        else
            $this->alter ();
    }
}

$build = new build_' . $this->_tablename . ' ( $this->_db_name, "' . $this->_tablename . '" );
$build->desc ();

?>';
        file_put_contents ( 'migrations/' . date("Y-m-d H-i-s") . '.' . $this->_tablename . '.php', $migration );
    }

}

$dba = new dba ( $tablename );
$dba->decision ();
