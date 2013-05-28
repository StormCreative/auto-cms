<?php

class validation
{
    public $pass = TRUE;

    public $errors = array ();

    public function duplicate( $table, $column, $value, $msg = "" )
    {
        $db = new query ();

        $table = DB_SUFFIX . '_' . $table;

        $data = $db->get ( 'SELECT ' . $column . ' FROM  ' . $table . ' WHERE ' . $column . ' = :'.$column.'', array ( $column => $value ) );

        if ( count ( $data ) > 0 ) {
            $this->pass = TRUE;
        } else {
            $this->errors[] = array ( 'function' => 'duplicate', 'message' => (!!$msg?$msg:$column.' has already been taken') );
            $this->pass = FALSE;
        }

        return $this;
    }

    public function not_empty( $field, $value = "", $msg = "" )
    {
        if ($value == "") {
            $this->errors[] = array ( 'function' => 'not_empty', 'field' => $field, 'message' => (!!$msg?$msg:$field.' can not be empty') );
            $this->pass = FALSE;
        }
    }

    public function valid_email ( $email, $msg = "" )
    {
        if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) == FALSE ) {
            $this->pass = FALSE;
            $this->errors[] = array( 'function' => 'valid_email', 'message' => (!!$msg?$msg:'Email address is invalid format') );
        }

        return $this;
    }

    public function check_match( $string, $string2, $msg )
    {
        if (!!$string && !!$string2) {
            if ($string != $string2) {
                $this->pass = FALSE;
                $this->errors[] = array ( 'function' => 'check_match', 'message' => $msg );
            } else {
                $this->pass = TRUE;
            }

            return $this;
        }
    }

}
