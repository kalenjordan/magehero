<?php

class Controller_Available extends Controller_Index
{
    protected function _getDevelopers()
    {
        $query = $this->_getContainer()->User()->selectAll(true);
        $query->having('vote_count > 0');
        $userRows = $this->_getContainer()->LocalConfig()->database()->fetchAll($query);

        $userModels = array();
        foreach ($userRows as $userRow) {
            $userModel = $this->_getContainer()->User()->setData($userRow);
            if (! $this->_shouldExcludeUser($userModel)) {
                $userModels[] = $userModel;
            }
        }

        return $userModels;
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

        return false;
    }
}