<?php
namespace AcceptanceTester;

class MemberSteps extends \AcceptanceTester
{
    public function login()
    {
        // @see https://www.sharklasers.com/inbox?mail_id=26371953
        $I = $this;
        $I->amOnPage('/');
        $I->click('Login');
        $I->fillField(['name' => 'login'], 'dummyqwerty');
        $I->fillField(['name' => 'password'], 'f7^JDmy!k94m8I@z');
        $I->click('input[name="commit"]');
        $I->click('dummyqwerty');
        $I->click('Save');
    }
}