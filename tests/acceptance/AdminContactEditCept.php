<?php

$I = new WebGuy($scenario);
$I->wantTo('Test the contact edit page');
$I->amOnPage('admin/contact/edit');

/** First check form cannot be sent if the user doesnt input any values **/
$I->amGoingTo('Submit user form with invalid values');
$I->click('Save');

$I->see("Name can not be empty");
            $I->see("Email can not be empty");
            $I->see("Phone can not be empty");
            $I->see("Postcode can not be empty");
            

$I->amGoingTo("Submit contact form without a name");
                                        $I->click("Save");
                                        $I->fillField ( "contact[email]", "Acceptance Test" );
                        $I->fillField ( "contact[phone]", "Acceptance Test" );
                        $I->fillField ( "contact[postcode]", "Acceptance Test" );
                        $I->amGoingTo("Submit contact form without a email");
                                        $I->click("Save");
                                        $I->fillField ( "contact[name]", "Acceptance Test" );
                        $I->fillField ( "contact[phone]", "Acceptance Test" );
                        $I->fillField ( "contact[postcode]", "Acceptance Test" );
                        $I->amGoingTo("Submit contact form without a phone");
                                        $I->click("Save");
                                        $I->fillField ( "contact[name]", "Acceptance Test" );
                        $I->fillField ( "contact[email]", "Acceptance Test" );
                        $I->fillField ( "contact[postcode]", "Acceptance Test" );
                        $I->amGoingTo("Submit contact form without a postcode");
                                        $I->click("Save");
                                        $I->fillField ( "contact[name]", "Acceptance Test" );
                        $I->fillField ( "contact[email]", "Acceptance Test" );
                        $I->fillField ( "contact[phone]", "Acceptance Test" );
                        

?>