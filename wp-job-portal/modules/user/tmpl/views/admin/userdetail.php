<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
* @param wp-job-portal User Details
*/
?>
<?php
	switch ($wpjobportal_layout) {
		case 'user':
			?>
			<div class="wpjobportal-user-cnt-wrp">
				<div class="wpjobportal-user-middle-wrp wpjob-portal-role-info-uid-<?php echo esc_attr($wpjobportal_user->id);?>">
					<div class="wpjobportal-user-data">
						<a class="wpjobportal-user-name" href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal_user&wpjobportallt=userdetail&id='.esc_attr($wpjobportal_user->id))); ?>" title="<?php echo esc_attr(__('user name','wp-job-portal')); ?>">
			            	<?php echo esc_html($wpjobportal_user->first_name) . ' ' . esc_html($wpjobportal_user->last_name); ?>
			            </a>
					</div>
					<div class="wpjobportal-user-data">
						<span class="wpjobportal-user-role role-<?php echo ($wpjobportal_user->roleid == 1) ? 'empl' : 'jobs'; ?>">
			                <?php
				                if($wpjobportal_user->roleid == 1){
				                    echo esc_html(__('Employer','wp-job-portal'));
				                }elseif($wpjobportal_user->roleid == 2){
				                    echo esc_html(__('Job seeker','wp-job-portal'));
				                }
			                ?>
			            </span>
					</div>
					<div class="wpjobportal-user-data">
					    <div class="wpjobportal-user-data-text">
					        <span class="wpjobportal-user-data-title">
					        	<?php echo esc_html(__('ID', 'wp-job-portal')) . ': '; ?>
					        </span>
				        	<span class="wpjobportal-user-data-value">
				        		<?php echo esc_html($wpjobportal_user->id); ?>
				        	</span>
					    </div>

					    <?php
					    /*
					    if($wpjobportal_user->roleid == 2){ ?>
						    <div class="wpjobportal-user-data-text">
						        <span class="wpjobportal-user-data-title">
						        	<?php echo esc_html(__('Resume', 'wp-job-portal')) . ': '; ?>
						        </span>
					        	<span class="wpjobportal-user-data-value">
					        		<?php echo esc_html($wpjobportal_user->resume_first_name) . ' ' . esc_html($wpjobportal_user->resume_last_name); ?>
					        	</span>
						    </div>
					    <?php }elseif($wpjobportal_user->roleid == 1){ ?>
						    <div class="wpjobportal-user-data-text">
						        <span class="wpjobportal-user-data-title">
						        	<?php echo esc_html(__('Company', 'wp-job-portal')) . ': '; ?>
						        </span>
						        <span class="wpjobportal-user-data-value">
						        	<?php echo esc_html($wpjobportal_user->companyname); ?>
						        </span>
						    </div>
					    <?php } */?>

					    <div class="wpjobportal-user-data-text">
					        <span class="wpjobportal-user-data-title">
					        	<?php echo esc_html(__('Group', 'wp-job-portal')) . ': '; ?>
					        </span>
				        	<span class="wpjobportal-user-data-value">
				        		<?php echo esc_html(WPJOBPORTALincluder::getJSModel('user')->getWPRoleNameById($wpjobportal_user->wpuid)); ?>
				        	</span>
					    </div>
					    <div class="wpjobportal-user-data-text">
					        <span class="wpjobportal-user-data-title">
					        	<?php echo esc_html(__('User Name', 'wp-job-portal')) . ': '; ?>
					        </span>
				        	<span class="wpjobportal-user-data-value">
				        		<?php echo esc_html($wpjobportal_user->user_login); ?>
				        	</span>
					    </div>
					    <div class="wpjobportal-user-data-text-role-info wpjobportal-user-data-text" >
					    </div>
					    <div class="wpjobportal-user-data-text">
					        <a href="#" class="wpjobportal-user-data-text-role-info-btn" onclick="getUserRoleBasedInfo(<?php echo esc_js($wpjobportal_user->roleid);?>,<?php echo esc_js($wpjobportal_user->id);?>)"><?php echo esc_html(__('Show Info', 'wp-job-portal'));?></a>
					    </div>
					</div>
				</div>
			</div>
			<?php
			break;
		case 'userdetail':
			?>
			 	<div class="wpjobportal-user-cnt-wrp">
			 		<div class="wpjobportal-user-middle-wrp">
			            <div class="wpjobportal-user-data">
			                <span class="wpjobportal-user-name">
			                	<?php echo esc_html($wpjobportal_user->first_name).' '.esc_html($wpjobportal_user->last_name); ?>
			                </span>
		             	</div>
			            <div class="wpjobportal-user-data">
			            	<span class="wpjobportal-user-role role-<?php echo ($wpjobportal_user->roleid == 1) ? 'empl' : 'jobs'; ?>">
		                        <?php if($wpjobportal_user->roleid == 1){
		                            echo esc_html(__('Employer','wp-job-portal'));
		                            }elseif($wpjobportal_user->roleid == 2){
		                                echo esc_html(__('Job seeker','wp-job-portal'));
		                            }
	                            ?>
		                    </span>
		            	</div>
		            	<div class="wpjobportal-user-data">
			            	<div class="wpjobportal-user-data-text">
			                    <span class="wpjobportal-user-data-title">
			                    	<?php echo esc_html(__('ID','wp-job-portal')); ?> :
			                    </span>
			                    <span class="wpjobportal-user-data-value">
			                    	<?php echo esc_html($wpjobportal_user->id); ?>
			                    </span>
			                </div>
			                <div class="wpjobportal-user-data-text">
			                    <span class="wpjobportal-user-data-title">
			                    	<?php echo esc_html(__('Email','wp-job-portal')); ?> :
			                    </span>
			                    <span class="wpjobportal-user-data-value">
			                    	<?php echo esc_html($wpjobportal_user->emailaddress); ?>
			                    </span>
			                </div>
			                <div class="wpjobportal-user-data-text">
			                    <span class="wpjobportal-user-data-title">
			                    	<?php echo esc_html(__('Group','wp-job-portal')); ?> :
			                    </span>
			                    <span class="wpjobportal-user-data-value">
			                    	<?php echo esc_html(WPJOBPORTALincluder::getJSModel('user')->getWPRoleNameById($wpjobportal_user->uid));  ?>
			                    </span>
			                </div>
			                <div class="wpjobportal-user-data-text">
			                    <span class="wpjobportal-user-data-title">
			                    	<?php echo esc_html(__('Created','wp-job-portal')); ?> :
			                    </span>
			                    <span class="wpjobportal-user-data-value">
			                    	<?php echo esc_html(date_i18n(wpjobportal::$_configuration['date_format'], strtotime($wpjobportal_user->created) )); ?>
			                    </span>
			                </div>
			                <div class="wjportal-custom-field-wrp">
			                    <?php
			                        $wpjobportal_customfields = WPJOBPORTALincluder::getObjectClass('customfields')->userFieldsData(4);
			                            foreach ($wpjobportal_customfields as $wpjobportal_field) {
			                                $wpjobportal_showCustom =  wpjobportal::$_wpjpcustomfield->showCustomFields($wpjobportal_field,12,$wpjobportal_user->params);
			                                echo wp_kses($wpjobportal_showCustom, WPJOBPORTAL_ALLOWED_TAGS);
			                            }
			                    ?>
			                </div>
		                </div>
		            </div>
	            </div>
		        <?php
			break;

		default:
			# code...
			break;
	}
?>


