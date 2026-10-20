<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALjobalertsettingTable extends WPJOBPORTALtable {

    public $id = '';
    public $uid = '';
    public $categoryid = '';
    public $subcategoryid = '';
    public $name = '';
    public $contactemail = '';
    public $country = '';
    public $state = '';
    public $county = '';
    public $city = '';
    public $zipcode = '';
    public $sendtime = '';
    public $status = '';
    public $created = '';
    public $keywords = '';
    public $price = '';
    public $alerttype = '';
    public $longitude = '';
    public $latitude = '';
    public $coordinatesradius = '';
    public $lastmailsend = '';
    public $userpackageid = '';
    public $serverid = '';
    public $serverstatus = '';
    // --- NEW FIELDS ---
    public $company = '';
    public $jobtype = '';
    public $workplace_type = '';
    public $is_urgent = '';
    public $tags = '';

    function __construct() {
        parent::__construct('jobalertsetting', 'id'); // tablename, primarykey
    }

}

?>
