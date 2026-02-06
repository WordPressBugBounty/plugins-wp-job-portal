<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
?>
<div class="wjportal-main-up-wrapper">
<?php
if ( !WPJOBPORTALincluder::getTemplate('templates/header', array('wpjobportal_module' => 'job') )) {
    return;
}
if (wpjobportal::$_error_flag == null) {
    $wpjobportal_job = isset(wpjobportal::$_data[0]) ? wpjobportal::$_data[0]  : null;
    $wpjobportal_jobfields = wpjobportal::$_data[2];
    
    /* Member Package Pop-up */
    function wpjobportal_getDataRow($title, $wpjobportal_value) {
        $wpjobportal_html = '<div class="wjportal-job-data">
                    <span class="wjportal-job-data-tit">' . $title . ': </span>
                    <span class="wjportal-job-data-val">' . $wpjobportal_value . '</span>
                </div>';
        return $wpjobportal_html;
    }

    function wpjobportal_getHeading2($wpjobportal_value) {
        $wpjobportal_html = '<div class="heading2">' . $wpjobportal_value . '</div>';
        return $wpjobportal_html;
    }

    function wpjobportal_getPeragraph($wpjobportal_value) {
        $wpjobportal_html = '<div class="peragraph">' . $wpjobportal_value . '</div>';
        return $wpjobportal_html;
    }
    echo '<meta property="description" content="'.esc_attr($wpjobportal_job->metadescription).'"/>';
    echo '<meta property="keywords" content="'.esc_attr($wpjobportal_job->metakeywords).'"/>';

    // published fields title array to handle visibilty on layouts
    wpjobportal::$wpjobportal_data['published_fields'] = array();
    foreach (wpjobportal::$_data[2] as $wpjobportal_key => $wpjobportal_value) {
        wpjobportal::$wpjobportal_data['published_fields'][$wpjobportal_key] = $wpjobportal_value->fieldtitle;
    }

    WPJOBPORTALincluder::getTemplate('job/views/frontend/viewjobdetail',array(
        'wpjobportal_job' => $wpjobportal_job,
        'wpjobportal_jobfields' => $wpjobportal_jobfields
    ));
    ?>
    <?php
} else {
    if(wpjobportal::$_error_flag_message !=''){
        echo wp_kses_post(wpjobportal::$_error_flag_message);
    }
}
?>
</div>
