<?php

Class ContactController {

    protected $i;

    public $errors;
    public $clean_errors;

    public $not_see_errors = array('telephone');

    public function __Construct( WebGuy $I )
    {
        $this->i = $I;
    }

    public function testSaveWithoutValues()
    {
        $this->i->click('enquire');

        $this->checkErrors();
    }

    private function resetNotErrors()
    {
        $this->not_see_errors = array( 'telephone' );

        return $this;
    }

    public function testWithoutField($field)
    {
        $this->clean_errors = $this->errors;

        $this->i->fillField( 'contact['.$field.']', 'Test '.$field );

        $this->i->click('enquire');

        $this->not_see_errors[] = $field;
        unset($this->errors[$field]);

        $this->checkErrors();

        $this->resetNotErrors();

        $this->errors = $this->clean_errors;
    }

    public function testWithAllValues()
    {
        $fields = array( 'name' => 'Test name' ,
                         'telephone' => "Banana",
                         'email' => 'test@test.com',
                         'enquiry' => 'shhshsh' );

        foreach ($fields as $field => $value) {
            $this->i->fillField( 'contact['.$field.']', $value );
        }

        $this->i->click('enquire');

        $this->not_see_errors[] = array( 'name', 'email', 'enquiry');

        $this->checkErrors();

        $this->i->see( 'Thank you for your enquiry' );

    }

    protected function checkErrors()
    {
        foreach ($this->errors as $error => $message) {
            if (!!$message) {
                if( !in_array($error, $this->not_see_errors) )
                    $this->i->see( $message );
                else
                    $this->i->DontSee($message);
            }
        }
    }

}
