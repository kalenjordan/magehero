<?php

use \Model\LocalConfig;

class Services_TwitterNotify implements Services_NotifyInterface {
    /**
     * @var Model_LocalConfig
     */
    protected $_localConfig;

    public function __construct(LocalConfig $config)
    {
        $this->_localConfig = $config;
    }

    /**
     * @param Model_User $to
     * @param Model_User $from
     * @param string $message
     * @return bool|string
     */
    public function send($to, $from = "", $message = "") {

        if ($message == "") {
            // Construct Tweet
            $message = "@".$to->getTwitterUsername()." you were upvoted by @".$from->getTwitterUsername(). " on magehero.com/" . $to->getGithubUsername();
        }

        $settings = array(
            'oauth_access_token' => $this->_localConfig->get('twitter_oauth_access_token'),
            'oauth_access_token_secret' => $this->_localConfig->get('twitter_oauth_access_token_secret'),
            'consumer_key' => $this->_localConfig->get('twitter_consumer_api_key'),
            'consumer_secret' => $this->_localConfig->get('twitter_consumer_api_secret')
        );
        $url = 'https://api.twitter.com/1.1/statuses/update.json';
        $requestMethod = 'POST';
        $postfields = array("status" => $message);
        try{
        $twitter = new TwitterAPIExchange($settings);
        $response = $twitter
            ->buildOauth($url, $requestMethod)
            ->setPostfields($postfields)
            ->performRequest();
        // Error handling for tweet failurs , is not required. I am pretty sure that the voters are not interested
        // in knowing if the tweet was posted or now. 
        return $response;
        }catch(Exception $e) {
            return false;
        }
        //var_dump(json_decode($response));die;
    }
}