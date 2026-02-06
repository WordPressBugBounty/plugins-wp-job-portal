<?php
if (!defined('ABSPATH'))
die('Restricted Access');
wp_enqueue_script('wpjobportal-res-tables', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/responsivetable.js');
if(!WPJOBPORTALincluder::getTemplate('templates/admin/header',array('wpjobportal_module'=>'jobtype'))){
    return;
}

wp_register_script( 'wpjobportal-inline-handle', '' );
wp_enqueue_script( 'wpjobportal-inline-handle' );
wp_enqueue_script('jquery-ui-sortable');
wp_enqueue_style('wp-jquery-ui-dialog');

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
?>



<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper">
    <!-- left menu -->
    <div id="wpjobportaladmin-leftmenu">
	    <?php WPJOBPORTALincluder::getTemplate('templates/admin/leftmenue',array('wpjobportal_module'=>'jobtype')); ?>
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
                        <li><?php echo esc_html(__('Job Types','wp-job-portal')); ?></li>
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
        <?php WPJOBPORTALincluder::getTemplate('templates/admin/pagetitle',array('wpjobportal_module' => 'jobtypes' , 'wpjobportal_layouts' => 'jobtype')); ?>
        <!-- page content -->
        <div id="wpjobportal-admin-wrapper" class="p0 bg-n bs-n">
            <!-- quick actions -->
            <div id="wpjobportal-page-quick-actions">
                <a class="wpjobportal-page-quick-act-btn multioperation" message="<?php echo esc_attr(WPJOBPORTALMessages::getMSelectionEMessage()); ?>" data-for="publish" href="#" title="<?php echo esc_attr(__('publish', 'wp-job-portal')) ?>">
                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/good.png" alt="<?php echo esc_attr(__('publish', 'wp-job-portal')) ?>" />
                    <?php echo esc_html(__('Publish', 'wp-job-portal')) ?>
                </a>
                <a class="wpjobportal-page-quick-act-btn multioperation" message="<?php echo esc_attr(WPJOBPORTALMessages::getMSelectionEMessage()); ?>" data-for="unpublish" href="#" title="<?php echo esc_attr(__('unpublish', 'wp-job-portal')) ?>">
                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/close.png" alt="<?php echo esc_attr(__('unpublish', 'wp-job-portal')) ?>" />
                    <?php echo esc_html(__('Unpublish', 'wp-job-portal')) ?>
                </a>
                <a class="wpjobportal-page-quick-act-btn multioperation" message="<?php echo esc_attr(WPJOBPORTALMessages::getMSelectionEMessage()); ?>" confirmmessage="<?php echo esc_attr(__('Are you sure to delete','wp-job-portal')) . ' ?'; ?>" data-for="remove" href="#" title="<?php echo esc_attr(__('delete', 'wp-job-portal')) ?>">
                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/forced-delete.png" alt="<?php echo esc_attr(__('delete', 'wp-job-portal')); ?>" />
                    <?php echo esc_html(__('Delete', 'wp-job-portal')) ?>
                </a>
            </div>
            <?php
                $wpjobportal_inline_js_script = "
                    function resetFrom() {
                        jQuery('input#title').val('');
                        jQuery('select#status').val('');
                        jQuery('form#wpjobportalform').submit();
                    }
                ";
                wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
            ?>
            <!-- filter form -->
            <form class="wpjobportal-filter-form" name="wpjobportalform" id="wpjobportalform" method="post" action="<?php echo esc_url_raw(admin_url("admin.php?page=wpjobportal_jobtype")); ?>">
                <?php echo wp_kses(WPJOBPORTALformfield::text('title', wpjobportal::$_data['filter']['title'], array('class' => 'inputbox wpjobportal-form-input-field', 'placeholder' => esc_html(__('Title', 'wp-job-portal')))),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('WPJOBPORTAL_form_search', 'WPJOBPORTAL_SEARCH'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::select('status', WPJOBPORTALincluder::getJSModel('common')->getstatus(), is_numeric(wpjobportal::$_data['filter']['status']) ? wpjobportal::$_data['filter']['status'] : '', esc_html(__('Select Status', 'wp-job-portal')), array('class' => 'inputbox wpjobportal-form-select-field')),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::submitbutton('btnsubmit', esc_html(__('Search', 'wp-job-portal')), array('class' => 'button wpjobportal-form-search-btn')),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::button('reset', esc_html(__('Reset', 'wp-job-portal')), array('class' => 'button wpjobportal-form-reset-btn', 'onclick' => 'resetFrom();')),WPJOBPORTAL_ALLOWED_TAGS); ?>

                <?php echo wp_kses(WPJOBPORTALformfield::select('pagesize', array((object) array('id'=>20,'text'=>20), (object) array('id'=>50,'text'=>50), (object) array('id'=>100,'text'=>100)), wpjobportal::$_data['filter']['pagesize'],esc_html(__("Records per page",'wp-job-portal')), array('class' => 'wpjobportal-form-select-field wpjobportal-right','onchange'=>'document.wpjobportalform.submit();')),WPJOBPORTAL_ALLOWED_TAGS);?>
            </form>
            <?php
                if (!empty(wpjobportal::$_data[0])) {
                    ?>
                    <form id="wpjobportal-list-form" method="post" action="<?php echo esc_url_raw(admin_url("admin.php?page=wpjobportal_jobtype&task=saveordering")); ?>">
                        <table id="wpjobportal-table" class="wpjobportal-table">
                            <thead>
                                <tr >
                                    <th>
                                        <input type="checkbox" name="selectall" id="selectall" value="">
                                    </th>
                                    <th >
                                        <?php echo esc_html(__('Ordering', 'wp-job-portal')); ?>
                                    </th>
                                    <th>
                                        <?php echo esc_html(__('Job Types', 'wp-job-portal')); ?>
                                    </th>
                                    <th>
                                        <?php echo esc_html(__('Default', 'wp-job-portal')); ?>
                                    </th>
                                    <th>
                                        <?php echo esc_html(__('Published', 'wp-job-portal')); ?>
                                    </th>
                                    <th>
                                        <?php echo esc_html(__('Color', 'wp-job-portal')); ?>
                                    </th>
                                    <th>
                                        <?php echo esc_html(__('Action', 'wp-job-portal')); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum', 'get', 1);
                                    $wpjobportal_pageid = ($wpjobportal_pagenum > 1) ? '&pagenum=' . $wpjobportal_pagenum : '';
                                    $wpjobportal_islastordershow = WPJOBPORTALpagination::isLastOrdering(wpjobportal::$_data['total'], $wpjobportal_pagenum);
                                    for ($wpjobportal_i = 0, $wpjobportal_n = count(wpjobportal::$_data[0]); $wpjobportal_i < $wpjobportal_n; $wpjobportal_i++) {
                                        $wpjobportal_row = wpjobportal::$_data[0][$wpjobportal_i];
                                        $wpjobportal_upimg = 'uparrow.png';
                                        $wpjobportal_downimg = 'downarrow.png';
                                        ?>
                                        <tr id="id_<?php echo esc_attr($wpjobportal_row->id);?>">
                                            <td>
                                                <input type="checkbox" class="wpjobportal-cb" id="wpjobportal-cb" name="wpjobportal-cb[]" value="<?php echo esc_attr($wpjobportal_row->id); ?>" />
                                            </td>
                                            <td class="wpjobportal-order-grab-column">
                                               <img alt="<?php echo esc_attr(__('grab','wp-job-portal')); ?>" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/list-full.png'?>"/>
                                            </td>
                                            <td>
                                                <a href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal_jobtype&wpjobportallt=formjobtype&wpjobportalid='.$wpjobportal_row->id)); ?>" title="<?php echo esc_attr(__('job types','wp-job-portal')); ?>">
                                                    <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_row->title)); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php if ($wpjobportal_row->isdefault == 1) { ?>
                                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/default.png" alt="<?php echo esc_attr(__('default','wp-job-portal')); ?>" border="0" />
                                                <?php } else { ?>
                                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_common&task=makedefault&action=wpjobportaltask&for=jobtypes&id='.$wpjobportal_row->id.$wpjobportal_pageid),'wpjobportal_common_entity_nonce')); ?>" title="<?php echo esc_attr(__('no default','wp-job-portal')); ?>">
                                                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/notdefault.png" border="0" alt="<?php echo esc_attr(__('no default','wp-job-portal')); ?>" />
                                                    </a>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <?php if ($wpjobportal_row->isactive == 1) { ?>
                                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_jobtype&task=unpublish&action=wpjobportaltask&wpjobportal-cb[]='.$wpjobportal_row->id.$wpjobportal_pageid),'wpjobportal_job_type_nonce')); ?>" title="<?php echo esc_attr(__('published', 'wp-job-portal')); ?>">
                                                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/good.png" border="0" alt="<?php echo esc_attr(__('published', 'wp-job-portal')); ?>" />
                                                    </a>
                                                <?php } else { ?>
                                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_jobtype&task=publish&action=wpjobportaltask&wpjobportal-cb[]='.$wpjobportal_row->id.$wpjobportal_pageid),'wpjobportal_job_type_nonce')); ?>" title="<?php echo esc_attr(__('not published', 'wp-job-portal')); ?>">
                                                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/close.png" border="0" alt="<?php echo esc_attr(__('not published', 'wp-job-portal')); ?>" />
                                                    </a>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <span class="wpjobportal-table-priority-color" style="background:<?php echo esc_attr($wpjobportal_row->color); ?>">
                                                    <?php echo esc_html($wpjobportal_row->color); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a class="wpjobportal-table-act-btn" href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal_jobtype&wpjobportallt=formjobtype&wpjobportalid='.$wpjobportal_row->id)); ?>" title="<?php echo esc_attr(__('edit', 'wp-job-portal')); ?>">
                                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/edit.png" alt="<?php echo esc_attr(__('edit', 'wp-job-portal')); ?>">
                                                </a>
                                                <a class="wpjobportal-table-act-btn" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_jobtype&task=remove&action=wpjobportaltask&wpjobportal-cb[]='.$wpjobportal_row->id),'wpjobportal_job_type_nonce')); ?>" onclick='return confirmdelete("<?php echo esc_js(__('Are you sure to delete', 'wp-job-portal')).' ?'; ?>");' title="<?php echo esc_attr(__('delete', 'wp-job-portal')); ?>">
                                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/delete.png" alt="<?php echo esc_attr(__('delete', 'wp-job-portal')); ?>">
                                                </a>
                                            </td>
                                        </tr>
                                <?php
                                    }
                                ?>
                            </tbody>
                        </table>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('fields_ordering_new', '123'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('action', 'jobtype_remove'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('task', ''),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('pagenum', ($wpjobportal_pagenum > 1) ? esc_html($wpjobportal_pagenum) : ''),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('form_request', 'wpjobportal'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('_wpnonce', esc_html(wp_create_nonce('wpjobportal_job_type_nonce'))),WPJOBPORTAL_ALLOWED_TAGS); ?>
                       <div class="wpjobportal-saveorder-wrp" style="display: none;">
                        <?php echo wp_kses(WPJOBPORTALformfield::submitbutton('save', esc_html(__('Save Ordering', 'wp-job-portal')), array('class' => 'button wpjobportal-form-act-btn')),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        </div>

                    </form>
            <?php
                if (wpjobportal::$_data[1]) {
                    echo '<div class="tablenav">
                            <div class="tablenav-pages">' . wp_kses_post(wpjobportal::$_data[1]) . '</div>
                        </div>';
                }
                } else {
                    $wpjobportal_msg = esc_html(__('No record found','wp-job-portal'));
                    $wpjobportal_link[] = array(
                            'link' => 'admin.php?page=wpjobportal_jobtype&wpjobportallt=formjobtype',
                           'text' => esc_html(__('Add New','wp-job-portal')) .' '. esc_html(__('Job Type','wp-job-portal'))
                        );
                    WPJOBPORTALlayout::getNoRecordFound($wpjobportal_msg,$wpjobportal_link);
                }
            ?>
        </div>
    </div>
</div>
