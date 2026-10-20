<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALupdates {

    /**
     * Bring the database up to the running plugin version.
     *
     * This used to read and execute the .sql files itself, and it read them
     * wrongly: it joined a statement's lines with implode('') - no separator -
     * and treated a semicolon at the end of a comment line as the end of a
     * statement. Every migration through 260 was written one statement per
     * line, so nothing ever glued and nothing ever showed. 270 is the first
     * that is not, and under the old reader its multi-line statements arrived
     * as things like "FROM DUALWHERE NOT EXISTS" - a syntax error, against a
     * query() whose result was never checked, so the settings those statements
     * create would simply have been missing with nothing said.
     *
     * The migration service is now the only thing that runs these files. It
     * reads them correctly, records each one in a ledger with its checksum so
     * a repeated visit cannot re-run it, and reports a failure instead of
     * swallowing it.
     *
     * @param int|string|null $cversion Target version, defaults to the running one.
     * @return void
     */
    static function checkUpdates($cversion=null) {
        if (is_null($cversion)) {
            $cversion = wpjobportal::$_currentversion;
        }

        $wpjobportal_installedversion = WPJOBPORTALupdates::getInstalledVersion();
        if ($wpjobportal_installedversion == $cversion) {
            return;
        }

        /*
         * Remember which version we are coming from before anything moves.
         * The migration service reads last_version when it reconciles the
         * migrations that predate its ledger.
         */
        $query = "REPLACE INTO `".wpjobportal::$_db->prefix."wj_portal_config` (`configname`, `configvalue`, `configfor`) VALUES ('last_version','','default');";
        wpjobportal::$_db->query($query);
        $query = "SELECT configvalue FROM `".wpjobportal::$_db->prefix."wj_portal_config` WHERE configname='versioncode'";
        $wpjobportal_versioncode = wpjobportal::$_db->get_var($query);
        $wpjobportal_versioncode = wpjobportalphplib::wpJP_str_replace('.','',$wpjobportal_versioncode);
        $query = wpjobportal::$_db->prepare("UPDATE `".wpjobportal::$_db->prefix."wj_portal_config` SET configvalue = %s WHERE configname = 'last_version';", $wpjobportal_versioncode);
        wpjobportal::$_db->query($query);

        if (class_exists('WPJOBPORTAL_Migration_Service')) {
            WPJOBPORTAL_Migration_Service::run($cversion);
        }
    }

    static function getInstalledVersion() {
        $query = "SELECT configvalue FROM `" . wpjobportal::$_db->prefix . "wj_portal_config` WHERE configname = 'versioncode'";
        $wpjobportal_version = wpjobportal::$_db->get_var($query);
        if (!$wpjobportal_version)
            $wpjobportal_version = '100';
        else
            $wpjobportal_version = wpjobportalphplib::wpJP_str_replace('.', '', $wpjobportal_version);
        return $wpjobportal_version;
    }

}

?>
