<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('ensure the homepage loads');
$I->amOnPage('/');
$I->see('Celebrating the work of Magento developers, the world over.');