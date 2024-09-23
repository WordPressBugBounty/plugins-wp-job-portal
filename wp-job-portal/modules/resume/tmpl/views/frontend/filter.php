<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
* 
*/
?>
<?php
switch ($filter) {
    case 'resume':
    ?>
        <div id="resume-list-navebar" class="wjportal-filter-wrp">
            <div class="wjportal-filter">
                <?php echo wp_kses(WPJOBPORTALformfield::select('sorting', $sortbylist, isset(wpjobportal::$_data['combosort']) ? wpjobportal::$_data['combosort'] : null,esc_html(__("Default",'wp-job-portal')),array('onchange'=>'changeCombo()')), WPJOBPORTAL_ALLOWED_TAGS); ?>
            </div>
        <div class="wjportal-filter-image">
            <a class="sort-icon" href="#" data-image1="<?php echo esc_attr($image1); ?>" data-image2="<?php echo esc_attr($image2); ?>" data-sortby="<?php echo esc_attr(wpjobportal::$_data['sortby']); ?>"><img id="sortingimage" src="<?php echo esc_url($image); ?>" /></a>
        </div>
    </div>
        <?php
    break;
    
}
