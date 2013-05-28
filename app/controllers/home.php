<?php

class home extends c_controller
{
    public function index ()
    {
        $this->addTag ('title', 'Home');
        $this->addTag ( 'meta_keywords', 'Pegisis');
        $this->addTag ( 'meta_desc', 'Pegisis');

        $this->setView('home/index');
    }
}
