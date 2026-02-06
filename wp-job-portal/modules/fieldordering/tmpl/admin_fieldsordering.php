<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
wp_enqueue_script('wpjobportal-res-tables', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/responsivetable.js');
wp_enqueue_script('jquery-ui-sortable');
$wpjobportal_protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
wp_enqueue_style('jquery-ui-css', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/jquery-ui-smoothness.css');
wp_enqueue_script('jquery-multisortable',esc_url(WPJOBPORTAL_PLUGIN_URL).'/includes/js/jquery.multisortable.js');
?>
<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper">
     <!-- left menu -->
    <div id="wpjobportaladmin-leftmenu">
        <?php  WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <div id="full_background" style="display:none;"></div>
        <div id="popup_main" style="display:none;"></div>
        <?php
            $wpjobportal_msgkey = WPJOBPORTALincluder::getJSModel('fieldordering')->getMessagekey();
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
                        <li><?php echo esc_html(__('Custom Fields','wp-job-portal')); ?></li>
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
        <div id="wpjobportal-head">
            <h1 class="wpjobportal-head-text">
                <?php
                    if(wpjobportal::$wpjobportal_data['fieldfor'] == 1){
                        echo esc_html(__('Company','wp-job-portal'));
                    } elseif (wpjobportal::$wpjobportal_data['fieldfor'] == 2){
                        echo esc_html(__('Job','wp-job-portal'));
                    } elseif (wpjobportal::$wpjobportal_data['fieldfor'] == 3){
                        echo esc_html(__('Resume','wp-job-portal'));
                    } elseif (wpjobportal::$wpjobportal_data['fieldfor'] == 5){
                        echo esc_html(__('Quick Apply','wp-job-portal'));
                    }
                    echo ' '.esc_html(__('Fields', 'wp-job-portal'));
                ?>
            </h1>
            <?php
            // advanced custom field addon no longer active
                // if(in_array('customfield', wpjobportal::$_active_addons)){
                //     do_action('wpjobportal_addons_customFields_addUser',wpjobportal::$wpjobportal_data['fieldfor']);
                // }else{
                if(wpjobportal::$wpjobportal_data['fieldfor'] != 5){
                    echo '<a class="wpjobportal-add-link button" href="?page=wpjobportal_customfield&wpjobportallt=formuserfield&ff='.esc_attr(wpjobportal::$wpjobportal_data['fieldfor']).'" title='. esc_html(__('add user fields','wp-job-portal')).'>
                            <img src='.esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/control_panel/dashboard/plus-icon.png>
                            '. esc_html(__('Add User Field','wp-job-portal')).'
                        </a>';
                }
                //}
            ?>
        </div>
        <!-- page content -->
        <div id="wpjobportal-admin-wrapper" class="p0 bg-n bs-n">
            <!-- quick actions -->
            <div id="wpjobportal-page-quick-actions">
                <a class="wpjobportal-page-quick-act-btn multioperation" message="<?php echo esc_attr(WPJOBPORTALMessages::getMSelectionEMessage()); ?>" data-for="fieldpublished" href="#" title="<?php echo esc_attr(__('user publish', 'wp-job-portal')) ?>">
                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/good.png" alt="<?php echo esc_attr(__('user publish', 'wp-job-portal')) ?>" />
                    <?php echo esc_html(__('User Publish', 'wp-job-portal')); ?>
                </a>
                <a class="wpjobportal-page-quick-act-btn multioperation" message="<?php echo esc_attr(WPJOBPORTALMessages::getMSelectionEMessage()); ?>" data-for="fieldunpublished" href="#" title="<?php echo esc_attr(__('user unpublish', 'wp-job-portal')) ?>">
                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/close.png" alt="<?php echo esc_attr(__('user unpublish', 'wp-job-portal')) ?>" />
                    <?php echo esc_html(__('User Unpublished', 'wp-job-portal')); ?>
                </a>
                <a class="wpjobportal-page-quick-act-btn multioperation" message="<?php echo esc_attr(WPJOBPORTALMessages::getMSelectionEMessage()); ?>" data-for="visitorfieldpublished" href="#" title="<?php echo esc_attr(__('visitor publish', 'wp-job-portal')) ?>">
                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/good.png" alt="<?php echo esc_attr(__('visitor publish', 'wp-job-portal')) ?>" />
                    <?php echo esc_html(__('Visitor Publish', 'wp-job-portal')); ?>
                </a>
                <a class="wpjobportal-page-quick-act-btn multioperation" message="<?php echo esc_attr(WPJOBPORTALMessages::getMSelectionEMessage()); ?>" data-for="visitorfieldunpublished" href="#" title="<?php echo esc_attr(__('visitor unpublish', 'wp-job-portal')) ?>">
                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/close.png" alt="<?php echo esc_attr(__('visitor unpublish', 'wp-job-portal')) ?>" />
                    <?php echo esc_html(__('Visitor Unpublished', 'wp-job-portal')); ?>
                </a>
                <a class="wpjobportal-page-quick-act-btn multioperation" message="<?php echo esc_attr(WPJOBPORTALMessages::getMSelectionEMessage()); ?>" data-for="fieldrequired" href="#" title="<?php echo esc_attr(__('required', 'wp-job-portal')) ?>">
                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/good.png" alt="<?php echo esc_attr(__('required', 'wp-job-portal')) ?>" />
                    <?php echo esc_html(__('Required', 'wp-job-portal')) ?>
                </a>
                <a class="wpjobportal-page-quick-act-btn multioperation" message="<?php echo esc_attr(WPJOBPORTALMessages::getMSelectionEMessage()); ?>" data-for="fieldnotrequired" href="#" title="<?php echo esc_attr(__('not required', 'wp-job-portal')) ?>">
                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/close.png" alt="<?php echo esc_attr(__('not required', 'wp-job-portal')) ?>" />
                    <?php echo esc_html(__('Not Required', 'wp-job-portal')) ?>
                </a>
            </div>
            <?php
                wp_register_script( 'wpjobportal-inline-handle', '' );
                wp_enqueue_script( 'wpjobportal-inline-handle' );

                $wpjobportal_inline_js_script = "
                    jQuery(document).ready(function () {
                        jQuery('div#full_background').click(function () {
                            closePopup();
                        });

                        jQuery('#wpjobportal-table tbody').multisortable({
                            items: '.sortable',
                            selectedClass: 'selected',
                            stop: function(e,ui){
                                jQuery('.js-form-button').slideDown('slow');
                                var abc =  jQuery('table#wpjobportal-table tbody').sortable('serialize');
                                jQuery('input#fields_ordering_new').val(abc);
                            }
                        });

                        jQuery('#saveordering').click(function(){
                            jQuery('#wpjobportal-list-form').attr('action','". esc_url_raw(admin_url("admin.php?page=wpjobportal&task=saveordering"))."');
                            return true;
                        });

                    });

                    function resetFrom() {
                        jQuery('input#title').val('');
                        jQuery('select#ustatus').val('');
                        jQuery('select#vstatus').val('');
                        jQuery('select#required').val('');
                        jQuery('form#wpjobportalform').submit();
                    }

                    
                        /*function showPopupAndSetValues(id) {
                            jQuery.post(ajaxurl, {action: 'wpjobportal_ajax', wpjobportalme: 'fieldordering', task: 'getOptionsForFieldEdit', field: id, '_wpnonce':'". esc_attr(wp_create_nonce("get-options-for-field-edit"))."'}, function (data) {
                                if (data) {
                                    var d = jQuery.parseJSON(data);
                                    jQuery('div#full_background').css('display', 'block');
                                    jQuery('div#popup_main').html(d);
                                    jQuery('div#popup_main').slideDown('slow');
                                }
                            });
                        }*/
                        

                    function closePopup() {
                        jQuery('div#popup_main').slideUp('slow');
                        setTimeout(function () {
                            jQuery('div#full_background').hide();
                            jQuery('div#popup_main').html('');
                        }, 700);
                    }
                ";
                wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
            ?>
            <!-- filter form -->
            <form class="wpjobportal-filter-form" name="wpjobportalform" id="wpjobportalform" method="post" action="<?php echo esc_url_raw(admin_url("admin.php?page=wpjobportal_fieldordering&ff=" . wpjobportal::$wpjobportal_data['fieldfor'])); ?>">
                <?php echo wp_kses(WPJOBPORTALformfield::text('title', wpjobportal::$_data['filter']['title'], array('class' => 'inputbox wpjobportal-form-input-field', 'placeholder' => esc_html(__('Title', 'wp-job-portal')))),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::select('ustatus', WPJOBPORTALincluder::getJSModel('common')->getStatus(), is_numeric(wpjobportal::$_data['filter']['ustatus']) ? wpjobportal::$_data['filter']['ustatus'] : '', esc_html(__('Select status', 'wp-job-portal'))/*esc_html(__('Select user status', 'wp-job-portal'))*/, array('class' => 'inputbox wpjobportal-form-select-field')),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::select('required', WPJOBPORTALincluder::getJSModel('common')->getYesNo(), is_numeric(wpjobportal::$_data['filter']['required']) ? wpjobportal::$_data['filter']['required'] : '', esc_html(__('Required', 'wp-job-portal')), array('class' => 'inputbox wpjobportal-form-select-field')),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('WPJOBPORTAL_form_search', 'WPJOBPORTAL_SEARCH'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::submitbutton('btnsubmit', esc_html(__('Search', 'wp-job-portal')), array('class' => 'button wpjobportal-form-search-btn')),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::button('reset', esc_html(__('Reset', 'wp-job-portal')), array('class' => 'button wpjobportal-form-reset-btn', 'onclick' => 'resetFrom();')),WPJOBPORTAL_ALLOWED_TAGS); ?>
            </form>
            <?php
                if (!empty(wpjobportal::$_data[0])) {
                    ?>
                    <form id="wpjobportal-list-form" method="post" class="wpjobportal-form" action="<?php echo esc_url_raw(admin_url("admin.php?page=wpjobportal_fieldordering")); ?>">
                        <table id="wpjobportal-table" class="wpjobportal-table">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" name="selectall" id="selectall" value="">
                                    </th>
                                    <th >
                                        <?php echo esc_html(__('Ordering', 'wp-job-portal')); ?>
                                    </th>
                                    <th class="wpjobportal-text-left">
                                        <?php echo esc_html(__('Field Title', 'wp-job-portal')); ?>
                                    </th>
                                    <th>
                                        <?php echo esc_html(__('User Published', 'wp-job-portal')); ?>
                                    </th>
                                    <th>
                                        <?php echo esc_html(__('Visitor Published', 'wp-job-portal')); ?>
                                    </th>
                                    <th>
                                        <?php echo esc_html(__('Required', 'wp-job-portal')); ?>
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
                                        if (isset(wpjobportal::$_data[0][$wpjobportal_i + 1]))
                                            $wpjobportal_row1 = wpjobportal::$_data[0][$wpjobportal_i + 1];
                                        else
                                            $wpjobportal_row1 = wpjobportal::$_data[0][$wpjobportal_i];
                                        $wpjobportal_uptask = 'fieldorderingup';
                                        $wpjobportal_downtask = 'fieldorderingdown';
                                        $wpjobportal_upimg = 'uparrow.png';
                                        $wpjobportal_downimg = 'downarrow.png';
                                        $wpjobportal_pubtask = $wpjobportal_row->published ? 'fieldunpublished' : 'fieldpublished';
                                        $wpjobportal_pubimg = $wpjobportal_row->published ? 'tick.png' : 'publish_x.png';
                                        $wpjobportal_alt = $wpjobportal_row->published ? esc_html(__('Published', 'wp-job-portal')) : esc_html(__('Unpublished', 'wp-job-portal'));
                                        $wpjobportal_visitorpubtask = $wpjobportal_row->isvisitorpublished ? 'visitorfieldunpublished' : 'visitorfieldpublished';
                                        $wpjobportal_visitorpubimg = $wpjobportal_row->isvisitorpublished ? 'tick.png' : 'publish_x.png';
                                        $wpjobportal_visitoralt = $wpjobportal_row->isvisitorpublished ? esc_html(__('Published', 'wp-job-portal')) : esc_html(__('Unpublished', 'wp-job-portal'));
                                        $wpjobportal_requiredtask = $wpjobportal_row->required ? 'fieldnotrequired' : 'fieldrequired';
                                        $wpjobportal_requiredpubimg = $wpjobportal_row->required ? 'tick.png' : 'publish_x.png';
                                        $wpjobportal_requiredalt = $wpjobportal_row->required ? esc_html(__('Required', 'wp-job-portal')) : esc_html(__('Not Required', 'wp-job-portal'));
                                        $wpjobportal_sec = wpjobportalphplib::wpJP_substr($wpjobportal_row->field, 0, 8); //get section_
                                        $wpjobportal_newsection = 0;
                                        ?>
                                        <tr class="<?php if($wpjobportal_row->is_section_headline == 1) echo 'wpjobportal-field-ordering-section-headline'; ?> sortable" id="id_<?php echo esc_attr($wpjobportal_row->id); ?>">
                                            <td>
                                                <input type="checkbox" class="wpjobportal-cb" id="wpjobportal-cb" name="wpjobportal-cb[]" value="<?php echo esc_attr($wpjobportal_row->id); ?>" />
                                            </td>
                                            <td class="wpjobportal-order-grab-column">
                                               <img alt="<?php echo esc_attr(__('grab','wp-job-portal')); ?>" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/list-full.png'?>"/>
                                            </td>
                                            <?php

                                                // $wpjobportal_sec = wpjobportalphplib::wpJP_substr($wpjobportal_row->field, 0, 8); //get section_
                                                // $wpjobportal_newsection = 0;
                                                if ($wpjobportal_row->is_section_headline == 1) {
                                                    $wpjobportal_newsection = 1;
                                                    /*
                                                    $wpjobportal_subsec = wpjobportalphplib::wpJP_substr($wpjobportal_row->field, 0, 12);
                                                    if ($wpjobportal_subsec == 'section_sub_') {
                                                        ?>
                                                        <td class="wpjobportal-text-left">
                                                            <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_row->fieldtitle)); ?>
                                                        </td>
                                                    <?php } else {*/ ?>
                                                        <td class="wpjobportal-text-left">
                                                            <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_row->fieldtitle)); ?>
                                                        </td>
                                                    <?php //} ?>
                                                    <td>
                                                        <?php if ($wpjobportal_row->cannotunpublish == 1) { ?>
                                                            <img src="<?php echo esc_url_raw(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/good.png" alt="<?php echo esc_attr(__('published', 'wp-job-portal')); ?>" />
                                                        <?php
                                                            } else {
                                                                $wpjobportal_icon_name = "close.png";
                                                                $wpjobportal_task = "fieldpublished";
                                                                if ($wpjobportal_row->published == 1) {
                                                                    $wpjobportal_task = "fieldunpublished";
                                                                    $wpjobportal_icon_name = "good.png";
                                                                }
                                                            ?>
                                                        <a href="<?php echo esc_url_raw(wp_nonce_url(admin_url('admin.php?page=wpjobportal_fieldordering&task='.$wpjobportal_task.'&action=wpjobportaltask&wpjobportal-cb[]='.$wpjobportal_row->id.'&ff='.wpjobportal::$wpjobportal_data['fieldfor']),'wpjobportal_field_nonce')); ?>" title="<?php echo esc_attr($wpjobportal_alt); ?>">
                                                            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/<?php echo esc_attr($wpjobportal_icon_name); ?>" alt="<?php echo esc_attr($wpjobportal_alt); ?>" />
                                                        </a>
                                                        <?php } ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($wpjobportal_row->cannotunpublish == 1) { ?>
                                                            <img src="<?php echo esc_url_raw(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/good.png" alt="<?php echo esc_attr(__('published', 'wp-job-portal')); ?>" />
                                                        <?php
                                                            } else {
                                                                $wpjobportal_icon_name = "close.png";
                                                                $wpjobportal_task = "visitorfieldpublished";
                                                                if ($wpjobportal_row->isvisitorpublished == 1) {
                                                                    $wpjobportal_task = "visitorfieldunpublished";
                                                                    $wpjobportal_icon_name = "good.png";
                                                                }
                                                            ?>
                                                            <a href="<?php echo esc_url_raw(wp_nonce_url(admin_url('admin.php?page=wpjobportal_fieldordering&task='.$wpjobportal_task.'&action=wpjobportaltask&wpjobportal-cb[]='.$wpjobportal_row->id.'&ff='.wpjobportal::$wpjobportal_data['fieldfor']),'wpjobportal_field_nonce')); ?>" title="<?php echo esc_attr($wpjobportal_visitoralt); ?>">
                                                                <img src="<?php echo esc_url_raw(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/<?php echo esc_attr($wpjobportal_icon_name); ?>" alt="<?php echo esc_attr($wpjobportal_visitoralt); ?>" />
                                                            </a>
                                                        <?php } ?>
                                                    </td>
                                                    <td>
                                                        <?php /* if ($wpjobportal_row->cannotunpublish == 1) { ?>
                                                            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/good.png" alt="<?php echo esc_attr(__('published', 'wp-job-portal')); ?>" />
                                                        <?php
                                                            } else {
                                                                $wpjobportal_icon_name = "close.png";
                                                                $wpjobportal_task = "visitorfieldpublished";
                                                                if ($wpjobportal_row->isvisitorpublished == 1) {
                                                                    $wpjobportal_task = "visitorfieldunpublished";
                                                                    $wpjobportal_icon_name = "good.png";
                                                                }
                                                            ?>
                                                            <a href="<?php echo esc_url_raw(wp_nonce_url(admin_url('admin.php?page=wpjobportal_fieldordering&task='.$wpjobportal_task.'&action=wpjobportaltask&wpjobportal-cb[]='.$wpjobportal_row->id.'&ff='.wpjobportal::$wpjobportal_data['fieldfor']),'wpjobportal_field_nonce')); ?>" title="<?php echo esc_attr($wpjobportal_visitoralt); ?>">
                                                                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/<?php echo esc_attr($wpjobportal_icon_name); ?>" alt="<?php echo esc_attr($wpjobportal_visitoralt); ?>" />
                                                            </a>
                                                        <?php } */?>
                                                    --</td>
                                                    <td><a class="wpjobportal-table-act-btn" href="<?php echo esc_url_raw(admin_url("admin.php?page=wpjobportal_fieldordering&wpjobportallt=formuserfield&ff=".wpjobportal::$wpjobportal_data['fieldfor']."&wpjobportalid=".$wpjobportal_row->id)); ?>" title="<?php echo esc_attr(__('edit', 'wp-job-portal')); ?>">
                                                            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/edit.png" alt="<?php echo esc_attr(__('edit', 'wp-job-portal')); ?>">
                                                        </a>
                                                        <?php if ($wpjobportal_row->isuserfield == 1) { ?>
                                                        <a class="wpjobportal-table-act-btn" href="<?php echo esc_url_raw(wp_nonce_url(admin_url('admin.php?page=wpjobportal_fieldordering&task=remove&action=wpjobportaltask&fieldid='.$wpjobportal_row->id.'&ff='.wpjobportal::$wpjobportal_data['fieldfor'].'&is_section_headline=1'),'wpjobportal_field_nonce')); ?>" onclick='return confirmdelete("<?php echo esc_js(__('Are you sure to delete', 'wp-job-portal')).'? &nbsp;';echo esc_html(__('Deleting section will delete all its fields.', 'wp-job-portal')); ?>");' title="<?php echo esc_attr(__('delete', 'wp-job-portal')); ?>">
                                                            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/delete.png" alt="<?php echo esc_attr(__('delete', 'wp-job-portal')); ?>">
                                                        </a>
                                                    <?php } ?>
                                                    </td>
                                            <?php } else { ?>
                                                <td class="wpjobportal-text-left">
                                                    <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_row->fieldtitle)); ?>
                                                </td>
                                                <td>
                                                    <?php if ($wpjobportal_row->cannotunpublish == 1) { ?>
                                                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/good.png" title="<?php echo esc_attr(__('published', 'wp-job-portal')); ?>" />
                                                    <?php
                                                        } else {
                                                            $wpjobportal_icon_name = "close.png";
                                                            $wpjobportal_task = "fieldpublished";
                                                            if ($wpjobportal_row->published == 1) {
                                                                $wpjobportal_task = "fieldunpublished";
                                                                $wpjobportal_icon_name = "good.png";
                                                            }
                                                            ?>
                                                        <a href="<?php echo esc_url_raw(wp_nonce_url(admin_url('admin.php?page=wpjobportal_fieldordering&task='.$wpjobportal_task.'&action=wpjobportaltask&wpjobportal-cb[]='.$wpjobportal_row->id.'&ff='.wpjobportal::$wpjobportal_data['fieldfor']),'wpjobportal_field_nonce')); ?>" title="<?php echo esc_attr($wpjobportal_alt); ?>">
                                                            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/<?php echo esc_attr($wpjobportal_icon_name); ?>" alt="<?php echo esc_attr($wpjobportal_alt); ?>" />
                                                        </a>
                                                    <?php } ?>
                                                </td>
                                                <td>
                                                    <?php if ($wpjobportal_row->cannotunpublish == 1) { ?>
                                                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/good.png" title="<?php echo esc_attr(__('Un published', 'wp-job-portal')); ?>" />
                                                    <?php
                                                        } else {
                                                            $wpjobportal_icon_name = "close.png";
                                                            $wpjobportal_task = "visitorfieldpublished";
                                                            if ($wpjobportal_row->isvisitorpublished == 1) {
                                                                $wpjobportal_task = "visitorfieldunpublished";
                                                                $wpjobportal_icon_name = "good.png";
                                                            }
                                                            ?>
                                                        <a href="<?php echo esc_url_raw(wp_nonce_url(admin_url('admin.php?page=wpjobportal_fieldordering&task='.$wpjobportal_task.'&action=wpjobportaltask&wpjobportal-cb[]='.$wpjobportal_row->id.'&ff='.wpjobportal::$wpjobportal_data['fieldfor']),'wpjobportal_field_nonce')); ?>" title="<?php echo esc_attr($wpjobportal_alt); ?>">
                                                            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/<?php echo esc_attr($wpjobportal_icon_name); ?>" alt="<?php echo esc_attr($wpjobportal_alt); ?>" />
                                                        </a>
                                                    <?php } ?>
                                                </td>
                                                <td>
                                                    <?php if ($wpjobportal_row->sys == 1 || $wpjobportal_row->field == 'termsandconditions') { ?>
                                                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/good.png" title="<?php echo esc_attr(__('required', 'wp-job-portal')); ?>" />
                                                        <?php // to make sure that file upload fields can not be made required
                                                    } elseif ( $wpjobportal_row->field == 'resumefiles' || $wpjobportal_row->field == 'photo' || $wpjobportal_row->field == 'logo' || $wpjobportal_row->userfieldtype == 'file') { ?>
                                                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/close.png" title="<?php echo esc_attr(__('required', 'wp-job-portal')); ?>" />
                                                        <?php
                                                    } else {
                                                        $wpjobportal_icon_name = "close.png";
                                                        $wpjobportal_task = "fieldrequired";
                                                        if ($wpjobportal_row->required == 1) {
                                                            $wpjobportal_task = "fieldnotrequired";
                                                            $wpjobportal_icon_name = "good.png";
                                                        }
                                                    ?>
                                                    <a href="<?php echo esc_url_raw(wp_nonce_url(admin_url('admin.php?page=wpjobportal_fieldordering&task='.$wpjobportal_task.'&action=wpjobportaltask&wpjobportal-cb[]='.$wpjobportal_row->id.'&ff='.wpjobportal::$wpjobportal_data['fieldfor']),'wpjobportal_field_nonce')); ?>" title="<?php echo esc_attr($wpjobportal_requiredalt); ?>">
                                                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/<?php echo esc_attr($wpjobportal_icon_name); ?>" alt="<?php echo esc_attr($wpjobportal_requiredalt); ?>" />
                                                    </a>
                                                    <?php } ?>
                                                </td>
                                                <td>
                                                    <a class="wpjobportal-table-act-btn" href="<?php echo esc_url_raw(admin_url("admin.php?page=wpjobportal_fieldordering&wpjobportallt=formuserfield&ff=".wpjobportal::$wpjobportal_data['fieldfor']."&wpjobportalid=".$wpjobportal_row->id)); ?>" title="<?php echo esc_attr(__('edit', 'wp-job-portal')); ?>">
                                                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/edit.png" alt="<?php echo esc_attr(__('edit', 'wp-job-portal')); ?>">
                                                    </a>
                                                    <?php if ($wpjobportal_row->isuserfield == 1) { ?>
                                                        <a class="wpjobportal-table-act-btn" href="<?php echo esc_url_raw(wp_nonce_url(admin_url('admin.php?page=wpjobportal_fieldordering&task=remove&action=wpjobportaltask&fieldid='.$wpjobportal_row->id.'&ff='.wpjobportal::$wpjobportal_data['fieldfor']),'wpjobportal_field_nonce')); ?>" onclick='return confirmdelete("<?php echo esc_js(__('Are you sure to delete', 'wp-job-portal')).' ?'; ?>");' title="<?php echo esc_attr(__('delete', 'wp-job-portal')); ?>">
                                                            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/delete.png" alt="<?php echo esc_attr(__('delete', 'wp-job-portal')); ?>">
                                                        </a>
                                                    <?php } ?>
                                                </td>
                                            <?php
                                                $wpjobportal_newsection = 0;
                                                }
                                            ?>
                                        </tr>
                                    <?php
                                    }
                                ?>
                            </tbody>
                        </table>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('task', ''),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('pagenum', ($wpjobportal_pagenum > 1) ? esc_html($wpjobportal_pagenum) : ''),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('form_request', 'wpjobportal'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('fieldfor', esc_html(wpjobportal::$wpjobportal_data['fieldfor'])),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('ff', esc_html(wpjobportal::$wpjobportal_data['fieldfor'])),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('fields_ordering_new', ''),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('ordering_for', 'fieldordering'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('pagenum_for_ordering', esc_html(WPJOBPORTALrequest::getVar('pagenum', 'get', 1))),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('_wpnonce', esc_html(wp_create_nonce('wpjobportal_field_nonce'))),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <div class="js-form-button wpjobportal-form-button" style="display: none;">
                            <?php echo wp_kses(WPJOBPORTALformfield::submitbutton('saveordering', esc_html(__('Save Ordering', 'wp-job-portal')), array('class' => 'button js-form-save wpjobportal-form-save-btn')),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        </div>
                    </form>
                    <div id="wpjobportal-field-ordering-notice">
                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/info-icon.png"> <?php echo esc_html(__('File upload fields and text editor fields cannot be made required.', 'wp-job-portal')); ?>
                    </div>
            <?php
            /*
                if (wpjobportal::$_data[1]) {
                    echo '<div class="tablenav">
                        <div class="tablenav-pages">' . wp_kses_post(wpjobportal::$_data[1]) . '</div>
                    </div>';
                }*/
                } else {
                    $wpjobportal_msg = esc_html(__('No record found','wp-job-portal'));
                    $wpjobportal_link[] = array(
                        'link' => 'admin.php?page=wpjobportal_customfield&wpjobportallt=formuserfield&ff='.wpjobportal::$wpjobportal_data['fieldfor'],
                        'text' => esc_html(__('Add New','wp-job-portal')) .' '. esc_html(__('User Field','wp-job-portal'))
                    );
                    WPJOBPORTALlayout::getNoRecordFound($wpjobportal_msg,$wpjobportal_link);
                }
            ?>
        </div>
    </div>
</div>
