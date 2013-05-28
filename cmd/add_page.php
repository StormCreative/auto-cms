<?php
include 'base.php';

class add_page extends Base
{
    private $_page;

    /**
     * Method to prompt the user to enter a name for the new page
     * Checks if the page already exists, if it does the user can re-enter a name or quit
     *
     * @access public
     */
    public function get_page ()
    {
        //Ask the user for a page name
        display ( "Enter a name for the new page...\n" );
        $page = get_input ();

        //If the user wants to make a contact page, pass the request to the contact object because it is handled differently than normal pages
        if ($page == 'contact') {
            include 'contact.php';
            die (0);
        }

        //Check if the page controller already exists
        do {
            if ( file_exists ( '../app/controllers/' . strtolower ( $page ) . '.php' ) ) {
                display ( "The page '" . $page . "' already exists. Pick another or enter 'n' to quit...\n" );
                $page = get_input ();
            } elseif ( $page == 'n' )
                die (0);
        } while ( file_exists ( '../app/controllers/' . strtolower ( $page ) . '.php' ) && $page != 'n' );

        $this->_page = $page;
    }

    /**
     * Method to connect to mysql
     *
     * @access public
     */
    public function connect_mysql ()
    {
        mysql_connect ( $this->_db_host, $this->_db_user, $this->_db_pass ) or die ( mysql_error () );
        mysql_select_db ( $this->_db_name );
    }

    /**
     * Method to check if the pages table already exists
     * If not it is created, same goes with the model
     *
     * @access public
     */
    public function check_exists ()
    {
        $pages_exists_query = mysql_query ( "SHOW TABLES LIKE '" . $this->_db_name . "_page'" );
        $pages_exists_count = mysql_num_rows ( $pages_exists_query );

        if ($pages_exists_count == 0) {
            mysql_query ( 'CREATE TABLE `' . $this->_db_name . '_page` (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `' . $this->_db_name . '_page_name` VARCHAR(255),
            `' . $this->_db_name . '_page_content` VARCHAR(255),
            `' . $this->_db_name . '_page_image_id` INT(11),
            `create_date` timestamp )' ) or die ( mysql_error () );

            display ( "The pages table was not found so it has been created.\n" );
        }

        if ( !file_exists ( '../app/models/pages/pages_model.php' ) ) {
            $pages_model = '<?php

class page_model extends active_record
{
    public $page_name;

    public function __Construct ( $page )
    {
        parent::__Construct ();
        $this->page_name = $page;
    }

    public function get_page_id ( $name )
    {
        $data = $this->find ( $name, "' . $this->_db_name . '_page_name" );
    }
}

?>';

            mkdir ( '../app/models/page' );
            file_put_contents ( '../app/models/page/page_model.php', $pages_model );

            display ( "\n\nThe pages model was not found so it has been created.\n\n" );
        }
    }

    public function create_controller ()
    {
        $page_controller = '
<?php

class ' . $this->_page . ' extends application_controller implements genesis
{
    protected $_page;

    public function __Construct ()
    {
        parent::__Construct ();
        $this->_page = new page_model ( "' . $this->_page . '" );

        $this->useImage();
    }

    public function index ()
    {
        $this->_page_id = $this->_page->get_page_id ( "' . $this->_page . '" );

        if ( post_set () ) {
            $this->images->imgname = $_POST[\'image\'][0];
        }

        $this->saveImage ( $this->images );

        if ( post_set () ) {
            $_POST[ \'page\' ][ \'image_id\' ] = $this->images->id;
            $this->_page->save( $_POST[ "page" ] );
            $success = $this->forms->getSuccessMessage ();
        }

        $this->_page->find( $this->_page->id );
        $this->images->find( $this->_page->image_id );

        // Sets the form values by the properties set through the Find method in active record
        $tags = $this->forms->setFormValues ( $this->_page );

        $this->mergeTags ( $tags );
        // Setting the image so we can do deletion on it
        $this->addTag ( \'image\', $this->images->id );
        $this->addTag ( \'imgname\', $this->images->imgname );
        $this->addTag ( \'success\', $success );
        $this->addStyle ( \'upload\' );

        $this->setView ( \'page/' . $this->_page . '\' );
    }

    public function add () {}

    public function edit ( $id = "" ) {}
}

?>';

        file_put_contents ( '../app/controllers/' . $this->_page . '.php', $page_controller );

        display ( "Controller Saved.\n" );
    }

    /**
     * Method to create the view
     * Needs the user to choose whether or not to include a image
     *
     * @access public
     */
    public function create_view ()
    {
        display ( "Will this page have a banner image? ( y / n )\n" );
        if ( get_input () == 'y' ) {
$image = '
<input type="hidden" id="image_id" name="page[image_id]" value="<?php echo $image; ?>" />
    <input type="hidden" name="image" id="imgname" value="<?php echo $imgname; ?>" />

    <label class="label" for="img">Image</label>

    <div id="image-list" class="img-list">
    <?php if ( !!$imgname ): ?>
        <div id="img_<?php echo $imgname; ?>">
            <span class="img" ><img src="<?php echo DIRECTORY; ?>Assets/Uploads/Images/<?php echo $imgname; ?>" /></span>
            <ol class="hoz btns">
                <li><input type="button" id="<?php echo $imgname; ?>"  class="btn del-image" value="Delete Image" /></li>
            </ol>
        </div>
    <?php endif; ?>
    </div>


    <ol class="hoz btn-upload" id="multi-upload">
        <li><input type="file" name="image" id="upload" accept="image/*" /></li>
        <li><input type="button" class="btn" <?php echo !!$imgname ? \'disabled="disabled"\' : \'\'; ?> id="upload-link" value="Upload Image" /></li>
    </ol>';
        }

//We are also going to need a view for this controller
$pages_view = '
<article class="article">
    <h1 class="heading gradbar">' . ucfirst ( $this->_page ) . ' <a href="<?php echo DIRECTORY; ?>" class="btn btn-back" />Back</a></h1>
    <form method="post" action="" id="form" enctype="multipart/form-data">
        <input type="hidden" name="page[id]" id="hidden_id" value="<?php echo $id; ?>" />
        <input type="hidden" name="page[name]" id="hidden_id" value="' . $this->_page . '" />
        <div class="module form cf" >

        <?php echo $success; ?>

        <label class="label" for="wysiwyg">Content</label>
        <textarea name="page[content]' . '" class="wysiwyg" id="wysiwyg"><?php echo $content; ?></textarea>

        ' . $image . '

    </div>
    <input type="submit" name="save" class="btn" value="Save" />
    </form>

    <script>

        var admin_images = [{ width: 484, uploader: \'#upload\', method: \'imageUploadMulti\' }];

    </script>

</article>';

        if ( !file_exists ( '../app/views/templates/page' ) )
            mkdir ( '../app/views/templates/page' );

        file_put_contents ( '../app/views/templates/page/' . $this->_page . '.php', $pages_view );

        display ( "View Created.\n" );
    }

    /**
     * Method to add the row for the page into the pages table
     * Checks if the row is already present, if it is no action is taken
     *
     * @access public
     */
    public function add_row ()
    {
        $page_check_sql = 'SELECT * FROM ' . $this->_db_name . '_page WHERE ' . $this->_db_name . '_page_name = "' . $this->_page . '"';
        $page_check_query = mysql_query ( $page_check_sql );

        if ( mysql_num_rows ( $page_check_query ) == 0 )
            mysql_query ( 'INSERT INTO ' . $this->_db_name . '_page ( ' . $this->_db_name . '_page_name ) VALUES ( "' . $this->_page . '" )' ) or die ( mysql_error () );

        display ( "End\n\n" );

    }

}

$add_page = new add_page ();
$add_page->get_page ();
$add_page->connect_mysql ();
$add_page->check_exists ();
$add_page->create_controller ();
$add_page->create_view ();
$add_page->add_row ();
