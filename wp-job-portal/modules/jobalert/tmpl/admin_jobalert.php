<?php if (!defined('ABSPATH')) die('Restricted Access');
wp_enqueue_script( 'wpjobportal-res-tables', WPJOBPORTAL_PLUGIN_URL . 'includes/js/responsivetable.js', array(), wpjobportal_asset_version('includes/js/responsivetable.js'), false );
?>
<script >
    function resetFrom() {
        document.getElementById('title').value = '';
        document.getElementById('email').value = '';
        document.getElementById('status').value = '';
        document.getElementById('category').value = '';
        //document.getElementById('location').value = '';
        // document.getElementById('alertstatus').value = '';
        document.getElementById('status').value = '';
        document.getElementById('wpjobportalform').submit();
    }
</script>
<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper">
    <!-- left menu -->
    <div id="wpjobportaladmin-leftmenu">
        <?php WPJOBPORTALincluder::getTemplate('templates/admin/leftmenue',array('wpjobportal_module'=>'jobalert')); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <!-- top head -->
        <?php WPJOBPORTALincluder::getTemplate('templates/admin/header',array('wpjobportal_module'=>'jobalert')); ?>
        <!-- top head -->
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
                        <li><?php echo esc_html__('Job Alert','wp-job-portal'); ?></li>
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
        <?php WPJOBPORTALincluder::getTemplate('templates/admin/pagetitle',array('wpjobportal_module' => 'jobalert' , 'wpjobportal_layouts' => 'jobalert')); ?>
        <!-- page content -->
        <div id="wpjobportal-admin-wrapper" class="p0 bg-n bs-n">
            <!-- filter form -->
            <form class="wpjobportal-filter-form" name="wpjobportalform" id="wpjobportalform" method="post" action="<?php echo esc_url(admin_url("admin.php?page=wpjobportal_jobalert")); ?>">
                <?php echo wp_kses(WPJOBPORTALformfield::text('title', wpjobportal::$_data['filter']['title'], array('class' => 'inputbox wpjobportal-form-input-field', 'placeholder' => __('Name', 'wp-job-portal'))), WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::text('email', wpjobportal::$_data['filter']['email'], array('class' => 'inputbox wpjobportal-form-input-field', 'placeholder' => __('Email', 'wp-job-portal'))), WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('WPJOBPORTAL_form_search', 'WPJOBPORTAL_SEARCH'), WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::select('category', WPJOBPORTALincluder::getJSModel('category')->getCategoriesForCombo(), wpjobportal::$_data['filter']['category'], __('Select','wp-job-portal') .' '. esc_html__('Category', 'wp-job-portal'), array('class' => 'inputbox wpjobportal-form-select-field')), WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php /* echo wp_kses(WPJOBPORTALformfield::select('alertstatus', wpjobportal::$_common->getOptionsForJobAlert(), is_numeric(wpjobportal::$_data['filter']['alertstatus']) ? wpjobportal::$_data['filter']['status'] : '', __('Select One', 'wp-job-portal'), array('class' => 'inputbox wpjobportal-form-select-field')), WPJOBPORTAL_ALLOWED_TAGS); */ ?>
                <?php echo wp_kses(WPJOBPORTALformfield::select('status', wpjobportal::$_common->getQueStatus(), is_numeric(wpjobportal::$_data['filter']['status']) ? wpjobportal::$_data['filter']['status'] : '', __('Select Status', 'wp-job-portal'), array('class' => 'inputbox wpjobportal-form-select-field')), WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::submitbutton('btnsubmit', __('Search', 'wp-job-portal'), array('class' => 'button wpjobportal-form-search-btn')), WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::button('reset', __('Reset', 'wp-job-portal'), array('class' => 'button wpjobportal-form-reset-btn', 'onclick' => 'resetFrom();')), WPJOBPORTAL_ALLOWED_TAGS); ?>
            </form>
            <?php
                if (!empty(wpjobportal::$_data[0])) {
                    ?>
                    <table id="wpjobportal-table" class="wpjobportal-table">
                        <thead>
                            <tr>
                                <th>
                                    <?php echo esc_html__('Name', 'wp-job-portal'); ?>
                                </th>
                                <th class="wpjobportal-text-left">
                                    <?php echo esc_html__('Contact Email', 'wp-job-portal'); ?>
                                </th>
                                <th class="wpjobportal-text-left">
                                    <?php echo esc_html__('Job Category', 'wp-job-portal'); ?>
                                </th>
                                <th>
                                    <?php echo esc_html__('Status', 'wp-job-portal'); ?>
                                </th>
                                <th>
                                    <?php echo esc_html__('Created', 'wp-job-portal'); ?>
                                </th>
                                <th>
                                    <?php echo esc_html__('Unsubscribe', 'wp-job-portal'); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $status = array('1' => __('Approved', 'wp-job-portal'), '-1' => __('Rejected', 'wp-job-portal'), '0' => __('Pending', 'wp-job-portal'),'3'=>__('Pending Payment','wp-job-portal'));
                                $wpjobportal_deleteimg = 'close.png';
                                $wpjobportal_deletealt = __('Unsubscribe', 'wp-job-portal');
                                for ($wpjobportal_i = 0, $wpjobportal_n = count(wpjobportal::$_data[0]); $wpjobportal_i < $wpjobportal_n; $wpjobportal_i++) {
                                    $wpjobportal_row = wpjobportal::$_data[0][$wpjobportal_i];
                                    ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_jobalert&wpjobportallt=formjobalert&wpjobportalid='.$wpjobportal_row->id)); ?>" title="<?php echo esc_attr__('Name','wp-job-portal'); ?>">
                                                <?php echo esc_html($wpjobportal_row->name); ?>
                                            </a>
                                        </td>
                                        <td class="wpjobportal-text-left">
                                            <?php echo esc_html($wpjobportal_row->contactemail); ?>
                                        </td>
                                        <td class="wpjobportal-text-left">
                                            <?php echo esc_html($wpjobportal_row->cat_title); ?>
                                        </td>
                                        <td>
                                            <?php
                                                if ($wpjobportal_row->status == 1)
                                                    echo "<span class='wpjobportal-table-priority-color' style='background:green'>" . esc_html($status[$wpjobportal_row->status]) . "</span>";
                                                else
                                                    echo "<span class='wpjobportal-table-priority-color' style='background:red'>" . esc_html($status[$wpjobportal_row->status]) . "</span>";
                                            ?>
                                        </td>
                                        <td>
                                            <?php echo esc_html(date_i18n('d/m/Y', strtotime($wpjobportal_row->created))); ?>
                                        </td>
                                        <td>
                                            <a class="wpjobportal-table-act-btn" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_jobalert&task=unsubscribeJobAlertSetting&action=wpjobportaltask&jobalertid='.$wpjobportal_row->id),'wpjobportal_job_alert_nonce')); ?>" title="<?php echo esc_attr($wpjobportal_deletealt); ?>">
                                                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/control_panel/dashboard/'); ?><?php echo esc_attr($wpjobportal_deleteimg); ?>" border="0" alt="<?php echo esc_attr($wpjobportal_deletealt); ?>" />
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            ?>
                        </tbody>
                    </table>
                    <?php
                    if (wpjobportal::$_data[1]) {
                        WPJOBPORTALincluder::getTemplate('templates/admin/pagination',array('wpjobportal_module'=>'jobalert','pagination'=>wpjobportal::$_data[1]));
                    }
                } else {
                    $wpjobportal_msg = __('No Records Found','wp-job-portal');
                    WPJOBPORTALlayout::getNoRecordFound($wpjobportal_msg);
                }
            ?>
        </div>
    </div>
</div>
