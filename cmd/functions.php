<?php

/**
 * Function to get some input from the cli
 *
 * @return string $input
 */
function get_input ()
{
    return trim ( fgets ( STDIN ) );
}

/**
 * Function to return some output to the user
 *
 * @param string $display
 *
 * @return string $output
 */
function display ( $display )
{
    return fwrite ( STDOUT, $display );
}

/**
 * Function to check if the column is in the database structure
 *
 * @param string $name
 * @param array $array
 *
 * @access global
 */
function check_col_exists ( $name, $array )
{
    foreach ($array as $value) {
        if ( in_array ( $name, $value ) )
            return TRUE;
    }
}

/**
 * Function to get a files contents and return it
 *
 * @param string $path
 *
 * @return string $contents
 *
 * @access global
 */
function get ( $path )
{
    return file_get_contents ( $path );
}

/**
 * Function to put the contents of a varible into a file
 *
 * @param string $path
 * @param string $contents
 *
 * @return bool
 *
 * @access global
 */
function put ( $path, $contents )
{
    if ( file_put_contents ( $path, $contents ) )
        return TRUE;
    else
        return FALSE;
}
