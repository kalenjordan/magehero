<?php

namespace Controller;

use \Controller\Controller;

class UserPosts extends Controller
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

        echo $this->_getTwig()->render('user.html.twig', array(
            'user'          => $user,
            'posts'         => $this->_fetchPosts($user),
            'session'       => $this->_getSession(),
            'local_config'  => $this->_getContainer()->LocalConfig(),
        ));
    }

    /**
     * @param $user Model_User
     * @return array
     */
    protected function _fetchPosts($user)
    {
        $posts = $this->_getContainer()->Post()->fetchByUserId($user->getId());
        return $posts;
    }
}