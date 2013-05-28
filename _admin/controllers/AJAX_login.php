<?php

class AJAX_login
{
    public function index ()
    {
        if (!!$_POST) {
            $access = new access_model();
            $data = $access->where( "email = :email" )->where( "password = :password" )->all( array ( "email" => $_POST[ 'username' ], 'password' => sha1 ( $_POST[ 'password' ] ) ) );

            if (!!$data) {
                //Set up the users ID in the session
                $_SESSION[ 'user' ][ 'id' ] = $data[ 0 ][ 'id' ];

                $return = array( 'ok' );
            } else
                $return = array( 'not ok' );
        }

        die( json_encode( $return ) );
    }

    public function forgotten_password ()
    {
        if (!!$_POST) {
            //Check that the user acually exists before setting them a new password
            $access = new access_model();
            $data = $access->where( 'email = :email' )->all( array ( 'email' => $_POST[ 'email' ] ) );

            if (!!$data) {

                //Generate a random string
                $new_password = random_string( 10 );
                //Save the new password to that user
                if ( $access->save( array ( 'id' => $data[0][ 'id' ], 'password' => sha1( $new_password ) ) ) ) {
                    $email = new mail ( $_POST[ 'email' ], 'forgotten-password', 'Forgotten Password' , array ( 'password' => $new_password ) );

                    if ( $email )
                        $return = array( 'status' => 200, 'msg' => 'A new password has been emailed to you' );
                } else
                    $return = array( 'status' => 400, 'msg' => 'A new password could not be sent to you at this time' );

            } else
                $return = array( 'status' => 400, 'msg' => 'Email address does not belong to a valid user' );
        }

        die( json_encode( $return ) );
    }
}
