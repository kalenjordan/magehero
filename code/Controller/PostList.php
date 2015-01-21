<?php

class Controller_PostList extends Controller_Abstract
{
    public function get()
    {
        $posts = $this->_getPosts();

        echo $this->_getTwig()->render('post_list.html.twig', array(
            'session'       => $this->_getSession(),
            'posts'         => $posts,
            'local_config'  => $this->_getContainer()->LocalConfig(),
        ));
    }

    protected function _getPosts()
    {
        $recentTimePeriod = $this->_getContainer()->LocalConfig()->getRecentTimePeriod();
        if (! $recentTimePeriod) {
            throw new Exception("Missing recent_time_period in config");
        }

        $select = $this->_getContainer()->Post()->selectAll()
            ->where('posts.is_active = 1');

        if (! isset($_GET['period']) || $_GET['period'] != 'all-time') {
            $select->where("posts.created_at > DATE_SUB(NOW(), INTERVAL $recentTimePeriod)");
        } else {
            $select->where("posts.created_at > DATE_SUB(NOW(), INTERVAL 1 MONTH)");
        }

        $postRows = $this->_getContainer()->LocalConfig()->database()->fetchAll($select);
        $postModels = array();

        foreach ($postRows as $postRow) {
            $postModel = $this->_getContainer()->Post()->setData($postRow);
            $postModels[] = $postModel;
        }

        return $postModels;
    }
}