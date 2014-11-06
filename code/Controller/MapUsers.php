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
                    'url_website'                       => $userModel->getWebsiteUrl(),
                    'twitter_username'                  => $userModel->getTwitterUsername(),
                    'github_username'                   => $userModel->getGithubUsername(),
                    'stackoverflow_url'                 => $userModel->stackoverflowUrl(),
                    'linkedin_url'                      => $userModel->linkedinUrl(),
                    'certification_board_url'           => $userModel->certificationBoardUrl(),
                    'certified_developer_url'           => $userModel->getCertifiedDeveloperUrl(),
                    'certified_developer_plus_url'      => $userModel->certifiedDeveloperPlusUrl(),
                    'certified_solution_specialist_url' => $userModel->certifiedSolutionSpecialistUrl(),
                    'certified_frontend_developer_url'  => $userModel->certifiedFrontendDeveloperUrl(),
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
