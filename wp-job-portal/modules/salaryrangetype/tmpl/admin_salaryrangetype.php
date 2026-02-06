<?php
    if (!defined('ABSPATH'))
        die('Restricted Access');
    wp_enqueue_script('wpjobportal-res-tables', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/responsivetable.js');
    if (!WPJOBPORTALincluder::getTemplate('templates/admin/header',array('wpjobportal_module' => 'salaryrangetype'))){
        return;
    }
?>
<?php 
wp_enqueue_script('jquery-ui-sortable');
wp_enqueue_style('wp-jquery-ui-dialog');
?>
<?php
    wp_register_script( 'wpjobportal-inline-handle', '' );
    wp_enqueue_script( 'wpjobportal-inline-handle' );

    $wpjobportal_inline_js_script = "
         jQuery(document).ready(function () {
            jQuery('table#wpjobportal-table tbody').sortable({ 
                handle : '.wpjobportal-order-grab-column',
                update  : function () {
                    jQuery('.wpjobportal-saveorder-wrp').slideDown('slow');
                    var abc =  jQuery('table#wpjobportal-table tbody').sortable('serialize');
                    jQuery('input#fields_ordering_new').val(abc);
                }
            });
        });
    ";
    wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );

    $wpjobportal_inline_js_script = "
        function resetFrom() {
            document.getElementById('title').value = '';
            document.getElementById('status').value = '';
            document.getElementById('wpjobportalform').submit();
        }
    ";
    wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
?>
<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper">
    <!-- left menu -->
    <div id="wpjobportaladmin-leftmenu">
        <?php  WPJOBPORTALincluder::getTemplate('templates/admin/leftmenue',array('wpjobportal_module' => 'salaryrangetype')); ?>
    </div>
    <div id="wpjobportaladmin-data">
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
                        <li><?php echo esc_html(__('Salary Range Type','wp-job-portal')); ?></li>
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
        <?php  WPJOBPORTALincluder::getTemplate('templates/admin/pagetitle',array('wpjobportal_module' => 'salaryrangetype','wpjobportal_layouts' => 'salaryrangetype')); ?>
        <!-- page content -->
        <div id="wpjobportal-admin-wrapper" class="p0 bg-n bs-n">
            <!-- quick actions -->
            <?php WPJOBPORTALincluder::getTemplate('salaryrangetype/views/multioperation') ;?>
            <!-- filter form -->
            <form class="wpjobportal-filter-form" name="wpjobportalform" id="wpjobportalform" method="post" action="<?php echo esc_url_raw(admin_url("admin.php?page=wpjobportal_salaryrangetype")); ?>">
                <?php WPJOBPORTALincluder::getTemplate('salaryrangetype/views/filter');?>
            </form>
            <?php
                if (!empty(wpjobportal::$_data[0])) {
                    ?>
                    <form id="wpjobportal-list-form" method="post" action="<?php echo esc_url_raw(admin_url("admin.php?page=wpjobportal_salaryrangetype&task=saveordering")); ?>">
                        <table id="wpjobportal-table" class="wpjobportal-table">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" name="selectall" id="selectall" value="">
                                    </th>
                                    <th>
                                        <?php echo esc_html(__('Ordering', 'wp-job-portal')); ?>
                                    </th>
                                    <th class="wpjobportal-text-left">
                                        <?php echo esc_html(__('Title', 'wp-job-portal')); ?>
                                    </th>
                                    <th>
                                        <?php echo esc_html(__('Default', 'wp-job-portal')); ?>
                                    </th>
                                    <th>
                                        <?php echo esc_html(__('Published', 'wp-job-portal')); ?>
                                    </th>
                                    <th>
                                        <?php echo esc_html(__('Action', 'wp-job-portal')); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $wpjobportal_k = 0;
                                    $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum', 'get', 1);
                                    $wpjobportal_pageid = ($wpjobportal_pagenum > 1) ? '&pagenum=' . $wpjobportal_pagenum : '';
                                    $wpjobportal_islastordershow = WPJOBPORTALpagination::isLastOrdering(wpjobportal::$_data['total'], $wpjobportal_pagenum);
                                    for ($wpjobportal_i = 0, $wpjobportal_n = count(wpjobportal::$_data[0]); $wpjobportal_i < $wpjobportal_n; $wpjobportal_i++) {
                                        $wpjobportal_row = wpjobportal::$_data[0][$wpjobportal_i];
                                        WPJOBPORTALincluder::getTemplate('salaryrangetype/views/main',array('wpjobportal_row' => $wpjobportal_row,'wpjobportal_i' => $wpjobportal_i ,'wpjobportal_pagenum' => $wpjobportal_pagenum ,'wpjobportal_n' => $wpjobportal_n ,'wpjobportal_pageid' => $wpjobportal_pageid ,'wpjobportal_islastordershow' => $wpjobportal_islastordershow ));
                                        $wpjobportal_k = 1 - $wpjobportal_k;
                                        }
                                ?>
                            </tbody>
                        </table>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('pagenum', ($wpjobportal_pagenum > 1) ? esc_html($wpjobportal_pagenum) : ''),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('task', ''),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('form_request', 'wpjobportal'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('fields_ordering_new', '123'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('_wpnonce', esc_html(wp_create_nonce('wpjobportal_salary_range_type_nonce'))),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <div class="wpjobportal-saveorder-wrp" style="display: none;">
                            <?php echo wp_kses(WPJOBPORTALformfield::submitbutton('save', esc_html(__('Save Ordering', 'wp-job-portal')), array('class' => 'button wpjobportal-form-act-btn')),WPJOBPORTAL_ALLOWED_TAGS); ?>
                       </div>


                    </form>
                <?php
                    if (wpjobportal::$_data[1]) {
                        WPJOBPORTALincluder::getTemplate('templates/admin/pagination',array('wpjobportal_module' => 'salaryrangetype' , 'pagination' => wpjobportal::$_data[1]));
                    }
                } else {
                    $wpjobportal_msg = esc_html(__('No record found','wp-job-portal'));
                    $wpjobportal_link[] = array(
                                'link' => 'admin.php?page=wpjobportal_salaryrangetype&wpjobportallt=formsalaryrangetype',
                                'text' => esc_html(__('Add New','wp-job-portal')) .' '. esc_html(__('Salary Range Type','wp-job-portal'))
                            );
                    WPJOBPORTALlayout::getNoRecordFound($wpjobportal_msg,$wpjobportal_link);
                }
            ?>
        </div>
    </div>
</div>
