<?php
/**
/
/ Core Controller
/ This is the core application controller all controllers
/ should extend if they want to able to render a view and such.
/
/
***/

class C_Controller
{
    /**
     * Tags array - build up tags to pass over to view
     * @access private
     */
    private $_tags = array ();

    /**
     * Array of stylesheets to set and use in application
     * @access private
     */
    private $_styles = array ();

    /**
     * The view file to load - string
     * @access private
     */
    private $_view;

    /**
     * Render the overall header
     * @access protected
     */
    protected $use_header = TRUE;

    /**
     * Render the overall footer
     * @access protected
     */
    protected $use_footer = TRUE;

    /**
     *  Path whether in Admin or not
     */
    protected $path = 'app';

    protected $stylesheet_path = '';

    /**
     * This constructor holds ALOT of default values - these are accessed gloabbly throughout the site
     */
    public function __Construct ()
    {

        $this->_router = new Router();

        $this->_router->map();

        if ( substr( $this->_router->path, 0, 5 ) == 'admin' ) {
            //if ( !$_SESSION[ 'user' ][ 'id' ] && $this->_router->path != 'admin' )
                //header( "Location: " . DIRECTORY . "error/access" );

            $this->stylesheet_path = '_admin/';
        } else {
            //$this->addTag( 'dynamic_services', Menu::get_dynamic() );
        }

        if (!!$_SESSION['cart_id']) {

            $basket = new Basket_model();

            $basket->where( DB_SUFFIX.'_basket.cart_id = :cart_id' );

            $data = $basket->all( array( 'cart_id' => $_SESSION['cart_id'] ) );


            $total = $basket->calculate_total();

            $cart_size = $basket->number_of_items();


            // Set session for checkout
            $_SESSION['cart']['size'] = $cart_size;

            $this->addTag( 'cart_total', $total );
            $this->addTag( 'cart_size', $cart_size );
            $this->addTag( 'basket', $data );

        }


        $this->setScript ( 'main' );
        
    }

    /**
     * To add an array of tags
     * @param array $tags - the array of tags
     */
    protected function addMutliTags ( $tags )
    {
        foreach ($tags as $t => $t_v) {
            if ( $t_v == '' )
                $t_v = ' ';
            $this->_tags[ $t ] = $t_v;
        }

        return $this;
    }

    protected function plain()
    {
        $this->use_header = FALSE;
        $this->use_footer = FALSE;

        return $this;
    }

    /**
     * Add a tag to the template tag array
     *
     * @param string $tag   - the title of the tag
     * @param string $value - the value to the tag
     * @return $this;
     */
    protected function addTag ( $tag, $value = "" )
    {
        if ( is_array ( $tag ) && !isset ( $value ) ) {
            foreach ($tag as $t => $t_v) {
                if ( $t_v == '' )
                    $t_v = ' ';
                $this->_tags[ $t ] = $t_v;
            }
        } else
            $this->_tags[ $tag ] = $value;

        return $this;
    }

    /**
     * Merge any other set tags with the class tags
     *
     * @param array $tags - the array of tags to merge
     */
    protected function mergeTags ( $tags )
    {
        $this->_tags = array_merge ( $this->_tags, $tags );

        return $this;
    }

    /**
     * Set the script ( as we use AMD requireJS we only need to call one script )
     *
     * @param string $script - the title of that script
     * @return $this;
     */
    protected function setScript ( $script )
    {
        $this->_tags[ 'script' ] = $script;

        return $this;
    }

    /**
     * Add the a style to the style sheet array
     * ( as we use SASS it isn't a neccessity, however if we add in a random plugin its good to have this )
     *
     * @param string $stylesheet - the title of the stylesheet
     */
    protected function addStyle ( $stylesheet, $raw = TRUE )
    {
        $this->_tags[ 'stylesheets' ][] = ($raw == TRUE ? DIRECTORY.$this->stylesheet_path.'/assets/styles/'.$stylesheet.'.css' : $stylesheet);

        return $this;
    }


    /**
     * Set the view to display
     *
     * @param string $view - the view, if its in a sub folder include that in the string
     * @return $this;
     */
    protected function setView ( $view )
    {
        $this->_view = $view;

        return $this;
    }


    /**
     * @returns array $tags
     */
    protected function getTags ()
    {
        return $this->_tags;
    }


    /**
     * Render a 404 page
     * @param string reason - the reason for the 404
     */
    protected function render404 ( $reason = "" )
    {
        $tags['404_reason'] = $reason;

        extract( $tags, EXTR_PREFIX_SAME, "wddx" );

        if ( $this->use_header )
            include $this->path .'/views/overall-header.php';

        if ( file_exists ( PATH .'app/views/templates/' . $this->_view . '.'.$ext ) ) {
            include ( PATH .'app/views/templates/404.php' );
        }

        if ( $this->use_footer )
            include PATH .'app/views/overall-footer.php';
    }


    /**
     * Display the view
     *
     * @param bool $header - Set to FALSE if not using overall header
     * @param bool $footer - Set to FALSE if not using overall footer
     */
    protected function render( $ext = 'php' )
    {
        // If no view is set - we default to the controller name / method name in the templates/views
        if ( !isset ( $this->_view ) ) {
            $class = get_class ( $this );
            $route = $this->_router->get();

            $this->_view = strtolower ( $class.'/'.$route['method'] );
        }

        $this->path = ( substr ( $this->_router->path, 0, 5 ) == 'admin' ? '_admin' : 'app' );

        if ( isset( $this->_tags ) && is_array( $this->_tags ) && count( $this->_tags ) > 0 )
            extract( $this->_tags, EXTR_PREFIX_SAME, "wddx" );

        if ( $this->use_header )
            include PATH . $this->path .'/views/overall-header.php';

        // Check whether a tmpl file has been specified - if it has we render 'blade_runner'
        if ( file_exists( $this->path .'/views/templates/' . $this->_view . '.php.tmpl' ) ) {
            $tmpl = PATH .$this->path .'/views/_rendered/' . str_replace('/', '-', $this->_view ) . '.php';

            if ( !file_exists( $tmpl ) ) {
                fopen( $tmpl );
            }


            // Only create new file if cache time has expired
            if( file_exists( $tmpl ) && (time()-9999 > filemtime($tmpl)) || !LIVE )
                file_put_contents( $tmpl, Blade_runner::rewrite( file_get_contents($this->path .'/views/templates/' . $this->_view . '.php.tmpl') ) );

            fclose( $tmpl );

            include $tmpl;
        } elseif ( file_exists ( PATH . $this->path .'/views/templates/' . $this->_view . '.'.$ext ) ) {
            include ( PATH . $this->path .'/views/templates/' . $this->_view . '.'.$ext );
        }

        if ( $this->use_footer )
            include $this->path .'/views/overall-footer.php';
    }

    /**
     * Destructor - automatically render
     */
    public function __Destruct()
    {
        $this->render ();
    }
}
