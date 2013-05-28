<?php

class menu
{
    public static function get_dynamic()
    {
        $generic_pages_model = new Generic_pages_model();
        $generic = $generic_pages_model->all();

        if( count($generic) > 0 )

            return $generic;
        else
            return false;
    }

}
