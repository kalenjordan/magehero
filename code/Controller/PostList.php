<?php

namespace Controller;

use \Controller\Controller;

class PostList extends Controller
{
    public function get()
    {
        $posts = $this->_getPosts();

        echo $this->_getTwig()->render('post_list.html.twig', array(
            'session'       => $this->_getSession(),
            'posts'         => $posts,
        ));
    }

    protected function _getPosts()
    {
        $postRows = $this->_getContainer()->Post()->fetchAll();
        $postModels = array();

        foreach ($postRows as $postRow) {
            $postModel = $this->_getContainer()->Post()->setData($postRow);
            $postModels[] = $postModel;
        }

        return $postModels;
    }
}