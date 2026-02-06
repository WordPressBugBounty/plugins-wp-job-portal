<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
/**
 * @param job      job object - optional
 * HOOK TO BE USED FOR FEATURE
*/
$wpjobportal_config_array = wpjobportal::$_data['config'];
$wpjobportal_curdate = date_i18n('Y-m-d');
$wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme');
if(isset($wpjobportal_module)){
    if($wpjobportal_module == "purchasehistory"){
        $wpjobportal_check = false;
    }else{
        $wpjobportal_check = true;
    }
}
?>
<?php
switch ($wpjobportal_layout) {
	case 'showalljobs':
		if ($wpjobportal_config_array['comp_viewalljobs']==1 && !empty(wpjobportal::$_data['0'])) {
        	$wpjobportal_compalias = wpjobportal::$_data[0]->alias.'-'.wpjobportal::$_data[0]->id;
			?>
           <div class="wjportal-company-btn-wrp">
            	<a href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobs', 'company'=>$wpjobportal_compalias))); ?>" class="wjportal-company-act-btn" title="<?php echo esc_attr(__('View all jobs', 'wp-job-portal')); ?>"><?php echo esc_html(__('View All Jobs', 'wp-job-portal')); ?></a>
           </div>
           <?php
            }
	break;
	case 'control':
        $wpjobportal_featuredexpiry = date_i18n('Y-m-d', strtotime($wpjobportal_company->endfeatureddate));
        ?>
        <div class="wjportal-company-action-wrp">
            <?php 
            if($wpjobportal_company->status == 1){ ?>
                <?php
                    if(in_array('multicompany', wpjobportal::$_active_addons)){
                        $wpjobportal_layout_mod = "multicompany";
                    }else{
                        $wpjobportal_layout_mod = "company";
                    }
                ?>
                <a class="wjportal-company-act-btn wjportal-list-act-btn-edit" href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=> $wpjobportal_layout_mod, 'wpjobportallt'=>'addcompany', 'wpjobportalid'=>$wpjobportal_company->id))); ?>" title="<?php echo esc_attr(__('Edit company','wp-job-portal')); ?>">
                    <?php echo esc_html(__('Edit Company','wp-job-portal')); ?>
                </a>
                <!-- //Specification Addon -->
                <?php do_action('wpjobportal_credit_for_featurecompany_ajaxpopup',wpjobportal::$_data['config'],$wpjobportal_company,$wpjobportal_featuredexpiry);  ?>
                <a class="wjportal-company-act-btn wjportal-list-act-btn-delete " href="<?php echo esc_url(wp_nonce_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'task'=>'remove', 'wpjobportal-cb[]'=>$wpjobportal_company->id, 'action'=>'wpjobportaltask','wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())),'wpjobportal_company_nonce')); ?>" onclick='return confirmdelete("<?php echo esc_js(__('Are you sure to delete','wp-job-portal')).' ?'; ?>");' title="<?php echo esc_attr(__('Delete company','wp-job-portal')); ?>">
                    <?php echo esc_html(__('Delete Company','wp-job-portal')); ?>
                </a>
               <?php
                } elseif ($wpjobportal_company->status == 0) {
    	           ?>
                    
                        <span class="wjportal-item-act-status wjportal-waiting">
                            <?php echo esc_html(__('Waiting For Approval', 'wp-job-portal')); ?>
                        </span>
                    
    	           <?php
                } elseif ($wpjobportal_company->status == -1) {
	            ?>
                    <span class="wjportal-item-act-status wjportal-rejected">
                        <?php echo esc_html(__('Rejected', 'wp-job-portal')); ?>
                    </span>
    	           <?php
                } elseif ($wpjobportal_company->status == 3 && in_array('credits',wpjobportal::$_active_addons) && $wpjobportal_check) {
                    #Member Lisitng Make Payment
                    ?>
                    <a class="wjportal-company-act-btn wjportal-list-act-btn-edit" href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'multicompany', 'wpjobportallt'=>'addcompany', 'wpjobportalid'=>$wpjobportal_company->id))); ?>" title="<?php echo esc_attr(__('Edit company','wp-job-portal')); ?>">
                            <?php echo esc_html(__('Edit Company','wp-job-portal')); ?>
                        </a>
                        <?php
                    do_action('wpjobportal_addons_makePayment_for_department',$wpjobportal_company,'paycompany');
                    ?>
                    <a class="wjportal-company-act-btn wjportal-list-act-btn-delete" href="<?php echo esc_url(wp_nonce_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'task'=>'remove', 'wpjobportal-cb[]'=>$wpjobportal_company->id, 'action'=>'wpjobportaltask','wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())),'wpjobportal_company_nonce')); ?>" onclick='return confirmdelete("<?php echo esc_js(__('Are you sure to delete','wp-job-portal')).' ?'; ?>");' title="<?php echo esc_attr(__('Delete company','wp-job-portal')); ?>
">
                            <?php echo esc_html(__('Delete Company','wp-job-portal')); ?>
                        </a>
                    <?php
                } elseif ($wpjobportal_company->status == 3 && in_array('credits',wpjobportal::$_active_addons) && !$wpjobportal_check) { ?>
                    <a class="wjportal-company-act-btn wjportal-list-act-btn-proceed-payment" href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'multicompany', 'wpjobportallt'=>'mycompanies', 'wpjobportalid'=>$wpjobportal_company->id))); ?>"><?php echo esc_html(__('Cancel Payment', 'wp-job-portal')); ?> </a>
                    <button type="button" class="wjportal-company-act-btn" id="proceedPaymentBtn">
                        <?php echo esc_html(__('Proceed To Payment','wp-job-portal')); ?>
                    </button>
                <?php } ?>
        </div>
		<?php
	break;
    case 'payfeatured':
        do_action('wpjobportal_addons_proceedPayment_PerListing',$wpjobportal_company->id,'multicompany','mycompanies');
     break;

   case 'paycompany':
       do_action('wpjobportal_addons_proceedPayment_PerListing',$wpjobportal_company->id,'multicompany','mycompanies');
        break;
   case 'companies':
       if(in_array('multicompany', wpjobportal::$_active_addons)){
            $wpjobportal_mod = "multicompany";
       }else{
            $wpjobportal_mod = "company";
       }
    ?>
        <div class="wjportal-company-action">
            <a class="wjportal-company-act-btn wjportal-company-list-view-btn wjportal-list-act-btn-view" href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>$wpjobportal_mod, 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_company->aliasid))); ?>" title="<?php echo esc_attr(__('View company','wp-job-portal')); ?>">
                <?php echo esc_html(__('View Company','wp-job-portal')); ?>
            </a>
        </div>
        <?php
        break;
}
?>