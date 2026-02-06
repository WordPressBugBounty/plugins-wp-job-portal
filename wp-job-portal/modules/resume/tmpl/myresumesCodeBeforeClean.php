<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
?>
<div class="wjportal-main-up-wrapper">
<?php
wp_enqueue_style('status-graph', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/status_graph.css');
if(!WPJOBPORTALincluder::getTemplate('templates/header',array('wpjobportal_module'=>'resume'))){
    return;
}
if (wpjobportal::$_error_flag == null) {
    $wpjobportal_subtype = wpjobportal::$_config->getConfigValue('submission_type');
    ?>

    <div id="wpjobportal-wrapper">
        <?php
        WPJOBPORTALincluder::getTemplate('templates/pagetitle',array(
            'wpjobportal_module' => 'multiresume',
            'wpjobportal_layout' => 'multiresumeadd'
        ));

        WPJOBPORTALincluder::getTemplate('resume/views/frontend/filter',array('wpjobportal_filter'=>'myresume'));
            $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
            if (!empty(wpjobportal::$_data[0])) {
                foreach (wpjobportal::$_data[0] AS $wpjobportal_myresume) {
                    $wpjobportal_status_array = WPJOBPORTALincluder::getJSModel('resume')->getResumePercentage($wpjobportal_myresume->id);
                    $wpjobportal_percentage = $wpjobportal_status_array['percentage'];
                     WPJOBPORTALincluder::getTemplate('resume/views/frontend/resumelist',array(
                        'wpjobportal_myresume' => $wpjobportal_myresume,
                        'wpjobportal_percentage' => $wpjobportal_percentage,
                        'wpjobportal_control' => 'myresumes',
                        'wpjobportal_model'=> 'myresume'
                     ));
            }

        if (wpjobportal::$_data[1]) {
            WPJOBPORTALincluder::getTemplate('templates/pagination',array('wpjobportal_module' => 'resume','pagination' => wpjobportal::$_data[1]));
        }
        echo wp_kses(WPJOBPORTALformfield::hidden('sortby', wpjobportal::$_data['sortby']),WPJOBPORTAL_ALLOWED_TAGS);
        echo wp_kses(WPJOBPORTALformfield::hidden('sorton', wpjobportal::$_data['sorton']),WPJOBPORTAL_ALLOWED_TAGS);

    } else {
        $wpjobportal_msg = esc_html(__('No record found','wp-job-portal'));
        $wpjobportal_links[] = array(
                    'link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'addresume', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())),
                    'text' => esc_html(__('Add New','wp-job-portal')) .' '. esc_html(__('Resume', 'wp-job-portal'))
                );
        WPJOBPORTALlayout::getNoRecordFound($wpjobportal_msg,$wpjobportal_links);
    }
?>
    </div>
<?php
}else{
    echo wp_kses_post(wpjobportal::$_error_flag_message);
}
?>
</div>
