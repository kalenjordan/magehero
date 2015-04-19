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

        $posts = $this->_getContainer()->Post()->fetchByUserId($user->getId());

        echo $this->_getTwig()->render('user_profile.html.twig', array(
            'user'          => $user,
            'body_class'    => 'user-profile',
            'posts'         => $posts,
            'session'       => $this->_getSession(),
            'local_config'  => $this->_getContainer()->LocalConfig(),
        ));
    }
}