<?php

class model
{
    /**
     * Constructor to handle everything because this script needs no user interaction
     */
    public function __Construct ( $model_name )
    {
        $this->check_functions ();
        $this->check_database ();

        if (!$model_name) {
            display ( "Enter a name for the model...\n" );
            $model_name = get_input ();
        }

        //Check the model doesnt already exist
        if ( file_exists ( '../app/models/' . $model_name . '/' . $model_name . '_model.php' ) )
            display ( 'The model "' . $model_name . '_model" already exists. The script will now end.' . "\n\n" );

        else {
            //Check if the model directory exists, if not create it
            if ( !file_exists ( '../app/models/' . $model_name . '/' ) )
                mkdir ( '../app/models/' . $model_name . '/' );

            $model = '<?php

class ' . $model_name . '_model extends active_record
{

}

?>';

            //Create the new model file
            file_put_contents ( '../app/models/' . $model_name . '/' . $model_name . '_model.php', $model );

            //Create the directory and the file to hold the newly created models unit tests
            if ( !file_exists ( '../tests/models/' . $model_name . '_model_test.php' ) ) {
                $test = '
        <?php

        include '../base_test.php';
        include '../../app/models/'. $model_name . '/' . $model_name . '_model.php";

        class ' . $model_name . '_model_test extends Base_test
        {
            private $_model;

            public function setUp ()
            {
                @$this->_model = new ' . $model_name . '_model ();
            }

            public function test_instantiation ()
            {
                $this->assertTrue ( $this->_model );
            }
        }

        ?>';
                file_put_contents ( '../tests/models/' . $model_name . '_model_test.php', $test );
            }

            //Ask the user if they could like to set up a view
            display ( "Would you like to set up the view for this controller? ( y / n ) \n" );

            if ( get_input () == 'y' ) {
                $view_name = $model_name;
                include 'view.php';
            }
        }
    }

    /**
     * Method to check if the functions file has been included and it includes it if false
     * This is done to ensure if this script is run without a previous script no fatal errors are returned
     *
     * @access private
     */
    private function check_functions ()
    {
        if ( !function_exists ( 'get_input' ) )
            include 'functions.php';
    }

    /**
     * Method to check if the database settings have been included
     *
     * @access private
     */
    private function check_database ()
    {
        if ( !defined ( 'DB_HOST' ) )
            include '../core/settings/database.php';
    }

}

$model = new Model ( $model_name );
