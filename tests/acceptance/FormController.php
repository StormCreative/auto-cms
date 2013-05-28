<?php

Class FormController {

    protected $i;

    public $submit;

    public $form;

    public $errors;
    public $clean_errors;

    public $not_see_errors = array();

    public function __Construct( WebGuy $I )
    {
        $this->i = $I;
    }

    public function testSaveWithoutValues()
    {
        $this->i->click($this->submit);

        $this->checkErrors();

        $this->errors = $this->clean_errors;
    }

    public function testSaveWithValues( $fields )
    {
        foreach ($fields as $key => $value) {
            if( !!$key )
                $this->i->fillField( $this->form.'['.$key.']', $value );
        }

        $this->i->click($this->submit);

        $this->checkErrors();

        $this->errors = $this->clean_errors;
    }

    public function checkSuccess( $message )
    {
        $this->i->see($message);
    }

    public function arrange_errors( $fields )
    {
        if(!is_array( $fields ))
            $fields[] = $fields;

        $this->clean_errors = $this->errors;

        foreach ($fields as $field) {
            unset($this->errors[$field]);
        }
    }

    protected function checkErrors()
    {
        foreach ($this->errors as $error => $message) {
            if (!!$message) {
                if ( in_array($error, $this->not_see_errors) ) {
                    $this->i->DontSee( $message );
                } else
                    $this->i->see( $message );
            }
        }
    }

}
