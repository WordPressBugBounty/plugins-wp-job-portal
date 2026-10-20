<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALtagTable extends WPJOBPORTALtable {

    public $id = '';
    public $tag = '';
    public $alias = '';
    public $tagfor = '';
    public $status = '';
    public $created = '';
    public $createdby = '';

    function __construct() {
        parent::__construct('tags', 'id'); // tablename, primarykey
    }

}

?>