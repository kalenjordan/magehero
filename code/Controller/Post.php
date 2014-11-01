<?php

class Controller_Post extends Controller_Abstract
{
    public function get($postId)
    {
        $post = $this->_getContainer()->Post()->load($postId);

        echo $this->_getTwig()->render('post.html.twig', array(
            'session'       => $this->_getSession(),
            'post'          => $post,
            'local_config'  => $this->_getContainer()->LocalConfig(),
        ));
    }
}