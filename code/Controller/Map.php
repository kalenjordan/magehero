<?php

namespace Controller;

use \Controller\Controller;

class Map extends Controller
{
    public function get()
    {
        echo $this->_getTwig()->render('map.html.twig', array(
            'session'      => $this->_getSession(),
            'local_config' => $this->_getContainer()->LocalConfig(),
        ));
    }
}
