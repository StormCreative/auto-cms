<?php

/**
 * The contact page will be handled differently than other pages so I decided to give it its own class
 * This will only be included if the user decides to add a page and enters 'contact'
 */
class contact extends Base
{
    private $_both;
    private $_image;

    /**
     * Method to check if the contact page has already been made
     * If it exists, the process stops and feedback is given to the user
     *
     * @access public
     */
    public function check_exists ()
    {
        if ( file_exists ( '../app/controllers/contact.php' ) ) {
            display ( "A contact page already exists.\n" );
            die (0);
        }
    }

    /**
     * Method to create the controller
     * Needs to be able to handle the contact information and / or page description / image.
     * If both are required a listing page is also needed
     * So there could be a maximum three methods
     *
     * @access public
     */
    public function controller ()
    {
        $controller = '<?php

class contact extends application_controller implements genesis
{
    protected $contact;
    protected $_page;

    public function __Construct ()
    {
        parent::__Construct ();

        $this->contact = new contact_model ();
        $this->_page = new page_model ();

        $this->useImage();
        // This is actioned in Trait - forms (\core\helpers\forms)
        $this->forms->setActionTable ( \'contact\' );
    }

    public function edit ( $id = "" )
    {
        //Set the ID to 1 because that is the only row in the database
        $this->contact->id = 1;

        if ( post_set() ) {
            $this->contact->save( $_POST[ \'contact\' ] );
            $success = $this->forms->getSuccessMessage ();
        }

        $this->contact->find( $this->contact->id );

        // Sets the form values by the properties set through the Find method in active record
        $tags = $this->forms->setFormValues ( $this->contact );

        $this->mergeTags ( $tags );
        // Setting the image so we can do deletion on it
        $this->addTag ( \'image\', $this->images->id );

        $this->addTag ( \'success\', $success );
        $this->addStyle ( \'upload\' );

        $this->setView ( \'contact/edit\' );

    }

    public function index ()
    {
        $this->setView ( \'contact/index\' );
    }

    //Needed to avoid a conflict with the interface
    public function add () {}
    ';

        //Ask the user if they would like a contact page with editable text and image
        display ( "Would you like a contact page with editable text and / or image? ( y / n )\n" );
        $this->_both = get_input ();

        if ($this->_both == 'y') {
            display ( "Allow a image upload? ( y / n )" );
            $this->_image = get_input ();
        }

        if ($this->_both == 'y') {
            $controller .= '

    public function main ()
    {
        $this->_page_id = $this->_page->get_page_id ( "contact" );

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

        $this->setView ( \'contact/main\' );
    }
        ';
        }

        $controller .= '
}

?>';

        file_put_contents ( '../app/controllers/contact.php', $controller );

    }

    /**
     * Method to create the model
     * The contact page will need its own table to store its data, it will only ever contain one row
     * This method also creates the database table and inserts the one row needed
     *
     * @access public
     */
    public function model ()
    {
        $this->connect_mysql ();

        //Check if the contact table already exists, if not create it
        $table_exists_query = mysql_query ( "SHOW TABLES LIKE '" . $this->_db_name . "_contact'" );
        $exists_count = mysql_num_rows ( $table_exists_query );

        if ($exists_count === 0) {
            mysql_query ( 'CREATE TABLE `' . $this->_db_name . '_contact` (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `address_line_one` VARCHAR(255),
            `address_line_two` VARCHAR(255),
            `town` VARCHAR(255),
            `county` VARCHAR(255),
            `postcode` VARCHAR(255),
            `email` VARCHAR(255),
            `enquiry_emails` VARCHAR(255),
            `create_date` TIMESTAMP )' );

            mysql_query ( 'INSERT INTO `' . $this->_db_name . '_contact` ( `town` ) VALUES ( "town" )' );
        }

        $model = '<?php

class contact_model extends active_record
{

}

?>';
        mkdir ( '../app/models/contact/' );
        file_put_contents ( '../app/models/contact/contact_model.php', $model );

        display ( "Contact model created.\n" );
    }

    /**
     * Method to create the necessary views, there could be a total of three
     * - Form for the contact information
     * - Listing page
     * - Normal page
     *
     * @access public
     */
    public function view ()
    {
        //Regardless the contact controller will need the view to edit the contact details
        $contact_details = '
<article class="article">
    <h1 class="heading gradbar">Contact Information <a href="<?php echo DIRECTORY; ?>contact" class="btn btn-back" />Back</a></h1>
    <form method="post" action="" id="form" enctype="multipart/form-data">

    <div class="module form cf" >
           <input type="hidden" name="id" id="id" value="1" />

        <?php echo $success; ?>

        <label class="label" for="address_line_one">Address Line One</label>
        <input type="input" id="address_line_one" name="contact[address_line_one]" value="<?php echo $address_line_one ?>" class="input"  />
        <label class="label" for="address_line_two">Address Line Two</label>
        <input type="input" id="address_line_two" name="contact[address_line_two]" value="<?php echo $address_line_two ?>" class="input"  />
        <label class="label" for="town">Town</label>
        <input type="input" id="town" name="contact[town]" value="<?php echo $town; ?>" class="input"  />
        <label class="label" for="county">County</label>
        <input type="input" id="county" name="contact[county]" value="<?php echo $county; ?>" class="input"  />
        <label class="label" for="postcode">Postcode</label>
        <input type="input" id="postcode" name="contact[postcode]" value="<?php echo $postcode; ?>" class="input"  />
        <label class="label" for="email">Email</label>
        <input type="input" id="email" name="contact[email]" value="<?php echo $email; ?>" class="input"  />

        <label class="label" for="enquiry_emails">Enquiry Emails</label>
        <textarea name="contact[enquiry_emails]" id="enquiry_emails" rows="10" class="input"><?php echo $enquiry_emails; ?></textarea>

    </div>

    <input type="submit" name="save" class="btn" value="Save" />
    </form>

</article>';

        mkdir ( '../app/views/templates/contact/' );
        file_put_contents ( '../app/views/templates/contact/edit.php', $contact_details );

        if ($this->_both == 'y') {
            $main = '
            <li>
                <p><a href="<?php echo DIRECTORY; ?>contact/main">Main Contact</a><small>Click to edit the main content</small></p>
                <ol class="hoz btns edit-opts">
                    <li><a href="<?php echo DIRECTORY; ?>contact/main" class="btn"><span class="spr i-penc">Edit</span></a></li></li>
                </ol>
            </li>';
        }

        $contact_index = '
<article class="article">
    <h1 class="heading gradbar">Contact Information <a href="<?php echo DIRECTORY; ?>Dashboard" class="btn btn-back" />Back</a></h1>
    <div class="module form cf" >
        <ol class="listing js-manage-elems">
                ' . $main . '
                <li>
                <p><a href="<?php echo DIRECTORY; ?>contact/edit">Contact Information</a><small>Click to edit the address, email and enquiry emails</small></p>
                <ol class="hoz btns edit-opts">
                    <li><a href="<?php echo DIRECTORY; ?>contact/edit" class="btn"><span class="spr i-penc">Edit</span></a></li></li>
                </ol>
            </li>
        </ol>
    </div>
</article>';

        file_put_contents ( '../app/views/templates/contact/index.php', $contact_index );

        if ($this->_both == 'y') {
            //Add a row in the pages database for the contact page
            mysql_query ( 'INSERT INTO `' . $this->_db_name . '_page` ( `' . $this->_db_name . '_page_name` ) VALUES ( "contact" )' );

            if ($this->_image == 'y') {
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

        $contact_main = '
<article class="article">
    <h1 class="heading gradbar">Contact <a href="<?php echo DIRECTORY; ?>contact" class="btn btn-back" />Back</a></h1>
    <form method="post" action="" id="form" enctype="multipart/form-data">
        <input type="hidden" name="page[id]" id="hidden_id" value="<?php echo $id; ?>" />
        <input type="hidden" name="page[name]" id="hidden_id" value="contact" />
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

            file_put_contents ( '../app/views/templates/contact/main.php', $contact_main );

        }

    }

}

$contact = new Contact ();
$contact->check_exists ();
$contact->controller ();
$contact->model ();
$contact->view ();

display ( "End.\n\n" );
