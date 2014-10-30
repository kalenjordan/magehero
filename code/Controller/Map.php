<?php

class Controller_Map extends Controller_Abstract
{
    public function get()
    {
	echo $this->_getTwig()->render('map.html.twig', array(
	    'session'      => $this->_getSession(),

	    'local_config' => $this->_getContainer()->LocalConfig(),
	));
    }
}
