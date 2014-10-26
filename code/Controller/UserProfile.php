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

        echo $this->_getTwig()->render('profile/view.html.twig', array(
            'user'      => $user,
            'session'   => $this->_getSession(),
        ));
    }
}