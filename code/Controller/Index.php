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
        $dataDirectory = dirname(dirname(dirname(__FILE__))) . "/data";

        $developerUsernames = array();
        $developers = array();

        if ($handle = opendir($dataDirectory)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != ".." && $entry != '.htaccess') {
                    $developerUsernames[] = $entry;
                }
            }
            closedir($handle);
        }

        foreach ($developerUsernames as $developerUsername) {
            $developerJsonFilename = $dataDirectory . "/" . $developerUsername;
            $developerJson = file_get_contents($developerJsonFilename);
            $developerArray = json_decode($developerJson, true);
            $lastUpdated = \Carbon\Carbon::createFromTimestamp(filemtime($developerJsonFilename))->diffForHumans();

            $developerArray['last_updated_friendly'] = $lastUpdated;
            $developerArray['location'] = $this->_buildLocation($developerArray);
            $developers[] = $developerArray;
        }
        usort($developers, array($this, 'sortDevelopers'));

        return $developers;
    }

    protected function _buildLocation($developerData)
    {
        $parts = array();
        if (isset($developerData['city'])) {
            $parts[] = $developerData['city'];
        }

        if (isset($developerData['state'])) {
            $parts[] = $developerData['state'];
        }

        if (isset($developerData['country'])) {
            $parts[] = $developerData['country'];
        }
        
        return implode(", ", $parts);
    }

    public function sortDevelopers($a, $b)
    {
        return ($a['last_updated'] < $b['last_updated']);
    }
}