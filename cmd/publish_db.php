<?php

include 'cmd/base_admin.php';

class publish_db extends base_admin
{
    private $_path_to_live_settings;

    public function __Construct ()
    {
        parent::__Construct ();

        $this->_path_to_live_settings = PATH . 'core/settings/live_database_settings.txt';

        $this->dump();

        display( "Do you want to upload this database to the server? ( y / n )\n" );
        if ( get_input () == 'y' )
            $this->upload_database();
    }

    /**
     * Method to get a list of all the tables
     * Loop through each one and pass it into the save_dump method
     *
     * @access public
     */
    public function dump ()
    {
        $tables = $this->_query->getAssoc ( 'SHOW TABLES' );

        foreach ($tables as $table) {
            $this->save_dump ( $table[ 0 ] );
            $this->dump_data ( $table[ 0 ] );
        }
    }

    /**
     * Method to describe each table
     * Then form a query to create the table
     * Then saves it in its own file
     *
     * It also creates a directory if one does not already exist
     *
     * @param string $table
     *
     * @access private
     */
    private function save_dump ( $table = "" )
    {
        if (!!$table) {
            $describe = $this->_query->getAssoc ( "DESCRIBE `" . $table . "`" );

            $sql = 'CREATE TABLE `' . $table . '` (';

            $i = 0;
            foreach ($describe as $field) {
                if ( $field[ 'Key' ] == 'PRI' )
                    $primary = 'PRIMARY';
                else
                    $primary = '';

                $sql .= '`' . $field[ 'Field' ] . '` ' . $field[ 'Type' ] . ' ' . $primary . ' ' . $field[ 'Extra' ] . ( $i == count ( $describe ) - 1 ? '' : ',' );

                $i++;
            }

            $sql .= ')';

            //Write the file to its own file in a directory with the same name as the database
            mkdir( PATH . 'core/database/publish' );
            mkdir( PATH . 'core/database/publish/' . DB_SUFFIX );

            if ( put ( PATH . 'core/database/publish/' . DB_SUFFIX . '/' . $table . '.txt', $sql ) )
                display ( "A SQL dump of the '" . $table . "' table has been saved successfully\n" );
        }
    }

    /**
     * Method to create a dump of the table data if some exists
     * If not nothing will happen
     *
     * @access private
     */
    private function dump_data ( $table = "" )
    {
        if (!!$table) {
            $data = $this->_query->getAssoc ( "SELECT * FROM `" . $table . "`" );

            if (!!$data) {
                $sql = '';

                foreach ($data as $row) {
                    //Get a count for working out the comma
                    $c = floor ( count ( $row ) / 2 );

                    $sql .= 'INSERT INTO `' . $table . '` ( ';

                    //Loop through each key in the array, ifs its numeric then ignore the value
                    $i = 0;
                    foreach ($row as $key => $value) {
                        if ( !is_numeric ( $key ) ) {
                            $sql .= $key . ( $i == $c - 1 ? '' : ', ' );
                            $i++;
                        }
                    }

                    $sql .= ') VALUES (';

                    $r = 0;
                    foreach ($row as $x => $y) {
                        if ( !is_numeric ( $x ) ) {
                            $sql .= "'" . $y . "'" . ( $r == $c - 1 ? '' : ', ' );
                            $r++;
                        }
                    }

                    $sql .= ')';
                }

                mkdir ( PATH . 'core/database/publish/' . DB_SUFFIX . '/data/' );

                //Write the sql queries to a file
                if ( put ( PATH . 'core/database/publish/' . DB_SUFFIX . '/data/' . $table . '-data.txt', $sql ) )\
                    display ( "Data query for '" . $table . "' generated successfully\n" );
            }
        }
    }

    /**
     * Method to grab the queries from all the files that were generated
     * I dont think I should delete the files so I am going to move them into a archive
     *
     * @access private
     */
    private function upload_database ()
    {
        //We need the new database details to the live database
        //Connect to the live server
        $this->connect_to_live_server( $this->get_new_db_details() );

        //
    }

    /**
     * Method to read the file that has the database information of the live server
     * Should just make it a associative array for ease
     *
     * @return array
     *
     * @access private
     */
    private function get_new_db_details ()
    {
        return get( $this->_path_to_live_settings );
    }

    /**
     * Method to connect to the live server
     * Seeing as we will probably have a active database connection to the local database I will establish a new connection
     *
     * Be easier to use the mysql_* functions for this because its only going to be a brief connection
     *
     * @access private
     */
    private function connect_to_live_server ()
    {

    }
}

$publish_db = new publish_db ();
