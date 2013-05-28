<?php
/***
/
/ Interface Gensis:
/ An interface for defining controllers that
/ are creating/editing more than one item.
/ This will be for defining lists, and many items.
/ Not one for updating just *one* item.
/
**/

interface genesis
{
    public function add ();

    public function edit ( $id = "" );

}
