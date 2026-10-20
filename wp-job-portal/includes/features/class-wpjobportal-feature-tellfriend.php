<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Email a job to a friend.
 *
 * Absorbed from the Tell a Friend add-on, which §10 folds into sharing.
 * It sits beside Social Share, now also in Core. Honours the existing
 * allow_tellafriend and tell_a_friend_captcha configuration.
 *
 * Hook names, routes and output are preserved exactly, and the callbacks are
 * carried over unchanged, so a site that removes the old add-on sees no
 * difference. Registration is skipped entirely while that add-on is still
 * installed; see WPJOBPORTAL_Absorbed_Features.
 */
final class WPJOBPORTAL_Feature_tellfriend {

    public static function register() {
        $feature = new self();

        add_action('wpjobportal_addons_newestjob_btm_btn_for_tellfriend', array($feature, 'wpjobportal_addons_newestjob_tell_a_friend'), 10, 1);
        add_action('wpjobportal_addons_tellfriend_shorlist', array($feature, 'wpjobportal_addons_shorlist_tellfriend'), 10, 1);
    }

    /* ------------------------------------------------------------------ */
    /* Callbacks carried over unchanged from the add-on                    */
    /* ------------------------------------------------------------------ */

    /**
     * The popup's captcha arrives by AJAX, after the page has loaded, so the
     * captcha script has to be on the page already.
     */
    private function enqueue_popup_captcha() {
        if (WPJOBPORTALincluder::getObjectClass('user')->isguest() && wpjobportal::$_config->getConfigValue('tell_a_friend_captcha') == 1) {
            WPJOBPORTAL_Captcha_Service::enqueue_script();
        }
    }

    public function wpjobportal_addons_newestjob_tell_a_friend($job){
        $this->enqueue_popup_captcha();
        $html = '';
        if (wpjobportal::$wpjobportal_theme_chk == 1) {
            $html = '<a class="wpj-jp-icon-btn" href="#" onclick="getTellaFriend(\''.$job->id.'\',1);" title="'. esc_html__('Tell A Friend','job-portal-theme').'"><i class="far fa-user"></i></a>';
        } elseif(WPJOBPORTALincluder::getJSModel('common')->isElegantDesignEnabled()){
            $html = '<a onclick="getTellaFriend(\''.$job->id.'\');" class="01 wjportal-elegant-addon-job-tell-friend-section" href="#" title="'. esc_html(__("Tell A Friend",'wp-job-portal')). '">
                <span class="wjportal-elegant-addon-job-tell-friend-section-logo">
                    <img class="wjportal-elegant-addon-job-tellfriend-colorlogo" src="'. esc_url(wpjobportal_elegant_design_url()). 'includes/images/tell-a-friend.png" alt="'. esc_html(__('Tell A Friend','wp-job-portal')). '" title="'. esc_html(__('Tell A Friend','wp-job-portal')). '">
                </span>
                <span class="wjportal-elegant-addon-job-tell-friend-section-title">'. esc_html(__("Tell A Friend",'wp-job-portal')) .'</span>
                <p class="wjportal-elegant-addon-job-tell-friend-section-disc">'. esc_html(__("Know someone perfect for this job? Share it with them!",'wp-job-portal')) .'</p>
            </a>';
        } else {
            $html = '<a class="wjportal-job-act-btn-tell  wjportal-list-act-btn-tell-a-friend" href="#" onclick="getTellaFriend(\''.$job->id.'\');">'. __('Tell A Friend','wp-job-portal').'</a>';
        }
        echo wp_kses($html, WPJOBPORTAL_ALLOWED_TAGS);
    }

    public function wpjobportal_addons_shorlist_tellfriend($jobid){
         $this->enqueue_popup_captcha();
         $html = '';
       /* Named, for the same reason as the shortlist button beside it. */
       $html = '<a class="wjportal-jobs-act-btn wjportal-list-act-btn-tell-a-friend" href="#" onclick="getTellaFriend(\''.$jobid.'\');" title="'. esc_js(__('Tell A Friend','wp-job-portal')).'">'. esc_html(__('Tell A Friend','wp-job-portal')) .'</a>';
       //$html = '<a class="wjportal-jobs-act-btn wjportal-list-act-btn-tell-a-friend" href="#" onclick="getTellaFriend(\''.$jobid.'\');">'. __('Tell A Friend','wp-job-portal').'</a>';
       echo wp_kses($html, WPJOBPORTAL_ALLOWED_TAGS);
    }

}
