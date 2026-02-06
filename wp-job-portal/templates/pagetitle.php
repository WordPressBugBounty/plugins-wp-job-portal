<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param module 		module name - optional
 * module => id
 *layouts => from which layouts
 *div uses for all is the same calling templates
 */
//Configuration
?>
<?php
	if ($wpjobportal_module) {
		// allow admin to pagetitle above wpjobportal breadcrumbs
		$wpjobportal_show_wpjobportal_page_title  = wpjobportal::$_config->getConfigurationByConfigName('show_wpjobportal_page_title');
		if($wpjobportal_show_wpjobportal_page_title == 1){
			echo '<div class="wjportal-page-heading">';
				switch ($wpjobportal_layout) {
					case 'company':
						if(WPJOBPORTALincluder::getJSModel('common')->isElegantDesignEnabled()){
                          if (isset($wpjobportal_data->name) && $wpjobportal_config_array['comp_name'] == 1) {
						 		echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_data->name));
				            }
			                $wpjobportal_curdate = date_i18n('Y-m-d');
			                $wpjobportal_featuredexpiry = date_i18n('Y-m-d', strtotime($wpjobportal_data->endfeatureddate));
			    			if ($wpjobportal_data->isfeaturedcompany == 1 && $wpjobportal_featuredexpiry >= $wpjobportal_curdate) { ?>
		    					<span class="wjportal-elegant-addon-featured-company">
									<img class="wjportal-elegant-addon-filter-search-field-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL) . '/includes/images/featured.png';?> " title="<?php echo esc_attr(__('Featured', 'wp-job-portal'));?>"  alt="<?php echo esc_attr(__('Featured', 'wp-job-portal')) ;?>" />
		    						<?php
									do_action('wpjobportal_addons_lable_comp_feature', $wpjobportal_data);
									echo esc_html__('Featured', 'wp-job-portal') ?>
								</span>
								<?php
							}
						}else{
                          echo esc_html(__('Company Detail', 'wp-job-portal'));
                        }
			        break;
					case 'mycompany':
				 		echo esc_html(__('My Companies', 'wp-job-portal'));
				 		/*if(in_array('multicompany',wpjobportal::$_active_addons)){
				 			echo '<a class="wjportal-header-btn" href='.esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'multicompany', 'wpjobportallt'=>'addcompany'))).'>'.esc_html(__('Add New','wp-job-portal')) .' '. esc_html(__('Company', 'wp-job-portal')) .'</a> ';
				 		}*/
					break;
					case 'companies':
				 		echo esc_html(__('Companies', 'wp-job-portal'));
					break;
					case 'myjob':
						echo esc_html(__('My Jobs', 'wp-job-portal'));
						//echo '<a class="wjportal-header-btn" href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'addjob'))).'>'. esc_html(__('Post a Job', 'wp-job-portal')).'</a>';
					break;
					case 'appliedres':
					 	echo esc_html(__('Job Applied Resume', 'wp-job-portal'));
					break;
					case 'jobdetail':
						if (!WPJOBPORTALincluder::getJSModel('common')->isElegantDesignEnabled()) { 
							echo esc_html(__('Job Detail','wp-job-portal'));
						} else {
							echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->title));
							$wpjobportal_curdate = date_i18n('Y-m-d');
							$wpjobportal_featuredexpiry = date_i18n('Y-m-d', strtotime($wpjobportal_job->endfeatureddate));
							if ($wpjobportal_job->isfeaturedjob == 1 && $wpjobportal_featuredexpiry >= $wpjobportal_curdate &&WPJOBPORTALincluder::getJSModel('common')->isElegantDesignEnabled()) {
								?>
								<span class="wjportal-elegant-addon-featured-job">
									<img class="wjportal-elegant-addon-filter-search-field-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL) . '/includes/images/featured.png';?> " title="<?php echo esc_attr(__('Featured', 'wp-job-portal'));?>"  alt="<?php echo esc_attr(__('Featured', 'wp-job-portal')) ;?>" />
								<?php echo esc_html__('Featured', 'wp-job-portal');?>
								</span>
								<?php
							}
						}
					break;
					case 'myapplied':
						echo esc_html(__('My Applied Jobs', 'wp-job-portal'));
					break;
					case 'newestjob':
						echo esc_html(__('Newest Jobs', 'wp-job-portal'));
					break;
					case 'jobsearch':
						echo esc_html(__('Search Job', 'wp-job-portal'));
					break;
					case 'companyinfo':
						if($wpjobportal_company) echo esc_html(__('Edit Company', 'wp-job-portal'));
						else echo esc_html(__('Add Company', 'wp-job-portal'));
						break;
					case 'comp':
						echo esc_html(__('Companies', 'wp-job-portal'));
					break;
					case 'addcompany':
						echo esc_html(__("Company Information",'wp-job-portal'));
					break;
					case 'addcomp':
						if($wpjobportal_job) echo esc_html(__('Edit Job', 'wp-job-portal'));
						else echo esc_html(__('Post a Job', 'wp-job-portal'));
					break;
					case 'folder':
						echo esc_html($wpjobportal_msg).' '. esc_html(__("Folder", 'wp-job-portal'));
					break;
					case 'myfolder':
					 	echo esc_html(__('My Folders', 'wp-job-portal'));
			        break;
					case 'viewfolder':
						echo esc_html(__('View Folder', 'wp-job-portal'));
					break;
					case 'mydepartments':
						echo esc_html(__('My Departments', 'wp-job-portal'));
			            /*echo ' <a class="wjportal-header-btn" href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'departments', 'wpjobportallt'=>'adddepartment'))) .'>'.  esc_html(__('Add New','wp-job-portal')) .' '. esc_html(__('Department', 'wp-job-portal')) .'</a> ';*/
					break;
					case 'viewcoverletter':
						echo esc_html(__('Cover Letter Detail', 'wp-job-portal'));
					break;
					case 'mycoverletters':
						echo esc_html(__('My Cover Letters', 'wp-job-portal'));
					break;
					case 'coverletter':
						$wpjobportal_msg = isset($coverletter) ? esc_html(__('Edit', 'wp-job-portal')) : esc_html(__('Add New', 'wp-job-portal'));
						echo esc_html($wpjobportal_msg) . ' ' . esc_html(__('Cover Letter', 'wp-job-portal'));
					break;
					case 'addjob':
						$wpjobportal_msg = isset($wpjobportal_job) ? esc_html(__('Edit', 'wp-job-portal')) : esc_html(__('Add New', 'wp-job-portal'));
						echo esc_html($wpjobportal_msg) . ' ' . esc_html(__('Job', 'wp-job-portal'));
					break;
					case 'login':
						$wpjobportal_msg = esc_html(__('Login ', 'wp-job-portal'));
						echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_msg));
					break;
					case 'departments':
						$wpjobportal_msg = isset($wpjobportal_departments) ? esc_html(__('Edit', 'wp-job-portal')) : esc_html(__('Add New', 'wp-job-portal'));
						echo esc_html($wpjobportal_msg) . ' ' . esc_html(__('Department', 'wp-job-portal'));
					break;
					case 'viewdepartment':
						echo esc_html(__('Department Detail', 'wp-job-portal'));
					break;
					case 'mydepartment':
						echo esc_html(__('Departments', 'wp-job-portal'));
					break;
					case 'jobbycatagory':
						echo esc_html(__('Jobs By Categories', 'wp-job-portal'));
						break;
					case 'reg':
						echo esc_html(__('Register Your Account', 'wp-job-portal'));
						break;
					case 'resumesearch':
						echo esc_html(__('Resume Search', 'wp-job-portal'));
						break;
					case 'resumelist':
						echo esc_html(__('Resume List', 'wp-job-portal'));
						break;
					case 'resumebycatagory':
						echo esc_html(__('Resume By Categories', 'wp-job-portal'));
						break;
					case 'departmentperlisting':
						echo esc_html(__('Pay per listing price to publish your ', 'wp-job-portal'). wpjobportal::wpjobportal_getVariableValue($wpjobportal_name) .' '.' : '.esc_html($wpjobportal_priceDepartmentlist));
						break;
					case 'coverletterperlisting':
						echo esc_html(__('Pay per listing price to publish your ', 'wp-job-portal'). wpjobportal::wpjobportal_getVariableValue($wpjobportal_name) .' '.' : '.esc_html($wpjobportal_priceCoverletterlist));
						break;
					case 'viewresume':
						echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_name));
						if(WPJOBPORTALincluder::getJSModel('common')->isElegantDesignEnabled()){
			                $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
			                $wpjobportal_curdate = date_i18n('Y-m-d');
			                $wpjobportal_featuredexpiry = date_i18n('Y-m-d', strtotime(wpjobportal::$_data[0]['personal_section']->endfeatureddate));
			                if (wpjobportal::$_data[0]['personal_section']->isfeaturedresume == 1 && $wpjobportal_featuredexpiry >= $wpjobportal_curdate) {
			                    $wpjobportal_featuredflag = false;
			                    echo '<span class="wjportal-elegant-addon-featured-resume">';
			                    do_action('wpjobportal_addons_feature_resume_lable',wpjobportal::$_data[0]['personal_section']);
			                    echo esc_html__('Featured', 'wp-job-portal');
			                    echo '</span>';
			                }
						}
						break;
					case 'myresume':
						echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_msg));
						break;
					case 'multiresumeadd':
					 	echo esc_html(__('My Resumes', 'wp-job-portal'));
						//do_action('wpjobportal_addon_resume_action_addResume');
		            	break;
		            case 'sendmessage':
					  echo esc_html(__('Send Message','wp-job-portal'));
						break;
					case 'message':
						echo esc_html(__('Messages','wp-job-portal'));
						break;
					case 'sendmessagejob':
						echo esc_html(__('Job Messages','wp-job-portal'));
						break;
					case 'visitorcanaddjob':
						echo esc_html($wpjobportal_data) .' '. esc_html(__("Job", 'wp-job-portal'));
						break;
					case 'jobalert':
						echo esc_html(__('Job Alert Info', 'wp-job-portal'));
						break;
					case 'resumesearch':
						echo esc_html(__('Resume Search', 'wp-job-portal'));
						break;

					case 'resumesearchlist':
						echo esc_html(__('Resume Saved Searches', 'wp-job-portal'));
						break;
					case 'purchasehistory':
						 echo esc_html(__('My Packages', 'wp-job-portal'));
						break;
					case 'mysubscriptions':
						  echo esc_html(__('My Subscription', 'wp-job-portal'));
						break;
					case 'mypackage':
						echo esc_html(__(' Packages', 'wp-job-portal'));
						break;
					case 'invoice':
						echo esc_html(__('My Invoices', 'wp-job-portal'));
						break;
					case 'shortListedJob':
						echo esc_html(__('Short Listed Jobs', 'wp-job-portal'));
						break;
					case 'jobtype':
						echo esc_html(__('Jobs By Types', 'wp-job-portal'));
						break;
					case 'employer_cp':
						echo esc_html(__('Dashboard','wp-job-portal'));
						break;
					case 'update':
						echo esc_html(__('Edit Profile', 'wp-job-portal'));
						break;
					case 'folderressume':
						echo esc_html(__('Folder Resume', 'wp-job-portal'));
						break;
					case 'jobcities':
						echo esc_html(__('Jobs By Cities', 'wp-job-portal'));
						break;
					case 'featuredjobs':
						echo esc_html(__('Featured Jobs', 'wp-job-portal'));
						break;
					case 'featuredcompanies':
						echo esc_html(__('Featured Companies', 'wp-job-portal'));
						break;
					case 'featuredresumes':
						echo esc_html(__('Featured Resumes', 'wp-job-portal'));
						break;
				}
			echo '</div>';
		}
		if(!WPJOBPORTALincluder::getJSModel('common')->isElegantDesignEnabled()){
			WPJOBPORTALbreadcrumbs::getBreadcrumbs();
		}
	}
?>
