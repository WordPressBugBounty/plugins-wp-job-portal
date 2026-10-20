<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
/**
* @param WP JOB PORTAL
* @param (WP RATING SHORTLIST FILE)
*/
?>
<div id="applied-resume-ratting">
    <?php
    $id = isset($shortListedJob->jobid) ? $shortListedJob->jobid : '';
    $wpjobportal_percent = 0;
    $wpjobportal_percent = $shortListedJob->rate * 20;
    ?>
            <div class="wjportal-container-small" style="margin-top:5px;display:inline-block;">
            <ul class="wjportal-stars-small">
            <li id="rating_<?php echo esc_attr($id); ?>" class="current-rating" style="width:<?php echo (int) $wpjobportal_percent; ?>%;"></li>
            <li><a href="javascript:void(null)" title="<?php echo esc_attr__('Very Poor', 'wp-job-portal'); ?>" class="one-star">1</a></li>
            <li><a href="javascript:void(null)" title="<?php echo esc_attr__('Poor', 'wp-job-portal'); ?>" class="two-stars">2</a></li>
            <li><a href="javascript:void(null)" title="<?php echo esc_attr__('Regular', 'wp-job-portal'); ?>" class="three-stars">3</a></li>
            <li><a href="javascript:void(null)" title="<?php echo esc_attr__('Good', 'wp-job-portal'); ?>" class="four-stars">4</a></li>
            <li><a href="javascript:void(null)" title="<?php echo esc_attr__('Very Good', 'wp-job-portal'); ?>" class="five-stars">5</a></li>
            </ul>
            </div>
        </small></span>
</div>