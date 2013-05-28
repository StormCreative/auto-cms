<?php

class login extends c_controller
{
    public function index ()
    {
        $this->setView('login/index');
        $this->addStyle ( 'login' );
        $this->setScript ( 'login' );
    }
}
