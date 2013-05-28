<?php
function friendly_url ( $string )
{
    $string = preg_replace( "`\[.*\]`U", "", $string );
    $string = preg_replace( '`&(amp;)?#?[a-z0-9]+;`i', '-', $string );
    $string = htmlentities( $string, ENT_COMPAT, 'utf-8' );
    $string = preg_replace( "`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i", "\\1", $string );
    $string = preg_replace( array( "`[^a-z0-9]`i","`[-]+`") , "-", $string );

    return strtolower( trim( $string, '-' ) );
}

function download ( $filename, $title = "", $location = 'admin/assets/uploads/documents'  )
{
    if ( !$filename )
        die ( 'must provide a file to download!' );
    else {
        $path =  PATH . $location . '/' . $filename;

        $ext = pathinfo ( $filename, PATHINFO_EXTENSION );

        if ( file_exists( $path ) ) {

            $size = filesize( $path );
            header( 'Content-Type: application/octet-stream' );
            //header( 'Content-Length: ' . $size );
            header( 'Content-Disposition: attachment; filename=' . $title . '.' . $ext );
            header( 'Content-Transfer-Encoding: binary' );

            $file = fopen( $path, 'rb' );

            if ($file) {
                fpassthru( $file );
                exit;
            } else {
                echo $err;
            }
        } else
            die ( 'Appears to be a problem with downloading that file.' );
    }
}

/**
 * This function is used by the work zone section to get the document type from the document name in a multidimensional array,
 * then add it to the multidimensional array and return the array
 *
 * @param array ( multi-dimensional ) $array
 * @param string $file_key
 *
 * @return array ( multi-dimensional )
 */
function get_file_type ( $array, $file_key )
{

    $i = 0;
    foreach ($array as $value) {
        $sections = explode ( '.', $value[ $file_key ] );

        switch ($sections[ 1 ]) {
            case ( 'doc' ) :
                $array[ $i ][ 'div' ] = 'word';
            break;

            case ( 'docx' ) :
                $array[ $i ][ 'div' ] = 'word';
            break;

            case ( 'ppt' ) :
                $array[ $i ][ 'div' ] = 'powerpoint';
            break;

            case ( 'pptx' ) :
                $array[ $i ][ 'div' ] = 'powerpoint';
            break;

            case ( 'xlsx' ) :
                $array[ $i ][ 'div' ] = 'excel';
            break;

            case ( 'xls' ) :
                $array[ $i ][ 'div' ] = 'excel';
            break;

            case ( 'pdf' ) :
                $array[ $i ][ 'div' ] = 'pdf';
            break;
        }

        $i++;
    }

    return $array;
}

function word_limiter ( $str, $limit = 100, $end_char = '&#8230;' )
{
    if ( trim( $str ) == '' )
        return $str;

    preg_match( '/^\s*+(?:\S++\s*+){1,' . (int) $limit .'}/', $str, $matches );

    if ( strlen( $str ) == strlen( $matches[0] ) )
        $end_char = '';

    //return rtrim ( $matches[0] ) . $end_char;
    return strip_tags( rtrim ( $matches[0] ) . $end_char );
}

/**
 * Creates a random string
 *
 * @param int $Length ( default 20 )
 *
 * @return string $string
 */
function random_string ( $length = 20 )
{
    $chars = "abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    srand ( (double) microtime () * 1000000 );
    $i = 0;
    $string = '' ;
    while ($i <= $length) {
        $num = rand () % 33;
        $tmp = substr ( $chars, $num, 1 );
        $string = $string . $tmp;
        $i++;
    }

    return $string;
}

/**
 * This function is used by the CMS to organise the success / error message
 *
 * @param string / array $feedback
 * @param bool $error ( Default FALSE )
 *
 * @return string $output
 */
function organise_feedback( $feedback, $error = FALSE )
{
    if ($error == TRUE) {
        $output = '<div class="error_message">';
        $output .= '<p>Errors occurred when attempting to save: </p>';
        $output .= '<ul>';

        foreach ($feedback as $item) {
            $output .= '<li>' . ucfirst ( str_replace( '_', ' ', $item[ 'message' ] ) ) . '</li>';
        }

        $output .= '</ul></div>';

        return $output;
    } else {
        return '<p class="success_message">' . $feedback . '</p>';
    }
}

/**
 * This function is to form the side menu
 *
 * I can form this getting all the database tables and removing the ones we done need
 * These will be images / uploads / access / migrations
 */
function get_menu ()
{
    $controllers = scandir ( PATH . '_admin/controllers/' );

    $db = new query();
    $tables = $db->getAssoc( 'SHOW TABLES' );

    //This gives us a multidimensional array so we need to extract the stuff we want
    $tables_filtered = array ();
    $not_allowed = array ( 'image', 'uploads', 'access', 'migrations' );

    foreach ($tables as $table) {
        $t = str_replace ( DB_SUFFIX . '_', "", $table[ 0 ] );

        if ( !in_array ( $t, $not_allowed ) )
            $tables_filtered[] = $t;
    }

    $output = '';

    //Process some output
    foreach ($tables_filtered as $ta) {
        $output .= '<li><a href="' . DIRECTORY . 'admin/listing/table/' . $ta . '">' . ucfirst( str_replace('_', ' ', $ta ) ) . '</a></li>';
    }

    return $output;
}

function starts_with( $value, $sep = "_" )
{
    $value = explode( $sep, $value );

    return $value[0];
}

function tidy_price($price)
{
    return number_format($price, 2, '.', '');
}

/**
 * Function to form the dropdowns on the products page
 *
 * @param array $items
 * @param string $selected
 *
 * @return string $options
 */
function form_dropdown ( $items, $selected = "" )
{
    $options = array();
    $options[] = '<option></option>';

    foreach ($items as $item) {
        if ( $item[ 'title' ] == $selected )
            $selected_attr = 'selected="selected"';
        else if ( $item[ 'title' ] != $selected )
            $selected_attr = NULL;

        $options[] = '<option value="' . $item[ 'title' ] . '" ' . $selected_attr . '>' . $item[ 'title' ] . '</option>';
    }

    return implode ( "", $options );
}

function in_array_r($needle, $haystack, $strict = false)
{
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}

function get_gallery ( $data )
{
    $image_model = new image_model();

    if ( is_array ( $data ) ) {
        $i = 0;
        foreach ($data as $item) {
            $data[ $i ][ 'images' ] = Image_model::many( $item[ '_gallery' ] );
            $i++;
        }
    } elseif ( is_object ( $data ) ) {
        $gallery = array();

        foreach ( explode ( ',', $data->attributes[ 'gallery' ] ) as $id ) {
            $item = $image_model->find( $id );
            $gallery[] = $item->attributes[ 'imgname' ];
        }

        $data->attributes[ 'gallery' ] = $gallery;
    }

    return $data;
}

function class_active( $page = '' )
{
    // Grab the current URI and remove the directory ( if working locally )
    $current_page = get_page();

    if ( is_array($page) && in_array ( $current_page, $page ) ) {
        return 'class="active"';
    } elseif ( $page == $current_page )

        return 'class="active"';
    else
        return '';
}

function get_page()
{
    // Set the current page
    $page = str_replace ( DIRECTORY, "", $_SERVER['REQUEST_URI'] );

    return ( !!$page ? $page : 'home' );
}
