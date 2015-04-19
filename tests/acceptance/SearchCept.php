<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('Search for magento and see post results');
$I->amOnPage('/');
$I->fillField('query','magento');
$I->click('Search');
$I->seeInCurrentUrl('search');
$I->seeElement('.listing-post');