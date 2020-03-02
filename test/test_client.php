<?php

class test_client {

    protected $t; // target.

    public function __construct() {

        $this->t = new StdClass;

        // Setup this settings for tests
        $this->t->baseurl = 'http://dev.moodle36.fr'; // The remote Moodle url to push in.
        $this->t->wstoken = '0c5bfc230c914ff15cf40d8b49abca10'; // the service token for access.
        $this->t->filepath = ''; // Some physical location on your system.

        $this->t->uploadservice = '/webservice/upload.php';
        $this->t->service = '/webservice/rest/server.php';
    }

    public function test_get_shop($shopid = 0) {

        if (empty($this->t->wstoken)) {
            echo "No token to proceed\n";
            return;
        }

        $params = array('wstoken' => $this->t->wstoken,
                        'wsfunction' => 'local_shop_get_shop',
                        'moodlewsrestformat' => 'json',
                        'shopid' => $shopid);

        $serviceurl = $this->t->baseurl.$this->t->service;

        return $this->send($serviceurl, $params);
    }

    public function test_get_catalog($catalogid = 0) {

        if (empty($this->t->wstoken)) {
            echo "No token to proceed\n";
            return;
        }

        $params = array('wstoken' => $this->t->wstoken,
                        'wsfunction' => 'local_shop_get_catalog',
                        'moodlewsrestformat' => 'json',
                        'catalogid' => $catalogid);

        $serviceurl = $this->t->baseurl.$this->t->service;

        return $this->send($serviceurl, $params);
    }

    public function test_get_catalogcategory($categoryid = 0) {

        if (empty($this->t->wstoken)) {
            echo "No token to proceed\n";
            return;
        }

        $params = array('wstoken' => $this->t->wstoken,
                        'wsfunction' => 'local_shop_get_catalogcategory',
                        'moodlewsrestformat' => 'json',
                        'categoryid' => $categoryid);

        $serviceurl = $this->t->baseurl.$this->t->service;

        return $this->send($serviceurl, $params);
    }

    public function test_get_catalogitem($itemidsource = 'id', $itemid = 0, $q = 1) {

        if (empty($this->t->wstoken)) {
            echo "No token to proceed\n";
            return;
        }

        $params = array('wstoken' => $this->t->wstoken,
                        'wsfunction' => 'local_shop_get_catalogitem',
                        'moodlewsrestformat' => 'json',
                        'itemidsource' => $itemidsource,
                        'itemid' => $itemid,
                        'q' => $q);

        $serviceurl = $this->t->baseurl.$this->t->service;

        return $this->send($serviceurl, $params);
    }

    public function test_get_catalogitems($catalogid, $categoryid = '*', $type = '*', $status = '*', $q = 1) {

        if (empty($this->t->wstoken)) {
            echo "No token to proceed\n";
            return;
        }

        $params = array('wstoken' => $this->t->wstoken,
                        'wsfunction' => 'local_shop_get_catalogitems',
                        'moodlewsrestformat' => 'json',
                        'catalogid' => $catalogid,
                        'categoryid' => $categoryid,
                        'status' => $status,
                        'type' => $type,
                        'q' => $q);

        $serviceurl = $this->t->baseurl.$this->t->service;

        return $this->send($serviceurl, $params);
    }

    protected function send($serviceurl, $params) {
        $ch = curl_init($serviceurl);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));

        echo "Firing CUrl $serviceurl ... \n";
        print_r($params);

        if (!$result = curl_exec($ch)) {
            echo "CURL Error : ".curl_errno($ch).' '.curl_error($ch)."\n";
            return;
        }

        echo $result;
        if (preg_match('/EXCEPTION/', $result)) {
            echo $result;
            return;
        }

        $result = json_decode($result);
        print_r($result);
        return $result;
    }
}

// Effective test scénario

$client = new test_client();

$client->test_get_shop(1); // Get shop info.
$client->test_get_catalog(1); // Get catalog info.

$client->test_get_catalogcategory(1); // Get category info.
$client->test_get_catalogitem('id', 1, 1); // Get item info by id for 1 unit.
$client->test_get_catalogitem('id', 1, 20); // Get item info by id for 20 units.
$client->test_get_catalogitem('code', 'TEST', 1); // Get item info by code for 1 unit. Failed product code
$client->test_get_catalogitem('code', 'PAGED', 1); // Get item info by code for 1 unit.
$client->test_get_catalogitems(1, 1, '*', '*', 1); // Get all category 1 products for 1 unit.
$client->test_get_catalogitems(1, 0, '*', 'AVAILABLE', 1); // Get item info by code.
$client->test_get_catalogitems(1, 0, 'plain', '*', 1); // Get all standalone product info for 1 unit.
$client->test_get_catalogitems(1, 1, 'plain', 'AVAILABLE', 1); // Get available standalone products info in category 1 for 1 unit.

