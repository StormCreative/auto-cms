<?php

$I = new WebGuy($scenario);
$I->wantTo('Test the about edit page');
$I->amOnPage('admin/about/edit');

/** First check form cannot be sent if the user doesnt input any values **/
$I->amGoingTo('Submit user form with invalid values');
$I->click('Save');

$I->see("Name can not be empty");
            $I->see("Email can not be empty");
            $I->see("Phone can not be empty");
            $I->see("Postcode can not be empty");
            

$I->amGoingTo("Submit about form without a name");
                                        $I->click("Save");
                                        $I->fillField ( "about[email]", "Acceptance Test" );
                        $I->fillField ( "about[phone]", "Acceptance Test" );
                        $I->fillField ( "about[postcode]", "Acceptance Test" );
                        $I->amGoingTo("Submit about form without a email");
                                        $I->click("Save");
                                        $I->fillField ( "about[name]", "Acceptance Test" );
                        $I->fillField ( "about[phone]", "Acceptance Test" );
                        $I->fillField ( "about[postcode]", "Acceptance Test" );
                        $I->amGoingTo("Submit about form without a phone");
                                        $I->click("Save");
                                        $I->fillField ( "about[name]", "Acceptance Test" );
                        $I->fillField ( "about[email]", "Acceptance Test" );
                        $I->fillField ( "about[postcode]", "Acceptance Test" );
                        $I->amGoingTo("Submit about form without a postcode");
                                        $I->click("Save");
                                        $I->fillField ( "about[name]", "Acceptance Test" );
                        $I->fillField ( "about[email]", "Acceptance Test" );
                        $I->fillField ( "about[phone]", "Acceptance Test" );
                        

?>