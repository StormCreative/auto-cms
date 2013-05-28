<?php

abstract class Application_controller extends C_Controller
{
    protected $images = NULL;
    protected $sessions;
    protected $forms;
    protected $type;
    protected $c_con;
    protected $_uploads;

    public function __Construct()
    {
        parent::__Construct();

        $this->path = '_admin';
        $this->stylesheet_path = '_admin';

        $this->sessions = new sessions ();
        $this->forms = new forms ();
        $this->type = 'page';
    }
}
