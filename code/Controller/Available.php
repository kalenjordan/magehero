<?php

class Controller_Available extends Controller_Index
{
    protected function _getDevelopers()
    {
        $developers = parent::_getDevelopers();

        /** @var $user Model_User */
        for ($i = 0; $i <= count($developers); $i++) {
            $user = $developers[$i];

            if ($this->_shouldExcludeUser($user)) {
                unset($developers[$i]);
            }
        }

        return $developers;
    }

    /**
     * @param $user Model_User
     */
    protected function _shouldExcludeUser($user)
    {
        if (! $user->getNextAvailable()) {
            return true;
        }

        // This checks to see whether it doesn't parse as a date (i.e. when people enter "No").
        // This json-as-mysql-schema is beginning to get a little bit absurd,
        // because obviously this logic would be a simple WHERE if the next_available
        // field were a nice tidy database column.  Alas, technical debt.
        if ($user->getNextAvailableFriendly() == $user->getNextAvailable()) {
            return true;
        }

        if ($user->getVoteCount() == 0) {
            return true;
        }

        return false;
    }
}