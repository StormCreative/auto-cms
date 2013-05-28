<?php

include 'cmd/base_admin.php';

class Build extends Base_admin
{
	private $_contents;

	private $_use_image = FALSE;
	private $_use_upload = FALSE;

    //Set some directory paths so we dont have to keep writing them out
    private $_unit_tests_path;
    private $_acceptance_tests_path;

	/**
	 * Constructors job is to call the parent constructor, this includes:
	 *	- Autoloader
	 *	- Helper functions
	 *	- Database classes
	 *
	 * Also reads the contents of the build.json file and decodes it into a property
	 *
	 * @access public
	 */
	public function __Construct()
	{
		parent::__Construct();

		$this->_contents = json_decode( file_get_contents( 'cmd/build/build.json' ) );

		$this->_unit_tests_path = PATH . 'tests/unit/';
    	$this->_acceptance_tests_path = PATH . 'tests/acceptance/';
	}

	/**
	 * Method to loop through the object retrieved from the build.json file and if the page type is normal we will construct
	 * the database table, migration file, controller, model, view, unit test and acceptance test.
	 * 
	 *
	 * @access public
	 */
	public function construct_normal_pages()
	{
		foreach ( $this->_contents as $key => $item )
		{
			$this->create_table( $key, $item->fields );
			$this->create_controller( $key );
			$this->create_model( $key, $item->fields );
			$this->create_view( $key, $item->fields );

			$this->create_unit_test( $key, $item->fields );
			$this->create_acceptance_test( $key, $item->fields );
		}
	}

	/**
	 * Method to create the database table for the page
	 *
	 * @param string $name
	 * @param object $fields
	 *
	 * @access public
	 */
	public function create_table( $name, $fields )
	{
		$query = 'CREATE TABLE `' . DB_SUFFIX . '_' . $name . '` (
           		 `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,';

        foreach( $fields as $key => $value ) {

        	if ( $key == 'image' ) {

        		$this->_use_image = TRUE;
        		$query .= '`gallery` VARCHAR(255),
        				  `image_id` INT(11),';
        	}
        	else {
        		$query .= '`' . $key . '` ' . type_to_field( $value ) . ',';
        	}

        	if ( $key == 'upload' ) {
        		$this->_use_upload = TRUE;
        	}
        }

        $query .= ' `create_date` TIMESTAMP )';

		$this->_query->plain( $query );

		display( "$name table has been created successfully\n" );
	}

	/**
	 * Method to create the controller for a page
	 * Dont think it needs to do anything fancy just input the controller name into the template and save it
	 * 
	 * @param string $name
	 *
	 * @access public
	 */
	public function create_controller( $name )
	{
		$use_image = '';
		$use_upload = '';

		if ( $this->_use_image == TRUE ) {
			$use_image = '//Handle the image
					        if ( !!$_POST["multi-image"] || !!$_FILES )
					            $_POST[ "' . $name . '" ][ "gallery" ] = implode( ",", Image_helper::save_many( $_POST[ "multi-image" ] ) );
					        else
					            $_POST[ "' . $name . '" ][ "gallery" ] = NULL;';
		}

		if ( $this->_use_upload == TRUE ) {
			$use_upload = '//If the user has selected some files to upload run this
                           //Send the $_FILES array to the application controller for the saving to be handled
                           if ( ( !!$_FILES[ "uploads" ] && $_POST[ "upload_name" ] ) || !!$_POST[ "downloads" ] ) 
                               $_POST[ "' . $name . '" ][ "uploads_id" ] = Document_helper::save();
                           else
                               $_POST[ "' . $name . '" ]["uploads_id"] = !!$_POST[ "uploads" ][ "id" ] ? $_POST[ "uploads" ][ "id" ] : NULL;';
		}

		$tmpl = str_replace( array( '{{CONTROLLER}}', '{{USE_IMAGE}}', '{{USE_UPLOAD}}' ), array( $name, $use_image, $use_upload ), file_get_contents( 'cmd/templates/controller_tmpl.txt' ) );

		if ( put( $this->_admin_path . "controllers/" . $name . ".php", $tmpl ) ) {
			display( "$name controller has been created successfully\n" );
		}
	}

	/**
	 * Method to create the model
	 * Just need to loop through and create the validation rules and whether the model needs a image / upload
	 *
	 * @param string $name
	 * @param object $fields
	 *
	 * @access public
	 */
	public function create_model ( $name, $fields )
	{
		$has_image = '';
		$has_upload = '';
		$validation = '';

		if ( $this->_use_image == TRUE ) {
			$has_image = '$this->_has_image = TRUE;';
		}

		if ( $this->_use_upload == TRUE ) {
			$has_upload = '$this->_has_upload = TRUE;';
		}

		$c = count( (array) $fields );
		if ( $c > 0 ) {
			$validation = '$this->validates = array(';
			//Organise the validation
			$i = 0;
			foreach ( $fields as $key => $value ) {

				if ( $i < ( $c - 1 ) ) {
					$comma = ',';
				}
				else {
					$comma = '';
				}

	            if ( $key != 'image' && $key != 'upload' )
	                $validation .= 'array( "not_empty", "' . $key . '" )' . $comma;

	            $i++;
	        }

	        $validation .= ');';
		}

		$tmpl = str_replace( array( '{{MODEL}}', '{{HAS_IMAGE}}', '{{HAS_UPLOAD}}', '{{VALIDATION}}' ), array( $name, $has_image, $has_upload, $validation ), file_get_contents( 'cmd/templates/model_tmpl.txt' ) );

		mkdir( "app/models/" . $name . "/" );
		if ( put( "app/models/" . $name . "/" . $name . "_model.php", $tmpl ) ) {
			display( "$name model has been created successfully\n" );
		}
	}

	/**
	 * Method to create the view
	 *
	 * @param string $name
	 * @param object $fields
	 * 
	 * @access public
	 */
	public function create_view( $name, $fields )
	{
		$view_tmpl = get ( PATH . "cmd/templates/view_edit_tmpl.txt" );

        $fields_tmpl = '';

        //Loop round the fields array and find the matching template for each field type
        foreach ( $fields as $key => $value ) {

        	//Get the options
            $options = explode( ':', $value );
            $tmpl = get ( PATH . "cmd/templates/form/" . $options[0] . ".txt" );

            //Replace the {{NAME}} with the name of the field
            $tmpl = str_replace( "{{NAME}}", $key, $tmpl );
            //Replace the {{CONTROLLER}} with the name of the controller
            $tmpl = str_replace( "{{CONTROLLER}}", $name, $tmpl );

            if ( $options[0] == 'radio' || $options[0] == 'checkbox' ) {

                switch ( $options[0] ) {
                    case 'checkbox':
                        //Organise the checkbox options into checkboxes
                        //Need to make sure the checked attribute is added if the item present
                        $boxes = '';

                        foreach ( explode ( ",", $options[1] ) as $item ) {
                            $boxes .= $item . ': <input type="checkbox" name="' . $name . '[' . $key . '][]" value="' . $item . '" />';
                        }

                        $tmpl = str_replace ( "{{BOXES}}", $boxes, $tmpl );

                    break;

                    case 'radio':
                        //Organise the radio options into radio buttons
                        //Also need to do the selected thingy
                        $radios = '';

                        foreach ( explode ( ",", $options[1] ) as $item ) {
                            $radios .= $item . ': <input type="radio" <?php echo ( $' . $key . ' == "' . $item . '" ? \'checked="checked"\' : "" ); ?> name="' . $name . '[' . $key . ']" value="' . $item . '" />';
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
        $view_tmpl = str_replace ( "{{CONTROLLER}}", $name, $view_tmpl );

        //Create its own directory
        mkdir ( $this->_admin_path . "views/templates/" . $name );

        if ( put ( $this->_admin_path . "views/templates/" . $name . "/edit.php", $view_tmpl ) )
            display ( "$name view has been created successfully\n" );
	}

	/**
	 * Method to get the unit test for the edit page
	 *
	 * @param string $name
	 * @param object $fields
	 *
	 * @access public
	 */
	public function create_unit_test( $name, $fields )
	{
		//We need to generate some tests for NULL data
        //So I need to loop round the fields array property and generate a unit test for each field
        //We also need to put the mock data as a property
        $test_tmpl = get ( PATH . 'cmd/templates/unit_test_tmpl.txt' );

        $mock_data_array_string = '';

        $i = 0;
        foreach ( $fields as $key => $value ) {
            if ( $key != 'image' ) {
                $count = !!$fields->image ? count ( (array) $fields ) - 1 : count ( (array) $fields );
                //Build the mock data array
                $mock_data_array_string .= '"' . $key . '" => "Unit Test Data"' . ( $count - 1 > $i ? ', ' : '' );
                $i++;
            }
        }

        $mock_data_c = 'array ( "' . $name . '" => array ( ' . $mock_data_array_string . ' ) )';

        $test_tmpl = str_replace ( "{{MOCK_DATA}}", $mock_data_c, $test_tmpl );

        $test_cases = '';

        //Now to form the actual text cases
        foreach ( $fields as $key => $value ) {

            if ( $key != 'image' ) {
                $test_cases .= 'public function testSaveWithout' . ucfirst ( $key ) . '() {
                unset ( $this->_data[ "' . $name . '" ][ "' . $key . '" ] );
                $this->assertFalse( $this->_model->save ( $this->_data[ "' . $name . '" ] ) );
           }
           ';
            }
        }

        $test_tmpl = str_replace ( "{{TEST_CASES}}" , $test_cases, $test_tmpl );

        //Finally we need to add the controller name
        $test_tmpl = str_replace ( "{{CONTROLLER}}", $name, $test_tmpl );

        if ( put ( $this->_unit_tests_path . ucfirst ( $name ) . 'AdminTest.php', $test_tmpl ) )
            display ( "$name unit test has been generated successfully\n" );
	}

	/**
	 * Method to create the acceptance test
	 *
	 * @param string $name
	 * @param object $fields
	 *
	 * @access public
	 */
	public function create_acceptance_test( $name, $fields )
	{
		$tmpl = get( PATH . 'cmd/templates/acceptance_test_tmpl.txt' );

        $all_null_check = '';

        foreach ( $fields as $key => $field ) {
            if ( $key != 'image' ) {
                $all_null_check .= '$I->see("' . ucfirst ( $key ) . ' can not be empty");
            ';
            }
        }

        $tmpl = str_replace ( "{{ALL_NULL_CHECK}}", $all_null_check, $tmpl );

        //Add the controller name to the file
        $tmpl = str_replace ( "{{CONTROLLER}}", $name, $tmpl );

        //Start the string that will hold th erest of the tests
        $single_field_tests = '';

        //Loop through all of the fields and create a test case that deals with each one being missing individually
        foreach ( $fields as $key => $value ) {

            if ( $key != 'image' ) {
                $single_field_tests .= '$I->amGoingTo("Submit ' . $name . ' form without a ' . $key . '");
                                        $I->click("Save");
                                        ';

                foreach ( $fields as $type => $item ) {
                    if ( $type != 'image' && $type != $key ) {
                        $single_field_tests .= '$I->fillField ( "' . $name . '[' . $type . ']", "Acceptance Test" );
                        ';
                    }
                }
            }
        }

        $tmpl = str_replace ( "{{SINGLE_FIELD_TESTS}}", $single_field_tests, $tmpl );

        //Save the file
        if ( put ( $this->_acceptance_tests_path . 'Admin' . ucfirst ( $name ) . 'EditCept.php', $tmpl ) )
            display ( "$name - acceptance test for " . $name . " has been saved successfully\n" );
	}
}

$build = new Build();
$build->construct_normal_pages();

?>