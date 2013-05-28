<?php

class bootstrap
{
    private static $instance;

    private function __Construct()
    {
        self::setAccess();
        self::embrace();
    }

    public static function start()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Sets the .htaccess file to that of the directory
     */
    private static function setAccess()
    {
        $aces = '
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteBase ' . DIRECTORY . '

            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule ^(.*)$ index.php [L]
        </IfModule>';

        if ( !file_exists( DR.'/.htaccess' ) ) {
            $create_file = fopen( DR.'/.htaccess' );

            if ( !$create_file )
                throw new Exception( 'Unable to create .htaccess file: Check all Path and Directories within settings are correct' );
        } else {
            $create_access = file_put_contents( DR.'/.htaccess', $aces );
        }
    }

    /**
     * Starts the application by loading in the router
     */
    private static function embrace()
    {
        // Initiate the controller
        try {
            $route = new route();
            $route->get();
        } catch ( Exception $e ) {
            die ( 'Oh no! An exception occurred. Here is why: ' .  $e->getMessage() );
        }
    }
}
