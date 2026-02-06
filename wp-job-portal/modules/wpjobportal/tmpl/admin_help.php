<?php
    if (!defined('ABSPATH'))
        die('Restricted Access');
    wp_enqueue_script('wpjobportal-res-tables', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/responsivetable.js');
?>
<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper">
    <!-- left menu -->
    <div id="wpjobportaladmin-leftmenu">
        <?php  WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <?php
            $wpjobportal_msgkey = WPJOBPORTALincluder::getJSModel('wpjobportal')->getMessagekey();
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
                        <li><?php echo esc_html(__('Help','wp-job-portal')); ?></li>
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
        <?php WPJOBPORTALincluder::getTemplate('templates/admin/pagetitle',array('wpjobportal_module' => 'wpjobportal' , 'wpjobportal_layouts' => 'help')); ?>
        <!-- page content -->
        <div id="wpjobportal-admin-wrapper" class="p0 bg-n bs-n">
            <!-- help page  -->
            <div class="wpjobportal-help-top">
                <div class="wpjobportal-help-top-left">
                    <div class="wpjobportal-help-top-left-cnt-img">
                        <img alt="<?php echo esc_attr(__('Help icon','wp-job-portal')); ?>" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/help-page/support-icon.jpg" />
                    </div>
                    <div class="wpjobportal-help-top-left-cnt-info">
                        <h2><?php echo esc_html(__('We Are Here to Help You','wp-job-portal')); ?></h2>
                        <p><?php echo esc_html(__('WP Job Portal is a simple yet powerful job board plugin with a step-by-step YouTube guide to ensure ease of use.','wp-job-portal')); ?></p>
                        <a href="https://www.youtube.com/channel/UCk_qYTzV6gusKmMHxTrgU2Q" target="_blank" class="wpjobportal-help-top-middle-action" title="<?php echo esc_attr(__('View all videos','wp-job-portal')); ?>"><img alt="<?php echo esc_attr(__('Video icon','wp-job-portal')); ?>" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/help-page/play-icon.jpg" /><?php echo esc_html(__('View All Videos','wp-job-portal')); ?></a>
                    </div>
                </div>
                <div class="wpjobportal-help-top-right">
                    <div class="wpjobportal-help-top-right-cnt-img">
                        <img alt="<?php echo esc_attr(__('WP JOB PORTAL icon','wp-job-portal')); ?>" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/help-page/support.png" />
                    </div>
                    <div class="wpjobportal-help-top-right-cnt-info">
                        <h2><?php echo esc_html(__('WP Job Portal Support','wp-job-portal')); ?></h2>
                        <p><?php echo esc_html(__("WP Job Portal offers timely customer support. If you have any queries, we're here to help you every step of the way.",'wp-job-portal')); ?></p>
                        <a target="_blank" href="https://wpjobportal.com/support/" class="wpjobportal-help-top-middle-action second" title="<?php echo esc_attr(__('Submit ticket','wp-job-portal')); ?>"><img alt="<?php echo esc_attr(__('Video icon','wp-job-portal')); ?>" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/help-page/ticket.png" /><?php echo esc_html(__('Submit Ticket','wp-job-portal')); ?></a>
                    </div>
                </div>
            </div>
            <div class="wpjobportal-help-btm">
                <!-- job portal -->
                <div class="wpjobportal-help-btm-wrp">
                    <h2 class="wpjobportal-help-btm-title"><?php echo esc_html(__('Plugin Walkthrough','wp-job-portal')); ?></h2>
                    <?php
                        $title = esc_html(__('WP Job Portal', 'wp-job-portal'));
                        $wpjobportal_url = 'UmO6EwsNMZo';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);
                    ?>
                </div>
                <div class="wpjobportal-help-btm-wrp">
                    <h2 class="wpjobportal-help-btm-title"><?php echo esc_html(__('How to Setup','wp-job-portal')); ?></h2>
                    <?php
                        $title = esc_html(__('How to setup WP Job Portal', 'wp-job-portal'));
                        $wpjobportal_url = 'eHUMwjFuV2I';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);
                    ?>
                </div>
                <!-- jobs -->
                <div class="wpjobportal-help-btm-wrp">
                    <h2 class="wpjobportal-help-btm-title"><?php echo esc_html(__('Jobs','wp-job-portal')); ?></h2>
                    <?php
                        $title = esc_html(__('Add Job', 'wp-job-portal'));
                        $wpjobportal_url = 'F0j8iDirJGU';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);

                        $title = esc_html(__('Copy Job', 'wp-job-portal'));
                        $wpjobportal_url = 'zU10SjrwgAM';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);

                        $title = esc_html(__('Featured Jobs', 'wp-job-portal'));
                        $wpjobportal_url = 'PrBB2Znkfu4';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);

                        $title = esc_html(__('Shortlist Jobs', 'wp-job-portal'));
                        $wpjobportal_url = 'WgAdjOC7Uoo';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);

                        $title = esc_html(__('Visitor Create a Job', 'wp-job-portal'));
                        $wpjobportal_url = 'xx2VWlbwuGw';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);

                        $title = esc_html(__('Job Alert', 'wp-job-portal'));
                        $wpjobportal_url = 'iaYjzbceigg';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);

                        $title = esc_html(__('Tell a Friend', 'wp-job-portal'));
                        $wpjobportal_url = 'DRNLvfBsbSs';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);
                        
                        $title = esc_html(__('Apply as a Visitor', 'wp-job-portal'));
                        $wpjobportal_url = 'YiDasKFGhjY';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);
                    ?>
                </div>
                <!-- resume -->
                <div class="wpjobportal-help-btm-wrp">
                    <h2 class="wpjobportal-help-btm-title"><?php echo esc_html(__('Resume','wp-job-portal')); ?></h2>
                    <?php
                        $title = esc_html(__('Add Resume', 'wp-job-portal'));
                        $wpjobportal_url = 'Fy6eWUya2GY';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);

                        $title = esc_html(__('Featured Resume', 'wp-job-portal'));
                        $wpjobportal_url = 'RQteSRpy5gM';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);

                        $title = esc_html(__('Advance Resume', 'wp-job-portal'));
                        $wpjobportal_url = 'B1YoPITnjPY';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);

                        $title = esc_html(__('Resume Search', 'wp-job-portal'));
                        $wpjobportal_url = 'WvwHLsg5XGk';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);

                        $title = esc_html(__('Resume Actions', 'wp-job-portal'));
                        $wpjobportal_url = '-QgsW3YL7F4&ab_channel=WPJobPortal';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);

                        
                    ?>
                </div>
                <!-- company -->
                <div class="wpjobportal-help-btm-wrp">
                    <h2 class="wpjobportal-help-btm-title"><?php echo esc_html(__('Company','wp-job-portal')); ?></h2>
                    <?php
                        $title = esc_html(__('Create Company', 'wp-job-portal'));
                        $wpjobportal_url = 'm9dp0EzzIdI';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);

                        $title = esc_html(__('Featured Companies', 'wp-job-portal'));
                        $wpjobportal_url = 'ShHfBG516NM';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);
                    ?>
                </div>
                <!-- Resume Data Management -->
                <div class="wpjobportal-help-btm-wrp">
                    <h2 class="wpjobportal-help-btm-title"><?php echo esc_html(__('Resume Data Management','wp-job-portal')); ?></h2>
                    <?php
                        $title = esc_html(__('PDF', 'wp-job-portal'));
                        $wpjobportal_url = 'lIaJhOr4fX8';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);
                                                
                        $title = esc_html(__('Export (csv)', 'wp-job-portal'));
                        $wpjobportal_url = 'G-uwkNg5Za4';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);
                                                
                        $title = esc_html(__('Print', 'wp-job-portal'));
                        $wpjobportal_url = 'Ao_9ald1Z4g';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);
                    ?>
                </div>
                <!-- Configuration Tutorials -->
                <div class="wpjobportal-help-btm-wrp">
                    <h2 class="wpjobportal-help-btm-title"><?php echo esc_html(__('Configuration Based Tutorials','wp-job-portal')); ?></h2>
                    <?php
                        $title = esc_html(__('Credit System', 'wp-job-portal'));
                        $wpjobportal_url = '_BLvpMvnUis';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);

                        $title = esc_html(__('Slugs', 'wp-job-portal'));
                        $wpjobportal_url = 'E8bKqHEK2zY';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);

                        $title = esc_html(__('Captcha', 'wp-job-portal'));
                        $wpjobportal_url = '9DyCo7sh2ng';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);
                        
                        $title = esc_html(__('Install Addons', 'wp-job-portal'));
                        $wpjobportal_url = 'VW4KqwDoWNw';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);
                    ?>
                </div>
                <!-- Theme Installation-->
                <div class="wpjobportal-help-btm-wrp">
                    <h2 class="wpjobportal-help-btm-title"><?php echo esc_html(__('Theme Installation','wp-job-portal')); ?></h2>
                    <?php
                        $title = esc_html(__('WP Job Portal Theme  Installation', 'wp-job-portal'));
                        $wpjobportal_url = 'qZyfgDAtCX0';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);
                    ?>
                </div>
                <!-- System Management-->
                <div class="wpjobportal-help-btm-wrp">
                    <h2 class="wpjobportal-help-btm-title"><?php echo esc_html(__('System Management','wp-job-portal')); ?></h2>
                    <?php                   
                        $title = esc_html(__('Activity Log and System Errors', 'wp-job-portal'));
                        $wpjobportal_url = 'MZKv9jltC9M';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);
                                                
                        $title = esc_html(__('Reports', 'wp-job-portal'));
                        $wpjobportal_url = '0kj6JmMbZsk';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);

                        $title = esc_html(__('Shortcodes', 'wp-job-portal'));
                        $wpjobportal_url = 'ySAb0uKgxLk';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);
                    ?>
                </div>
                 <!-- Custom Fields-->
                <div class="wpjobportal-help-btm-wrp">
                    <h2 class="wpjobportal-help-btm-title"><?php echo esc_html(__('Custom Fields','wp-job-portal')); ?></h2>
                    <?php
                        $title = esc_html(__('Custom Fields', 'wp-job-portal'));
                        $wpjobportal_url = 'JVWShD3SeuQ';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);
                    ?>
                </div>
                <!-- social apps -->
                <div class="wpjobportal-help-btm-wrp">
                    <h2 class="wpjobportal-help-btm-title"><?php echo esc_html(__('Social Apps','wp-job-portal')); ?></h2>
                    <?php
                        $title = esc_html(__('Social Share', 'wp-job-portal'));
                        $wpjobportal_url = 'Xw88w-21VWQ';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);

                        $title = esc_html(__('Social Login', 'wp-job-portal'));
                        $wpjobportal_url = 'XM6IUzsUw9o';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);
                    ?>
                </div>
                <!-- Miscellaneous Tutorials -->
                <div class="wpjobportal-help-btm-wrp">
                    <h2 class="wpjobportal-help-btm-title"><?php echo esc_html(__('Miscellaneous Tutorials','wp-job-portal')); ?></h2>
                    <?php
                        $title = esc_html(__('Widget Settings', 'wp-job-portal'));
                        $wpjobportal_url = 'wC7g6ELwMGE';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);
                        
                        $title = esc_html(__('Tags', 'wp-job-portal'));
                        $wpjobportal_url = 'hE6-blhggeg';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);
                        
                        $title = esc_html(__('RSS', 'wp-job-portal'));
                        $wpjobportal_url = '_m2Y2WuvzN8';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);
                        
                        
                        $title = esc_html(__('Folders', 'wp-job-portal'));
                        $wpjobportal_url = 'hLj8fsgwE6E';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);

                        
                        $title = esc_html(__('Departments', 'wp-job-portal'));
                        $wpjobportal_url = 'HNopX7oU6NU';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);

                        
                        $title = esc_html(__('Color Manager', 'wp-job-portal'));
                        $wpjobportal_url = 'ERjwnU7ps98';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);
                        
                        $title = esc_html(__('Address Data', 'wp-job-portal'));
                        $wpjobportal_url = 'N2PqbNOtqs4';
                        wpjobportal_printVideoPlaylist($title, $wpjobportal_url);
                        

                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
    function wpjobportal_printVideoPlaylist($video_title,$video_url){
        $wpjobportal_html = '
        <div class="wpjobportal-help-btm-cnt">
            <a href="https://www.youtube.com/watch?v='.$video_url.'" class="wpjobportal-help-btm-link"  target="_blank" title="'.esc_attr($video_title).'">
                <div class="wpjobportal-help-btm-cnt-img">
                    <img alt="'.esc_attr($video_title).'" src="'. esc_url(WPJOBPORTAL_PLUGIN_URL) .'includes/images/help-page/video-icon.jpg" />
                </div>
                <div class="wpjobportal-help-btm-cnt-title">
                    <span>'.esc_html($video_title).'</span>
                </div>
            </a>
        </div>
        ';
        echo wp_kses($wpjobportal_html, WPJOBPORTAL_ALLOWED_TAGS);
    }
      
?>
