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
        return json_encode(array(
            'email' => 'joe@example.com',
            'name'  => 'Joe Smith',
        ), JSON_PRETTY_PRINT);
    }
}