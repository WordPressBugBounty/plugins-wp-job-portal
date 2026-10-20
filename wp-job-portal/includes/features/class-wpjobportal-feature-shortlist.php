<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Saved jobs, with ratings and notes.
 *
 * Absorbed from the Shortlist add-on. Job seekers save jobs, rate
 * them and leave private comments. Uses the existing jobshortlist table,
 * adopted in place by migration 271.
 *
 * Hook names, routes and output are preserved exactly, and the callbacks are
 * carried over unchanged, so a site that removes the old add-on sees no
 * difference. Registration is skipped entirely while that add-on is still
 * installed; see WPJOBPORTAL_Absorbed_Features.
 */
final class WPJOBPORTAL_Feature_shortlist {

    public static function register() {
        $feature = new self();

        add_action('wpjobportal_addons_newestjob_btm_btn_for_shortlist', array($feature, 'wpjobportal_addons_newest_job_shortlistjobs'), 10, 1);
        add_action('wpjobportal_addons_jobseeker_dashboard_bottom_btn_shortlist', array($feature, 'wpjobportal_addons_list_job_shortlist'), 10, 2);
        add_action('wpjobportal_addons_upper_lable_shortlist_rating', array($feature, 'wpjobportal_addons_rating_for_shortlist'), 10, 1);
        add_filter('wpjobportal_addons_rating_shortlist', array($feature, 'wpjobportal_addons_rating_for_shortlist_form'), 10, 2);
        add_action('wpjobportal_addons_shortlist_comments', array($feature, 'wpjobportal_addons_comments_joblist'), 10, 1);
        add_action('wpjobportal_addons_dashboard_link_shortlist', array($feature, 'wpjobportal_addons_dashboard_shortlist_view'), 10, 1);
    }

    /* ------------------------------------------------------------------ */
    /* Callbacks carried over unchanged from the add-on                    */
    /* ------------------------------------------------------------------ */

    public function wpjobportal_addons_newest_job_shortlistjobs($job){
        $shortlisted_job = wpjobportalincluder::getJSModel('shortlist')->getDataForShortlist($job->id);

        if($shortlisted_job){
            $fa_icon = 'fas fa-heart';
        }else{
            $fa_icon = 'far fa-heart';
        }

        $html = '';
        $wpjobportal_is_job_page = WPJOBPORTALrequest::getVar('wpjobportallt') == 'viewjob'
            || (!empty(wpjobportal::$_data['sanitized_args']['wpjobportallt']) && wpjobportal::$_data['sanitized_args']['wpjobportallt'] == 'viewjob');
        if (wpjobportal::$wpjobportal_theme_chk == 1) {
            $html = '<a class="wpj-jp-icon-btn" href="#" onclick="getShortlistViewByJobid('.esc_js($job->id).',1);" title="'. esc_html__('short List','job-portal-theme').'"><i class="'.$fa_icon.'"></i></a>';
        } elseif($wpjobportal_is_job_page && WPJOBPORTALincluder::getJSModel('common')->isElegantDesignEnabled()){
            /*
             * Elegant Design's shortlist card belongs on the job's own page,
             * beside its Tell A Friend card. In a list it was a 640px panel
             * under every job, next to a Tell A Friend that is a button - so
             * a list gets the same button every other design gets.
             */
            $html = '<a onclick="getShortlistViewByJobid('.esc_js($job->id).');" class="wjportal-elegant-addon-job-shortlist-section" href="#"title="'. esc_html(__("Shortlist",'wp-job-portal')) .'">
                <span class="wjportal-elegant-addon-job-shortlist-section-logo">
                    <img class="wjportal-elegant-addon-job-shortlist-colorlogo" src="'. esc_url(wpjobportal_elegant_design_url()) .'includes/images/shortlist.png" alt="'. esc_html(__('Shortlist','wp-job-portal')) .'" title="'. esc_html(__('Shortlist','wp-job-portal')) .'">
                </span>
                <span class="wjportal-elegant-addon-job-shortlist-section-title">'. esc_html(__("Mark As Shortlist Job",'wp-job-portal')) .'</span>
                <p class="wjportal-elegant-addon-job-shortlist-section-disc">'. esc_html(__("Keep this job on your list! Mark it as shortlisted and find it easily whenever you need.",'wp-job-portal')) .'</p>
            </a>';
        } else {
            if(!empty(wpjobportal::$_data['sanitized_args']['wpjobportallt']) && wpjobportal::$_data['sanitized_args']['wpjobportallt'] == 'viewjob'){
                $html = '<a class="wjportal-jobs-act-btn wjportal-job-act-btn-shortlist" href="#" onclick="getShortlistViewByJobid('.esc_js($job->id).');">'. esc_html__('Short List','wp-job-portal').'</a>';
            }else{
                /*
                 * The label belongs on the list button too.
                 *
                 * The detail page spells it out and the list did not: the
                 * anchor was empty, carrying the name only in a title
                 * attribute, which appears on hover and never on a touch
                 * screen. So on a phone it was an unnamed square. The button
                 * style has always been built for both - inline-flex with a
                 * gap, and a comment in style.css that reads "Space between
                 * icon and text" - the text was simply never passed.
                 */
                $html = '<a class="wjportal-jobs-act-btn wjportal-job-act-btn-shortlist" href="#" onclick="getShortlistViewByJobid('.esc_js($job->id).');" title="'. esc_js(__('Short List','wp-job-portal')).'">'. esc_html(__('Short List','wp-job-portal')) .'</a>';
            }
        }
        echo wp_kses($html, WPJOBPORTAL_ALLOWED_TAGS);
    }

    public function wpjobportal_addons_list_job_shortlist($print, $elegantdesign = ''){
        $print = wpjobportal_jobseekercheckLinks('listjobshortlist');
        if ($print) {
            if ($elegantdesign == 1) {
                $url = wpjobportal_elegant_design_url();
            } else {
                $url = WPJOBPORTAL_PLUGIN_URL;
            }
            echo '<div class="wjportal-cp-list">
                <a class="wjportal-list-anchor" href='.esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'shortlist', 'wpjobportallt'=>'shortlistedjobs'))).'>'. wp_kses(wpjobportal_cp_menu_icon('star'), WPJOBPORTAL_ALLOWED_TAGS) .'<span class="wjportal-cp-link-text">'. esc_html__('Short Listed Jobs', 'wp-job-portal').'</span></a>
            </div>';
        }
    }

    public function wpjobportal_addons_rating_for_shortlist($shortListedJob){
        WPJOBPORTALincluder::getTemplate('views/rating_shortlist',array(
            'module_name' => 'shortlist',
            'shortListedJob' => $shortListedJob
        ));

    }

    public function wpjobportal_addons_rating_for_shortlist_form($default_val,$job){
        $content = '';
        $id = $job->jobid;
        $percent = 0;
        $percent = $job->rate * 20;
        $stars = '-small';
        $content ="
                    <div class=\"wpjobportal-container" . $stars . "\">
                        <span id='shortlist-stars'><ul class=\"wpjobportal-stars" . $stars . "\" >
                            <li id=\"rating_" . $jobid . "\" class=\"current-rating\" style=\"width:" . (int) $percent . "%;\"></li>
                            <li><a anchor=\"anchor\" href=\"javascript:void(null)\" onclick=\"javascript:setrating('rating_" . $jobid . "',1);\" title=\"" . esc_attr__('Very Poor', 'wp-job-portal') . "\" class=\"one-star\">1</a></li>
                            <li><a anchor=\"anchor\" href=\"javascript:void(null)\" onclick=\"javascript:setrating('rating_" . $jobid . "',2);\" title=\"" . esc_attr__('Poor', 'wp-job-portal') . "\" class=\"two-stars\">2</a></li>
                            <li><a anchor=\"anchor\" href=\"javascript:void(null)\" onclick=\"javascript:setrating('rating_" . $jobid . "',3);\" title=\"" . esc_attr__('Regular', 'wp-job-portal') . "\" class=\"three-stars\">3</a></li>
                            <li><a anchor=\"anchor\" href=\"javascript:void(null)\" onclick=\"javascript:setrating('rating_" . $jobid . "',4);\" title=\"" . esc_attr__('Good', 'wp-job-portal') . "\" class=\"four-stars\">4</a></li>
                            <li><a anchor=\"anchor\" href=\"javascript:void(null)\" onclick=\"javascript:setrating('rating_" . $jobid . "',5);\" title=\"" . esc_attr__('Very Good', 'wp-job-portal') . "\" class=\"five-stars\">5</a></li>
                        </ul></span>
                    </div>
                    ";
        return $content;


    }

    public function wpjobportal_addons_comments_joblist($job){
        if ($job->comments != ' ') {
            if (wpjobportal::$wpjobportal_theme_chk == 1) { ?>
                <li class="list-inline-item">
                    <div class="wpj-jp-list-comment" id="comments_<?php echo esc_attr($job->jobid); ?>">
                        <span class="wpj-jp-meta-tit">
                            <?php echo esc_html__('Comments','job-portal-theme'). " : "; ?>
                        </span>
                        <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($job->comments)); ?>
                    </div>
                </li>
            <?php  } else { ?>
                <div class="wjportal-shortlist-job-comments" id="comments_<?php echo esc_attr($job->jobid); ?>">
                    <span class="wjportal-shortlist-job-comment-tit">
                        <?php echo esc_html__('Comments','wp-job-portal'). " : "; ?>
                    </span>
                    <span class="wjportal-shortlist-job-comment-val">
                        <?php echo esc_html($job->comments); ?>
                    </span>
                </div>
            <?php } 
        }
    }

    public function wpjobportal_addons_dashboard_shortlist_view($shortlist){
       echo '<div class="wjportal-cp-box box4">
            <div class="wjportal-cp-box-top">
                <img src=' . esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/control_panel/employer/cp-shortlisted-jobs.png') . ' alt='.esc_attr__("shortlisted jobs","wp-job-portal").'>
                <div class="wjportal-cp-box-num">
                    '. esc_html(isset($shortlist) ? $shortlist : '') .'
                </div>
                <div class="wjportal-cp-box-tit">
                    '. esc_html__('Shortlisted Jobs','wp-job-portal').'
                </div>
            </div>
            <div class="wjportal-cp-box-btm clearfix">
                <a href="'.esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'shortlist', 'wpjobportallt'=> 'shortlistedjobs'))).'" title="'. esc_attr__('View Detail','wp-job-portal').'">
                    <span class="wjportal-cp-box-text">
                        '. esc_html__('View Detail','wp-job-portal').'
                    </span>
                    <i class="fa fa-arrow-right"></i>
                </a>
            </div>
        </div>';
    }

}
