<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<script >
    function resetFrom() {
        document.getElementById('searchname').value = '';
        document.getElementById('status').value = '';
        document.getElementById('wpjobportalform').submit();
    }
</script>
<?php
    wp_enqueue_script( 'wpjobportal-res-tables', WPJOBPORTAL_PLUGIN_URL . 'includes/js/responsivetable.js', array(), wpjobportal_asset_version('includes/js/responsivetable.js'), false );
    if(!WPJOBPORTALincluder::getTemplate('templates/admin/header',array('wpjobportal_module' => 'tag'))){
        return;
    }
?>
<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper">
    <!-- left menu -->
    <div id="wpjobportaladmin-leftmenu">
        <?php WPJOBPORTALincluder::getTemplate('templates/admin/leftmenue',array('wpjobportal_module' => 'tag')); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <!-- top bar -->
        <div id="wpjobportal-wrapper-top">
            <div id="wpjobportal-wrapper-top-left">
                <div id="wpjobportal-breadcrumbs">
                    <ul>
                        <li>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal')); ?>" title="<?php echo esc_attr__('Dashboard','wp-job-portal'); ?>">
                                <?php echo esc_html__('Dashboard','wp-job-portal'); ?>
                            </a>
                        </li>
                        <li><?php echo esc_html__('Tags','wp-job-portal'); ?></li>
                    </ul>
                </div>
            </div>
            <div id="wpjobportal-wrapper-top-right">
                <div id="wpjobportal-config-btn">
                    <a href="admin.php?page=wpjobportal_configuration" title="<?php echo esc_attr__('Configuration','wp-job-portal'); ?>">
                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/control_panel/dashboard/config.png'); ?>">
                   </a>
                </div>
                <div id="wpjobportal-help-btn" class="wpjobportal-help-btn">
                    <a href="admin.php?page=wpjobportal&wpjobportallt=help" title="<?php echo esc_attr__('Help','wp-job-portal'); ?>">
                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/control_panel/dashboard/help.png'); ?>">
                   </a>
                </div>
                <div id="wpjobportal-vers-txt">
                    <?php echo esc_html__('Version','wp-job-portal').': '; ?>
                    <span class="wpjobportal-ver"><?php echo esc_html(WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue('versioncode')); ?></span>
                </div>
            </div>
        </div>
        <!-- top head -->
        <?php WPJOBPORTALincluder::getTemplate('templates/admin/pagetitle',array('wpjobportal_module' => 'tag','wpjobportal_layouts' => 'tags')); ?>
        <!-- page content -->
        <div id="wpjobportal-admin-wrapper" class="p0 bg-n bs-n">
            <!-- quick actions -->
            <?php WPJOBPORTALincluder::getTemplate('views/multioperation',array('module_name' => 'tag')); ?>
            <!-- filter form -->
            <form class="wpjobportal-filter-form" name="wpjobportalform" id="wpjobportalform" method="post" action="<?php echo esc_url(admin_url("admin.php?page=wpjobportal_tag&wpjobportallt=tags")); ?>">
                <?php WPJOBPORTALincluder::getTemplate('views/filter',array('module_name' => 'tag')); ?>
            </form>
            <?php
                if (!empty(wpjobportal::$_data[0])) {
                    ?>
                    <form id="wpjobportal-list-form" method="post" action="<?php echo esc_url(admin_url("admin.php?page=wpjobportal_tag")); ?>">
                        <table id="wpjobportal-table" class="wpjobportal-table">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" name="selectall" id="selectall" value="">
                                    </th>
                                    <th class="wpjobportal-text-left">
                                        <?php echo esc_html__('Tag Name', 'wp-job-portal'); ?>
                                    </th>
                                    <th>
                                        <?php echo esc_html__('Tag For', 'wp-job-portal'); ?>
                                    </th>
                                    <th>
                                        <?php echo esc_html__('Published', 'wp-job-portal'); ?>
                                    </th>
                                    <th>
                                        <?php echo esc_html__('Action', 'wp-job-portal'); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum', 'get', 1);
                                    $wpjobportal_pageid = ($wpjobportal_pagenum > 1) ? '&pagenum=' . $wpjobportal_pagenum : '';
                                    for ($wpjobportal_i = 0, $wpjobportal_n = count(wpjobportal::$_data[0]); $wpjobportal_i < $wpjobportal_n; $wpjobportal_i++) {
                                        $wpjobportal_row = wpjobportal::$_data[0][$wpjobportal_i];
                                        $link = admin_url('admin.php?page=wpjobportal_tag&wpjobportallt=formtag&wpjobportalid=' . $wpjobportal_row->id);
                                        WPJOBPORTALincluder::getTemplate('views/detail',array('module_name' => 'tag','row' => $wpjobportal_row,'link' => $link,'pageid'=>$wpjobportal_pageid));
                                    }
                                ?>
                            </tbody>
                        </table>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('action', 'tag_remove'), WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('pagenum', ($wpjobportal_pagenum > 1) ? $wpjobportal_pagenum : ''), WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('task', ''), WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('form_request', 'wpjobportal'), WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('_wpnonce', wp_create_nonce('wpjobportal_tag_nonce')), WPJOBPORTAL_ALLOWED_TAGS); ?>
                    </form>
                    <?php
                    if (wpjobportal::$_data[1]) {
                        WPJOBPORTALincluder::getTemplate('templates/admin/pagination',array('wpjobportal_module' => 'tag','pagination' => wpjobportal::$_data[1]));
                    }
                } else {
                    $wpjobportal_msg = __('No Records Found','wp-job-portal');
                    $link[] = array(
                                'link' => 'admin.php?page=wpjobportal_tag&wpjobportallt=formtag',
                                'text' => __('Add New','wp-job-portal') .' '. esc_html__('Tag','wp-job-portal')
                            );
                    WPJOBPORTALlayout::getNoRecordFound($wpjobportal_msg,$link);
                }
            ?>
        </div>
    </div>
</div>
