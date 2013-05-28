<?php
//die ( 'hello' . "\n" );
include 'cmd/base.php';

class base_migrate extends base
{
    private $migrations = array ();
    private $_data_for_migration = array ();

    public function __Construct ()
    {
        parent::__Construct ();
        $this->connect_mysql ();
    }

    /**
     * Method to get the migrations from the migrations folder
     * Breaks up the file name to get the date of the change and the name of the table
     * Saves this into the migration array property
     *
     * @access public
     */
    public function get ()
    {
        $files = scandir ( 'core/database/migrations' );

        foreach ($files as $file) {
            if ($file != '.' && $file != '..' && $file != '.DS_Store') {
                $parts = explode ( ".", $file );
                $date_parts = explode ( " ", $parts[0] );

                $this->_migrations[] = array ( 'date' => $date_parts[0],
                                               'time' => str_replace ( "-", ":", $date_parts[1] ),
                                               'name' => str_replace ( ".php", "", $parts[1] ) );
            }
        }
    }

    /**
     * Method to create a dump of the data that is in the table so we dont lose it when the migration happens
     *
     * @return obj $this
     *
     * @access public
     */
    public function dump_data ()
    {
        foreach ($this->_migrations as $migration) {
            $data = mysql_query ( 'SELECT * FROM `' . $this->_db_name . '_' . $migration[ 'name' ] . '`' );

            while ( $row = mysql_fetch_assoc ( $data ) ) {
                $this->_data_for_migration[ $migration[ 'name' ] ][] = $row;
            }
        }

        return $this;
    }

    /**
     * Method to check if the migration the user is trying to run is newer than the previous migration they have run
     * If the migration they are trying to run is older, it will not run and a notice is displayed
     *
     * @access public
     */
    public function run ()
    {
        foreach ($this->_migrations as $migration) {
            //$data = mysql_query ( 'SELECT * FROM `migrations` WHERE `name` LIKE "%' . $migration['name'] . '%" AND `create_date` > "' . $migration['date'] . ' ' . $migration['time'] . '"' );

            //if ( mysql_num_rows ( $data ) > 0 )
                //display ( 'The "' . $migration['name'] . '" table has had a more recent migration applied to it. You better check.' . "\n" );

           // else {
                /**
                * I commented this out because rather than delete the whole table and make another resulting in a loss of data,
                * I decided to alter the structure to maintain data
                *
                //Check if there is a current version of the table, if there is drop it and move on, if not just move on
                $table = mysql_query ( "SHOW TABLES LIKE '" . $this->_db_name . "_" . $migration['name'] . "'" );

                if ( mysql_num_rows ( $table ) > 0 ) {
                    if ( !mysql_query ( 'DROP TABLE `' . $this->_db_name . '_' . $migration['name'] . '`' ) )
                        display ( mysql_error () );
                }
                **/

                //display ( 'core/database/migrations/'. $migration['date'] . ' ' . str_replace ( ":", "-", $migration['time'] ) . '.' . $migration['name'] . '.php' . "\n" );

                include 'core/database/migrations/'. $migration['date'] . ' ' . str_replace ( ":", "-", $migration['time'] ) . '.' . $migration['name'] . '.php';
            //}
        }
    }

    public function put_data ()
    {
        if ( count ( $this->_data_for_migration ) > 0 ) {
            $successful = 0;
            foreach ($this->_data_for_migration as $key => $data) {
                foreach ($data as $row) {
                    $query = 'INSERT INTO ' . $this->_db_name . '_' . $key . ' (';

                    $i = 1;
                    $total = count ( $row );

                    $values = '';

                    foreach ($row as $col => $value) {
                        if ($i != $total) {
                            $query .= ' ' . $col . ',';
                            $values .= ' "' . $value . '",';
                        } else {
                            $query .= ' ' . $col;
                            $values .= ' "' . $value . '"';
                        }

                        $i++;
                    }

                    $query .= ' ) VALUES ( ' . $values . ' )';

                    if ( mysql_query ( $query ) )
                        $successful++;
                }
            }

            display ( "A total of $successful rows have been successfully migrated\n\n" );
        }
    }
}
