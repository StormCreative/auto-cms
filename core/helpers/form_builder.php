<?php

function text_field ( $for, $name, $class = "" )
{
    $class = !!$class ? $class : "";
    $field_name = $for.'['.$name.']';
    $value = !!$_POST[ $name ] ? $_POST[ $name ] : "";

    return '<label for="'.$name.'">'.ucwords($name).': </label><input type="text" name="'. $field_name .'" '.$class.' value="'.$value.'">';
}

function post_set ()
{
    if ( !!$_POST )
        return TRUE;
    else
        return FALSE;
}
