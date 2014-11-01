<?php

class Model_PostTag extends Model_Record
{
    protected $_user;

    protected function _getTable() { return 'post_tag'; }
    protected function _getTableIdFieldname() { return 'post_tag_id'; }
    protected function _getColumns()
    {
        return array('user_id', 'post_id', 'tag_id');
    }

    /**
     * @var Model_LocalConfig
     */
    protected $_localConfig;

    public function __construct(Model_LocalConfig $config)
    {
        $this->_localConfig = $config;
    }
}