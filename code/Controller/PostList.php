<?php

class Controller_PostList extends Controller_Abstract
{
    public function get()
    {
        $selectThisWeek = $this->_getSelectByWeek(1);
        $selectLastWeek = $this->_getSelectByWeek(2, 1);

        $postsThisWeek = $this->_selectToModelArray($selectThisWeek);
        $postsLastWeek = $this->_selectToModelArray($selectLastWeek);

        echo $this->_getTwig()->render('post_list.html.twig', array(
            'session'           => $this->_getSession(),
            'posts_this_week'   => $postsThisWeek,
            'posts_last_week'   => $postsLastWeek,
            'local_config'      => $this->_getContainer()->LocalConfig(),
        ));
    }

    protected function _getSelectByWeek($fromWeek, $toWeek = null)
    {
        $select = $this->_getContainer()->Post()->selectAll()
            ->reset('order')
            ->order(array("DATE_FORMAT(posts.created_at, '%Y-%m-%d') DESC", "COUNT(DISTINCT post_vote_id) DESC"))
            ->where('posts.is_active = 1')
            ->where("posts.created_at > DATE_SUB(NOW(), INTERVAL $fromWeek WEEK)");

        if ($toWeek) {
            $select->where("posts.created_at < DATE_SUB(NOW(), INTERVAL $toWeek WEEK)");
        }

        return $select;
    }

    /**
     * @param $select Zend_Db_Select
     */
    protected function _selectToModelArray($select)
    {
        $postRows = $this->_getContainer()->LocalConfig()->database()->fetchAll($select);
        $postModels = array();

        foreach ($postRows as $postRow) {
            $postModel = $this->_getContainer()->Post()->setData($postRow);
            $postModels[] = $postModel;
        }

        return $postModels;
    }
}