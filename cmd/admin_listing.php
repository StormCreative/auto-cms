<?php

include 'cmd/base_admin.php';

class admin_listing extends base_admin
{
    private $_controller_name;
    private $_fields = array ();

    private $_table_exists;

    private $_unit_tests_path;
    private $_acceptance_tests_path;

    public function __Construct ()
    {
        parent::__Construct ();

        //Set some directoty paths so we dont have to keep writing them out
        $this->_unit_tests_path = PATH . 'tests/unit/';
        $this->_acceptance_tests_path = PATH . 'tests/acceptance/';

        $this->get_controller_name ();
        $this->get_view_fields ();

        $this->process_controller ();
        $this->process_model ();
        $this->process_view_edit ();

        $this->process_database_table ();

        $this->generate_unit_tests ();
        $this->generate_acceptance_tests ();
    }

    /**
     * Ask the user for the name of the controller
     */
    private function get_controller_name ()
    {
        display ( "Controller name: \n" );

        $this->_controller_name = get_input ();
    }

    /**
     * Get the view fields for the edit page
     *
     * This will keep asking the user for input until the user enters 'n'
     */
    private function get_view_fields ()
    {
        do {
            display ( "Enter a field: ( name:type:data type:max length ) \n" );
            $field = get_input ();

            if ($field != 'n') {
                $x = explode ( ":", $field );

                $this->_fields[ $x[ 0 ] ] = array ( 'name' => $x[ 0 ],
                                                       'type' => $x[ 1 ],
                                                       'data_type' => $x[ 2 ],
                                                       'length' => $x[ 3 ],
                                                       'options' => $x[ 4 ] );
            }
        } while ( $field != 'n' );
    }

    /**
     * Set up and save the controller
     *
     * This will be generated from the template with the obvious name change
     */
    public function process_controller ()
    {
        $controller_tmpl = get ( PATH . "cmd/templates/controller_tmpl.txt" );

        $use_image = '';
        $use_upload = '';

        //If image upload is enabled included the section to handle it
        if ( $this->_fields[ 'image' ] ) {
            $use_image = '//Handle the image
                            if ( !!$_POST["image"] || !!$_FILES )
                                $_POST[ "' . $this->_controller_name . '" ][ "image_id" ] = Image_helper::save_one( $_POST[ "image" ] );
                            else
                                $_POST[ "' . $this->_controller_name . '" ][ "image_id" ] = NULL;';
        }

        //Same with the documents
        if ( $this->_fields[ 'upload' ] ) {
            $use_upload = '//If the user has selected some files to upload run this
                           //Send the $_FILES array to the application controller for the saving to be handled
                           if ( ( !!$_FILES[ "uploads" ] && $_POST[ "upload_name" ] ) || !!$_POST[ "downloads" ] ) 
                               $_POST[ "' . $this->_controller_name . '" ][ "uploads_id" ] = Document_helper::save();
                           else
                               $_POST[ "' . $this->_controller_name . '" ]["uploads_id"] = !!$_POST[ "uploads" ][ "id" ] ? $_POST[ "uploads" ][ "id" ] : NULL;';
        }

        //Replace any instance of {{CONTROLLER}} with the actual controller name, also the image and upload if applicable
        $controller_tmpl = str_replace( array( "{{CONTROLLER}}", "{{USE_IMAGE}}", "{{USE_UPLOAD}}" ), array( $this->_controller_name, $use_image, $use_upload ), $controller_tmpl );

        if ( put ( $this->_admin_path . "controllers/" . $this->_controller_name . ".php", $controller_tmpl ) )
            display ( "The " . $this->_controller_name . " controller has been created successfully\n" );
    }

    /**
     * Set up and save the model
     *
     * This will be generated from a template
     */
    public function process_model ()
    {
        $model_tmpl = get ( PATH . "cmd/templates/model_tmpl.txt" );
        //Replace any instance of {{MODEL}} with the actual model name
        $model_tmpl = str_replace ( "{{MODEL}}", $this->_controller_name, $model_tmpl );

        //Organise the validation rules
        $validation = '';
        $use_image = '';
        $use_upload = '';

        foreach ($this->_fields as $field) {
            if ( $field[ 'name' ] != 'image' && $field[ 'name' ] != 'upload' )
                $validation .= '$this->validates( "not_empty", "' . $field[ 'name' ] . '" ); ';
        }

        //Check if the model needs to use a image
        if ( !!$this->_fields[ 'image' ] ) {
            $use_image = '$this->_has_image = TRUE;';
        }

        //Check if the model needs to use a upload
        if ( !!$this->_fields[ 'upload' ] ) {
            $use_upload = '$this->_has_upload = TRUE;';
        }

        //Replace the {{VALIDATION}} with the actual set of validation rules
        $model_tmpl = str_replace ( array( "{{VALIDATION}}", "{{HAS_IMAGE}}", "{{HAS_UPLOAD}}" ), array( $validation, $use_image, $use_upload ), $model_tmpl );
        //Make its own directory
        mkdir ( PATH . "app/models/" . $this->_controller_name );

        if ( put ( PATH . "app/models/" . $this->_controller_name . "/" . $this->_controller_name . "_model.php", $model_tmpl ) )
            display ( $this->_controller_name . " model has been created successfully\n" );
    }

    /**
     * Set up and save the view for the edit page
     *
     * This will be generated from a template
     */
    public function process_view_edit ()
    {
        $view_tmpl = get ( PATH . "cmd/templates/view_edit_tmpl.txt" );

        $fields_tmpl = '';

        //Loop round the fields array and find the matching template for each field type
        foreach ($this->_fields as $field) {
            $tmpl = get ( PATH . "cmd/templates/form/" . $field[ 'type' ] . ".txt" );

            //Replace the {{NAME}} with the name of the field
            $tmpl = str_replace( "{{NAME}}", $field[ 'name' ], $tmpl );
            //Replace the {{CONTROLLER}} with the name of the controller
            $tmpl = str_replace( "{{CONTROLLER}}", $this->_controller_name, $tmpl );

            if (!!$field[ 'options' ]) {
                switch ($field[ 'type' ]) {
                    case 'checkbox':
                        //Organise the checkbox options into checkboxes
                        //Need to make sure the checked attribute is added if the item present
                        $boxes = '';

                        foreach ( explode ( ",", $field[ 'options' ] ) as $item ) {
                            $boxes .= $item . ': <input type="checkbox" name="' . $this->_controller_name . '[' . $field[ 'name' ] . '][]" value="' . $item . '" />';
                        }

                        $tmpl = str_replace ( "{{BOXES}}", $boxes, $tmpl );

                    break;

                    case 'radio':
                        //Organise the radio options into radio buttons
                        //Also need to do the selected thingy
                        $radios = '';

                        foreach ( explode ( ",", $field[ 'options' ] ) as $item ) {
                            $radios .= $item . ': <input type="radio" <?php echo ( $' . $field[ 'name' ] . ' == "' . $item . '" ? \'checked="checked"\' : "" ); ?> name="' . $this->_controller_name . '[' . $field[ 'name' ] . ']" value="' . $item . '" />';
                        }

                        $tmpl = str_replace ( "{{RADIOS}}", $radios, $tmpl );

                    break;
                }
            }

            $fields_tmpl .= $tmpl;
        }

        //At this point we should have all the necessary fields sorted
        $view_tmpl = str_replace ( "{{FIELDS}}" , $fields_tmpl, $view_tmpl );
        //Put the controller name into the view
        $view_tmpl = str_replace ( "{{CONTROLLER}}", $this->_controller_name, $view_tmpl );

        //Create its own directory
        mkdir ( $this->_admin_path . "views/templates/" . $this->_controller_name );

        if ( put ( $this->_admin_path . "views/templates/" . $this->_controller_name . "/edit.php", $view_tmpl ) )
            display ( "The edit view has been created successfully\n" );
    }

    /**
     * Its not feasible to pass the fields array property to the dba script without altering it a great deal so I decided to
     * just take the methods I need ( there are only a few ) and integrate them into this script
     */
    public function process_database_table ()
    {
        //First check if the table already exists
        $this->check_table_exists ();
        //Form the query that will be run to create the table
        $query = $this->form_query_create ();

        //Run the query and create the table
        $this->_query->plain ( $query );
        display ( "The " . $this->_controller_name . " has been created successfully\n" );
    }

    /**
     * Queries the database to check if the table exists
     * Applied the result to the table_exists property
     *
     * @access public
     */
    private function check_table_exists ()
    {
        $table_exists = $this->_query->getAssoc ( "SHOW TABLES LIKE '" . DB_SUFFIX . "_" . $this->_controller_name . "'" );
        $this->_table_exists = ( !!$table_exists ? TRUE : FALSE );
    }

    /**
     * Takes the fields property and organises it into a query if the table doesnt already exist
     * If the table does already exist a warning is displayed and the script is killed
     *
     * @access private
     */
    private function form_query_create ()
    {
        if ($this->_table_exists === TRUE) {
            display ( "The table '" . $this->_controller_name . "' already exists.\n" );
        } else {
            $query = 'CREATE TABLE ' . DB_SUFFIX . '_' . $this->_controller_name . ' (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,';

            foreach ($this->_fields as $field) {
                if ( $field[ 'name' ] == 'image' )
                    $query .= '`image_id` INT(11), ';

                elseif ( $field[ 'name' ] == 'upload' )
                    $query .= '`uploads_id` INT(11), ';

                else
                    $query .= '`' . $field['name'] . '` ' . $field['data_type'] . '' . ( !!$field['length'] ? '(' . $field['length'] . ')' : '' ) . ',';
            }

            $query .= '`approved` INT(1) DEFAULT 0 NOT NULl, ';
            $query .= '`create_date` TIMESTAMP )';

            $this->create_schema ( $this->_controller_name, $query );

            return $query;
        }
    }

    /**
     * Method to save a migration class into the 'migrations' folder
     *
     * @access public
     */
    public function create_schema ()
    {
        //Form the schema array from the fields array because for some reason I cant just pop that into the file string
        $schema = 'array ( "id" => array ( "name" => "id",
                                           "type" => "int",
                                           "limit" => "11" ),
                           "create_date" => array ( "name" => "create_date",
                                                    "type" => "timestamp",
                                                    "limit" => "" ),
                           "approved" => array ( "name" => "approved",
                                                 "type" => "int",
                                                 "limit" => "11" ),
                  ';

        //If the user has chosen to allow images we need to add this to the schema
        if ( !!$this->_fields[ 'image' ] ) {
            $schema .= '"image_id" => array ( "name" => "image_id",
                                              "type" => "int",
                                              "limit" => "11" ),';
        }

        //Same with the uploads
        if ( !!$this->_fields[ 'upload' ] ) {
            $schema .= '"uploads_id" => array ( "name" => "uploads_id",
                                                "type" => "int",
                                                "limit" => "255" ),';
        }

        $i = 1;
        $count = count ( $this->_fields );

        foreach ($this->_fields as $field) {
            $comma = ( $i != $count ) ? ', ' : '';

            $schema .= '"' . $field[ 'name' ] . '" => array ( "name" => "' . $field[ 'name' ] . '",
                                                              "type" => "' . $field[ 'data_type' ] . '",
                                                              "limit" => "' . $field[ 'length' ] . '" )' . $comma . '
                                                              ';
            $i++;
        }

        $schema .= ' )';

        //Start the class
        $migration = '<?php

if ( class_exists ( "query_builder" ) != TRUE )
    include "cmd/query_builder.php";

if ( class_exists ( "base_build" ) != TRUE )
    include "cmd/base_build.php";

class build_' . $this->_controller_name . ' extends base_build
{
    private $_builder;

    protected $_schema = '  . $schema . ';

    public function __Construct ( $db_name, $tablename )
    {
        $this->_tablename = $tablename;
        $this->_db_name = $db_name;

        $this->_build = new query_builder ( $db_name, "' . $this->_controller_name . '" );
    }

    public function put ()
    {
        $this->_build->create_table ( "' . $this->_controller_name . '" );
';
        foreach ($this->_fields as $field) {
            $migration .= '
        $this->_build->' . $field['data_type'] . ' ( "' . $field['name'] . '"' . ( !!$field['length'] ? ', "' . $field['length'] . '"' : '' ) . ' );';
        }

        $migration .= '
        $this->_build->timestamp ( "create_date" );';

    $migration .= '
        $this->_build->run ();
    }


    /**
     * Method to decide whether to create the whole table or to send it to the method so it can be altered
     *
     * @access public
     */
    public function desc ()
    {
        $table_exists = mysql_query ( "SHOW TABLES LIKE ' . $this->_db_name . '_' . $this->_tablename . '" );

        if ( mysql_num_rows ( $table_exists ) == 0 )
            $this->put ();

        else
            $this->alter ();
    }
}

$build = new build_' . $this->_controller_name . ' ( $this->_db_name, "' . $this->_controller_name . '" );
$build->desc ();

?>';
        //Make sure the migrations directory already exists
        mkdir ( PATH . 'core/database/migrations/' );

        file_put_contents ( PATH . 'core/database/migrations/' . date("Y-m-d H-i-s") . '.' . $this->_controller_name . '.php', $migration );
    }

    /**
     * Now that everything should be made some unit tests need to be generated
     */
    public function generate_unit_tests ()
    {
        //We need to generate some tests for NULL data
        //So I need to loop round the fields array property and generate a unit test for each field
        //We also need to put the mock data as a property
        $test_tmpl = get ( PATH . 'cmd/templates/unit_test_tmpl.txt' );

        $mock_data_array_string = '';

        $i = 0;
        foreach ($this->_fields as $field) {
            if ($field[ 'name' ] != 'image') {
                $count = !!$this->_fields[ 'image' ] ? count ( $this->_fields ) - 1 : count ( $this->_fields ) ;
                //Build the mock data array
                $mock_data_array_string .= '"' . $field[ 'name' ] . '" => "Unit Test Data"' . ( $count - 1 > $i ? ', ' : '' );
                $i++;
            }
        }

        $mock_data_c = 'array ( "' . $this->_controller_name . '" => array ( ' . $mock_data_array_string . ' ) )';

        $test_tmpl = str_replace ( "{{MOCK_DATA}}", $mock_data_c, $test_tmpl );

        $test_cases = '';

        //Now to form the actual text cases
        foreach ($this->_fields as $field) {
            if ($field[ 'name' ] != 'image') {
                $test_cases .= 'public function testSaveWithout' . ucfirst ( $field[ 'name' ] ) . '() {
                unset ( $this->_data[ "' . $this->_controller_name . '" ][ "' . $field[ 'name' ] . '" ] );
                $this->assertFalse( $this->_model->save ( $this->_data[ "' . $this->_controller_name . '" ] ) );
           }

           ';
            }
        }

        $test_tmpl = str_replace ( "{{TEST_CASES}}" , $test_cases, $test_tmpl );

        //Finally we need to add the controller name
        $test_tmpl = str_replace ( "{{CONTROLLER}}", $this->_controller_name, $test_tmpl );

        if ( put ( $this->_unit_tests_path . ucfirst ( $this->_controller_name ) . 'AdminTest.php', $test_tmpl ) )
            display ( "Unit test has been generated successfully\n" );
    }

    /**
     * Method to generate some acceptance tests
      */
    public function generate_acceptance_tests ()
    {
        $tmpl = get ( PATH . 'cmd/templates/acceptance_test_tmpl.txt' );

        $all_null_check = '';

        foreach ($this->_fields as $field) {
            if ($field[ 'name' ] != 'image') {
                $all_null_check .= '$I->see("' . ucfirst ( $field[ 'name' ] ) . ' can not be empty");
            ';
            }
        }

        $tmpl = str_replace ( "{{ALL_NULL_CHECK}}", $all_null_check, $tmpl );

        //Add the controller name to the file
        $tmpl = str_replace ( "{{CONTROLLER}}", $this->_controller_name, $tmpl );

        //Start the string that will hold th erest of the tests
        $single_field_tests = '';

        //Loop through all of the fields and create a test case that deals with each one being missing individually
        foreach ($this->_fields as $field) {
            if ($field[ 'name' ] != 'image') {
                $single_field_tests .= '$I->amGoingTo("Submit ' . $this->_controller_name . ' form without a ' . $field[ 'name' ] . '");
                                        $I->click("Save");
                                        ';

                foreach ($this->_fields as $item) {
                    if ($item[ 'name' ] != 'image' && $item[ 'name' ] != $field[ 'name' ]) {
                        $single_field_tests .= '$I->fillField ( "' . $this->_controller_name . '[' . $item[ 'name' ] . ']", "Acceptance Test" );
                        ';
                    }
                }
            }
        }

        $tmpl = str_replace ( "{{SINGLE_FIELD_TESTS}}", $single_field_tests, $tmpl );

        //Save the file
        if ( put ( $this->_acceptance_tests_path . 'Admin' . ucfirst ( $this->_controller_name ) . 'EditCept.php', $tmpl ) )
            display ( "Acceptance test for " . $this->_controller_name . " has been saved successfully\n" );
    }
}

$admin_listing = new admin_listing ();
