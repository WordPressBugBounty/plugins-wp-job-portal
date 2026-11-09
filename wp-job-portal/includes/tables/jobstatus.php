<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALjobstatusTable extends WPJOBPORTALtable {

    public $id = '';
    public $title = '';
    public $isactive = '';
    public $isdefault = '';
    public $ordering = '';
    public $serverid = '';
    public $acceptsapplications = 1; // W-2: 0 = closed to applications (Closed, On hold)

    function __construct() {
        parent::__construct('jobstatus', 'id'); // tablename, primarykey
    }

}

?>