<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
?>
<div class="wjportal-main-up-wrapper">
<?php
if ( !WPJOBPORTALincluder::getTemplate('templates/header',array('module' => 'jobsearch')) ) {
    return;
}
if (wpjobportal::$_error_flag == null) {
    $radiustype = array(
        (object) array('id' => '0', 'text' => esc_html(__('Select One', 'wp-job-portal'))),
        (object) array('id' => '1', 'text' => esc_html(__('Meters', 'wp-job-portal'))),
        (object) array('id' => '2', 'text' => esc_html(__('Kilometers', 'wp-job-portal'))),
        (object) array('id' => '3', 'text' => esc_html(__('Miles', 'wp-job-portal'))),
        (object) array('id' => '4', 'text' => esc_html(__('Nautical Miles', 'wp-job-portal'))),
    );
    ?>

<div class="wjportal-main-wrapper wjportal-clearfix">
    <div class="wjportal-page-header">
        <?php WPJOBPORTALincluder::getTemplate('templates/pagetitle',array('module' => 'jobsearch' , 'layout' => 'jobsearch')); ?>
    </div>
    <div class="wjportal-form-wrp wjportal-search-job-form">
        <form class="wjportal-form" id="job_form" method="post" action="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobs', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()))); ?>">
            <?php
                $formfields = WPJOBPORTALincluder::getTemplate('jobsearch/form-field',array(
                    'fields' => wpjobportal::$_data[2],
                    'radiustype' => $radiustype
                ));
                foreach ($formfields as $formfield) {
                    WPJOBPORTALincluder::getTemplate('templates/form-field', $formfield);
                }
            ?>
            <div class="wjportal-form-btn-wrp" id="save-button">
                <?php echo wp_kses(WPJOBPORTALformfield::submitbutton('save', esc_html(__('Search Job', 'wp-job-portal')), array('class' => 'button wjportal-form-btn wjportal-save-btn')),WPJOBPORTAL_ALLOWED_TAGS); ?>
            </div>
            <input type="hidden" id="default_longitude" name="default_longitude" value="<?php echo esc_attr(wpjobportal::$_configuration['default_longitude']); ?>"/>
            <input type="hidden" id="default_latitude" name="default_latitude" value="<?php echo esc_attr(wpjobportal::$_configuration['default_latitude']); ?>"/>
            <input type="hidden" id="issearchform" name="issearchform" value="1"/>
            <input type="hidden" id="WPJOBPORTAL_form_search" name="WPJOBPORTAL_form_search" value="WPJOBPORTAL_SEARCH"/>
            <input type="hidden" id="wpjobportallay" name="wpjobportallay" value="jobs"/>
        </form>
    </div>
    <?php
    } else {
        echo wp_kses_post(wpjobportal::$_error_flag_message);
    } ?>
</div>
</div>
