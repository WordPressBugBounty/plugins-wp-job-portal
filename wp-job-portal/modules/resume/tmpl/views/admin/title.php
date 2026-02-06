<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
* @param WP JOB PORTAL
* @param Resume Detail
*/
$wpjobportal_listing_fields = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldOrderingDataForListing(3);
?>
<div class="wpjobportal-resume-cnt-wrp">
    <div class="wpjobportal-resume-middle-wrp">
        
        <div class="wpjobportal-resume-data">
            <span class="wpjobportal-resume-name">
                <a href="<?php echo esc_url_raw(admin_url("admin.php?page=wpjobportal_resume&wpjobportallt=formresume&wpjobportalid=".$wpjobportal_resume->id));?>">
                    <?php
                    // since application title is not required showing name in its place
                        echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_resume->first_name));
                        echo '&nbsp;'. esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_resume->last_name));?>
                </a>
            </span>
            <?php
                if ($wpjobportal_resume->status == 0) {
                    echo '<span class="wpjobportal-item-status pending">' . esc_html(__('Pending', 'wp-job-portal')) . '</span>';
                } elseif ($wpjobportal_resume->status == 1) {
                    echo '<span class="wpjobportal-item-status approved">' . esc_html(__('Approved', 'wp-job-portal')) . '</span>';
                } elseif ($wpjobportal_resume->status == -1) {
                    echo '<span class="wpjobportal-item-status rejected">' . esc_html(__('Rejected', 'wp-job-portal')) . '</span>';
                } elseif ($wpjobportal_resume->status == 3) {
                    echo '<span class="wpjobportal-item-status rejected">' . esc_html(__('Pending Payment', 'wp-job-portal')) . '</span>';
                }
            ?>
        </div>
        <div class="wpjobportal-resume-data wpjobportal-resume-appilcation-title">
            <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_resume->application_title)); ?>
        </div>
        <div class="wpjobportal-resume-data">
            <?php if(isset($wpjobportal_listing_fields['job_category'])){ ?>
                <div class="wpjobportal-resume-data-text">
                    <span class="wpjobportal-resume-data-value wpjobportal-listing-data-category">
                        <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_resume->cat_title)); ?>
                    </span>
                </div>
            <?php } ?>
            <?php if(isset($wpjobportal_listing_fields['salaryfixed'])){ ?>
                <div class="wpjobportal-resume-data-text">
                    <span class="wpjobportal-resume-data-value wpjobportal-listing-data-salary">
                        <?php // was showing label twice
                            echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_resume->salaryfixed));
                        ?>
                    </span>
                </div>
            <?php } ?>
            <?php
            if(in_array('advanceresumebuilder', wpjobportal::$_active_addons)){
                if(isset($wpjobportal_listing_fields['employer'])) {
                ?>

                    <div class="wpjobportal-resume-data-text">
                        <span class="wpjobportal-resume-data-value wpjobportal-listing-data-experince">
                            <?php echo esc_html(wpjobportal::$_common->getTotalExp($wpjobportal_resume->resumeid)); ?>
                        </span>
                    </div>
                <?php
                }
                if(isset($wpjobportal_listing_fields['address_city'])) { ?>
                        <div class="wpjobportal-resume-data-text">
                            <span class="wpjobportal-resume-data-value wpjobportal-listing-data-location">
                                <?php echo esc_html(WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($wpjobportal_resume->city)); ?>
                            </span>
                        </div>
                <?php }
            }
            ?>
        </div>
    </div>
    <div class="wpjobportal-resume-right-wrp">
        <?php if(isset($wpjobportal_listing_fields['jobtype'])){ ?>
            <span class="wpjobportal-resume-job-type" style="background-color: <?php echo esc_attr($wpjobportal_resume->color); ?>" >
                <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_resume->jobtypetitle)); ?>
            </span>
        <?php } ?>
    </div>

</div>


