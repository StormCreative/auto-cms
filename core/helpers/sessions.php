<?php
/***
/
/ Trait for sessions around the Money Republic CMS
/
/
***/

class sessions
{
    public function checklogin ()
    {
        if ( !isset ( $_SESSION[ 'user-id' ] ) ) {
            session_destroy();
            header ( 'location: ' . DIRECTORY . '' );
        }
    }

    public function setSession ( $id )
    {
        if (!!$id) {
            $_SESSION[ 'user-id' ] = $id;
        }
    }
}
