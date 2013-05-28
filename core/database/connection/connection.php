<?php
/**
 * Abstract the Connection in case I start adding a few other
 * Database drivers in the future to play with
 */

abstract class connection
{
    protected static $_instance = NULL;

    abstract public function connect ();

}
