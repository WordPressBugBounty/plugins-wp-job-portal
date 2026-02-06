<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
 /**
 * @param job      job object - optional
 */
?>
<?php
if (!isset($wpjobportal_company)) {
	$wpjobportal_company[0]=null;
	$wpjobportal_comp = isset(wpjobportal::$_data[0]) ? wpjobportal::$_data[0]:'';
} else if (isset($wpjobportal_company)) {
	$wpjobportal_comp = $wpjobportal_company;
} else {
	$wpjobportal_comp='';
}

/**
* @param wp job portal url
* redirection for More than One Company
* # multicompany vs company
**/
if(in_array('multicompany',wpjobportal::$_active_addons)){
    // Mudlue
    $wpjobportal_mod = "multicompany";
}else{
    $wpjobportal_mod = "company";
}
$wpjobportal_published_fields = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldOrderingData(1);
switch ($wpjobportal_layout) {
	case 'complogo':
        $wpjobportal_data_class = (isset(wpjobportal::$_data[2]['logo'])) ? 'two_column' : 'one_column';
        $wpjobportal_logopath = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('employer');
        $wpjobportal_class = '';
        if(!empty($wpjobportal_classname)){
            $wpjobportal_class = $wpjobportal_classname;
        }
		if(isset($wpjobportal_published_fields['logo']) && $wpjobportal_published_fields['logo'] != ''){
            if ($wpjobportal_comp->logofilename) {
                $wpjobportal_data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
                $wpjobportal_wpdir = wp_upload_dir();
                $wpjobportal_logopath = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/employer/comp_' . $wpjobportal_comp->id . '/logo/' . $wpjobportal_comp->logofilename;
            } ?>
            <div class="wjportal-company-logo">
                <?php
                $wpjobportal_company_url = '#';
                if(!empty($wpjobportal_comp->aliasid)){
                    $wpjobportal_company_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>$wpjobportal_mod, 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_comp->aliasid));
                }
                ?>
           	    <a href="<?php echo esc_url($wpjobportal_company_url); ?>">
               		<img src="<?php echo esc_url($wpjobportal_logopath); ?>" class="<?php echo esc_attr($wpjobportal_class);?>" alt="<?php echo esc_attr(__('Company logo','wp-job-portal')); ?>" />
            	</a>
            </div>
            <?php
        } ?>
        <div>
            <?php do_action('wpjobportal_addons_social_share_company',$wpjobportal_comp,$wpjobportal_data_class); ?>
        </div>
        <?php
    break;
}
?>
