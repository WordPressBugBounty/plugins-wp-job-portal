<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Give every front-end shortcode somewhere to live.
 *
 * A shortcode is only half a feature. The other half is a page carrying it,
 * and until now Core created exactly one - the jobseeker control panel, in
 * WPJOBPORTALactivation::insertMenu(). The employer control panel, the job
 * list and everything else came from the post-installation wizard, and only
 * if the admin ticked the box. Anything shipped after that wizard was written,
 * or shipped by an add-on, got no page at all.
 *
 * That is not a cosmetic gap. A shortcode with no page is a feature that
 * exists, passes its tests, answers its handlers, and cannot be reached:
 *
 *   [wpjobportal_candidate_portal]  the candidate's only view of their own
 *                                   application. Every message, action request
 *                                   and interview invite the hiring team sends
 *                                   lands here, so with no page the team is
 *                                   writing to an address nobody can open.
 *   [wpjobportal_recruit_team]      an employer adding a recruiter to their own
 *                                   company. The handler behind it has always
 *                                   accepted a front-end post - only wp-admin
 *                                   ever rendered the form, so adding a
 *                                   colleague needed an administrator who
 *                                   should not have been involved.
 *   [wpjobportal_registration]      signing up. With no page an account can
 *                                   only be made by an administrator, and the
 *                                   two above stay out of reach of the person
 *                                   who has not got one yet.
 *
 * The rule here is deliberately timid, because creating pages on somebody
 * else's live site is not a thing to be casual about:
 *
 *   - a shortcode already carried by a page - in any status, trash included -
 *     is left alone, whatever that page is called and wherever it lives;
 *   - a shortcode is considered exactly once. After that the decision is
 *     written to the option below and never revisited, so an admin who deletes
 *     a page we made gets to keep it deleted;
 *   - add-ons contribute through the filter rather than being listed here, so
 *     a page appears when its feature does and never before.
 *
 * @see WPJOBPORTALactivation::insertMenu() for the one page that predates this.
 */
class WPJOBPORTAL_Page_Provisioning_Service {

    /**
     * Shortcode => page id we settled on. A key present here means the question
     * has been answered and must not be asked again.
     */
    const OPTION = 'wpjobportal_provisioned_pages';

    public static function register() {
        add_action('admin_init', array(__CLASS__, 'ensure'));
    }

    /**
     * The pages Core itself owns.
     *
     * Registration only. The control panels are created by activation and by
     * the post-installation wizard, and listing them again here would fight
     * with both.
     *
     * @return array
     */
    private static function core_pages() {
        return array(
            'wpjobportal_registration' => array(
                'slug' => 'wp-job-portal-registration',
                'title' => __('Register', 'wp-job-portal'),
            ),
        );
    }

    /**
     * Every page wanting to exist, Core's and the add-ons'.
     *
     * @return array shortcode => array with slug and title
     */
    public static function catalogue() {
        /**
         * Filter the pages WP Job Portal will create if nothing carries them yet.
         *
         * Add-ons register their own front-end shortcodes here. Each entry is
         * keyed by shortcode name - without brackets - and provides a slug and
         * a title. An entry is acted on once, ever.
         *
         * @param array $pages shortcode => array with slug and title.
         */
        $pages = apply_filters('wpjobportal_pages_to_provision', self::core_pages());

        $clean = array();
        foreach ((array) $pages as $shortcode => $page) {
            $shortcode = sanitize_key($shortcode);
            if ($shortcode === '' || empty($page['slug']) || empty($page['title'])) {
                continue;
            }
            $clean[$shortcode] = array(
                'slug' => sanitize_title($page['slug']),
                'title' => sanitize_text_field($page['title']),
            );
        }

        return $clean;
    }

    /**
     * Settle any shortcode that has not been settled before.
     *
     * Cheap enough for admin_init: one option read, and a database lookup only
     * for shortcodes never seen, which after the first run is none.
     *
     * @return array shortcode => page id, for whatever was decided this time.
     */
    public static function ensure() {
        $settled = get_option(self::OPTION, array());
        if (!is_array($settled)) {
            $settled = array();
        }

        $decided = array();
        foreach (self::catalogue() as $shortcode => $page) {
            if (array_key_exists($shortcode, $settled)) {
                continue;
            }

            $existing = self::page_carrying($shortcode);
            if ($existing > 0) {
                $settled[$shortcode] = $existing;
                $decided[$shortcode] = $existing;
                continue;
            }

            $created = wp_insert_post(array(
                'post_name' => $page['slug'],
                'post_title' => $page['title'],
                'post_status' => 'publish',
                'post_content' => '[' . $shortcode . ']',
                'post_type' => 'page',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
            ));

            // A failure is not recorded: the next admin request tries again,
            // which is right for a transient problem and harmless otherwise.
            if (is_wp_error($created) || !$created) {
                continue;
            }

            $settled[$shortcode] = absint($created);
            $decided[$shortcode] = absint($created);
        }

        if (!empty($decided)) {
            update_option(self::OPTION, $settled, false);

            /*
             * Some features need to be told which page they ended up on -
             * the Recruit Suite keeps a candidate-portal page id that its
             * e-mails link to, and an unset one sends candidates to the
             * wrong screen. Announcing it here means nobody has to find a
             * settings field and type a page number into it.
             */
            foreach ($decided as $shortcode => $page_id) {
                do_action('wpjobportal_page_provisioned', $shortcode, absint($page_id));
            }

            // New page, new permalink. insertMenu() does the same thing the
            // same way, rather than a full flush during an ordinary request.
            update_option('rewrite_rules', '');
        }

        return $decided;
    }

    /**
     * The page already carrying a shortcode, if there is one.
     *
     * Any status counts, trash included. A trashed page means somebody decided
     * they did not want it, and making a fresh one would overrule them.
     *
     * @param string $shortcode Shortcode name, no brackets.
     * @return int Page id, or 0.
     */
    public static function page_carrying($shortcode) {
        global $wpdb;

        $needle = '%[' . $wpdb->esc_like($shortcode) . '%';

        return absint(wpjobportaldb_direct::get_var($wpdb->prepare(
            "SELECT ID FROM `{$wpdb->posts}`
              WHERE post_type = 'page' AND post_content LIKE %s
              ORDER BY ID ASC LIMIT 1",
            $needle
        )));
    }
}
