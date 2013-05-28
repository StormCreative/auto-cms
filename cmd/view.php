<?php

class view
{
    private $_fields = array ();
    private $_view;
    private $_view_name;

    public function __Construct ( $view_name )
    {
        $this->_view_name = $view_name;

        $this->check_functions ();
        $this->check_database ();
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
     * Method to check if the view already exists
     * If it does we want to end the script because strict naming conventions wont accept a different name
     *
     * @access public
     */
    public function check_exists ()
    {
        if ( file_exists ( '../app/views/templates/' . $this->_view_name . '/' . $this->_view_name . '.php' ) ) {
            display ( 'The view "' . $this->_view_name . '" already exists. The script will now end.' . "\n\n" );
            die (0);
        }
    }

    /**
     * Method to check if the view directory exists, if not it is created
     *
     * @access public
     */
    public function create_dir ()
    {
        if ( !file_exists ( '../app/views/templates/' . $this->_view_name . '/' ) )
            mkdir ( '../app/views/templates/' . $this->_view_name . '/' );
    }

    /**
     * Method to create and save the list view
     *
     * @access public
     */
    public function create_list_view ()
    {
        //Set up the view for the listing section
        $listing_view = '
<article class="article">
    <a href="<?php echo DIRECTORY .$table; ?>/edit" class="fbk add">
       <p>Add new <?php echo $page_title; ?> to this list.</p>
    </a>
    <h1 class="heading gradbar"><?php echo $page_title; ?> - Listing</h1>

    <form class="module cf js-form" name="delete-listing" method="post" action="" data-module="module">
    <?php echo $alert; ?>
        <ol class="listing js-manage-elems">
        <?php foreach ( $data as $row ): ?>
            <li>
                <p><a href="<?php echo DIRECTORY; ?><?php echo $table; ?>/edit/<?php echo $row[\'id\']; ?>"><?php echo $row[\'name\']; ?></a><small>Last edited: <?php echo $row[\'create_date\']; ?></small></p>
                <ol class="hoz btns edit-opts">
                    <li><a href="<?php echo DIRECTORY; ?><?php echo $table; ?>/edit/<?php echo $row[\'id\']; ?>" class="btn f-btn"><span class="spr i-penc">Edit</span></a></li>
                    <li><input type="checkbox" name="delete[<?php echo $row[\'id\']; ?>]" value="<?php echo $row[\'id\']; ?>" id="delete_<?php echo $row[\'id\']; ?>" data-type="remove" /><label for="delete_<?php echo $row[\'id\']; ?>" class="btn e-btn"><span class="spr i-bin">Delete</span></label></li>
                </ol>
            </li>
        <?php endforeach; ?>
        </ol>
        <input type="submit" class="btn btn-dis js-manage-btn" value="Delete" name="delete-btn" disabled="disabled" data-modal="true" />
    </form>
</article>';

        //Put the listing view into the template folder under the controller name
        file_put_contents ( '../app/views/templates/' . $this->_view_name . '/list.php', $listing_view );
    }

    /**
     * Method to let the user enter as many fields as they want
     * Populates the fields property as a multidimentional array
     *
     * @access public
     */
    public function get_fields ()
    {
        do {
            display ( "Add a field or enter 'n' to continue... ( name:type:mysql type:max length )\n" );
            $field = get_input ();

            if ($field != 'n') {
                $parts = explode ( ":", $field );

                $this->_fields[] = array ( 'name' => $parts[0],
                                           'type' => $parts[1],
                                           'mysql_type' => $parts[2],
                                           'max_length' => $parts[3] );
            }

        } while ( $field != 'n' );
    }

    /**
     * Method to organise the fields property into html to be used in the view
     *
     * @access private
     */
    private function format_fields ()
    {
        foreach ($this->_fields as $field) {
            switch ($field['type']) {
                /**
                 * Text
                 */
                case ( 'text' ) :

                    $view .= '
        <label class="label" for="' . $field['name']  . '">' . ucfirst ( $field['name'] ) . '</label>
        <input type="input" name="' . $this->_view_name . '[' . $field['name'] . ']' . '" value="<?php echo $' . $field['name'] . '; ?>" class="input" />';

                break;

                /**
                 * Image
                 */
                case ( 'image' ) :

                    $view .= '
        <input type="hidden" id="image_id" name="' . $this->_view_name . '[image_id]" value="<?php echo $image; ?>" />
        <input type="hidden" name="image" id="imgname" value="<?php echo $imgname; ?>" />

        <label class="label" for="img">Image</label>

        <div id="image-list" class="img-list">
        <?php if ( count ( $images ) > 0 ): ?>
            <?php foreach ( $images as $i ) : ?>
                <div id="img_<?php echo $i[\'rel_imgname\']; ?>">
                    <span class="img" ><img src="<?php echo DIRECTORY; ?>Assets/Uploads/Images/<?php echo $i[\'rel_imgname\']; ?>" /></span>
                    <ol class="hoz btns">
                        <li><input type="button" id="<?php echo $i[\'rel_imgname\']; ?>" image-id="<?php echo $i[\'id\']; ?>" class="btn del-image" value="Delete Image" /></li>
                    </ol>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        </div>

       <ol class="hoz btn-upload" id="multi-upload">
                <li><input type="file" name="image[]" id="upload" accept="image/*" /></li>
                <li><input type="button" class="btn" <?php echo !!$imgname ? \'disabled="disabled"\' : \'\'; ?> id="upload-link" value="Upload Image" /></li>
            </ol>';

                break;

                /**
                 * Image-multi
                 */
                case ( 'image-multi' ) :

                    $view .= '
        <input type="hidden" id="image_id" name="' . $this->_view_name . '[image_id]" value="<?php echo $image; ?>" />
        <input type="hidden" name="image" id="imgname" value="<?php echo $imgname; ?>" />

        <label class="label" for="img">Image</label>

        <div id="image-list" class="img-list">
        <?php if ( count ( $images ) > 0 ): ?>
            <?php foreach ( $images as $i ) : ?>
                <div id="img_<?php echo $i[\'rel_imgname\']; ?>">
                    <span class="img" ><img src="<?php echo DIRECTORY; ?>Assets/Uploads/Images/<?php echo $i[\'rel_imgname\']; ?>" /></span>
                    <ol class="hoz btns">
                        <li><input type="button" id="<?php echo $i[\'rel_imgname\']; ?>" image-id="<?php echo $i[\'image_id\'] ?>" image-rel-id="<?php echo $i[\'id\']; ?>" class="btn del-image" value="Delete Image" /></li>
                    </ol>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        </div>

        <ol class="hoz btn-upload" id="multi-upload">
            <li><input type="file" name="image[]" id="upload" multiple="true" accept="image/*" /></li>
            <li><input type="button" class="btn" id="upload-link" value="Upload Image" /></li>
        </ol>';

                break;

                /**
                 * Textarea
                 */
                case ( 'textarea' ) :

                    $view .= '
        <label class="label" for="wysiwyg">' . $field['name'] . '</label>
        <textarea name="' . $this->_view_name . '[' .$field['name'] . ']' . '" class="wysiwyg" id="wysiwyg"><?php echo $' . $field['name'] . '; ?></textarea>';

                break;

                /**
                 * Date
                 */
                case ( 'date' ) :

                    $view .= '';

                break;
            }
        }

        return $view;
    }

    /**
     * Method to create and save the view
     *
     * @access public
     */
    public function create_view ()
    {
        //Beginning of view
        $this->_view = '
<article class="article">
    <a href="<?php echo DIRECTORY; ?>' . $this->_view_name . '/edit" class="fbk add">
       <p>Add a new ' . $this->_view_name . ' article.</p>
    </a>
    <h1 class="heading gradbar">' . ucfirst ( $this->_view_name ) . ' <a href="<?php echo DIRECTORY; ?>' . $this->_view_name . '" class="btn btn-back" />Back</a></h1>
    <form method="post" action="" id="form" enctype="multipart/form-data">
        <input type="hidden" name="' . $this->_view_name . '[id]" id="hidden_id" value="<?php echo $id; ?>" />
        <div class="module form cf" >

        <?php echo $success; ?>';

        //Get the fields html
        $this->_view .= $this->format_fields ();

        //End of the view
        $this->_view .= '
   </div>
    <input type="submit" name="save" class="btn" value="Save" />
    </form>

    <script>

        var admin_images = [{ width: 484, uploader: \'#upload\', method: \'imageUploadMulti\' }];

    </script>

</article>';

    }

    /**
     * Method to save the view
     *
     * @access public
     */
    public function save_view ()
    {
        if ( file_put_contents ( '../app/views/templates/' . $this->_view_name . '/add.php', $this->_view ) )
            display ( "The view was saved successfully.\n" );
        else
            display ( "Something went wrong, Try again.\n" );
    }

    /**
     * Method to ask the user if they want to proceed and pass the fields property to the db script
     *
     * @access public
     */
    public function proceed ()
    {
        display ( "Do you want to set up the database for this controller? ( y / n ) \n" );
        if ( get_input () == 'y' ) {
            $table = $this->_view_name;
            $fields = $this->_fields;
            include 'db.php';
        } else {
            display ( "End!" );
            die (0);
        }
    }

}

$view = new View ( $view_name );
$view->check_exists ();
$view->create_dir ();
$view->create_list_view ();
$view->get_fields ();
$view->create_view ();
$view->save_view ();
$view->proceed ();
