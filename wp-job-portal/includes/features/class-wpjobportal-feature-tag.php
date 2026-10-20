<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Job tags and tag administration.
 *
 * Absorbed from the Tag add-on. Uses the existing tags table,
 * adopted in place by migration 271.
 *
 * Hook names, routes and output are preserved exactly, and the callbacks are
 * carried over unchanged, so a site that removes the old add-on sees no
 * difference. Registration is skipped entirely while that add-on is still
 * installed; see WPJOBPORTAL_Absorbed_Features.
 */
final class WPJOBPORTAL_Feature_tag {

    public static function register() {
        $feature = new self();

        add_action('wpjobportal_addons_admin_sidemenu_links_for_tags', array($feature, 'wpjobportal_addons_credit_addcompany'), 10, 2);
        add_filter('wpjobportal_credit_job_input_for_tagline', array($feature, 'wp_job_portaladdons_credit_input_tagline_for_jobs'), 10, 3);
        add_action('wpjobportal_credit_addons_search_job_ref_tags', array($feature, 'wp_job_portaladdons_searc_job_tags'), 10, 1);
        add_filter('wpjobportal_addons_makeanchor_for_tags', array($feature, 'wpjobportal_addons_make_anchor_for_tags'), 10, 2);
    }

    /* ------------------------------------------------------------------ */
    /* Callbacks carried over unchanged from the add-on                    */
    /* ------------------------------------------------------------------ */

    public function wpjobportal_addons_credit_addcompany($c,$layout){
        $html = '';
        $class = '';
        if(!empty($c)){
            if($c == 'wpjobportal_tag') $class = 'active';
            $html .= '<li class="treeview '.esc_attr($class).'">
                        <a href="admin.php?page=wpjobportal_tag" title="'. __('Tags' , 'wp-job-portal').'">
                            <img class="wpjobportaladmin-menu-icon" alt="'.__('Tags' , 'wp-job-portal').'" src='.WPJOBPORTAL_PLUGIN_URL.'includes/images/control_panel/dashboard/admin-left-menu/tags.png />
                            <span class="wpjobportaladmin-text">
                                '.__('Tags' , 'wp-job-portal').'
                            </span>
                        </a>
                        <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">';
                            $class = '';
                            if($c == 'wpjobportal_tag' && ($layout == '')) $class = 'active';
                            $html .= '<li class="'.$class.'">
                                <a href="admin.php?page=wpjobportal_tag" title="'.__('Tags','wp-job-portal').'">
                                    '.__('Tags','wp-job-portal').'
                                </a>
                            </li>';
                            $class = '';
                            if($c == 'wpjobportal_tag' && ($layout == 'formtag')) $class = 'active';
                            $html .= '<li class="'.esc_attr($class).'">
                                <a href="admin.php?page=wpjobportal_tag&wpjobportallt=formtag" title="'.__('Add New Tag','wp-job-portal').'">
                                    '.__('Add New Tag','wp-job-portal').'
                                </a>
                            </li>
                        </ul>
                    </li>
            ';
        }
        echo wp_kses($html, WPJOBPORTAL_ALLOWED_TAGS);
    }

    public function wp_job_portaladdons_credit_input_tagline_for_jobs($default_val,$field,$job=''){
       $html = WPJOBPORTALformfield::text('tags', $job ? $job->tags : '', array('class' => 'inputbox wpjobportal-form-input-field', $field->validation));
       return $html;
    }

    public function wp_job_portaladdons_searc_job_tags($data){
        if (wpjobportal::$wpjobportal_theme_chk == 1) {
            $a_tags = self::makeanchorfortags(wpjobportal::$_data[0]->tags);
            if ($a_tags != "") {
                echo wp_kses($a_tags, WPJOBPORTAL_ALLOWED_TAGS);
            }
        } else {
            $a_tags = self::makeanchorfortags(wpjobportal::$_data[0]->tags);
            if ($a_tags != "") {
            echo '<div id="wpjobportal_jstags" class="wjportal-tags-wrp">';
                    echo '<div class="wjportal-tags-sec-title">' . esc_html(wpjobportal::wpjobportal_getVariableValue(wpjobportal::$wpjobportal_data['fields_titles_array']['tags'])) . '</div>';
                    echo wp_kses($a_tags, WPJOBPORTAL_ALLOWED_TAGS);
            echo '</div>';
            }
        }
    }

    public function wpjobportal_addons_make_anchor_for_tags($default_val,$viewtags){
        return WPJOBPORTALincluder::getJSModel('tag')->makeanchorfortags($viewtags);
    }

    public function makeanchorfortags($tags) {
        if (empty($tags))
            return "";
        $array = wpjobportalphplib::wpJP_explode(',', $tags);
        $anchor = "";
        for ($i = 0; $i < count($array); $i++) {
            $with_spaces = wpjobportal::tagfillin($array[$i]);
            if (wpjobportal::$wpjobportal_theme_chk == 1) {
                $anchor .= '<a class="wpj-jp-tag" href="' . esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobs', 'tags'=>$with_spaces))) . '">' . wpjobportal::wpjobportal_getVariableValue( $array[$i]) . '</a>';
            } else {
                $anchor .= '<a class="wpjobportal_tags_a wjportal-tags-item" href="' . wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobs', 'tags'=>$with_spaces)) . '">' . wpjobportal::wpjobportal_getVariableValue($array[$i]) . '</a>';
            }
        }
        return $anchor;
    }

}
