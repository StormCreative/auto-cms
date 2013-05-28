<?php

class AJAX_delete
{
    private $_image;
    private $_upload;

    public function __Construct ()
    {
        $this->_image = new image_model();
        $this->_upload = new uploads_model();
    }

    public function normal_delete ()
    {
        if (!!$_POST) {
            if ( $this->_image->delete_by_column ( 'imgname', $_POST[ 'imagename' ] ) )
                $return = array( 'status' => 200 );

            else
               $return = array( 'status' => 400, 'msg' => 'The image could not be deleted at this time' );
        }

        die( json_encode( $return ) );
    }

    public function normal_upload ()
    {
        if (!!$_POST) {
            if ( $this->_upload->delete ( $_POST[ 'id' ] ) ) {
                if ( unlink ( PATH . '_admin/assets/uploads/documents/' . $_POST[ 'name' ] ) )
                    $return = array( 'status' => 200 );
                else
                    $return = array( 'status' => 400 );
            }
        }

        die( json_encode( $return ) );
    }
}
