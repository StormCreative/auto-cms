<?php

ob_start ();
session_start ();

/**
 * Working Directory Constant
 */
define( 'DR', getcwd() );

/**
 * Inclusion of all neccessary files.
 */

include 'core/settings/database.php';

include 'core/settings/site.php';
include 'core/config/config.php';
include 'core/helpers/helpers.php';
include 'core/loader/autoloader.php';
include 'core/helpers/form_builder.php';
include 'core/routing/route.php';

if (!LIVE) {
    error_reporting(1);
    ini_set( 'display_errors', 'on' );
}

/**
 * Autoloader for composer ( Managing dependencies )
 * Composer is PHP 5.3.3 compatitble and we only have 2 on
 * the server - so have an if to check
 */
//if ( !LIVE ) require 'vendor/autoload.php';

/**
 * Core application autoloader
 */
Autoloader::autoload();

/**
 * Bootstrapper starts the application
 */
Bootstrap::start();

ob_end_flush();
