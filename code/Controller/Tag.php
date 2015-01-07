<?php

class Controller_Tag extends Controller_Abstract
{
    public function get($tagId, $slug = null)
    {

        $tag = $this->_getContainer()->Tag()->load($tagId);

        // While we only need tag ID, ensure that we use tag slug for SEO
        if ($slug != $tag->getSlug()) {
            header("Location: " . $tag->getUrl(), true, 301);
        }

        $posts = $this->_getContainer()->Post()->fetchByTagId($tagId);

        echo $this->_getTwig()->render('tag.html.twig', array(
            'session'       => $this->_getSession(),
            'tag'           => $tag,
            'posts'         => $posts,
            'local_config'  => $this->_getContainer()->LocalConfig(),
        ));
    }

}