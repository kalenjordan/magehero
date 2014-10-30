<?php

class Controller_MapUsers extends Controller_Abstract
{
    public function get()
    {

//        example
//$db = new PDO('sqlite:leaflet.sqlite');
//$sql = "SELECT id, name, website, city, lat, lng FROM users;";
//
//$rs = $db->query($sql);
//if (!$rs) {
//    echo "An SQL error occured.\n";
//    exit;
//}
//
//$rows = array();
//while($r = $rs->fetch(PDO::FETCH_ASSOC)) {
//    $rows[] = $r;
//}
//print json_encode($rows);


        $data = [
            'u1' => 'u2'
        ];

        echo json_encode($data);
    }
}
