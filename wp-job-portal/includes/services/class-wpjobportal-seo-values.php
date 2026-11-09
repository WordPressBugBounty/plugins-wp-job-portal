<?php
if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * P-2: what job and company addresses are built from, loaded once per request.
 * A list primes all its rows with one query, so a card no longer costs one query
 * per word of its address, twice over.
 */
class WPJOBPORTAL_Seo_Values {

    private static $jobs = array();
    private static $companies = array();
    private static $cities = array();

    /** @param int[] $ids Job ids about to be shown. */
    public static function prime_jobs($ids) {
        $ids = array_diff(array_filter(array_map('absint', (array) $ids)), array_keys(self::$jobs));
        if (empty($ids)) {
            return;
        }
        $db = wpjobportal::$_db;
        $p = $db->prefix . 'wj_portal_';
        $rows = $db->get_results("SELECT job.id, job.title, job.city, job.companyid, category.cat_title AS category, company.name AS company, jt.title AS jobtype
            FROM `" . $p . "jobs` AS job
            LEFT JOIN `" . $p . "categories` AS category ON category.id = job.jobcategory
            " . wpjobportal::$_company_job_table_join . " JOIN `" . $p . "companies` AS company ON company.id = job.companyid
            LEFT JOIN `" . $p . "jobtypes` AS jt ON jt.id = job.jobtype
            WHERE job.id IN (" . implode(',', $ids) . ")");
        $company_ids = array();
        $city_ids = array();
        foreach ($ids as $id) {
            self::$jobs[$id] = null;
        }
        foreach ((array) $rows as $row) {
            self::$jobs[(int) $row->id] = $row;
            $company_ids[] = (int) $row->companyid;
            $city_ids = array_merge($city_ids, explode(',', (string) $row->city));
        }
        self::prime_companies($company_ids);
        self::prime_cities($city_ids);
    }

    /** @param int[] $ids Company ids about to be shown. */
    public static function prime_companies($ids) {
        $ids = array_diff(array_filter(array_map('absint', (array) $ids)), array_keys(self::$companies));
        if (empty($ids)) {
            return;
        }
        $db = wpjobportal::$_db;
        $rows = $db->get_results("SELECT id, name, city FROM `" . $db->prefix . "wj_portal_companies` WHERE id IN (" . implode(',', $ids) . ")");
        $city_ids = array();
        foreach ($ids as $id) {
            self::$companies[$id] = null;
        }
        foreach ((array) $rows as $row) {
            self::$companies[(int) $row->id] = $row;
            $city_ids = array_merge($city_ids, explode(',', (string) $row->city));
        }
        self::prime_cities($city_ids);
    }

    private static function prime_cities($ids) {
        $ids = array_diff(array_filter(array_map('absint', (array) $ids)), array_keys(self::$cities));
        if (empty($ids)) {
            return;
        }
        $db = wpjobportal::$_db;
        foreach ($ids as $id) {
            self::$cities[$id] = null;
        }
        foreach ((array) $db->get_results("SELECT id, name FROM `" . $db->prefix . "wj_portal_cities` WHERE id IN (" . implode(',', $ids) . ")") as $row) {
            self::$cities[(int) $row->id] = $row;
        }
    }

    /** @return object|null With ->col, the way the address builders read it. */
    public static function job($id, $key) {
        $id = (int) $id;
        if (!array_key_exists($id, self::$jobs)) {
            self::prime_jobs(array($id));
        }
        $map = array('title' => 'title', 'category' => 'category', 'company' => 'company', 'jobtype' => 'jobtype', 'location' => 'city');
        $row = isset(self::$jobs[$id]) ? self::$jobs[$id] : null;
        return ($row && isset($map[$key])) ? (object) array('col' => $row->{$map[$key]}) : null;
    }

    /** @return object|null With ->col. */
    public static function company($id, $key) {
        $id = (int) $id;
        if (!array_key_exists($id, self::$companies)) {
            self::prime_companies(array($id));
        }
        $map = array('name' => 'name', 'location' => 'city');
        $row = isset(self::$companies[$id]) ? self::$companies[$id] : null;
        return ($row && isset($map[$key])) ? (object) array('col' => $row->{$map[$key]}) : null;
    }

    /** @return object|null With ->name. */
    public static function city($id) {
        $id = (int) $id;
        if (!array_key_exists($id, self::$cities)) {
            self::prime_cities(array($id));
        }
        return isset(self::$cities[$id]) ? self::$cities[$id] : null;
    }
}
