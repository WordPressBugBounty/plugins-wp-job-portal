<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALsitemap {

    const QUERY_VAR = 'wpjobportal_jobs_sitemap';
    const SITEMAP_PATH = 'wp-job-portal-jobs-sitemap.xml';

    public static function init() {
        add_action('init', array(__CLASS__, 'addRewriteRule'));
        add_filter('query_vars', array(__CLASS__, 'addQueryVar'));
        add_action('template_redirect', array(__CLASS__, 'maybeRenderSitemap'), 0);
        add_filter('robots_txt', array(__CLASS__, 'addRobotsTxtSitemap'), 10, 2);
        add_filter('wpseo_sitemap_index', array(__CLASS__, 'addYoastSitemapIndex'));
    }

    public static function addRewriteRule() {
        add_rewrite_rule('^' . preg_quote(self::SITEMAP_PATH, '/') . '$', 'index.php?' . self::QUERY_VAR . '=1', 'top');
    }

    public static function addQueryVar($vars) {
        $vars[] = self::QUERY_VAR;
        return $vars;
    }

    public static function getSitemapUrl() {
        return home_url('/' . self::SITEMAP_PATH);
    }

    public static function maybeRenderSitemap() {
        $query_var = get_query_var(self::QUERY_VAR);
        $request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        $is_direct_path = (false !== strpos($request_uri, '/' . self::SITEMAP_PATH));
        $is_query_request = isset($_GET[self::QUERY_VAR]) && absint($_GET[self::QUERY_VAR]) === 1;

        if (!$query_var && !$is_direct_path && !$is_query_request) {
            return;
        }

        if (!self::isEnabled()) {
            status_header(404);
            nocache_headers();
            exit;
        }

        self::renderSitemap();
    }

    public static function addRobotsTxtSitemap($output, $public) {
        if (!$public || !self::isEnabled()) {
            return $output;
        }

        $sitemap_url = self::getSitemapUrl();
        if (false === strpos($output, $sitemap_url)) {
            $output = rtrim($output) . "\nSitemap: " . $sitemap_url . "\n";
        }
        return $output;
    }

    public static function addYoastSitemapIndex($sitemap_index) {
        if (!self::isEnabled()) {
            return $sitemap_index;
        }

        $lastmod = self::getLatestJobLastmod();
        if (empty($lastmod)) {
            return $sitemap_index;
        }

        $sitemap_index .= "\n<sitemap>\n";
        $sitemap_index .= '<loc>' . esc_url(self::getSitemapUrl()) . "</loc>\n";
        $sitemap_index .= '<lastmod>' . esc_html($lastmod) . "</lastmod>\n";
        $sitemap_index .= "</sitemap>\n";
        return $sitemap_index;
    }

    private static function isEnabled() {
        $enabled = '1';
        if (isset(wpjobportal::$_config) && is_object(wpjobportal::$_config) && method_exists(wpjobportal::$_config, 'getConfigurationByConfigName')) {
            $config_value = wpjobportal::$_config->getConfigurationByConfigName('job_sitemap_enable');
            if ($config_value !== null && $config_value !== '') {
                $enabled = $config_value;
            }
        }
        return (string) $enabled === '1';
    }

    private static function getLimit() {
        $limit = 5000;
        if (isset(wpjobportal::$_config) && is_object(wpjobportal::$_config) && method_exists(wpjobportal::$_config, 'getConfigurationByConfigName')) {
            $config_value = wpjobportal::$_config->getConfigurationByConfigName('job_sitemap_limit');
            if (is_numeric($config_value)) {
                $limit = absint($config_value);
            }
        }
        if ($limit < 1) {
            $limit = 5000;
        }
        if ($limit > 50000) {
            $limit = 50000;
        }
        return $limit;
    }

    private static function getDefaultPageId() {
        $query = "SELECT configvalue FROM `" . wpjobportal::$_db->prefix . "wj_portal_config` WHERE configname = 'default_pageid'";
        return absint(wpjobportal::$_db->get_var($query));
    }

    private static function getPublicJobs() {
        $today = current_time('Y-m-d');
        $limit = self::getLimit();
        $query = wpjobportal::$_db->prepare(
            "SELECT job.id, job.alias, job.created, job.modified
            FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
            LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON company.id = job.companyid
            WHERE job.status = 1
              AND DATE(job.startpublishing) <= %s
              AND DATE(job.stoppublishing) >= %s
              AND (company.status = 1 OR company.status IS NULL)
            ORDER BY job.modified DESC, job.created DESC
            LIMIT %d",
            $today,
            $today,
            $limit
        );
        return wpjobportaldb::get_results($query);
    }

    private static function getLatestJobLastmod() {
        $today = current_time('Y-m-d');
        $query = wpjobportal::$_db->prepare(
            "SELECT job.created, job.modified
            FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
            LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON company.id = job.companyid
            WHERE job.status = 1
              AND DATE(job.startpublishing) <= %s
              AND DATE(job.stoppublishing) >= %s
              AND (company.status = 1 OR company.status IS NULL)
            ORDER BY job.modified DESC, job.created DESC
            LIMIT 1",
            $today,
            $today
        );
        $job = wpjobportaldb::get_row($query);
        if (empty($job)) {
            return '';
        }
        return self::formatLastmod($job);
    }

    private static function formatLastmod($job) {
        $date = '';
        if (!empty($job->modified) && $job->modified !== '0000-00-00 00:00:00') {
            $date = $job->modified;
        } elseif (!empty($job->created) && $job->created !== '0000-00-00 00:00:00') {
            $date = $job->created;
        }
        if (empty($date)) {
            return current_time('c');
        }
        return mysql2date('c', $date, false);
    }

    private static function getJobUrl($job, $page_id) {
        $job_id = absint($job->id);
        if ($job_id < 1) {
            return '';
        }
        $alias = isset($job->alias) ? sanitize_title($job->alias) : '';
        $alias_id = $alias !== '' ? $alias . '-' . $job_id : $job_id;
        return wpjobportal::wpjobportal_makeUrl(array(
            'wpjobportalme' => 'job',
            'wpjobportallt' => 'viewjob',
            'wpjobportalid' => $alias_id,
            'wpjobportalpageid' => $page_id,
        ));
    }

    public static function renderSitemap() {
        while (ob_get_level()) {
            ob_end_clean();
        }

        status_header(200);
        nocache_headers();
        header('Content-Type: application/xml; charset=UTF-8');

        $page_id = self::getDefaultPageId();
        $jobs = self::getPublicJobs();

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        if (!empty($jobs)) {
            foreach ($jobs as $job) {
                $url = self::getJobUrl($job, $page_id);
                if (empty($url)) {
                    continue;
                }
                echo "\t<url>\n";
                echo "\t\t<loc>" . esc_url($url) . "</loc>\n";
                echo "\t\t<lastmod>" . esc_html(self::formatLastmod($job)) . "</lastmod>\n";
                echo "\t</url>\n";
            }
        }

        echo '</urlset>';
        exit;
    }
}
