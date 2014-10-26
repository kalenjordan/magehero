<?php

require_once dirname(dirname(dirname(__FILE__))) . '/vendor/lusitanian/oauth/src/OAuth/bootstrap.php';

use OAuth\OAuth2\Service\GitHub;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;

class Controller_Login extends Controller_Abstract
{
    public function get()
    {
        $github = $this->_getGithubService();

        if (!empty($_GET['code'])) {
            // This was a callback request from github, get the token
            $github->requestAccessToken($_GET['code']);

            $result = json_decode($github->request('user'), true);
            if (!isset($result['login'])) {
                throw new Exception("Couldn't get github username");
            }

            $username = isset($result['login']) ? $result['login'] : null;
            $_SESSION['magedevs']['github_username'] = $username;
            $_SESSION['magedevs']['image_url'] = isset($result['avatar_url']) ? $result['avatar_url'] : null;

            header("location: /magedevs/");
        } else {
            $url = $github->getAuthorizationUri();
            header('Location: ' . $url);
        }
    }

    /**
     * @return \OAuth\OAuth2\Service\GitHub
     */
    protected function _getGithubService()
    {
        // Session storage
        $storage = new Session();
        $serviceFactory = new \OAuth\ServiceFactory();

        $url = $this->_getConfigData('base_url') . "/login/";
        $apiKey = $this->_getConfigData('github_api_key');
        $apiSecret = $this->_getConfigData('github_api_secret');

        $credentials = new Credentials($apiKey, $apiSecret, $url);
        $github = $serviceFactory->createService('GitHub', $credentials, $storage, array('user:email'));

        return $github;
    }
}