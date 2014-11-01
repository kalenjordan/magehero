<?php

class Controller_MapUsers extends Controller_Abstract
{

    protected function _getDevelopers()
    {
	$query      = $this->_getContainer()->User()->selectAll();
	$userRows   = $this->_getContainer()->LocalConfig()->database()->fetchAll($query);
	$userModels = array();
	foreach ($userRows as $userRow) {
	    $userModel    = $this->_getContainer()->User()->setData($userRow);
	    $userModels[] = array(
		'lat'      => $userModel->getLatitude(),
		'lng'      => $userModel->getLongitude(),
		'name'     => $userModel->getName(),
		'company'  => $userModel->getCompany(),
		'username' => $userModel->getUsername(),
		'website'  => $userModel->getWebsiteUrl(),
		'tw'       => $userModel->getTwitterUsername(),
		'gh'       => $userModel->getGithubUsername(),
		'img'      => $userModel->getImageUrl(),
	    );
	}

	return $userModels;
    }

    public function get()
    {
	$this->_jsonResponse($this->_getDevelopers());
    }
}
