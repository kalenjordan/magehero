<?php

class Controller_Feed extends Controller_Abstract
{

    public function get()
    {
        $posts = $this->_getPosts();

        $latestPost = current($posts);
        $updated = $latestPost ? $latestPost->getCreatedAt() : \Carbon\Carbon::now()->toDateTimeString();

        echo $this->_getTwig()->render('feed.xml.twig', array(
            'session'       => $this->_getSession(),
            'posts'         => $posts,
            'local_config'  => $this->_getContainer()->LocalConfig(),
            'feed_config'   => array(
                'title'         => 'MageHero',
                'updated'       => $updated,
                'description'   => '',
            ),
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


