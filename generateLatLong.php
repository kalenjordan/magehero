<?php

/**
 * This script is only a convenience helper to initially prefill the latitude and longitude values for the heros.
 * It skips heros which already have set the lat long value.
 */

if ('cli' !== php_sapi_name()) {
    die('This script can only run on command line!');
}

require_once(dirname(__FILE__) . '/vendor/autoload.php');
spl_autoload_register(function ($class) {
    $parts = explode("_", $class);
    $classSlashSeparated = implode('/', $parts);
    $pathToFile = "code/$classSlashSeparated.php";
    $fullPathToFile = dirname(__FILE__) . '/' . $pathToFile;
    if (file_exists($fullPathToFile)) {
        include $fullPathToFile;
    }
});

class Controller_MapUsersLngLat extends Controller_Abstract
{
    /**
     * This method is not meant to be in the user model. we only need it once per user.
     * after that the user is h/er/im self responsible for lat long values.
     *
     * @param $address
     *
     * @return array
     */
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
        $lat = 0;
        $long = 0;
        if (isset($response->results[0]) && isset($response->results[0]->geometry)) {
            $lat = $response->results[0]->geometry->location->lat;
            $long = $response->results[0]->geometry->location->lng;
        }
        return array(
            'lat' => (float)$lat,
            'lng' => (float)$long,
        );
    }

    public function updateLongitudeLatitude()
    {
        $query = $this->_getContainer()->User()->selectAll();
        $userRows = $this->_getContainer()->LocalConfig()->database()->fetchAll($query);

        foreach ($userRows as $userRow) {
            $userModel = $this->_getContainer()->User()->setData($userRow);

            // skip users which already have lat long set.
            // int is a terrible hack 8-) but we will not have any mage dev near GPS point ~0.45,~-0.45
            if (0 !== (int)$userModel->getLatitude() && 0 !== (int)$userModel->getLongitude()) {
                continue;
            }

            $location = $userModel->getLocation();
            $lnglat = $this->_getLongLat($location);
            $detailsArray = json_decode($userModel->get('details_json'), true);
            $detailsArray['longitude'] = $lnglat['lng'];
            $detailsArray['latitude'] = $lnglat['lat'];
            $data = array(
                'details_json' => json_encode($detailsArray, JSON_PRETTY_PRINT)
            );
            $this->_getContainer()->LocalConfig()->database()->update(
                'users',
                $data,
                'user_id=' . $userModel->getId()
            );
            printf("ID: %d\t%s\t\tGPS: %f,%f\n", $userModel->getId(), $location, $lnglat['lat'], $lnglat['lng']);
            flush();
        }
        echo "\nDone\n";
    }
}

$c = new Controller_MapUsersLngLat();
$c->updateLongitudeLatitude();
