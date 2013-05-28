<?php

class error extends application_controller
{
    public function access ()
    {
        die ( 'You need to be logged in to access this page.' );
    }
}
