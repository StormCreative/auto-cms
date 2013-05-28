<?php

class operations
{
    private static $_con;

    private $_table;

    public function __Construct ()
    {
        self::$_con = mysql_connect::connect();
    }

    public function table( $table )
    {
        $this->_table = $table;

        return $this;
    }

    public function insert( array $data )
    {
        if (!!$this->_table && !!$data) {

            $columns = array();
            $values = array();

            foreach ($data as $key => $value) {
                array_push( $columns , $key );
                array_push( $values , ':'. $key );
            }

            try {

                $stmt = self::$_con->prepare( 'INSERT INTO ' . $this->_table . ' ( ' . implode ( ', ', $columns ) . ' ) VALUES ( ' . implode ( ', ', $values  ) . ' )' );

                foreach ($data as $key => $value) {

                    if ( substr ( $value, 0, 1 ) == ' ' )
                        $value = str_replace ( substr ( $value, 0, 1 ), '', $value );
                    else
                        $value = $value;

                    $stmt->bindValue( ':' . $key , $value );

                }

                $stmt->execute();

            } catch ( PDOException $e ) {
                die ( 'Error within insert '.$e->getMessage () );
            }

            // Check if any rows were inserted then return last insert ID
            if ( $stmt->rowCount() == 1 )
                return self::$_con->lastInsertId();
            else  {
                $this->pdoError();

                return FALSE;
            }

        } else {

            throw new Exception (  "Table and Data must be set to insert into Database" );

            return FALSE;

        }

    }

    public function update( array $data, $id )
    {
        if (!!$this->_table && !!$data) {

            $update = array();
            $values = array();

            foreach ($data as $key => $value) {
                // PDO takes ? to be replaced by a value upon execute
                array_push( $update , $key . ' = ? '  );
                // Set up the values to be replaced by said ? and escape for good measure.
                array_push( $values, $value  );
            }

            // Push in the array last to be prepped
            array_push( $values, $id );

            try {

                $stmt = self::$_con->prepare( 'UPDATE ' . $this->_table . ' SET ' . implode ( ', ', $update ) . ' WHERE id = ?'  );

                $stmt->execute( $values );

                return TRUE;

            } catch ( PDOException $e ) {
                die ( 'Error within update'.$e->getMessage () );
            }

        } else {
            throw new Exception ( "Table and Data must be set to update an entry within Database" );
        }
    }

}
