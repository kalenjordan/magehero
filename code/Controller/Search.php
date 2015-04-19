<?php

class Controller_Search extends Controller_Abstract
{
    public function get()
    {
        $posts = $this->_getContainer()->Post()->fetchByTerm($_GET['query']);
        $users = $this->_getContainer()->User()->search($_GET['query']);

        echo $this->_getTwig()->render('search.html.twig', array(
            'session'       => $this->_getSession(),
            'term'          => htmlspecialchars($_GET['query']),
            'posts'         => $posts,
            'users'         => $users,
            'local_config'  => $this->_getContainer()->LocalConfig(),
        ));
    }
}