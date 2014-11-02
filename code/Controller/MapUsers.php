<?php

class Controller_MapUsers extends Controller_Abstract
{

    protected function _getDevelopers()
    {
        $query = $this->_getContainer()->User()->selectAll();
        $userRows = $this->_getContainer()->LocalConfig()->database()->fetchAll($query);
        $users = array();
        foreach ($userRows as $userRow) {
            $userModel = $this->_getContainer()->User()->setData($userRow);
            $users[] = array(
                'latitude'  => $userModel->getLatitude(),
                'longitude' => $userModel->getLongitude(),
                'name'      => $userModel->getName(),
                'city'      => $userModel->getLocation(),
                'username'  => $userModel->getUsername(),
                'image'     => $userModel->getImageUrl(),
                'links'     => array(
                    'website'            => $userModel->getWebsiteUrl(),
                    'twitter'            => $userModel->getTwitterUsername(),
                    'github'             => $userModel->getGithubUsername(),
                    'stackOverflow'      => $userModel->stackoverflowUrl(),
                    'linkedIn'           => $userModel->linkedinUrl(),
                    'developer'          => $userModel->getCertifiedDeveloperUrl(),
                    'developerPlus'      => $userModel->certifiedDeveloperPlusUrl(),
                    'developerFrontend'  => $userModel->certifiedFrontendDeveloperUrl(),
                    'solutionSpecialist' => $userModel->certifiedSolutionSpecialistUrl(),
                    'certificationBoard' => $userModel->certificationBoardUrl(),
                ),
            );
        }
        return $users;
    }

    public function get()
    {
        $this->_jsonResponse($this->_getDevelopers());
    }
}
