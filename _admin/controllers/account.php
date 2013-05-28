<?php

class account extends application_controller
{
    private $_access;

    public function __Construct ()
    {
        parent::__Construct();

        $this->_access = new access_model();
    }

    public function index ()
    {
        if (!!$_POST[ 'access' ]) {
            //Get the user information, mainly we need their ID ( that will be in the session ) and their email address
            $this->_access->find ( $_SESSION[ 'user' ][ 'id' ] );

            $success = FALSE;

            if (!!$_POST[ 'access' ][ 'current_password' ] && !!$_POST[ 'access' ][ 'new_password' ] && !!$_POST[ 'access' ][ 'confirm_password' ]) {
                if ( $_POST[ 'access' ][ 'new_password' ] != $_POST[ 'access' ][ 'confirm_password' ] )
                    $feedback = '<p class="deleted_message">Your new and confirm password do not match</p>';

                elseif ( $this->_access->_data[ 'password' ] != sha1 ( $_POST[ 'access' ][ 'current_password' ] ) )
                    $feedback = '<p class="deleted_message">Your current password is incorrect</p>';
                else {
                    //Hash and save the new password with a email reminding the user of their password
                    if ( $this->_access->save ( array ( 'id' => $_SESSION[ 'user' ][ 'id' ], 'password' => sha1( $_POST[ 'access' ][ 'new_password' ] ) ) ) ) {
                        $email = new mail ( $this->_access->_data[ 'email' ], 'password-change', 'Password Change', array ( 'password' => $_POST[ 'access' ][ 'new_password' ] ) );
                        $feedback = '<p class="success_message">Your password has been changed successfully</p>';
                        $success = TRUE;
                    }
                }
            } else
                $feedback = 'Fill out all fields';

            $this->addTag ( 'success', $success );
            $this->addTag ( 'feedback', $feedback );
        }

        $this->addStyle ( 'listing' );
        $this->addStyle ( 'edit' );
        $this->setView ( 'account/index' );
    }

    public function logout ()
    {
        session_destroy ();
        header ( 'Location: ' . DIRECTORY . 'admin' );
    }
}
