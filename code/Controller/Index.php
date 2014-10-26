<?php

class Controller_Index extends Controller_Abstract
{
    public function get()
    {
        $developers = $this->_getDevelopers();

        echo $this->_getTwig()->render('index.html.twig', array(
           'developers'    => $developers,
           'session'       => $this->_getSession(),
        ));
    }

    protected function _getDevelopers()
    {
        $userModels = array();
        $userRows = $this->_getContainer()->User()->fetchAll();
        foreach ($userRows as $userRow) {
            $userModels[] = $this->_getContainer()->User()->setData($userRow);
        }

        return $userModels;
    }
}