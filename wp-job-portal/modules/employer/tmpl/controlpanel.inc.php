<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
wp_enqueue_script( 'jp-google-charts', esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/js/google-charts.js', array(), '1.1.1', false );
wp_register_script( 'google-charts-handle', '' );
wp_enqueue_script( 'google-charts-handle' );
?>
<?php
    wp_register_script( 'wpjobportal-inline-handle', '' );
    wp_enqueue_script( 'wpjobportal-inline-handle' );

    $wpjobportal_inline_js_script = "
        jQuery(document).ready(function() {

        //for notifications
        jQuery('div.notifications').hide();
        jQuery('img.notifications').on('click', function(){
            jQuery('div.notifications, div.notifications').slideToggle();
        });
        jQuery('span.count_notifications').on('click', function(){
            jQuery('div.notifications, div.notifications').slideToggle();
        });
        var counter = jQuery('span.count_notifications').text();
                //for messages
                jQuery('div.messages').hide();
                jQuery('img.messages').on('click', function(){
                    jQuery('div.messages, div.messages').slideToggle();
                });
                jQuery('span.count_messages').on('click', function(){
                    jQuery('div.messages, div.messages').slideToggle();
                });
                jQuery('div#wpjobportal-popup-background, img#popup_cross').click(function(){
                    jQuery('div#wpjobportal-popup').hide();
                    jQuery('div#wpjobportal-popup-background').hide();
                });
            });
        google.load('visualization', '1', {packages:['corechart']});



        /*function showLoginPopup(){
            jQuery('div#wpjobportal-popup-background').show();
            jQuery('div#wpjobportal-popup').show();
        }*/
    ";
    wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
?>
<?php

/*function wpjobportal_employercheckLinks($wpjobportal_name) {
    $wpjobportal_print = false;
    switch ($wpjobportal_name) {
        case 'formcompany': $wpjobportal_visname = 'vis_emformcompany';
        break;
        case 'alljobsappliedapplications': $wpjobportal_visname = 'vis_emalljobsappliedapplications';
        break;
        case 'mycompanies': $wpjobportal_visname = 'vis_emmycompanies';
        break;
        case 'resumesearch': $wpjobportal_visname = 'vis_emresumesearch';
        break;
        case 'formjob': $wpjobportal_visname = 'vis_emformjob';
        break;
        case 'my_resumesearches': $wpjobportal_visname = 'vis_emmy_resumesearches';
        break;
        case 'myjobs': $wpjobportal_visname = 'vis_emmyjobs';
        break;
        case 'formdepartment': $wpjobportal_visname = 'vis_emformdepartment';
        break;
        case 'my_stats': $wpjobportal_visname = 'vis_emmy_stats';
        break;
        case 'empresume_rss': $wpjobportal_visname = 'vis_resume_rss';
        break;
        case 'newfolders': $wpjobportal_visname = 'vis_emnewfolders';
        break;
        case 'empregister': $wpjobportal_visname = 'vis_emempregister';
        break;
        case 'empcredits': $wpjobportal_visname = 'vis_empcredits';
        break;
        case 'empcreditlog': $wpjobportal_visname = 'vis_empcreditlog';
        break;
        case 'emppurchasehistory': $wpjobportal_visname = 'vis_emppurchasehistory';
        break;
        case 'empmessages': $wpjobportal_visname = 'vis_emmessages';
        break;
        case 'empregister': $wpjobportal_visname = 'vis_emregister';
        break;
        case 'empratelist': $wpjobportal_visname = 'vis_empratelist';
        break;
        case 'jobs_graph': $wpjobportal_visname = 'vis_jobs_graph';
        break;
        case 'resume_graph': $wpjobportal_visname = 'vis_resume_graph';
        break;
        case 'box_newestresume': $wpjobportal_visname = 'vis_box_newestresume';
        break;
        case 'box_appliedresume': $wpjobportal_visname = 'vis_box_appliedresume';
        break;
        case 'emploginlogout': $wpjobportal_visname = 'emploginlogout';
        break;
        case 'empmystats': $wpjobportal_visname = 'vis_empmystats';
        break;
        case 'emresumebycategory': $wpjobportal_visname = 'vis_emresumebycategory';
        break;
        case 'temp_employer_dashboard_stats_graph': $wpjobportal_visname = 'vis_temp_employer_dashboard_stats_graph';
        break;
        case 'temp_employer_dashboard_useful_links': $wpjobportal_visname = 'vis_temp_employer_dashboard_useful_links';
        break;
        case 'temp_employer_dashboard_applied_resume': $wpjobportal_visname = 'vis_temp_employer_dashboard_applied_resume';
        break;
        case 'temp_employer_dashboard_saved_search': $wpjobportal_visname = 'vis_temp_employer_dashboard_saved_search';
        break;
        case 'temp_employer_dashboard_credits_log': $wpjobportal_visname = 'vis_temp_employer_dashboard_credits_log';
        break;
        case 'temp_employer_dashboard_purchase_history': $wpjobportal_visname = 'vis_temp_employer_dashboard_purchase_history';
        break;
        case 'temp_employer_dashboard_newest_resume': $wpjobportal_visname = 'vis_temp_employer_dashboard_newest_resume';
        break;
        default:$wpjobportal_visname = 'vis_em' . $wpjobportal_name;
        break;
    }

    $wpjobportal_isouruser = WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPortalUser();
    $wpjobportal_isguest = WPJOBPORTALincluder::getObjectClass('user')->isguest();

    $guest = false;

    if($wpjobportal_isguest == true){
        $guest = true;
    }
    if($wpjobportal_isguest == false && $wpjobportal_isouruser == false){
        $guest = true;
    }

    $wpjobportal_config_array = wpjobportal::$_data['config'];

    if ($guest == false) {
        if (WPJOBPORTALincluder::getObjectClass('user')->isemployer()) {
            if ($wpjobportal_config_array[$wpjobportal_name] == 1){
                $wpjobportal_print = true;
            }
        }elseif (WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()) {
            if ($wpjobportal_config_array["$wpjobportal_visname"] == 1) {
                $wpjobportal_print = true;
            }
        }
    } else {
        if ($wpjobportal_config_array["$wpjobportal_visname"] == 1)
            $wpjobportal_print = true;
    }
    return $wpjobportal_print;
}*/

function wpjobportal_jobWrapper($wpjobportal_resumeid, $wpjobportal_path, $wpjobportal_first_name, $middle_name, $last_name, $wpjobportal_application_title, $wpjobportal_email_address, $Category) {
    $wpjobportal_html = '<div class="job-wrapper">
    <div class="img">
        <a href="' . esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'viewresume', 'wpjobportalid'=>$wpjobportal_resumeid))) . '">
            <img src="' . esc_url($wpjobportal_path) . '">
        </a>
    </div>
    <div class="detail">
       <div class="upper">
          <a href="' . esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'viewresume', 'wpjobportalid'=>$wpjobportal_resumeid))) . '">' . esc_html($wpjobportal_first_name) . ' ' . esc_html($middle_name) . ' ' . esc_html($last_name) . '</a>
      </div>
      <div class="lower">
          <div class="resume_title">(' . esc_html($wpjobportal_application_title) . ')</div>
          <div class="for-rtl">
             <span class="text">'. esc_html(__('Email','wp-job-portal')) .': </span>
             <span class="get-text ">' . esc_html($wpjobportal_email_address) . '</span>
         </div>
         <div class="for-rtl">
             <span class="text">'. esc_html(__('Category','wp-job-portal')) .': </span>
             <span class="get-text">' . wpjobportal::wpjobportal_getVariableValue($Category) . '</span>
         </div>
     </div>
 </div>
</div>';
return $wpjobportal_html;
}
?>
