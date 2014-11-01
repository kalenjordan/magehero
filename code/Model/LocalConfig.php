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

    public function get($key)
    {
        return (isset($this->_data[$key]) ? $this->_data[$key] : null);
    }

    protected function _getDatabaseConfig()
    {
        return array(
            'dbname'    => $this->get('db_name'),
            'password'  => $this->get('db_password'),
            'username'  => $this->get('db_username'),
            'host'  => $this->get('db_hostname')
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

    public function getGoogleAnalyticsUa() { return $this->get('google_analytics_ua'); }
    public function getRankMehMinimumVotecount() { return $this->get('rank_meh_minimum_votecount'); }
    public function getRankStarMinimumVotecount() { return $this->get('rank_star_minimum_votecount'); }
    public function getRankRocketMinimumVotecount() { return $this->get('rank_rocket_minimum_votecount'); }

    public function getHellobarEnabled() { return $this->get('hellobar_enabled'); }
    public function getHellobarMessage() { return $this->get('hellobar_message'); }
    public function getHellobarUrl() { return $this->get('hellobar_url'); }
    public function getDisqusShortname() { return $this->get('disqus_shortname'); }
}