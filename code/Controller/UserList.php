<?php

class Controller_UserList extends Controller_Abstract
{
    public function get($page = null)
    {
        $developers = $this->_getDevelopers();

        echo $this->_getTwig()->render('user_list.html.twig', array(
            'developers'    => $developers,
            'session'       => $this->_getSession(),
            'local_config'  => $this->_getContainer()->LocalConfig(),
        ));
    }

    protected function _getSelect()
    {
        $select = $this->_getContainer()->User()->selectAll()
            ->limit(20);

        return $select;
    }

    protected function _getDevelopers()
    {
        $select = $this->_getSelect();
        $userRows = $this->_getContainer()->LocalConfig()->database()->fetchAll($select);

        $userModels = array();
        foreach ($userRows as $userRow) {
            $userModel = $this->_getContainer()->User()->setData($userRow);
            if ($this->_shouldIncludeUser($userModel)) {
                $userModels[] = $userModel;
            }
        }

        return $userModels;
    }

    /**
     * @param $user Model_User
     * @return bool
     */
    protected function _shouldIncludeUser($user)
    {
        if (isset($_GET['country'])) {
            $country = preg_replace('~[^A-Za-z]~','', $_GET['country']);
            if ($user->getDetail('country') == $country) {
                return true;
            } else {
                return false;
            }
        }

        return true;
    }
}