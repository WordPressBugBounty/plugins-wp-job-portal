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
                                        $raw_error  = $wpjobportal_systemerror->error;
                                        $error_data = json_decode( $raw_error, true );

                                        if ( is_array( $error_data ) ) :
                                        ?>
                                            <div class="wjp-error-cell">
                                                <div class="wjp-error-row">
                                                    <span class="wjp-badge wjp-badge-danger">Error</span>
                                                    <span class="wjp-error-text"><?php echo esc_html( isset( $error_data['error'] ) ? $error_data['error'] : 'Unknown Error' ); ?></span>
                                                </div>

                                                <div class="wjp-error-row">
                                                    <span class="wjp-badge wjp-badge-slate">URL</span>
                                                    <span class="wjp-url-text"><?php echo esc_html( isset( $error_data['url'] ) ? $error_data['url'] : 'N/A' ); ?></span>
                                                </div>

                                                <details class="wjp-error-details">
                                                    <summary><span class="wjp-summary-text">View Query & Trace</span></summary>
                                                    <div class="wjp-error-expanded">
                                                        <div class="wjp-code-group">
                                                            <div class="wjp-code-title">Path</div>
                                                            <div class="wjp-code-block"><?php echo esc_html( isset( $error_data['path'] ) ? $error_data['path'] : 'N/A' ); ?></div>
                                                        </div>

                                                        <div class="wjp-code-group">
                                                            <div class="wjp-code-title">Query</div>
                                                            <div class="wjp-code-block"><?php echo esc_html( isset( $error_data['query'] ) ? $error_data['query'] : 'N/A' ); ?></div>
                                                        </div>
                                                    </div>
                                                </details>
                                            </div>
                                        <?php
                                        // Fallback for plain text
                                        elseif ( ! empty( $raw_error ) ) :
                                        ?>
                                            <div class="wjp-error-cell">
                                                <div class="wjp-error-row">
                                                    <span class="wjp-badge wjp-badge-danger">Legacy Error</span>
                                                </div>
                                                <div class="wjp-code-block wjp-text-danger">
                                                    <?php echo esc_html( $raw_error ); ?>
                                                </div>
                                            </div>
                                        <?php else : ?>
                                            <span class="wjp-text-light">No error data recorded.</span>
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
