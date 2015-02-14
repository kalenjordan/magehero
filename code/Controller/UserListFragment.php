<?php

class Controller_UserListFragment extends Controller_UserList
{
    protected $_page;

    public function get($page = null)
    {
        $this->_page = $page;
        $developers = $this->_getDevelopers();

        $html = null;
        if ($developers) {
            $html = $this->_getTwig()->render('fragment/user_list.html.twig', array(
                'developers'    => $developers,
                'session'       => $this->_getSession(),
                'local_config'  => $this->_getContainer()->LocalConfig(),
            ));
        }

        $this->_jsonResponse(array(
            'success'   => true,
            'count'     => count($developers),
            'html'      => $html,
        ));
    }

    protected function _getSelect()
    {
        $select = $this->_getContainer()->User()->selectAll()
            ->limit(20, $this->_page);

        return $select;
    }
}