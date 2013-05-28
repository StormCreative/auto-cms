<?php

class dashboard extends application_controller
{
    public function index ()
    {
        $this->setView ( 'dashboard/index' );
        $this->addStyle ( 'dashboard' );
    }
}
