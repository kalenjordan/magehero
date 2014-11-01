<?php

class Model_Tag extends Model_Record
{
    protected $_user;

    protected function _getTable() { return 'tags'; }
    protected function _getTableIdFieldname() { return 'tag_id'; }
    protected function _getColumns()
    {
        return array('tag_text');
    }

    /**
     * @var Model_LocalConfig
     */
    protected $_localConfig;

    public function __construct(Model_LocalConfig $config)
    {
        $this->_localConfig = $config;
    }

    public function getTagText()    { return $this->get('tag_text'); }


    public function fetchByPostId($postId)
    {
        $query = $this->_localConfig->database()->select()
            ->from($this->_getTable())
            ->joinLeft(
                'post_tag',
                "post_tag.post_id = $postId AND post_tag.tag_id = tags.tag_id",
                array()
            )
            ->where('post_tag.post_tag_id IS NOT NULL')
            ->order('tags.tag_text ASC');

        $rows = $this->_localConfig->database()->fetchAll($query);

        $models = array();
        foreach ($rows as $row) {
            $model = $this->_getContainer()->Tag()->setData($row);
            $models[] = $model;
        }

        return $models;
    }

    public function fetchAll()
    {
        $rows = parent::fetchAll();

        $models = array();
        foreach ($rows as $row) {
            $model = $this->_getContainer()->Tag()->setData($row);
            $models[] = $model;
        }

        return $models;
    }
}