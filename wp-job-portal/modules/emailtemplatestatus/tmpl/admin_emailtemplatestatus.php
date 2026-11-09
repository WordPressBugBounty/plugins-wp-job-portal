<?php
if (!defined('ABSPATH'))
die('Restricted Access');
wp_enqueue_script( 'wpjobportal-res-tables', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/responsivetable.js', array(), wpjobportal_asset_version('includes/js/responsivetable.js'), false );
?>
<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper" class="wjpl-page">
    <!-- left menu -->
	<div id="wpjobportaladmin-leftmenu">
        <?php  WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <?php
            $wpjobportal_msgkey = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getMessagekey();
            WPJOBPORTALMessages::getLayoutMessage($wpjobportal_msgkey);
        ?>
        <!-- top head -->
        <?php ob_start(); ?>
                <?php echo esc_html(__('Email Templates Options', 'wp-job-portal')) ?>
            <?php WPJOBPORTAL_Admin_List_UI::page_head(trim(html_entity_decode(wp_strip_all_tags(ob_get_clean()), ENT_QUOTES)), '', array(__('Send a test email', 'wp-job-portal') => WPJOBPORTAL_Mail_Health_Notice::test_url())); ?>
        <?php
        // W-12: most mail servers refuse a sender address on a bare IP, and say nothing to the site.
        $wpjobportal_from_address = (string) wpjobportal::$_config->getConfigValue('mailfromaddress');
        $wpjobportal_from_domain = substr((string) strrchr($wpjobportal_from_address, '@'), 1);
        if ($wpjobportal_from_domain === '' || filter_var($wpjobportal_from_domain, FILTER_VALIDATE_IP)) { ?>
            <div class="notice notice-warning inline"><p><?php
                /* translators: %s: the sender email address */
                echo esc_html(sprintf(__('Emails are sent from "%s". Most mail servers reject this address, so these emails may never arrive. Set a sender address on your own domain under Settings.', 'wp-job-portal'), $wpjobportal_from_address));
            ?></p></div>
        <?php } ?>
        <!-- page content -->
        <div id="wpjobportal-admin-wrapper" class="p0 bg-n bs-n">
            <table id="wpjobportal-table" class="wpjobportal-table wpjobportal-templates-status-table">
                <thead>
                    <tr>
                        <th class="wpjobportal-text-left">
                            <?php echo esc_html(__('Title', 'wp-job-portal')); ?>
                        </th>
                        <th>
                            <?php echo esc_html(__('Employer', 'wp-job-portal')); ?>
                        </th>
                        <th>
                            <?php echo esc_html(__('Job Seeker', 'wp-job-portal')); ?>
                        </th>
                        <th>
                            <?php echo esc_html(__('Admin', 'wp-job-portal')); ?>
                        </th>
                        <th>
                            <?php echo esc_html(__('Job Seeker Visitor', 'wp-job-portal')); ?>
                        </th>
                        <th>
                            <?php echo esc_html(__('Employer Visitor', 'wp-job-portal')); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <!-- compnay section -->
                    <tr>
                        <td colspan="6" class="wpjobportal-table-section-header">
                            <?php echo esc_html(__('Company', 'wp-job-portal')); ?>
                        </td>
                    </tr>
                    <tr>
                        <?php
                            $wpjobportal_lang = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getLanguageForEmail(wpjobportal::$_data[0]['add_new_company']['tempname']);
                        ?>
                        <td class="wpjobportal-text-left">
                            <?php echo esc_html($wpjobportal_lang); ?>
                        </td>
                        <td>
                            <?php if (wpjobportal::$_data[0]['add_new_company']['employer'] == 1) { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['add_new_company']['tempid'].'&actionfor=1'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } else { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['add_new_company']['tempid'].'&actionfor=1'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } ?>
                        </td>
                        <td> - </td>
                        <td>
                            <?php if (wpjobportal::$_data[0]['add_new_company']['admin'] == 1) { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['add_new_company']['tempid'].'&actionfor=3'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } else { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['add_new_company']['tempid'].'&actionfor=3'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } ?>
                        </td>
                        <td> - </td>
                        <td> - </td>
                    </tr>
                    <tr>
                        <?php
                            $wpjobportal_lang = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getLanguageForEmail(wpjobportal::$_data[0]['delete_company']['tempname']);
                        ?>
                        <td class="wpjobportal-text-left">
                            <?php echo esc_html($wpjobportal_lang); ?>
                        </td>
                        <td>
                            <?php if (wpjobportal::$_data[0]['delete_company']['employer'] == 1) { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['delete_company']['tempid'].'&actionfor=1'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } else { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['delete_company']['tempid'].'&actionfor=1'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } ?>
                        </td>
                        <td> - </td>
                        <td> - </td>
                        <td> - </td>
                        <td> - </td>
                    </tr>
                    <tr>
                        <?php
                            $wpjobportal_lang = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getLanguageForEmail(wpjobportal::$_data[0]['company_status']['tempname']);
                        ?>
                        <td class="wpjobportal-text-left">
                            <?php echo esc_html($wpjobportal_lang); ?>
                        </td>
                        <td>
                            <?php if (wpjobportal::$_data[0]['company_status']['employer'] == 1) { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['company_status']['tempid'].'&actionfor=1'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } else { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['company_status']['tempid'].'&actionfor=1'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } ?>
                        </td>
                        <td> - </td>
                        <td> - </td>
                        <td> - </td>
                        <td> - </td>
                    </tr>
                    <!-- job section -->
                    <tr>
                        <td colspan="6" class="wpjobportal-table-section-header">
                            <?php echo esc_html(__('Job', 'wp-job-portal')); ?>
                        </td>
                    </tr>
                    <tr>
                        <?php
                            $wpjobportal_lang = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getLanguageForEmail(wpjobportal::$_data[0]['add_new_job']['tempname']);
                        ?>
                        <td class="wpjobportal-text-left">
                            <?php echo esc_html($wpjobportal_lang); ?>
                        </td>
                        <td>
                            <?php if (wpjobportal::$_data[0]['add_new_job']['employer'] == 1) { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['add_new_job']['tempid'].'&actionfor=1'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } else { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['add_new_job']['tempid'].'&actionfor=1'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } ?>
                        </td>
                        <td> - </td>
                        <td>
                            <?php if (wpjobportal::$_data[0]['add_new_job']['admin'] == 1) { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['add_new_job']['tempid'].'&actionfor=3'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } else { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['add_new_job']['tempid'].'&actionfor=3'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } ?>
                        </td>
                        <td>-</td>
                        <td>
                            <?php if (wpjobportal::$_data[0]['add_new_job']['employer_vis'] == 1) { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['add_new_job']['tempid'].'&actionfor=5'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } else { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['add_new_job']['tempid'].'&actionfor=5'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <?php
                            $wpjobportal_lang = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getLanguageForEmail(wpjobportal::$_data[0]['delete_job']['tempname']);
                        ?>
                        <td class="wpjobportal-text-left">
                            <?php echo esc_html($wpjobportal_lang); ?>
                        </td>
                        <td>
                            <?php if (wpjobportal::$_data[0]['delete_job']['employer'] == 1) { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['delete_job']['tempid'].'&actionfor=1'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } else { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['delete_job']['tempid'].'&actionfor=1'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } ?>
                        </td>
                        <td> - </td>
                        <td> - </td>
                        <td> - </td>
                        <td> - </td>
                    </tr>
                    <tr>
                        <?php
                            $wpjobportal_lang = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getLanguageForEmail(wpjobportal::$_data[0]['job_status']['tempname']);
                        ?>
                        <td class="wpjobportal-text-left">
                            <?php echo esc_html($wpjobportal_lang); ?>
                        </td>
                        <td>
                            <?php if (wpjobportal::$_data[0]['job_status']['employer'] == 1) { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['job_status']['tempid'].'&actionfor=1'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } else { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['job_status']['tempid'].'&actionfor=1'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } ?>
                        </td>
                        <td> - </td>
                        <td> - </td>
                        <td> - </td>
                        <td>
                            <?php if (wpjobportal::$_data[0]['job_status']['employer_vis'] == 1) { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['job_status']['tempid'].'&actionfor=5'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } else { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['job_status']['tempid'].'&actionfor=5'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } ?>
                        </td>
                    </tr>
                    <!-- resume section -->
                    <tr>
                        <td colspan="6" class="wpjobportal-table-section-header">
                            <?php echo esc_html(__('Resume', 'wp-job-portal')); ?>
                        </td>
                    </tr>
                    <tr>
                        <?php
                            $wpjobportal_lang = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getLanguageForEmail(wpjobportal::$_data[0]['add_new_resume']['tempname']);
                        ?>
                        <td class="wpjobportal-text-left">
                            <?php echo esc_html($wpjobportal_lang); ?>
                        </td>
                        <td>-</td>
                        <td>
                            <?php if (wpjobportal::$_data[0]['add_new_resume']['jobseeker'] == 1) { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['add_new_resume']['tempid'].'&actionfor=2'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } else { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['add_new_resume']['tempid'].'&actionfor=2'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } ?>
                        </td>
                        <td>
                            <?php if (wpjobportal::$_data[0]['add_new_resume']['admin'] == 1) { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['add_new_resume']['tempid'].'&actionfor=3'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } else { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['add_new_resume']['tempid'].'&actionfor=3'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } ?>
                        </td>
                        <td>
                            <?php if (wpjobportal::$_data[0]['add_new_resume']['jobseeker_vis'] == 1) { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['add_new_resume']['tempid'].'&actionfor=4'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } else { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['add_new_resume']['tempid'].'&actionfor=4'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } ?>
                        </td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <?php
                            $wpjobportal_lang = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getLanguageForEmail(wpjobportal::$_data[0]['resume-delete']['tempname']);
                        ?>
                        <td class="wpjobportal-text-left">
                            <?php echo esc_html($wpjobportal_lang); ?>
                        </td>
                        <td>-</td>
                        <td>
                            <?php if (wpjobportal::$_data[0]['resume-delete']['jobseeker'] == 1) { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['resume-delete']['tempid'].'&actionfor=2'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } else { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['resume-delete']['tempid'].'&actionfor=2'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } ?>
                        </td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <?php
                            $wpjobportal_lang = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getLanguageForEmail(wpjobportal::$_data[0]['resume_status']['tempname']);
                        ?>
                        <td class="wpjobportal-text-left">
                            <?php echo esc_html($wpjobportal_lang); ?>
                        </td>
                        <td>-</td>
                        <td>
                            <?php if (wpjobportal::$_data[0]['resume_status']['jobseeker'] == 1) { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['resume_status']['tempid'].'&actionfor=2'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } else { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['resume_status']['tempid'].'&actionfor=2'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } ?>
                        </td>
                        <td>-</td>
                        <td>
                            <?php if (wpjobportal::$_data[0]['resume_status']['jobseeker_vis'] == 1) { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['resume_status']['tempid'].'&actionfor=4'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } else { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['resume_status']['tempid'].'&actionfor=4'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } ?>
                        </td>
                        <td>-</td>
                    </tr>
                    <!-- employer section -->
                    <tr>
                        <td colspan="6" class="wpjobportal-table-section-header">
                            <?php echo esc_html(__('Employer', 'wp-job-portal')); ?>
                        </td>
                    </tr>
                    <tr>
                        <?php
                            $wpjobportal_lang = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getLanguageForEmail(wpjobportal::$_data[0]['add_new_employer']['tempname']);
                        ?>
                        <td class="wpjobportal-text-left">
                            <?php echo esc_html($wpjobportal_lang); ?>
                        </td>
                        <td>
                            <?php if (wpjobportal::$_data[0]['add_new_employer']['employer'] == 1) { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['add_new_employer']['tempid'].'&actionfor=1'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } else { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['add_new_employer']['tempid'].'&actionfor=1'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } ?>
                        </td>
                        <td>-</td>
                        <td>
                            <?php if (wpjobportal::$_data[0]['add_new_employer']['admin'] == 1) { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['add_new_employer']['tempid'].'&actionfor=3'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } else { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['add_new_employer']['tempid'].'&actionfor=3'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } ?>
                        </td>
                        <td>-</td>
                        <td>-</td>
                    </tr>
                    <!-- jobseeker section -->
                    <tr>
                        <td colspan="6" class="wpjobportal-table-section-header">
                            <?php echo esc_html(__('Job seeker', 'wp-job-portal')); ?>
                        </td>
                    </tr>
                    <tr>
                        <?php
                            $wpjobportal_lang = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getLanguageForEmail(wpjobportal::$_data[0]['add_new_jobseeker']['tempname']);
                        ?>
                        <td class="wpjobportal-text-left">
                            <?php echo esc_html($wpjobportal_lang); ?>
                        </td>
                        <td>-</td>
                        <td>
                            <?php if (wpjobportal::$_data[0]['add_new_jobseeker']['jobseeker'] == 1) { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['add_new_jobseeker']['tempid'].'&actionfor=2'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } else { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['add_new_jobseeker']['tempid'].'&actionfor=2'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } ?>
                        </td>
                        <td>
                            <?php if (wpjobportal::$_data[0]['add_new_jobseeker']['admin'] == 1) { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['add_new_jobseeker']['tempid'].'&actionfor=3'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } else { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['add_new_jobseeker']['tempid'].'&actionfor=3'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } ?>
                        </td>
                        <td>-</td>
                        <td>-</td>
                    </tr>
                    <!-- miscellaneous section -->
                    <tr>
                        <td colspan="6" class="wpjobportal-table-section-header">
                            <?php echo esc_html(__('Miscellaneous', 'wp-job-portal')); ?>
                        </td>
                    </tr>
                    <?php if(in_array('credits', wpjobportal::$_active_addons)){ ?>
                            <tr>
                                <?php
                                    $wpjobportal_lang = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getLanguageForEmail(wpjobportal::$_data[0]['package_purchase']['tempname']);
                                ?>
                               <td class="wpjobportal-text-left">
                                    <?php echo esc_html($wpjobportal_lang); ?>
                                </td>
                                <td>
                                    <?php if (wpjobportal::$_data[0]['package_purchase']['employer'] == 1) { ?>
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['package_purchase']['tempid'].'&actionfor=1'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                        </a>
                                    <?php } else { ?>
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['package_purchase']['tempid'].'&actionfor=1'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                        </a>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php if (wpjobportal::$_data[0]['package_purchase']['jobseeker'] == 1) { ?>
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['package_purchase']['tempid'].'&actionfor=2'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                        </a>
                                    <?php } else { ?>
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['package_purchase']['tempid'].'&actionfor=2'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                        </a>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php if (wpjobportal::$_data[0]['package_purchase']['admin'] == 1) { ?>
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['package_purchase']['tempid'].'&actionfor=3'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                        </a>
                                    <?php } else { ?>
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['package_purchase']['tempid'].'&actionfor=3'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                        </a>
                                    <?php } ?>
                                </td>
                                <td>-</td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <?php
                                    $wpjobportal_lang = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getLanguageForEmail(wpjobportal::$_data[0]['package_status']['tempname']);
                                ?>
                                <td class="wpjobportal-text-left">
                                    <?php echo esc_html($wpjobportal_lang); ?>
                                </td>
                                <td>
                                    <?php if (wpjobportal::$_data[0]['package_status']['employer'] == 1) { ?>
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['package_status']['tempid'].'&actionfor=1'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                        </a>
                                    <?php } else { ?>
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['package_status']['tempid'].'&actionfor=1'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                        </a>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php if (wpjobportal::$_data[0]['package_status']['jobseeker'] == 1) { ?>
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['package_status']['tempid'].'&actionfor=2'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                        </a>
                                    <?php } else { ?>
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['package_status']['tempid'].'&actionfor=2'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                        </a>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php if (wpjobportal::$_data[0]['package_status']['admin'] == 1) { ?>
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['package_status']['tempid'].'&actionfor=3'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                        </a>
                                    <?php } else { ?>
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['package_status']['tempid'].'&actionfor=3'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                        </a>
                                    <?php } ?>
                                </td>
                                <td>-</td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <?php
                                    $wpjobportal_lang = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getLanguageForEmail(wpjobportal::$_data[0]['package-purchase-admin']['tempname']);
                                ?>
                                 <td class="wpjobportal-text-left">
                                    <?php echo esc_html($wpjobportal_lang); ?>
                                </td>
                                <td>
                                    <?php if (wpjobportal::$_data[0]['package-purchase-admin']['employer'] == 1) { ?>
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['package-purchase-admin']['tempid'].'&actionfor=1'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                        </a>
                                    <?php } else { ?>
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['package-purchase-admin']['tempid'].'&actionfor=1'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                        </a>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php if (wpjobportal::$_data[0]['package-purchase-admin']['jobseeker'] == 1) { ?>
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['package-purchase-admin']['tempid'].'&actionfor=2'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                        </a>
                                    <?php } else { ?>
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['package-purchase-admin']['tempid'].'&actionfor=2'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                        </a>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php if (wpjobportal::$_data[0]['package-purchase-admin']['admin'] == 1) { ?>
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['package-purchase-admin']['tempid'].'&actionfor=3'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                        </a>
                                    <?php } else { ?>
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['package-purchase-admin']['tempid'].'&actionfor=3'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                        </a>
                                    <?php } ?>
                                </td>
                                <td>-</td>
                                <td>-</td>
                            </tr>
                    <?php } ?>
                    <tr>
                        <?php
                            $wpjobportal_lang = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getLanguageForEmail(wpjobportal::$_data[0]['jobapply_jobapply']['tempname']);
                        ?>
                        <td class="wpjobportal-text-left">
                            <?php echo esc_html($wpjobportal_lang); ?>
                        </td>
                        <td>
                            <?php if (wpjobportal::$_data[0]['jobapply_jobapply']['employer'] == 1) { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['jobapply_jobapply']['tempid'].'&actionfor=1'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } else { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['jobapply_jobapply']['tempid'].'&actionfor=1'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } ?>
                        </td>
                        <td>
                            <?php if (wpjobportal::$_data[0]['jobapply_jobapply']['jobseeker'] == 1) { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['jobapply_jobapply']['tempid'].'&actionfor=2'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } else { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['jobapply_jobapply']['tempid'].'&actionfor=2'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } ?>
                        </td>
                        <td>
                            <?php if (wpjobportal::$_data[0]['jobapply_jobapply']['admin'] == 1) { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['jobapply_jobapply']['tempid'].'&actionfor=3'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } else { ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['jobapply_jobapply']['tempid'].'&actionfor=3'),'wpjobportal_emailstatus_nonce')); ?>" title="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>">
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" />
                                </a>
                            <?php } ?>
                        </td>
                        <td>-</td>
                        <td>-</td>
                    </tr>
                <?php if(in_array('resumeaction', wpjobportal::$_active_addons)){ ?>
                        <tr>
                            <?php
                                $wpjobportal_lang = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getLanguageForEmail(wpjobportal::$_data[0]['applied-resume_status']['tempname']);
                            ?>
                            <td class="wpjobportal-text-left"><?php echo esc_html($wpjobportal_lang); ?></td>
                            <td>-</td>
                            <td>
                                <?php if (wpjobportal::$_data[0]['applied-resume_status']['jobseeker'] == 1) { ?>
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['applied-resume_status']['tempid'].'&actionfor=2'),'wpjobportal_emailstatus_nonce')); ?>">
                                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" /></a>
                                <?php } else { ?>
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['applied-resume_status']['tempid'].'&actionfor=2'),'wpjobportal_emailstatus_nonce')); ?>">
                                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" /></a>
                                <?php } ?>
                            </td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                        </tr>
                <?php } ?>
                <?php if(in_array('message', wpjobportal::$_active_addons)){ ?>
                        <tr>
                            <?php
                                $wpjobportal_lang = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getLanguageForEmail(wpjobportal::$_data[0]['new_message']['tempname']);
                            ?>
                            <td class="wpjobportal-text-left"><?php echo esc_html($wpjobportal_lang); ?></td>

                            <td>
                                <?php if (wpjobportal::$_data[0]['new_message']['employer'] == 1) { ?>
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['new_message']['tempid'].'&actionfor=1'),'wpjobportal_emailstatus_nonce')); ?>">
                                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" /></a>
                                <?php } else { ?>
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['new_message']['tempid'].'&actionfor=1'),'wpjobportal_emailstatus_nonce')); ?>">
                                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" /></a>
                                <?php } ?>
                            </td>

                            <td>
                                <?php if (wpjobportal::$_data[0]['new_message']['jobseeker'] == 1) { ?>
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=noSendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['new_message']['tempid'].'&actionfor=2'),'wpjobportal_emailstatus_nonce')); ?>">
                                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-enable.png" border="0" alt="<?php echo esc_attr(__('Send email', 'wp-job-portal')); ?>" /></a>
                                <?php } else { ?>
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_emailtemplatestatus&task=sendEmail&action=wpjobportaltask&wpjobportalid='.wpjobportal::$_data[0]['new_message']['tempid'].'&actionfor=2'),'wpjobportal_emailstatus_nonce')); ?>">
                                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/view-disable.png" border="0" alt="<?php echo esc_attr(__('Do not send email', 'wp-job-portal')); ?>" /></a>
                                <?php } ?>
                            </td>

                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                        </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
