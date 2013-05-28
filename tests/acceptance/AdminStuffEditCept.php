<?php

$I = new WebGuy($scenario);
$I->wantTo('Test the stuff edit page');
$I->amOnPage('admin/stuff/edit');

/** First check form cannot be sent if the user doesnt input any values **/
$I->amGoingTo('Submit user form with invalid values');
$I->click('Save');

$I->see("Title can not be empty");
            $I->see("Content can not be empty");
            $I->see("Type can not be empty");
            $I->see("Tag can not be empty");
            

$I->amGoingTo("Submit stuff form without a title");
                                        $I->click("Save");
                                        $I->fillField ( "stuff[content]", "Acceptance Test" );
                        $I->fillField ( "stuff[type]", "Acceptance Test" );
                        $I->fillField ( "stuff[tag]", "Acceptance Test" );
                        $I->amGoingTo("Submit stuff form without a content");
                                        $I->click("Save");
                                        $I->fillField ( "stuff[title]", "Acceptance Test" );
                        $I->fillField ( "stuff[type]", "Acceptance Test" );
                        $I->fillField ( "stuff[tag]", "Acceptance Test" );
                        $I->amGoingTo("Submit stuff form without a type");
                                        $I->click("Save");
                                        $I->fillField ( "stuff[title]", "Acceptance Test" );
                        $I->fillField ( "stuff[content]", "Acceptance Test" );
                        $I->fillField ( "stuff[tag]", "Acceptance Test" );
                        $I->amGoingTo("Submit stuff form without a tag");
                                        $I->click("Save");
                                        $I->fillField ( "stuff[title]", "Acceptance Test" );
                        $I->fillField ( "stuff[content]", "Acceptance Test" );
                        $I->fillField ( "stuff[type]", "Acceptance Test" );
                        

?>