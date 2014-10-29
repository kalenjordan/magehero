<?php

class Controller_UserProfile extends Controller_Abstract
{
    public function get($username)
    {
        try {
            $this->_get($username);
        } catch (Exception $e) {
            return $this->_jsonResponse(array(
                'success' => false,
                'message' => $e->getMessage(),
            ));
        }

        return $this;
    }

    protected function _get($username)
    {
        $user = $this->_getContainer()->User()->loadByUsername($username);
        if (! $user->getId()) {
            die("Not found");
        }

        $developers = $this->_getDevelopers($user);

        echo $this->_getTwig()->render('index.html.twig', array(
            'user'          => $user,
            'developers'    => $developers,
            'session'       => $this->_getSession(),
            'local_config'  => $this->_getContainer()->LocalConfig(),
        ));
    }

    /**
     * @param $user Model_User
     * @return array
     */
    protected function _getDevelopers($user)
    {
        $userModels = array($user);
        $userRows = $this->_getContainer()->User()->fetchAll();

        foreach ($userRows as $userRow) {
            $developer = $this->_getContainer()->User()->setData($userRow);
            if ($developer->getUsername() != $user->getUsername()) {
                $userModels[] = $developer;
            }
        }

        return $userModels;
    }

    /**
     * @param $a Model_User
     * @param $b Model_User
     * @return bool
     */
    public function sortDevelopers($a, $b)
    {
        if ($a->getUsername() == 'kalenjordan') {
            return true;
        }
        if ($b->getUsername() == 'kalenjordan') {
            return true;
        }
    }

}