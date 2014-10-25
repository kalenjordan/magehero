<?php

class Controller_Profile extends Controller_Abstract
{
    public function get()
    {
        $profileJson = $this->_getProfileJson();
        if (! $profileJson) {
            $profileJson = $this->_getPlaceholderProfileJson();
        }


        echo $this->_getTwig()->render('profile.html.twig', array(
            'session'       => $this->_getSession(),
            'profile_json'  => $profileJson,
        ));
    }

    public function post()
    {
        if (! isset($_POST['profile'])) {
            throw new Exception("Missing profile data");
        }
        $profileJson = $_POST['profile'];

        $decodeJson = json_decode($profileJson, true);
        if (! is_array($decodeJson)) {
            die("There was a problem decoding the JSON, please check to make sure it was valid");
        }

        $username = $this->_getUsername();
        if (! $username) {
            throw new Exception("Couldn't find username");
        }

        $pathToUserJson = dirname(dirname(dirname(__FILE__))) . '/data/' . $username . ".json";
        file_put_contents($pathToUserJson, $_POST['profile']);

        header("location: /magedevs/profile");
    }

    protected function _getPlaceholderProfileJson()
    {
        $session = $this->_getSession();
        $sampleJsonFile = dirname(dirname(dirname(__FILE__))) . "/sample.json";
        $sampleData = json_decode(file_get_contents($sampleJsonFile), true);

        $imageUrl = isset($session['image_url']) ? $session['image_url'] : null;
        $sampleData['image_url'] = $imageUrl;
        $sampleData['github_username'] = $this->_getUsername();

        return json_encode($sampleData, JSON_PRETTY_PRINT);
    }
}