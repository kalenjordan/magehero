<?php

class Controller_MapUsersLngLat extends Controller_Abstract
{
    /**
     * temporary controller for adding long lat values
     */
    public function get()
    {

	$query      = $this->_getContainer()->User()->selectAll();
	$userRows   = $this->_getContainer()->LocalConfig()->database()->fetchAll($query);
	$userModels = array();
	foreach ($userRows as $userRow) {
	    $userModel = $this->_getContainer()->User()->setData($userRow);

	    $location = $userModel->getLocation();
	    Zend_Debug::dump([ $userModel->getId(), $location]);
	}

	Zend_Debug::dump('Done');
    }
}
