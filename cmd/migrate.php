<?php

include 'cmd/base_migrate.php';

class migrate extends base_migrate
{
    private $_queries = array ();

    public function __Construct ()
    {
        parent::__Construct ();
        $this->get ();
        //$this->dump_data ();
        $this->run ();
        //$this->put_data ();
    }
}

$migrate = new migrate ();
