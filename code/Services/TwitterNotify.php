<?php
class Services_TwitterNotify implements Services_NotifyInterface {
    /**
     * @var Model_LocalConfig
     */
    protected $_localConfig;

    public function __construct(Model_LocalConfig $config)
    {
        $this->_localConfig = $config;
    }

    protected function _getTwitterSettings()
    {
        $settings = array(
            'oauth_access_token' => $this->_localConfig->get('twitter_oauth_access_token'),
            'oauth_access_token_secret' => $this->_localConfig->get('twitter_oauth_access_token_secret'),
            'consumer_key' => $this->_localConfig->get('twitter_consumer_api_key'),
            'consumer_secret' => $this->_localConfig->get('twitter_consumer_api_secret')
        );

        return $settings;
    }

    protected function _postTweet($message)
    {
        $url = 'https://api.twitter.com/1.1/statuses/update.json';
        $requestMethod = 'POST';
        $postfields = array("status" => $message);

        $twitter = new TwitterAPIExchange($this->_getTwitterSettings());
        $response = $twitter
            ->buildOauth($url, $requestMethod)
            ->setPostfields($postfields)
            ->performRequest();

        // Error handling for tweet failures , is not required. I am pretty sure that the voters are not interested
        // in knowing if the tweet was posted or now. 
        return $response;
    }
    /**
     * @param Model_User $to
     * @param Model_User $from
     * @param string $message
     * @return bool|string
     */
    public function send($to, $from = "", $message = "")
    {

        try {

            if ($message == "") {
                // Construct Tweet
                $message = "@".$to->getTwitterUsername()." you were upvoted by @".$from->getTwitterUsername(). " on magehero.com/" . $to->getGithubUsername();
            }

            $this->_postTweet($message);

        } catch(Exception $e) {
            return false;
        }

    }
}