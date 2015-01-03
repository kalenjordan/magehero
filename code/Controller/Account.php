<?php

require_once dirname(dirname(dirname(__FILE__))) . '/vendor/lusitanian/oauth/src/OAuth/bootstrap.php';

use OAuth\OAuth2\Service\GitHub;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;

class Controller_Account extends Controller_Abstract
{

    protected function _preDispatch()
    {

        // Return if already logged in
        if ($this->_getUsername())
            return;

        // This was a callback request from github, get the token
        if (!empty($_GET['code'])) {
            $github = $this->_getGithubService();

            $github->requestAccessToken($_GET['code']);

            $result = json_decode($github->request('user'), true);
            if (!isset($result['login'])) {
                throw new Exception("Couldn't get github username");
            }

            $this->_setUsername($result['login']);
            $this->_setSession('image_url', $result['avatar_url']);

        // Forward on to github to login
        } else {
            $github = $this->_getGithubService($this->_getConfigData('base_url') . $_SERVER['REQUEST_URI']);
            $url = $github->getAuthorizationUri();

            header('Location: ' . $url);
            exit;
        }
    }

    /**
     *
     * @param call
     * @return \OAuth\OAuth2\Service\GitHub
     */
    protected function _getGithubService($callbackUrl = null)
    {
        // Session storage
        $storage = new Session();
        $serviceFactory = new \OAuth\ServiceFactory();

        if (is_null($callbackUrl)) {
            $callbackUrl = $this->_getConfigData('base_url') . "/login/";
        }

        $apiKey = $this->_getConfigData('github_api_key');
        $apiSecret = $this->_getConfigData('github_api_secret');

        $credentials = new Credentials($apiKey, $apiSecret, $callbackUrl);
        $github = $serviceFactory->createService('GitHub', $credentials, $storage, array('user:email'));

        return $github;
    }
}
