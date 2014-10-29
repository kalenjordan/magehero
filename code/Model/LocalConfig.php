<?php

class Model_LocalConfig
{
    protected $_data = array();
    protected $_databaseAdapter;

    public function __construct()
    {
        $configJsonFile = dirname(dirname(dirname(__FILE__))) . "/etc/config.json";
        $json = file_get_contents($configJsonFile);
        $configArray = json_decode($json, true);

        $this->_data = $configArray;
    }

    public function get($key, $default = null)
    {
        return (isset($this->_data[$key]) ? $this->_data[$key] : $default);
    }

    protected function _getDatabaseConfig()
    {
        return array(
            'dbname'    => $this->get('db_name'),
            'password'  => $this->get('db_password'),
            'username'  => $this->get('db_username'),
            'host'      => $this->get('db_host', 'localhost'),
        );
    }

    public function database()
    {
        if (isset($this->_databaseAdapter)) {
            return $this->_databaseAdapter;
        }

        $this->_databaseAdapter = new Zend_Db_Adapter_Pdo_Mysql($this->_getDatabaseConfig());
        return $this->_databaseAdapter;
    }
}