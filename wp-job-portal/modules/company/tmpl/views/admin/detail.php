<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
/**
* @param Detail Body
* wpjobportalPopupAdmin
*/
$wpjobportal_listing_fields = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldOrderingDataForListing(1);
?>
<div class="wpjobportal-company-cnt-wrp">
    <div class="wpjobportal-company-middle-wrp">
        <div class="wpjobportal-company-data">
           <a class="wpjobportal-company-name" href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal_company&wpjobportallt=formcompany&wpjobportalid='.$wpjobportal_company->id)); ?>">
                <?php echo esc_html($wpjobportal_company->name); ?>
            </a> 
        </div>
        <?php if(isset($wpjobportal_listing_fields['description'])) {?>
            <div class="wpjobportal-company-data wpjobportal-company-desc">
                <?php echo isset($wpjobportal_company->description) ? wp_kses($wpjobportal_company->description, WPJOBPORTAL_ALLOWED_TAGS) : ''; ?>
            </div>
        <?php }?>
        <div class="wpjobportal-company-data">
            <?php //if(isset($wpjobportal_listing_fields['url']) && $wpjobportal_company->url != '') {?>
            <?php if( $wpjobportal_company->url != '') {?>
                    <div class="wpjobportal-company-data-text">
                        <span class="wpjobportal-company-data-value wpjobportal-listing-data-website">
                            <a href="<?php echo esc_url($wpjobportal_company->url); ?>" target="_blank">
                                <?php echo esc_html($wpjobportal_company->url); ?>
                            </a>
                        </span>
                    </div>
            <?php } ?>
            <?php if(isset($wpjobportal_listing_fields['city'])) {?>
                    <div class="wpjobportal-company-data-text">
                        <span class="wpjobportal-company-data-value wpjobportal-listing-data-location">
                            <?php echo esc_html(WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($wpjobportal_company->city)); ?>
                        </span>
                    </div>
                
            <?php } ?>
        </div>
    </div>
    <div class="wpjobportal-company-right-wrp">
        <div class="wpjobportal-company-status">
            <?php
                if ($wpjobportal_company->status == 0) {
                    echo '<span class="wpjobportal-company-status-txt pending">' . esc_html(__('Pending', 'wp-job-portal')) . '</span>';
                } elseif ($wpjobportal_company->status == 1) {
                    echo '<span class="wpjobportal-company-status-txt approved">' . esc_html(__('Approved', 'wp-job-portal')) . '</span>';
                } elseif ($wpjobportal_company->status == -1) {
                    echo '<span class="wpjobportal-company-status-txt rejected">' . esc_html(__('Rejected', 'wp-job-portal')) . '</span>';
                }elseif ($wpjobportal_company->status == 3) {
                    echo '<span class="wpjobportal-company-status-txt pending-payment">' . esc_html(__('Pending Payment', 'wp-job-portal')) . '</span>';
                }
            ?> 
        </div>
    </div>
</div>

                    
                        
