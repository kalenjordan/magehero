<?php 
$I = new AcceptanceTester\MemberSteps($scenario);
$I->wantTo('Create a new post');
$I->login();
$I->click('Post');
$I->selectOption('select[name="is_active"]', 'Published');
$I->selectOption('Post Type', "What I'm working on");
$I->click('#tag_ids_chosen');
$I->click('[data-option-array-index="0"]');
$I->fillField(['name' => 'subject'], "I love magento");
$I->click('Save');
$I->see('I love magento');
$I->dontSeeInCurrentUrl('edit');
