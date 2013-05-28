<?php

class controller
{
    /**
     * Controller name
     *
     * @access private
     */
    private $_name;

    /**
     * Array of properties the controller will have
     *
     * @access private
     */
    private $_properties = array ();

    /**
     * Array of methods the controller will have
     *
     * @access private
     */
    private $_methods = array ();

    /**
     * Holds the rendered controller
     *
     * @access private
     */
    private $_controller;

    /**
     * Constructor to run the methods to check if the functions and database settings are included
     * Also displays a welcome message
     */
    public function __Construct ()
    {
        $this->check_functions ();
        $this->check_database ();

        display ( "Controller script\n\n" );
        display ( "Enter the controller name...\n\n" );
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

    /**
     * Method to set the controller name
     * Checks if the controller already exists before setting it
     * Keeps asking the user for a controller name until the script is exited
     *
     * @param string $name
     *
     * @access public
     */
    public function set_name ( $name )
    {
        do {
            if ( file_exists ( '../app/controllers/' . $name . '.php' ) ) {
                display ( "The controller named '$name' already exists. Pick another or enter n to continue...\n" );
                $name = get_input ();

                //Die if the user enters 'n'
                if ( $name == 'n' )
                    display ( "End!\n" );
            }
        } while ( file_exists ( '../controllers/' . $name . '.php' ) );

        $this->_name = $name;
    }

    /**
     * Method to set as many properties into the properties array as the user wants
     * These will be used later in the script by the render method
     *
     * @access public
     */
    public function set_properties ()
    {
        do {
            display ( "Enter a property ( name:access:value ) or enter n to continue...\n" );
            $prop = trim ( fgets ( STDIN ) );

            if ($prop != 'n') {
                $parts = explode ( ':', $prop );
                $this->_properties[] = array ( 'name' => $parts[0],
                                               'access' => $parts[1],
                                               'value' => $parts[2] );
            }
        } while ( $prop != 'n' );
    }

    /**
     * Method to render the properties array
     *
     * @return string $rendered_properties
     *
     * @access private
     */
    private function render_properties ()
    {
        foreach ($this->_properties as $property) {
            $rendered_properties .= "
    " . ( !!$property['access'] ? strtolower ( $property['access'] ) : 'public' ) . " $" . strtolower ( $property['name'] ) . " " . ( !!$property['value'] ? " = '" . $property['value'] . "'" : '' ) . ";\n";
        }

        return $rendered_properties;
    }

    /**
     * Method so the user can set as many extra methods as they like
     * This array will be used later when the controller file is rendered
     *
     * @access public
     */
    public function set_methods ()
    {
        do {
            display ( "Enter a method ( name:access ) or enter n to continue...\n" );
            $method = trim ( fgets ( STDIN ) );

            if ($method != 'n') {
                $meth = explode ( ":", $method );
                $this->_methods[] = array ( 'name' => $meth[0],
                                            'access' => $meth[1] );
            }
        } while ( $method != 'n' );
    }

    /**
     * Method to render the methods array
     *
     * @access private
     */
    private function render_methods ()
    {
        foreach ($this->_methods as $method) {
            $rendered_methods .= "
    " . ( !!$method['access'] ? strtolower ( $method['access'] ) : 'public' ) . " function " . strtolower ( $method['name'] ) . " ()
    {
        die ( 'This is the \'" . $method['name'] . "\' method' );
    }

    ";
        }

        return $rendered_methods;
    }

    /**
     * Method to render the final controller
     *
     * @access public
     */
    public function render ()
    {
        $this->_controller = '
<?php

class ' . $this->_name . ' extends application_controller implements genesis
{

    ' . $this->render_properties () . '

    public $' . $this->_name . ';

    public function __Construct ()
    {
        parent::__Construct ();

        $this->c_con = new ' . $this->_name . '_model ();
        $this->' . $this->_name . ' = $this->c_con;
        $this->type = "' . $this->_name . '";

        $this->useImage();
        // This is actioned in Trait - forms (\core\helpers\forms)
        $this->forms->setActionTable ( \'' . $this->_name . '\' );
    }

    /**
    * Use the index method to display a list of everything in the section
    */
    public function index ()
    {
        if (!!$_POST[ \'delete\' ]) {
            $number_deleted = $this->' . $this->_name . '->delete( $_POST[ \'delete\' ], array ( \'image\' ) );
            $this->addTag ( \'alert\', $this->forms->getDelete( $number_deleted ) );
        }

        $data = $this->' . $this->_name . '->all();

        $this->addTag ( \'data\', $data );
        $this->addTag ( \'page_title\', \'' . ucfirst ( $this->_name ) . '\' );
        $this->addTag ( \'table\', \'' . $this->_name . '\' );
        $this->setView ( \'' . $this->_name . '/list\' );
    }

    /**
    * Solely \'Creates\' a article - nothing more
    */
    public function add ()
    {
        // This is actioned in Trait - forms (\core\helpers\forms)
        $this->actionInsert ();

        $this->addStyle ( \'upload\' );
    }

    /**
    * Displays a article and Saves
    * @param int $id - the ID of the news article to edit
    */
    public function edit ( $id = "" )
    {
        $this->' . $this->_name . '->id = !!$id ? $id : $_POST[\'' . $this->_name . '\'][\'id\'];

        if ( post_set() ) {
            $_POST[ \'' . $this->_name . '\' ][ \'image_id\' ] = $this->images->id;
            $this->' . $this->_name . '->save( $_POST[ \'' . $this->_name . '\' ] );
            $success = $this->forms->getSuccessMessage ();
        }

        $this->' . $this->_name . '->find( $this->' . $this->_name . '->id );
        //$this->images->find( $this->' . $this->_name . '->image_id );
        if (!!$_POST[ \'image\' ]) {
            $this->saveImage ( $this->images );
        }


        // Sets the form values by the properties set through the Find method in active record
        $tags = $this->forms->setFormValues ( $this->' . $this->_name . ' );

        $this->mergeTags ( $tags );
        // Setting the image so we can do deletion on it
        $this->addTag ( \'image\', $this->images->id );

        $images = $this->get_multiple_images ();
        $this->addTag ( \'images\', $images );
        $this->addTag ( \'success\', $success );
        $this->addStyle ( \'upload\' );

        $this->setView ( \'' . $this->_name . '/add\' );
    }

    ' . $this->render_methods () . '
}
?>';

        //Save the controller
        if ( file_put_contents ( '../app/controllers/' . $this->_name . '.php', $this->_controller ) )
            display ( "The controller was saved successfully.\n" );
        else
            display ( "Something went wrong, Try again.\n" );
    }

    /**
     * Method to ask the user if they want to proceed to creating the model and then onto creating the database
     *
     * @access public
     */
    public function proceed ()
    {
        //Ask the user if they would like to create a model for the controller
        display ( "Would you like to create a model for this controller? ( y / n )\n" );

        if ( get_input () == 'y' ) {
            $model_name = $this->_name;
            include 'model.php';
        }
    }
}

$controller = new Controller ();
$controller->set_name ( get_input () );
$controller->set_properties ();
$controller->set_methods ();
$controller->render ();

$controller->proceed ();
