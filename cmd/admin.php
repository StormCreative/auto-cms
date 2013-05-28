<?php
error_reporting ( 1 );
ini_set ( 'display_errors', 'on' );

include 'functions.php';
include '../core/settings/database.php';

class Start
{
    /**
     * The array of available options
     *
     * @access private
     */
    private $available = array ( 1 => " - Create Listing Page\n",
                                 2 => " - Create a normal page\n\n" );

    /**
     * Constructor to display a welcome message and call the method to show the available options
     */
    public function __Construct ()
    {
        display ( "\n** Admin creation script **\n** @author Dave Jones **\n\n" );
        display ( "What would you like to do? ( Enter number of your choice )\n\n" );
        $this->display_options ();
    }

    /**
     * Method to display the options from the available property
     */
    private function display_options ()
    {
        foreach ($this->available as $key => $value) {
            display ( "$key - $value" );
        }
    }

    /**
     * Method to decide what script to include based on the users input
     */
    public function direct ( $decision )
    {
        switch ($decision) {
            case ( '1' ) :
                include 'controller.php';
            break;

            case ( '2' ) :
                include 'add_page.php';
            break;
        }
    }
}

$start = new Start ();
$start->direct ( get_input () );
