<?php
if (!defined('ABSPATH'))
die('Restricted Access');
?>
<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper">
    <div id="wpjobportaladmin-leftmenu">
        <?php  WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <?php
            $wpjobportal_msgkey = WPJOBPORTALincluder::getJSModel('thirdpartyimport')->getMessagekey();
            WPJOBPORTALMessages::getLayoutMessage($wpjobportal_msgkey);
        ?>
        <!-- top bar -->
        <div id="wpjobportal-wrapper-top">
            <div id="wpjobportal-wrapper-top-left">
                <div id="wpjobportal-breadcrumbs">
                    <ul>
                        <li>
                            <a href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal')); ?>" title="<?php echo esc_attr(__('dashboard','wp-job-portal')); ?>">
                                <?php echo esc_html(__('Dashboard','wp-job-portal')); ?>
                            </a>
                        </li>
                        <li><?php echo esc_html(__('Import Third Party Data','wp-job-portal')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="wpjobportal-wrapper-top-right">
                <div id="wpjobportal-config-btn">
                    <a href="admin.php?page=wpjobportal_configuration" title="<?php echo esc_attr(__('configuration','wp-job-portal')); ?>">
                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/config.png">
                   </a>
                </div>
                <div id="wpjobportal-help-btn" class="wpjobportal-help-btn">
                    <a href="admin.php?page=wpjobportal&wpjobportallt=help" title="<?php echo esc_attr(__('help','wp-job-portal')); ?>">
                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/help.png">
                   </a>
                </div>
                <div id="wpjobportal-vers-txt">
                    <?php echo esc_html(__('Version','wp-job-portal')).': '; ?>
                    <span class="wpjobportal-ver"><?php echo esc_html(WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue('versioncode')); ?></span>
                </div>
            </div>
        </div>
        <!-- top head -->
        <?php WPJOBPORTALincluder::getTemplate('templates/admin/pagetitle',array('wpjobportal_module' => 'thirdpartyimport' , 'wpjobportal_layouts' => 'importdata')); ?>
        <!-- page content -->
        <div id="wpjobportal-admin-wrapper" class="wpjobportal-importdata-adddons-main-wrap">
            <?php
                $wpjobportal_count_for = wpjobportal::$_data['count_for'];
                $wpjobportal_entity_counts = [];
                if($wpjobportal_count_for > 0 &&  !empty(wpjobportal::$_data['entity_counts'])){
                    $wpjobportal_entity_counts = wpjobportal::$_data['entity_counts'];
                }
                //echo '<pre>';print_r($wpjobportal_entity_counts);echo '</pre>';

                // plugins for which we support importing data
                $wpjobportal_plguins_array = [];

                // plugin data
                // $wpjobportal_plguins_array['dummp_plgn'] = [];
                // $wpjobportal_plguins_array['dummp_plgn']['name'] = esc_html(__('Dummy Plugin','wp-job-portal'));
                // $wpjobportal_plguins_array['dummp_plgn']['path'] = "dummy-manager/dummy-manager.php"; // needed to check if plugin is active
                // $wpjobportal_plguins_array['dummp_plgn']['internalid'] = 2; // value used to identfy the plugin

                // plugin data
                $wpjobportal_plguins_array['wp_job_manager'] = [];
                $wpjobportal_plguins_array['wp_job_manager']['name'] = esc_html(__('WP Job Manager','wp-job-portal'));
                $wpjobportal_plguins_array['wp_job_manager']['path'] = "wp-job-manager/wp-job-manager.php";  // needed to check if plugin is active
                $wpjobportal_plguins_array['wp_job_manager']['internalid'] = 1; // value used to identfy the plugin

                // plugin data
                // $wpjobportal_plguins_array['dummp_plgn2'] = [];
                // $wpjobportal_plguins_array['dummp_plgn2']['name'] = esc_html(__('Dummy Plugin 2','wp-job-portal'));
                // $wpjobportal_plguins_array['dummp_plgn2']['path'] = "dummy-manager/dummy-manager.php"; // needed to check if plugin is active
                // $wpjobportal_plguins_array['dummp_plgn2']['internalid'] = 3; // value used to identfy the plugin


            foreach ($wpjobportal_plguins_array as $plugin) {
                // check if Plugin is active
                if($wpjobportal_count_for != $plugin['internalid']){
                    $wpjobportal_extr_clss = 'wpjobportal-plugin-notinstalled';
                    if ( is_plugin_active( $plugin['path'] ) ) {
                        $wpjobportal_extr_clss = '';
                    }
                ?>
                    <div class="wpjobportal-plugins-imprt-datasec <?php echo esc_attr($wpjobportal_extr_clss);?>">
                        <span class="wpjobportal-plugins-imprt-data-plgnnme"><?php echo esc_html($plugin['name']); ?></span>
                        <?php if($wpjobportal_extr_clss != ''){ ?>
                            <span class="wpjobportal-plugins-imprt-databtn">
                                <img class="wpjobportal-plugins-imprterror-image" alt="<?php echo esc_attr(__('icon','wp-job-portal')); ?>" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/imprt-icon.png" />
                                <?php echo esc_html(__('Plugin not installed','wp-job-portal')); ?>
                            </span>
                        <?php }else{ ?>
                            <a class="wpjobportal-plugins-imprt-databtn" href="<?php echo esc_url_raw(admin_url("admin.php?page=wpjobportal_thirdpartyimport&wpjobportallt=importdata&selected_plugin=".$plugin['internalid'])); ?>" title="<?php echo esc_attr(__('Fetch Data','wp-job-portal')); ?>"><?php echo esc_html(__('Fetch Data','wp-job-portal')); ?></a>
                        <?php } ?>
                    </div>
                <?php
                }else{
                    if(!empty($wpjobportal_entity_counts)){ ?>
                        <div class="wpjobportal-singleplugin-imprt-data-sec">
                            <span class="wpjobportal-singleplugin-imprt-datatitle"><?php echo esc_html($plugin['name']); ?></span>


                            <?php foreach ($wpjobportal_entity_counts as $wpjobportal_entity_name => $wpjobportal_entity_val) {
                                $wpjobportal_entity_name = ucwords(str_replace('_', ' ', $wpjobportal_entity_name));
                                $wpjobportal_extr_clss = '';
                                if($wpjobportal_entity_name == "Tags"){
                                    if(!in_array('tag', wpjobportal::$_active_addons)){
                                        $wpjobportal_extr_clss = 'wpjobportal-singleplugin-imprt-data-addonnot-instllwrp';
                                    }
                                }
                            ?>
                                <div class="wpjobportal-singleplugin-imprt-datadisc <?php echo esc_attr($wpjobportal_extr_clss);?>">
                                    <?php echo esc_html($wpjobportal_entity_val).'&nbsp;'.esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_entity_name)).'&nbsp;'.esc_html(__('found','wp-job-portal'));

                                    if($wpjobportal_entity_name == "Resumes"){
                                        if(!in_array('advanceresumebuilder', wpjobportal::$_active_addons)){
                                         ?>
                                            <br>
                                            <span class="wpjobportal-import-data-addon-message"><?php echo esc_html(__('Advanced Resume Builder Addon missing, resume sections data will not be imported!','wp-job-portal')); ?></span>
                                            <?php
                                        }
                                    }
                                    if($wpjobportal_extr_clss != ''){ ?>
                                    <span class="wpjobportal-singleplugin-imprt-data-addonnot-instll">
                                        <img class="wpjobportal-plugins-imprterror-image" alt="<?php echo esc_attr(__('icon','wp-job-portal')); ?>" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/imprt-icon.png" />
                                        <?php echo esc_html(__('Addon not installed please install addon first.','wp-job-portal')); ?>

                                    </span> <?php
                                    } ?>
                                </div>
                                <?php
                            } ?>
                            <div class="wpjobportal-singleplugin-imprt-databtn-wrp">
                                    <a class="wpjobportal-singleplugin-imprt-databtn" title="<?php echo esc_attr(__('Import Data','wp-job-portal')); ?>" href="<?php echo esc_url_raw(wp_nonce_url(admin_url("admin.php?page=wpjobportal_thirdpartyimport&task=importjobmanagerdata&action=wpjobportaltask&selected_plugin=".$plugin['internalid']),'wpjobportal_job_manager_import_nonce')) ?>"><?php echo esc_html(__('Import Data','wp-job-portal')); ?></a>
                            </div>
                        </div><?php
                    }
                }
            } ?>
        </div>
    </div>
</div>
<?php

/*

<div class="wpjobportal-singleplugin-imprt-datadisc wpjobportal-singleplugin-imprt-data-addonnot-instllwrp">
                                <?php echo esc_html(__('10254 Departments found','wp-job-portal')); ?>

                            </div>

*/

?>
