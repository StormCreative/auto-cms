<?php
// Here you can initialize variables that will for your tests

error_reporting ( 1 );
ini_set ( 'display_errors', 'on' );

define( 'DR', getcwd() . '/' );

include 'core/settings/database.php';

include 'core/settings/site.php';

include 'core/config/config.php';

require_once 'core/loader/autoloader.php';

/**
 * Core application autoloader
 */
Autoloader::autoload();
