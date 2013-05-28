<?php

class MySQL_connect extends connection
{
    private function __Construct () {}

    //private static $_instance = NULL;

    public function connect ()
    {
        $hostname = DB_HOST;
        $database = DB_NAME;
        $db_user = DB_USER;
        $db_pass = DB_PASS;

        if (!self::$_instance) {
            $host = "mysql:host=$hostname;dbname=$database";

            try {
                self::$_instance = new PDO ( $host, $db_user, $db_pass );

                self::$_instance->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            } catch ( PDOException $e ) {
                die ( $e->getMessage () );
            }
        }

        return self::$_instance;
    }

    private function __clone () {}

}
