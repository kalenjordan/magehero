<?php

class Controller_PostList extends Controller_Abstract
{
    public function get()
    {
        $selectThisWeek = $this->_getSelectByWeek(1);
        $selectLastWeek = $this->_getSelectByWeek(4, 1);  // This grabbing last month instead of last week

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
            ->columns(array(
                'is_recent' => new Zend_Db_Expr("IF(posts.created_at > DATE_SUB(NOW(), INTERVAL 1 DAY), 1, 0)")
            ))
            ->order(new Zend_Db_Expr("COUNT(DISTINCT post_vote_id) + (IF(posts.created_at > DATE_SUB(NOW(), INTERVAL 1 DAY), 99, 0)) DESC"))
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