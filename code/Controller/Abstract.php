<?php

class Controller_Abstract
{
    protected $_container;

    protected function _getSession()
    {
        $session = isset($_SESSION['magedevs']) ? $_SESSION['magedevs'] : array();
        return $session;
    }

    protected function _getUsername()
    {
        $session = $this->_getSession();
        $username = isset($session['github_username']) ? $session['github_username'] : null;

        return $username;
    }

    protected function _getTwig()
    {
        $loader = new Twig_Loader_Filesystem(dirname(dirname(dirname(__FILE__))) . '/template');
        $twig = new Twig_Environment($loader);
        return $twig;
    }

    protected function _getProfileJson()
    {
        $username = $this->_getUsername();
        if (! $username) {
            return null;
        }

        $json = @file_get_contents(dirname(dirname(dirname(__FILE__))) . '/data/' . $username . ".json");
        if (! $json) {
            return null;
        }

        return $json;
    }

    protected function _getProfileData()
    {
        $data = json_decode($json, true);
        return $data;
    }

    protected function _getConfigData($key)
    {
        $configJsonFile = dirname(dirname(dirname(__FILE__))) . "/etc/config.json";
        $json = file_get_contents($configJsonFile);
        $configArray = json_decode($json, true);

        $value = isset($configArray[$key]) ? $configArray[$key] : null;
        return $value;
    }

    protected function _getContainer()
    {
        if (isset($this->_container)) {
            return $this->_container;
        }

        $container = new Model_Container();

        $this->_container = $container;
        return $this->_container;
    }

    protected function _jsonResponse($response)
    {
        header('Content-type: application/json');
        echo json_encode($response);
        return $this;
    }
}
