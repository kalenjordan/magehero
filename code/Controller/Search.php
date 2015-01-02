<?php

class Controller_Search extends Controller_Abstract
{
    public function get()
    {
        echo $this->_getTwig()->render('search.html.twig', array(
            'local_config'  => $this->_getContainer()->LocalConfig(),
        ));
    }
}