<?php 
$I = new AcceptanceTester\MemberSteps($scenario);
$I->wantTo('Login');
$I->login();
$I->see('dummyqwerty');