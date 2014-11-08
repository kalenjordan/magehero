<?php

class Controller_Post extends Controller_Abstract
{
    public function get($postId, $slug = null)
    {
        $post = $this->_getContainer()->Post()->load($postId);

        // While we only need post ID, ensure that we use post slug for SEO
        if ($slug != $post->getSlug()) {
            header("Location: " . $post->getUrl());
        }

        echo $this->_getTwig()->render('post.html.twig', array(
            'session'       => $this->_getSession(),
            'post'          => $post,
            'local_config'  => $this->_getContainer()->LocalConfig(),
        ));
    }
}
