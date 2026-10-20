<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * RSS feeds for jobs and resumes.
 *
 * Absorbed from the RSS Feed add-on. Registers the feed endpoints
 * and the discovery links in the document head.
 *
 * Hook names, routes and output are preserved exactly, and the callbacks are
 * carried over unchanged, so a site that removes the old add-on sees no
 * difference. Registration is skipped entirely while that add-on is still
 * installed; see WPJOBPORTAL_Absorbed_Features.
 */
final class WPJOBPORTAL_Feature_rssfeedback {

    /**
     * Feed tuning carried over from the add-on.
     *
     * These were class properties on the old plugin, and the callbacks below
     * read them. Extracting the methods without them left the reads pointing at
     * nothing, which throws mid-render and silently truncates whatever page is
     * being built.
     */
    public static $rss_job_description_mode = 'summary';
    public static $rss_job_summary_word_limit = 70;
    public static $rss_resume_summary_word_limit = 60;
    public static $rss_job_include_company_logo = true;
    public static $rss_resume_feed_requires_login = true;
    public static $rss_resume_include_file_enclosures = false;

    public static function register() {
        $feature = new self();

        add_action('wpjobportal_addons_mystuff_employer_dashboard_side_menue', array($feature, 'wpjobportal_dashboard_link_for_rss_feedback'), 10, 2);
        add_action('init', array($feature, 'add_wpjobportal_jobsFeed'), 1, 1);
        add_action('init', array($feature, 'add_wpjobportal_resumesFeed'), 1, 1);
        add_action('wp_head', array($feature, 'wpjobportal_rss_feed_discovery_links'), 10, 1);
        add_action('wpjobportal_addons_jobseeker_dashboard_bottom_btn_rss', array($feature, 'wpjobportal_addons_jobseeker_rssFeed'), 10, 1);
    }

    /* ------------------------------------------------------------------ */
    /* Callbacks carried over unchanged from the add-on                    */
    /* ------------------------------------------------------------------ */

    public function wpjobportal_dashboard_link_for_rss_feedback($data, $elegantdesign = ''){
        $html = '';
        if(!empty($data)){
            $data = wpjobportal_employercheckLinks('empresume_rss');
            if ($data) {
                $rss_enabled = wpjobportal::$_config->getConfigurationByConfigName('resume_rss');
                if ($rss_enabled && $this->is_resume_feed_allowed()) {
                    if ($elegantdesign == 1) {
                        $url = wpjobportal_elegant_design_url();
                    } else {
                        $url = WPJOBPORTAL_PLUGIN_URL;
                    }
                    $html = '<div class="wjportal-cp-list">
                            <a class="wjportal-list-anchor" target="_blank" href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('feed'=>'wpjobportal_resumesfeed', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()))) .'>'. wp_kses(wpjobportal_cp_menu_icon('rss'), WPJOBPORTAL_ALLOWED_TAGS) .'<span class="wjportal-cp-link-text">'. esc_html__('Resume RSS', 'wp-job-portal') .'</span></a>
                         </div>';
                }
            }
        }
        echo wp_kses($html, WPJOBPORTAL_ALLOWED_TAGS);
    }

    public function add_wpjobportal_jobsFeed() {
        add_feed('wpjobportal_jobsfeed', array($this, 'show_wpjobportal_jobsfeed'));
    }

    public function add_wpjobportal_resumesFeed() {
        add_feed('wpjobportal_resumesfeed', array($this, 'show_wpjobportal_resumesfeed'));
    }

    public function wpjobportal_rss_feed_discovery_links() {
        if (!class_exists('wpjobportal')) {
            return;
        }
        $job_rss = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('job_rss');
        if ($job_rss == 1) {
            echo '<link rel="alternate" type="application/rss+xml" title="' . esc_attr(get_bloginfo('name') . ' ' . __('Jobs Feed', 'wp-job-portal')) . '" href="' . esc_url(get_feed_link('wpjobportal_jobsfeed')) . '" />' . "\n";
        }
        $resume_rss = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('resume_rss');
        if ($resume_rss == 1 && $this->is_resume_feed_allowed()) {
            echo '<link rel="alternate" type="application/rss+xml" title="' . esc_attr(get_bloginfo('name') . ' ' . __('Resumes Feed', 'wp-job-portal')) . '" href="' . esc_url(get_feed_link('wpjobportal_resumesfeed')) . '" />' . "\n";
        }
    }

    public function wpjobportal_addons_jobseeker_rssFeed($elegantdesign = ''){
        $print = wpjobportal_jobseekercheckLinks('wpjobportal_rss');
        if ($print) {
            $rss_enabled = wpjobportal::$_config->getConfigurationByConfigName('job_rss');
            if ($rss_enabled) {
                if ($elegantdesign == 1) {
                    $url = wpjobportal_elegant_design_url();
                } else {
                    $url = WPJOBPORTAL_PLUGIN_URL;
                }
                echo '<div class="wjportal-cp-list">
                    <a class="wjportal-list-anchor" target="_blank" href='.esc_url(wpjobportal::wpjobportal_makeUrl(array('feed'=>'wpjobportal_jobsfeed', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()))).'>'. wp_kses(wpjobportal_cp_menu_icon('rss'), WPJOBPORTAL_ALLOWED_TAGS) .'<span class="wjportal-cp-link-text">'. esc_html__('Job RSS', 'wp-job-portal').'</span></a>
                </div>';
            }
        }
    }

    public function is_resume_feed_allowed() {
        $requires_login = apply_filters('wpjobportal_rss_resume_feed_requires_login', self::$rss_resume_feed_requires_login);
        if (!$requires_login) {
            return true;
        }
        if (!is_user_logged_in()) {
            return false;
        }
        if (current_user_can('manage_options')) {
            return true;
        }
        if (class_exists('WPJOBPORTALincluder') && method_exists('WPJOBPORTALincluder', 'getObjectClass')) {
            $user = WPJOBPORTALincluder::getObjectClass('user');
            if (is_object($user) && method_exists($user, 'isemployer') && $user->isemployer()) {
                return true;
            }
        }
        return false;
    }


    /* ------------------------------------------------------------------ */
    /* Feed rendering, carried over from the add-on                        */
    /*                                                                     */
    /* The absorption took the add_feed() registrations and the properties */
    /* these read, but not the callbacks themselves, so requesting either  */
    /* feed fataled on a method that did not exist. Copied verbatim rather */
    /* than rewritten: the output is parsed by feed readers, and this is   */
    /* the markup they have been consuming.                                */
    /* ------------------------------------------------------------------ */

    public function show_wpjobportal_jobsfeed($for_comments) {
        $config_array = WPJOBPORTALincluder::getJSModel('configuration')->getConfigByFor('rss');
        $jobs = WPJOBPORTALincluder::getJSModel('rssfeedback')->getrssjobs();
        if ($jobs === false) {
            $jobs = array();
        }

        $title = $this->get_config_value($config_array, 'rss_job_title', get_bloginfo('name') . ' Jobs');
        $description = $this->get_config_value($config_array, 'rss_job_description', __('Latest jobs from the job board.', 'wp-job-portal'));
        $this->send_rss_headers();
        $this->open_rss_channel('wpjobportal_jobsfeed', $title, $description, $config_array, 'job');

        foreach ($jobs AS $job) {
            if (empty($job->title)) {
                continue;
            }
            $itemlink = $this->get_job_item_link($job);
            echo '<item>' . "\n";
            echo '<title>' . esc_xml(wp_strip_all_tags((string) $job->title)) . '</title>' . "\n";
            echo '<link>' . esc_url($itemlink) . '</link>' . "\n";
            echo '<guid isPermaLink="true">' . esc_url($itemlink) . '</guid>' . "\n";
            echo '<pubDate>' . esc_xml($this->rss_date($this->get_best_job_date($job))) . '</pubDate>' . "\n";
            if (!empty($job->cat_title) && (int) $this->get_config_value($config_array, 'rss_job_categories', 1) == 1) {
                echo '<category>' . esc_xml(wp_strip_all_tags((string) $job->cat_title)) . '</category>' . "\n";
            }
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CDATA body, filtered by sanitize_cdata_html() (wp_kses_post); entity-escaping would show as text in feed readers.
            echo '<description><![CDATA[' . $this->sanitize_cdata_html($this->build_job_description($job, $config_array)) . ']]></description>' . "\n";
            $content_encoded = $this->build_job_content_encoded($job, $config_array);
            if ($content_encoded != '') {
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CDATA body, filtered by sanitize_cdata_html() (wp_kses_post); entity-escaping would show as text in feed readers.
                echo '<content:encoded><![CDATA[' . $this->sanitize_cdata_html($content_encoded) . ']]></content:encoded>' . "\n";
            }
            echo '</item>' . "\n";
        }

        $this->close_rss_channel();
    }

    public function show_wpjobportal_resumesfeed($for_comments) {
        $config_array = WPJOBPORTALincluder::getJSModel('configuration')->getConfigByFor('rss');
        $title = $this->get_config_value($config_array, 'rss_resume_title', get_bloginfo('name') . ' Resumes');
        $description = $this->get_config_value($config_array, 'rss_resume_description', __('Latest searchable resumes from the job board.', 'wp-job-portal'));

        $this->send_rss_headers();
        $this->open_rss_channel('wpjobportal_resumesfeed', $title, $description, $config_array, 'resume');

        if (!$this->is_resume_feed_allowed()) {
            $this->close_rss_channel();
            return;
        }

        $resumes = WPJOBPORTALincluder::getJSModel('rssfeedback')->getrssresumes();
        if ($resumes === false) {
            $resumes = array();
        }

        foreach ($resumes AS $result) {
            if (empty($result->application_title)) {
                continue;
            }
            $itemlink = $this->get_resume_item_link($result);
            echo '<item>' . "\n";
            echo '<title>' . esc_xml(wp_strip_all_tags((string) $result->application_title)) . '</title>' . "\n";
            echo '<link>' . esc_url($itemlink) . '</link>' . "\n";
            echo '<guid isPermaLink="true">' . esc_url($itemlink) . '</guid>' . "\n";
            echo '<pubDate>' . esc_xml($this->rss_date($this->get_best_resume_date($result))) . '</pubDate>' . "\n";
            if (!empty($result->cat_title) && (int) $this->get_config_value($config_array, 'rss_resume_categories', 1) == 1) {
                echo '<category>' . esc_xml(wp_strip_all_tags((string) $result->cat_title)) . '</category>' . "\n";
            }
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CDATA body, filtered by sanitize_cdata_html() (wp_kses_post); entity-escaping would show as text in feed readers.
            echo '<description><![CDATA[' . $this->sanitize_cdata_html($this->build_resume_description($result, $config_array)) . ']]></description>' . "\n";

            $allow_enclosures = apply_filters('wpjobportal_rss_resume_include_file_enclosures', self::$rss_resume_include_file_enclosures);
            if ($allow_enclosures && (int) $this->get_config_value($config_array, 'rss_resume_file', 0) == 1 && !empty($result->filename)) {
                $wpdir = wp_upload_dir();
                $data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
                foreach ($result->filename AS $file) {
                    if (empty($file->filename)) {
                        continue;
                    }
                    $filelink = $wpdir['baseurl'] . '/' . $data_directory . '/data/jobseeker/resume_' . $result->id . '/resume/' . $file->filename;
                    echo '<enclosure url="' . esc_url($filelink) . '" length="' . esc_attr(absint($file->filesize)) . '" type="' . esc_attr($this->get_enclosure_mime_type($file->filetype)) . '" />' . "\n";
                }
            }
            echo '</item>' . "\n";
        }

        $this->close_rss_channel();
    }

    public function send_rss_headers() {
        if (!headers_sent()) {
            status_header(200);
            // Use text/xml so common browsers open the feed as readable XML instead of downloading it.
            header('Content-Type: text/xml; charset=' . get_option('blog_charset'), true);
        }
    }

    public function open_rss_channel($feed_name, $title, $description, $config_array, $type) {
        echo '<?xml version="1.0" encoding="' . esc_attr(get_option('blog_charset')) . '"?' . '>' . "\n";
        echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/">' . "\n";
        echo '<channel>' . "\n";
        echo '<title>' . esc_xml(wp_strip_all_tags((string) $title)) . '</title>' . "\n";
        echo '<link>' . esc_url(home_url('/')) . '</link>' . "\n";
        echo '<atom:link href="' . esc_url(get_feed_link($feed_name)) . '" rel="self" type="application/rss+xml" />' . "\n";
        echo '<description>' . esc_xml(wp_strip_all_tags((string) $description)) . '</description>' . "\n";
        echo '<language>' . esc_xml(wp_strip_all_tags((string) get_bloginfo('language'))) . '</language>' . "\n";
        echo '<lastBuildDate>' . esc_xml(gmdate('D, d M Y H:i:s +0000', current_time('timestamp', true))) . '</lastBuildDate>' . "\n";
        echo '<generator>WP Job Portal RSS</generator>' . "\n";
        echo '<docs>https://www.rssboard.org/rss-specification</docs>' . "\n";

        $ttl_key = ($type == 'resume') ? 'rss_resume_ttl' : 'rss_job_ttl';
        $ttl = absint($this->get_config_value($config_array, $ttl_key, 60));
        if ($ttl < 1) {
            $ttl = 60;
        }
        echo '<ttl>' . esc_html($ttl) . '</ttl>' . "\n";

        $copyright_key = ($type == 'resume') ? 'rss_resume_copyright' : 'rss_job_copyright';
        $editor_key = ($type == 'resume') ? 'rss_resume_editor' : 'rss_job_editor';
        $webmaster_key = ($type == 'resume') ? 'rss_resume_webmaster' : 'rss_job_webmaster';

        $copyright = $this->get_config_value($config_array, $copyright_key, '');
        if ($copyright != '') {
            echo '<copyright>' . esc_xml(wp_strip_all_tags((string) $copyright)) . '</copyright>' . "\n";
        }
        $editor = $this->get_config_value($config_array, $editor_key, '');
        if ($editor != '') {
            echo '<managingEditor>' . esc_xml(wp_strip_all_tags((string) $editor)) . '</managingEditor>' . "\n";
        }
        $webmaster = $this->get_config_value($config_array, $webmaster_key, '');
        if ($webmaster != '') {
            echo '<webMaster>' . esc_xml(wp_strip_all_tags((string) $webmaster)) . '</webMaster>' . "\n";
        }
    }

    public function close_rss_channel() {
        echo '</channel>' . "\n";
        echo '</rss>' . "\n";
    }

    public function build_job_description($job, $config_array) {
        $description = '';

        if (self::$rss_job_include_company_logo && (int) $this->get_config_value($config_array, 'rss_job_image', 1) == 1 && !empty($job->logofilename)) {
            $wpdir = wp_upload_dir();
            $data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
            $imagelink = $wpdir['baseurl'] . '/' . $data_directory . '/data/employer/comp_' . $job->companyid . '/logo/' . $job->logofilename;
            $description .= '<p><img src="' . esc_url($imagelink) . '" width="100" alt="' . esc_attr($job->comp_title) . '" /></p>';
        }

        $mode = $this->get_job_description_mode($config_array);
        if ($mode == 'full') {
            $full_description = $this->get_clean_full_html(isset($job->description) ? $job->description : '');
            if ($full_description != '') {
                $this->append_feed_html_section($description, __('Description', 'wp-job-portal'), $full_description);
            }
        } else {
            $excerpt = $this->get_clean_excerpt(isset($job->description) ? $job->description : '', self::$rss_job_summary_word_limit);
            if ($excerpt != '') {
                $this->append_feed_row($description, __('Summary', 'wp-job-portal'), $excerpt);
            }
        }

        $this->append_feed_row($description, __('Company', 'wp-job-portal'), isset($job->comp_title) ? $job->comp_title : '');
        $this->append_feed_row($description, __('Location', 'wp-job-portal'), isset($job->locations) ? $job->locations : '');
        $this->append_feed_row($description, __('Category', 'wp-job-portal'), isset($job->cat_title) ? $job->cat_title : '');
        $this->append_feed_row($description, __('Job Type', 'wp-job-portal'), isset($job->jobtype) ? $job->jobtype : '');
        $this->append_feed_row($description, __('Job Status', 'wp-job-portal'), isset($job->jobstatus) ? $job->jobstatus : '');
        $this->append_feed_row($description, __('Number of Jobs', 'wp-job-portal'), isset($job->noofjobs) && (int) $job->noofjobs > 0 ? (int) $job->noofjobs : '');

        if (empty($job->hidesalaryrange) && !empty($job->salarytype)) {
            // No figure on file means no salary line at all - the rate suffix
            // on its own would read as "Salary: / Per Month".
            $salary = (string) wpjobportal::$_common->getSalaryRangeView($job->salarytype, $job->salarymin, $job->salarymax, $job->currency);
            if ($salary !== '' && !empty($job->srangetypetitle)) {
                $salary .= ' / ' . $job->srangetypetitle;
            }
            $this->append_feed_row($description, __('Salary', 'wp-job-portal'), $salary, true);
        }

        $this->append_feed_row($description, __('Posted', 'wp-job-portal'), $this->display_date($this->get_best_job_date($job)));
        $this->append_feed_row($description, __('Closing Date', 'wp-job-portal'), $this->display_date(isset($job->stoppublishing) ? $job->stoppublishing : ''));
        $this->append_feed_link($description, __('View / Apply', 'wp-job-portal'), $this->get_job_item_link($job));

        return apply_filters('wpjobportal_rss_job_item_description', $description, $job, $config_array);
    }

    public function build_job_content_encoded($job, $config_array) {
        $mode = $this->get_job_description_mode($config_array);
        if ($mode != 'both') {
            return '';
        }

        $content = '';
        $full_description = $this->get_clean_full_html(isset($job->description) ? $job->description : '');
        if ($full_description != '') {
            $this->append_feed_html_section($content, __('Full Job Description', 'wp-job-portal'), $full_description);
        }

        $this->append_feed_row($content, __('Company', 'wp-job-portal'), isset($job->comp_title) ? $job->comp_title : '');
        $this->append_feed_row($content, __('Location', 'wp-job-portal'), isset($job->locations) ? $job->locations : '');
        $this->append_feed_row($content, __('Category', 'wp-job-portal'), isset($job->cat_title) ? $job->cat_title : '');
        $this->append_feed_row($content, __('Job Type', 'wp-job-portal'), isset($job->jobtype) ? $job->jobtype : '');
        $this->append_feed_row($content, __('Job Status', 'wp-job-portal'), isset($job->jobstatus) ? $job->jobstatus : '');

        if (empty($job->hidesalaryrange) && !empty($job->salarytype)) {
            // No figure on file means no salary line at all - the rate suffix
            // on its own would read as "Salary: / Per Month".
            $salary = (string) wpjobportal::$_common->getSalaryRangeView($job->salarytype, $job->salarymin, $job->salarymax, $job->currency);
            if ($salary !== '' && !empty($job->srangetypetitle)) {
                $salary .= ' / ' . $job->srangetypetitle;
            }
            $this->append_feed_row($content, __('Salary', 'wp-job-portal'), $salary, true);
        }

        $this->append_feed_row($content, __('Posted', 'wp-job-portal'), $this->display_date($this->get_best_job_date($job)));
        $this->append_feed_row($content, __('Closing Date', 'wp-job-portal'), $this->display_date(isset($job->stoppublishing) ? $job->stoppublishing : ''));
        $this->append_feed_link($content, __('View / Apply', 'wp-job-portal'), $this->get_job_item_link($job));

        return apply_filters('wpjobportal_rss_job_content_encoded', $content, $job, $config_array);
    }

    public function get_job_description_mode($config_array) {
        $mode = strtolower(trim((string) apply_filters('wpjobportal_rss_job_description_mode', self::$rss_job_description_mode, $config_array)));
        $allowed_modes = array('summary', 'both', 'full');
        if (!in_array($mode, $allowed_modes)) {
            $mode = 'summary';
        }
        return $mode;
    }

    public function build_resume_description($result, $config_array) {
        $description = '';

        if ((int) $this->get_config_value($config_array, 'rss_resume_image', 0) == 1 && !empty($result->photo)) {
            $wpdir = wp_upload_dir();
            $data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
            $imagelink = $wpdir['baseurl'] . '/' . $data_directory. '/data/jobseeker/resume_' . $result->id . '/photo/' . $result->photo;
            $description .= '<p><img src="' . esc_url($imagelink) . '" width="100" alt="' . esc_attr($result->application_title) . '" /></p>';
        }

        $summary = $this->get_clean_excerpt(isset($result->resume) ? $result->resume : '', self::$rss_resume_summary_word_limit);
        if ($summary != '') {
            $this->append_feed_row($description, __('Summary', 'wp-job-portal'), $summary);
        }

        $this->append_feed_row($description, __('Category', 'wp-job-portal'), isset($result->cat_title) ? $result->cat_title : '');
        $this->append_feed_row($description, __('Total Experience', 'wp-job-portal'), WPJOBPORTALincluder::getJSModel('common')->getTotalExp($result->id));

        if ((int) $this->get_config_value($config_array, 'rss_resume_email', 0) == 1) {
            $this->append_feed_row($description, __('Email Address', 'wp-job-portal'), isset($result->email_address) ? $result->email_address : '');
        }

        $this->append_feed_row($description, __('Posted', 'wp-job-portal'), $this->display_date($this->get_best_resume_date($result)));
        $this->append_feed_link($description, __('View Resume', 'wp-job-portal'), $this->get_resume_item_link($result));

        return apply_filters('wpjobportal_rss_resume_item_description', $description, $result, $config_array);
    }

    public function append_feed_row(&$description, $label, $value, $already_safe = false) {
        if ($value === null || $value === '') {
            return;
        }
        $description .= '<p><strong>' . esc_html($label) . ':</strong> ' . ($already_safe ? wp_kses_post($value) : esc_html(wp_strip_all_tags($value))) . '</p>';
    }

    public function append_feed_html_section(&$description, $label, $html) {
        $html = trim((string) $html);
        if ($html == '') {
            return;
        }
        $description .= '<p><strong>' . esc_html($label) . ':</strong></p>' . $html;
    }

    public function append_feed_link(&$description, $label, $url) {
        if (empty($url)) {
            return;
        }
        $description .= '<p><a href="' . esc_url($url) . '">' . esc_html($label) . '</a></p>';
    }

    public function get_job_item_link($job) {
        return wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'viewjob', 'wpjobportalid'=>$job->jobaliasid, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
    }

    public function get_resume_item_link($result) {
        return wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'viewresume', 'wpjobportalid'=>$result->resumealiasid, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
    }

    public function get_clean_excerpt($content, $word_limit) {
        $content = strip_shortcodes((string) $content);
        $content = wp_strip_all_tags($content, true);
        $content = trim(preg_replace('/\s+/', ' ', $content));
        if ($content == '') {
            return '';
        }
        $word_limit = absint(apply_filters('wpjobportal_rss_description_word_limit', $word_limit));
        if ($word_limit < 1) {
            $word_limit = 70;
        }
        return wp_trim_words($content, $word_limit, '...');
    }

    public function get_clean_full_html($content) {
        $content = strip_shortcodes((string) $content);
        $content = preg_replace('#<(script|style|iframe|object|embed|form|input|button|textarea|select)[^>]*>.*?</\1>#is', '', $content);
        $content = preg_replace('#<(script|style|iframe|object|embed|form|input|button|textarea|select)[^>]*>#is', '', $content);
        $content = trim($content);
        if ($content == '') {
            return '';
        }

        $allowed_tags = array(
            'p' => array(),
            'br' => array(),
            'ul' => array(),
            'ol' => array(),
            'li' => array(),
            'strong' => array(),
            'b' => array(),
            'em' => array(),
            'i' => array(),
            'u' => array(),
            'h2' => array(),
            'h3' => array(),
            'h4' => array(),
            'blockquote' => array(),
            'a' => array(
                'href' => true,
                'title' => true,
                'target' => true,
                'rel' => true,
            ),
            'table' => array(),
            'thead' => array(),
            'tbody' => array(),
            'tr' => array(),
            'th' => array(),
            'td' => array(),
        );

        if ($content === wp_strip_all_tags($content, true)) {
            $content = wpautop(esc_html($content));
        }

        $content = wp_kses($content, apply_filters('wpjobportal_rss_full_description_allowed_tags', $allowed_tags));
        if (function_exists('balanceTags')) {
            $content = balanceTags($content, true);
        }
        return trim($content);
    }

    public function sanitize_cdata_html($html) {
        $html = wp_kses_post($html);
        return str_replace(']]>', ']]]]><![CDATA[>', $html);
    }

    public function xml_escape($value) {
        return esc_html(wp_strip_all_tags((string) $value));
    }

    public function get_config_value($config_array, $key, $default = '') {
        if (is_array($config_array) && isset($config_array[$key])) {
            return $config_array[$key];
        }
        return $default;
    }

    public function get_best_job_date($job) {
        if (!empty($job->startpublishing) && $job->startpublishing != '0000-00-00 00:00:00') {
            return $job->startpublishing;
        }
        if (!empty($job->created) && $job->created != '0000-00-00 00:00:00') {
            return $job->created;
        }
        if (!empty($job->modified) && $job->modified != '0000-00-00 00:00:00') {
            return $job->modified;
        }
        return current_time('mysql');
    }

    public function get_best_resume_date($resume) {
        if (!empty($resume->created) && $resume->created != '0000-00-00 00:00:00') {
            return $resume->created;
        }
        if (!empty($resume->last_modified) && $resume->last_modified != '0000-00-00 00:00:00') {
            return $resume->last_modified;
        }
        return current_time('mysql');
    }

    public function rss_date($date) {
        $timestamp = strtotime((string) $date);
        if (!$timestamp) {
            $timestamp = current_time('timestamp', true);
            return gmdate('D, d M Y H:i:s +0000', $timestamp);
        }
        return mysql2date('D, d M Y H:i:s +0000', get_gmt_from_date(gmdate('Y-m-d H:i:s', $timestamp)), false);
    }

    public function display_date($date) {
        $timestamp = strtotime((string) $date);
        if (!$timestamp || $date == '0000-00-00 00:00:00') {
            return '';
        }
        return date_i18n(get_option('date_format'), $timestamp);
    }

    public function get_enclosure_mime_type($filetype) {
        $filetype = strtolower(trim((string) $filetype));
        $map = array(
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'rtf' => 'application/rtf',
            'txt' => 'text/plain'
        );
        return isset($map[$filetype]) ? $map[$filetype] : 'application/octet-stream';
    }

    public function checkLinks($name,$fieldfor = null) {
        if($fieldfor == 3){
            wpjobportal::$wpjobportal_data['fields'] = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldsOrderingforForm(3);
        }else{
            wpjobportal::$wpjobportal_data['fields'] = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldsOrderingforForm(2);
        }

        foreach (wpjobportal::$wpjobportal_data['fields'] as $field) {
            $array =  array();
            $array[0] = 0;
            switch ($field->field) {
                case $name:
                    if($field->showonlisting == 1){
                        $array[0] = 1;
                        $array[1] =  $field->fieldtitle;
                    }
                return $array;
                break;
            }
        }
        return $array;
    }
}
