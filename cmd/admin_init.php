<?php

include 'cmd/base_admin.php';

class admin_init extends base_admin
{
    /**
     * The construct function should handle checking that the admin command hasnt already been run
     * This can simply be done by checking if the access tbale exists, if it does the script will be terminated
     */
    public function __Construct ()
    {
        parent::__Construct ();

        //Check if the access table exists
        //$data = $this->_query->getAssoc( "SHOW TABLES LIKE '" . DB_SUFFIX . "_access'" );

        if ( count ( $data ) > 0 ) {
            display ( "\nIt seems the admin init script has already been run for this site.\nIf you want to run it again you will need to do a manual rollback.\n\n" );
            die ( 0 );
        }
    }

    /**
     * This is just a wrapper method that calls all the other methods necessary
     */
    public function init ()
    {
        //Create the access table and controller
        $this->create_access ();
        //Create the image table and controller
        $this->create_image ();
        //Create the uploads table and controller
        $this->create_uploads ();
        //Create the dashboard controller / view
        $this->create_dashboard ();
    }

    /**
     * Method to create or action:
     * - access model
     * - controller
     * - login backbone view
     * - view
     */
    private function create_access ()
    {
        $model = get ( 'cmd/templates/access/access_model.txt' );

        //Create the directory in models and the models directory if it doesnt already exist
        mkdir ( PATH . "app/models/" );
        mkdir ( PATH . "app/models/access/" );

        if ( put ( PATH . 'app/models/access/access_model.php', $model ) == TRUE )
            display ( "Access model created successfully\n" );

        $this->create_access_table ();
        $this->create_access_files ();
    }

    /**
     * Method to create the database table if it doesnt already exist
     */
    private function create_access_table ()
    {
        //If the script gets to here we know that the access table doesnt exists so we can go ahread and create it
        $query = 'CREATE TABLE ' . DB_SUFFIX . '_access (
                 `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                 `email` VARCHAR(255),
                 `password` VARCHAR(255),
                 `create_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL )';

        $this->_query->plain ( $query );
        display ( "Access table has been created successfully\n" );
    }

    /**
     * This will create:
     * - Controller
     * - View
     * - Backbone view
     *
     * So in actualality it will be the home controller
     */
    private function create_access_files ()
    {
        //Controller
        $controller = get ( PATH . "cmd/templates/home/home-controller.txt" );
        if ( put ( $this->_admin_path . "controllers/home.php", $controller ) )
            display ( "Home controller created succesfully\n" );

        //View
        $view = get ( PATH . "cmd/templates/home/home-view.txt" );
        mkdir ( $this->_admin_path . "views/templates/home/" );
        if ( put ( $this->_admin_path . "views/templates/home/index.php", $view ) )
            display ( "Home view has been created successfully\n" );

        //Main javascript file in the app folder
        $mainjs = get ( PATH . "cmd/templates/home/home-mainjs.txt" );
        if ( put ( $this->_admin_path . "assets/scripts/app/login.js", $mainjs ) )
            display ( "Home main javascript file has been created successfully\n" );

        //Backbone view
        $backbone_view = get ( PATH . "cmd/templates/home/home-backbone-view.txt" );
        if ( put ( $this->_admin_path . "assets/scripts/views/Login.js", $backbone_view ) )
            display ( "Login backbone view has been created successfully\n" );
    }

    /**
     * This method will create or action:
     * - image model
     * - image table
     */
    private function create_image ()
    {
        $model = file_get_contents ( "cmd/templates/image/image_model.txt" );

        //Create the directory for the image model
        mkdir ( PATH . "app/models/image/" );

        if ( put ( PATH . "app/models/image/image_model.php", $model ) == TRUE )
            display ( "Image model has been created successfully\n" );

        $this->create_image_table ();
    }

    /**
     * Method to create the image table if it doesnt already exist
     * This will need a check to see if the image table already exists
     */
    private function create_image_table ()
    {
        $query = 'CREATE TABLE ' . DB_SUFFIX . '_image (
                 `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                 `imgname` VARCHAR(255),
                 `create_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL )';

        $this->_query->plain ( $query );
        display ( "Image table has been created successfully\n" );
    }

    /**
     * This method will create or action:
     *	- uploads model
     *  - uploads table
     */
    private function create_uploads ()
    {
        $model = file_get_contents ( 'cmd/templates/uploads/uploads_model.txt' );

        //Create the directory for the uploads model
        mkdir ( PATH . "app/models/uploads" );

        if ( file_put_contents ( PATH . "app/models/uploads/uploads_model.php", $model ) == TRUE )
            display ( "Uploads model has been created successfully\n" );

        $this->create_uploads_table ();
    }

    /**
     * Creates the uploads table
     */
    private function create_uploads_table ()
    {
        $query = 'CREATE TABLE ' . DB_SUFFIX . '_uploads (
                 `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                 `title` VARCHAR(255),
                 `name` VARCHAR(255),
                 `create_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL )';

        $this->_query->plain ( $query );
        display ( "Uploads table has been created successfully\n" );
    }

    private function create_dashboard ()
    {
        //The controller
        $controller = get ( 'cmd/templates/dashboard/dashboard-controller.txt' );
        if ( put ( $this->_admin_path . 'controllers/dashboard.php', $controller ) == TRUE )
            display ( "Dashboard controller created successfully\n" );

        //The view
        $view = get ( 'cmd/templates/dashboard/dashboard-view.txt' );
        mkdir ( $this->_admin_path . "views/templates/dashboard" );
        if ( put ( $this->_admin_path . 'views/templates/dashboard/index.php', $view ) == TRUE )
            display ( "Dashboard view ( index ) has been created successfully\n" );
    }
}

$admin_init = new admin_init ();
$admin_init->init ();
