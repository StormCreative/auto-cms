<?php

class home extends application_controller
{
    public function index ()
    {
        $this->addStyle ( 'login' );
        $this->setScript ( 'login' );

        $this->addTag ( 'dont_show_menu', TRUE );
        $this->addTag ( 'dont_show_header', TRUE );
        $this->setView ( 'home/index' );

    }

    public function device_test ()
    {

        $this->setScript( 'image-handler' );

        $this->setScript ( 'login' );
        $this->setView ( 'home/index' );

    }
}
