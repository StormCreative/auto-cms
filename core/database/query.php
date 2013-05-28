<?php

class query
{
    private static $_con;

    public function __Construct ()
    {
        self::$_con = MySQL_connect::connect ();
    }

    /**
     * Returns an associative array
     * @param string - the query
     * @param optional array - binded paramaters key => value
     */
    public function getAssoc ( $query, $binded = array () )
    {
        try {
            $stmt = self::$_con->prepare( $query );

            if ( count ( $binded ) > 0 ) {
                foreach ($binded as $key => $value) {
                    $stmt->bindValue ( ":$key", $value, PDO::PARAM_STR );
                }
            }

            $stmt->execute();

            return $stmt->fetchAll();

        } catch ( PDOException $e ) {
            echo $e->getMessage ();
        }
    }

    public function getObj ( $query, $binded = array () )
    {
        try {
            $stmt = self::$_con->prepare( $query );

            if ( count ( $binded ) > 0 ) {
                foreach ($binded as $key => $value) {
                    $stmt->bindValue ( ":$key", $value, PDO::PARAM_STR );
                }
            }

            $stmt->execute();

            $result = $stmt->fetch( PDO::FETCH_OBJ );

            return $result;
        } catch ( PDOException $e ) {
            echo $e->getMessage ();
        }
    }

    public function plain ( $query, $binded = array () )
    {
        if (!!$query) {
            try {

                $stmt = self::$_con->prepare( $query );

                if ( count ( $binded ) > 0 ) {
                    foreach ($binded as $key => $value) {
                        $stmt->bindValue ( ":$key", $value, PDO::PARAM_STR );
                    }
                }

                $stmt->execute();
            } catch ( PDOException $e ) {
                echo $e->getMessage ();
            }
        } else

            return FALSE;
    }

    public function describe_table ( $table )
    {

        //$table = DB_SUFFIX.'_'.$table;
        $describe = self::$_con->prepare( "DESCRIBE $table" );
        $describe->execute();

        return $describe->fetchAll( PDO::FETCH_COLUMN );
    }

}
