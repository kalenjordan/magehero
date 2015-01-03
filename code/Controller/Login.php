<?php

require_once dirname(dirname(dirname(__FILE__))) . '/vendor/lusitanian/oauth/src/OAuth/bootstrap.php';

class Controller_Login extends Controller_Account
{

    protected function _preDispatch()
    {
        parent::_preDispatch();
    }

    public function get()
    {
        $this->_preDispatch();

        $this->_redirect($_SERVER['HTTP_REFERER']);
    }
}