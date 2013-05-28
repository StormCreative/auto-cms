<?php

$I = new WebGuy($scenario);
$I->wantTo('Test the projects edit page');
$I->amOnPage('admin/projects/edit');

/** First check form cannot be sent if the user doesnt input any values **/
$I->amGoingTo('Submit user form with invalid values');
$I->click('Save');

$I->see("Title can not be empty");
            $I->see("Content can not be empty");
            

$I->amGoingTo("Submit projects form without a title");
                                        $I->click("Save");
                                        $I->fillField ( "projects[content]", "Acceptance Test" );
                        $I->amGoingTo("Submit projects form without a content");
                                        $I->click("Save");
                                        $I->fillField ( "projects[title]", "Acceptance Test" );
                        

?>