<?php

class Controller_MapUsersLngLat extends Controller_Abstract
{
    protected function _getLongLat($address)
    {
        $url = "http://maps.google.com/maps/api/geocode/json?sensor=false&address=" . urlencode($address);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response);
        $lat      = 0;
        $long     = 0;
        if (isset($response->results[0]) && isset($response->results[0]->geometry)) {
            $lat  = $response->results[0]->geometry->location->lat;
            $long = $response->results[0]->geometry->location->lng;
        }
        return array(
            'lat' => (float)$lat,
            'lng' => (float)$long,
        );
    }

    /**
     * temporary controller for adding long lat values
     */
    public function get()
    {

        $query    = $this->_getContainer()->User()->selectAll();
        $userRows = $this->_getContainer()->LocalConfig()->database()->fetchAll($query);

        foreach ($userRows as $userRow) {
            $userModel                 = $this->_getContainer()->User()->setData($userRow);
            $location                  = $userModel->getLocation();
            $lnglat                    = $this->_getLongLat($location);
            $detailsArray              = json_decode($userModel->get('details_json'), true);
            $detailsArray['longitude'] = $lnglat['lng'];
            $detailsArray['latitude']  = $lnglat['lat'];
            $data                      = array(
                'details_json' => json_encode($detailsArray)
            );
            $this->_getContainer()->LocalConfig()->database()->update(
                'users',
                $data,
                'user_id=' . $userModel->getId()
            );
            Zend_Debug::dump([$userModel->getId(), $location, $lnglat]);
            echo '<hr>';
            flush();
        }

        Zend_Debug::dump('Done');
    }
}
