<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALemployerviewresumeTable extends WPJOBPORTALtable {

    public $id = '';
    public $uid = '';
    public $resumeid = '';
    public $status = '';
    public $created = '';
    public $profileid = '';

    function __construct() {
        parent::__construct('employer_view_resume', 'id'); // tablename, primarykey
    }

}

?>