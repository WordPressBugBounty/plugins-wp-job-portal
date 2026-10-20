<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALjobalertcitiesTable extends WPJOBPORTALtable {

    public $id = '';
    public $alertid = '';
    public $cityid = '';
    public $serverid = '';

    public function check() {
        if ($this->alertid == '') {
            return false;
        }

        return true;
    }

    function __construct() {
        parent::__construct('jobalertcities', 'id'); // tablename, primarykey
    }

}

?>