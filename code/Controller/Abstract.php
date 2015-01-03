<?php

class Controller_Abstract
{
    CONST SESSION_NAMESPACE = "magedev";

    protected $_container;
    protected $_currentUser;

    protected function _preDispatch()
    {

    }

    /**
     * @param null $path
     * @param null $params
     */
    protected function _redirect($path = null, $params = null)
    {
        // If internal URL, ensure to include base URL
        if (strpos($path, 'http') === false) {
            $path = self::_getConfigData('base_url') . '/' . $path;
        }

        // Redirect with GET parameters
        if (isset($params)) {
            $path .= '?' . http_build_query($params);
        }

        header('Location: ' . $path);
        exit;
    }

    /**
     * @param $key
     * @param $value
     */
    protected function _setSession($key, $value)
    {
        $_SESSION[self::SESSION_NAMESPACE][$key] = $value;
    }

    protected function _getSession()
    {
        $session = isset($_SESSION[self::SESSION_NAMESPACE]) ? $_SESSION[self::SESSION_NAMESPACE] : array();
        return $session;
    }

    /**
     * @param $username
     */
    protected function _setUsername($username)
    {
        $this->_setSession('github_username', $username);
    }

    protected function _getUsername()
    {
        $session = $this->_getSession();
        $username = isset($session['github_username']) ? $session['github_username'] : null;

        return $username;
    }

    /**
     * @return Model_User
     */
    protected function _getCurrentUser()
    {
        if (isset($this->_currentUser)) {
            return $this->_currentUser;
        }

        $this->_currentUser = $this->_getContainer()->User()->loadByUsername($this->_getUsername());
        return $this->_currentUser;
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
