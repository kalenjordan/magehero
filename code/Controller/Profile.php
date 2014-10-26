<?php

class Controller_Profile extends Controller_Abstract
{
    public function get()
    {
        $user = $this->_getContainer()->User()->loadByUsername($this->_getUsername());

        if ($user->getId()) {
            $profileJson = $user->get('details_json');
        } else {
            $profileJson = $this->_getPlaceholderProfileJson();
        }

        echo $this->_getTwig()->render('profile/edit.html.twig', array(
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

        $profileData = json_decode($profileJson, true);
        if (! is_array($profileData)) {
            die("There was a problem decoding the JSON, please check to make sure it was valid");
        }

        $username = $this->_getUsername();
        if (! $username) {
            throw new Exception("Couldn't find username");
        }

        $user = $this->_getContainer()->User()->loadByUsername($username);
        $user->set('details_json', $profileJson)
            ->set('username', $this->_getUsername())
            ->set('name', isset($profileData['name']) ? $profileData['name'] : null)
            ->save();

        header("location: /profile");
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