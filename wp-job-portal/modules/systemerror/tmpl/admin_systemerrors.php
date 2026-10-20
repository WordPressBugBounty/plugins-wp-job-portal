<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
wp_enqueue_script( 'wpjobportal-res-tables', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/responsivetable.js', array(), wpjobportal_asset_version('includes/js/responsivetable.js'), false );
?>
<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper">
    <!-- left menu -->
    <div id="wpjobportaladmin-leftmenu">
        <?php  WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <?php
            $wpjobportal_msgkey = WPJOBPORTALincluder::getJSModel('systemerror')->getMessagekey();
            WPJOBPORTALMessages::getLayoutMessage($wpjobportal_msgkey);
        ?>
        <!-- top bar -->
        <div id="wpjobportal-wrapper-top">
            <div id="wpjobportal-wrapper-top-left">
                <div id="wpjobportal-breadcrumbs">
                    <ul>
                        <li>
                            <a href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal')); ?>" title="<?php echo esc_attr(__('Dashboard','wp-job-portal')); ?>">
                                <?php echo esc_html(__('Dashboard','wp-job-portal')); ?>
                            </a>
                        </li>
                        <li><?php echo esc_html(__('Error Log','wp-job-portal')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="wpjobportal-wrapper-top-right">
                <div id="wpjobportal-config-btn">
                    <a href="admin.php?page=wpjobportal_configuration" title="<?php echo esc_attr(__('Configuration','wp-job-portal')); ?>">
                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/config.png">
                   </a>
                </div>
                <div id="wpjobportal-help-btn" class="wpjobportal-help-btn">
                    <a href="admin.php?page=wpjobportal&wpjobportallt=help" title="<?php echo esc_attr(__('Help','wp-job-portal')); ?>">
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
        <?php WPJOBPORTALincluder::getTemplate('templates/admin/pagetitle',array('wpjobportal_module' => 'systemerror' , 'wpjobportal_layouts' => 'systemerror')); ?>
        <!-- page content -->
        <div id="wpjobportal-admin-wrapper" class="p0 wpjobportal-admin-system-error-layout">
            <?php
                if (!empty(wpjobportal::$_data[0])) {
                    ?>
                    <table id="wpjobportal-table" class="wpjobportal-table">
                        <thead>
                            <tr>
                                <th class="wpjobportal-text-left w70">
                                    <?php echo esc_html(__('Error', 'wp-job-portal')); ?>
                                </th>
                                <th>
                                    <?php echo esc_html(__('View', 'wp-job-portal')); ?>
                                </th>
                                <th>
                                    <?php echo esc_html(__('Date', 'wp-job-portal')); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach (wpjobportal::$_data[0] AS $wpjobportal_systemerror) {
                                $wpjobportal_isview = ($wpjobportal_systemerror->isview == 1) ? 'close.png' : 'good.png';
                                ?>
                                <tr>
                                    <td class="wpjobportal-text-left w70" style="padding: 12px 15px; vertical-align: top;">
                                        <?php
                                        $wpjobportal_raw_error  = WPJOBPORTAL_Redactor::redact($wpjobportal_systemerror->error);
                                        $wpjobportal_error_data = json_decode( $wpjobportal_raw_error, true );

                                        if ( is_array( $wpjobportal_error_data ) ) :
                                        ?>
                                            <div class="wjp-error-cell">
                                                <div class="wjp-error-row">
                                                    <span class="wjp-badge wjp-badge-danger"><?php echo esc_html__('Error', 'wp-job-portal'); ?></span>
                                                    <span class="wjp-error-text"><?php echo esc_html( isset( $wpjobportal_error_data['error'] ) ? $wpjobportal_error_data['error'] : __('Unknown Error', 'wp-job-portal') ); ?></span>
                                                </div>

                                                <div class="wjp-error-row">
                                                    <span class="wjp-badge wjp-badge-slate"><?php echo esc_html__('URL', 'wp-job-portal'); ?></span>
                                                    <span class="wjp-url-text"><?php echo esc_html( isset( $wpjobportal_error_data['url'] ) ? $wpjobportal_error_data['url'] : __('N/A', 'wp-job-portal') ); ?></span>
                                                </div>

                                                <details class="wjp-error-details">
                                                    <summary><span class="wjp-summary-text"><?php echo esc_html__('View Query & Trace', 'wp-job-portal'); ?></span></summary>
                                                    <div class="wjp-error-expanded">
                                                        <div class="wjp-code-group">
                                                            <div class="wjp-code-title"><?php echo esc_html__('Path', 'wp-job-portal'); ?></div>
                                                            <div class="wjp-code-block"><?php echo esc_html( isset( $wpjobportal_error_data['path'] ) ? $wpjobportal_error_data['path'] : __('N/A', 'wp-job-portal') ); ?></div>
                                                        </div>

                                                        <div class="wjp-code-group">
                                                            <div class="wjp-code-title"><?php echo esc_html__('Query', 'wp-job-portal'); ?></div>
                                                            <div class="wjp-code-block"><?php echo esc_html( isset( $wpjobportal_error_data['query'] ) ? $wpjobportal_error_data['query'] : __('N/A', 'wp-job-portal') ); ?></div>
                                                        </div>
                                                    </div>
                                                </details>
                                            </div>
                                        <?php
                                        // Fallback for plain text
                                        elseif ( ! empty( $wpjobportal_raw_error ) ) :
                                        ?>
                                            <div class="wjp-error-cell">
                                                <div class="wjp-error-row">
                                                    <span class="wjp-badge wjp-badge-danger"><?php echo esc_html__('Legacy Error', 'wp-job-portal'); ?></span>
                                                </div>
                                                <div class="wjp-code-block wjp-text-danger">
                                                    <?php echo esc_html( $wpjobportal_raw_error ); ?>
                                                </div>
                                            </div>
                                        <?php else : ?>
                                            <span class="wjp-text-light"><?php echo esc_html__('No error data recorded.', 'wp-job-portal'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/<?php echo esc_attr($wpjobportal_isview); ?>" />
                                    </td>
                                    <td>
                                        <?php
                                            echo esc_html(date_i18n(wpjobportal::$_configuration['date_format'], strtotime($wpjobportal_systemerror->created)));
                                        ?>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                            <style></style>
                        </tbody>
                    </table>
                    <?php
                    if (wpjobportal::$_data[1]) {
                        echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(wpjobportal::$_data[1]) . '</div></div>';
                    }
                } else {
                    $wpjobportal_msg = esc_html(__('No Records Found','wp-job-portal'));
                    WPJOBPORTALlayout::getNoRecordFound($wpjobportal_msg);
                }
            ?>
        </div>
    </div>
</div>
