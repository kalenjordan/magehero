<?php

class Model_Post extends Model_Record
{
    protected $_data;
    protected $_user;

    protected function _getTable() { return 'posts'; }
    protected function _getTableIdFieldname() { return 'post_id'; }
    protected function _getColumns()
    {
        return array('user_id', 'subject', 'body');
    }

    /**
     * @var Model_LocalConfig
     */
    protected $_localConfig;

    public function __construct(Model_LocalConfig $config)
    {
        $this->_localConfig = $config;
    }

    public function getSubject()    { return $this->get('subject'); }
    public function getBody()       { return $this->get('body'); }
    public function getUserId()     { return $this->get('user_id'); }

    public function getUser()
    {
        if (isset($this->_user)) {
            return $this->_user;
        }

        $this->_user = $this->_getContainer()->User()->load($this->getUserId());
        return $this->_user;
    }

    public function fetchTags()
    {
        $tags = $this->_getContainer()->Tag()->fetchByPostId($this->getId());
        return $tags;
    }

    public function fetchByUserId($userId)
    {
        $query = $this->selectAll(true);
        $query->where('posts.user_id = ?', $userId);
        $rows = $this->_localConfig->database()->fetchAll($query);

        $models = array();
        foreach ($rows as $row) {
            $model = $this->_getContainer()->Post()->setData($row);
            $models[] = $model;
        }

        return $models;
    }
}