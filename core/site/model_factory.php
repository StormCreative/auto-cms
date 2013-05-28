<?php

class Model_factory
{
    public static function load( $model )
    {
        $model = $model.'_model';

        if( class_exists($model) )

            return new $model();
        else
            return false;
    }

}
