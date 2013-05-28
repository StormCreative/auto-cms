<?php
/***
/
/ Trait for various helpers for forms
/
/
***/
class forms
{
    public $table;

    public function getSuccess ()
    {
        $msg = 'Congratulations! The item has been saved.';

        return $msg;
    }

    public function getDelete ( $num )
    {
        return (string) $num . ' item(s) have been deleted.';
    }

    /**
     * Due to changes in the active record class it will be a array that is
     *
     */
    public function setFormValues ( $object )
    {
        $tags = array ();

        foreach ( get_object_vars( $object ) as $key => $field ) {
            $tags[$key] = $field;
        }

        return $tags;
    }

    public function setActionTable ( $table )
    {
        $this->table = $table;

        return $this;
    }

    public function actionInsert ()
    {
        if (!!$_POST[ 'save' ]) {
            $this->save ();

            header ( 'location: ' . DIRECTORY . $this->table.'/edit/'.$this->{$this->table}->id.'/?inserted=1' );
        }
    }

    public function getSuccessMessage ()
    {
        // Display the success if there is a GET paramter ( which comes from Add once a save is made )
        // Or if a POST is set
        if ( !!$_GET[ 'inserted' ] || !!$_POST )
            $success = $this->getSuccess ();

        return $success;
    }

}
