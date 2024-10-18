<?php
    if (!defined('ABSPATH'))
        die('Restricted Access');

    wp_register_script( 'wpjobportal-inline-handle', '' );
    wp_enqueue_script( 'wpjobportal-inline-handle' );

    $inline_js_script = "
    function resetFrom() {
        jQuery('input#countryname').val('');
        jQuery('select#status').val('');
        jQuery('#states1').prop('checked', false);
        jQuery('#city1').prop('checked', false);
        jQuery('form#wpjobportalform').submit();
    }
    ";
    wp_add_inline_script( 'wpjobportal-inline-handle', $inline_js_script );
?>

<?php
    wp_enqueue_script('wpjobportal-res-tables', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/responsivetable.js');
    if (!WPJOBPORTALincluder::getTemplate('templates/admin/header',array('module' => 'country'))){
        return;
    }
?>
<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper">
    <!-- left menu -->
    <div id="wpjobportaladmin-leftmenu">
        <?php  WPJOBPORTALincluder::getTemplate('templates/admin/leftmenue',array('module' => 'country')); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <!-- top bar -->
        <div id="wpjobportal-wrapper-top">
            <div id="wpjobportal-wrapper-top-left">
                <div id="wpjobportal-breadcrumbs">
                    <ul>
                        <li>
                            <a href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal')); ?>" title="<?php echo esc_html(__('dashboard','wp-job-portal')); ?>">
                                <?php echo esc_html(__('Dashboard','wp-job-portal')); ?>
                            </a>
                        </li>
                        <li><?php echo esc_html(__('Countries','wp-job-portal')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="wpjobportal-wrapper-top-right">
                <div id="wpjobportal-config-btn">
                    <a href="admin.php?page=wpjobportal_configuration" title="<?php echo esc_html(__('configuration','wp-job-portal')); ?>">
                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/config.png">
                   </a>
                </div>
                <div id="wpjobportal-help-btn" class="wpjobportal-help-btn">
                    <a href="admin.php?page=wpjobportal&wpjobportallt=help" title="<?php echo esc_html(__('help','wp-job-portal')); ?>">
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
        <?php  WPJOBPORTALincluder::getTemplate('templates/admin/pagetitle',array('module' => 'country','layouts' => 'countries')); ?>
        <!-- page content -->
        <div id="wpjobportal-admin-wrapper" class="p0 bg-n bs-n">
            <!-- quick actions -->
            <?php  WPJOBPORTALincluder::getTemplate('country/views/multioperation'); ?>
            <!-- filter form -->
            <form class="wpjobportal-filter-form" name="wpjobportalform" id="wpjobportalform" method="post" action="<?php echo esc_url_raw(admin_url("admin.php?page=wpjobportal_country")); ?>">
                <?php WPJOBPORTALincluder::getTemplate('country/views/filter'); ?>
            </form>
            <?php
                if (!empty(wpjobportal::$_data[0])) {
                    ?>
                    <form id="wpjobportal-list-form" method="post" action="<?php echo esc_url_raw(admin_url("admin.php?page=wpjobportal_country")); ?>">
                        <table id="wpjobportal-table" class="wpjobportal-table">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" name="selectall" id="selectall" value="">
                                    </th>
                                    <th>
                                        <?php echo esc_html(__('Name', 'wp-job-portal')); ?>
                                    </th>
                                    <th>
                                        <?php echo esc_html(__('International Name', 'wp-job-portal')); ?>
                                    </th>
                                    <th>
                                        <?php echo esc_html(__('Native Name', 'wp-job-portal')); ?>
                                    </th>
                                    <th>
                                        <?php echo esc_html(__('Published', 'wp-job-portal')); ?>
                                    </th>
                                    <th>
                                        <?php echo esc_html(__('States', 'wp-job-portal')); ?>
                                    </th>
                                    <th>
                                        <?php echo esc_html(__('Cities', 'wp-job-portal')); ?>
                                    </th>
                                    <th>
                                        <?php echo esc_html(__('Action', 'wp-job-portal')); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $pagenum = WPJOBPORTALrequest::getVar('pagenum', 'get', 1);
                                    $pageid = ($pagenum > 1) ? '&pagenum=' . $pagenum : '';
                                    foreach (wpjobportal::$_data[0] AS $row) {
                                        $published = ($row->enabled == 1) ? 'yes.png' : 'no.png';
                                        WPJOBPORTALincluder::getTemplate('country/views/main',array('row' => $row ,'pagenum' => $pagenum ,'pageid' => $pageid,'published' => $published ));
                                    }
                                ?>
                            </tbody>
                        </table>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('action', 'country_removecountry'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('pagenum', ($pagenum > 1) ? esc_html($pagenum) : ''),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('task', ''),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('form_request', 'wpjobportal'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('_wpnonce', esc_html(wp_create_nonce('wpjobportal_country_nonce'))),WPJOBPORTAL_ALLOWED_TAGS); ?>
                    </form>
                    <?php
                    if (wpjobportal::$_data[1]) {
                        WPJOBPORTALincluder::getTemplate('templates/admin/pagination',array('module' => 'country' , 'pagination' => wpjobportal::$_data[1]));
                    }
                } else {
                    $msg = esc_html(__('No record found','wp-job-portal'));
                    $link[] = array(
                            'link' => 'admin.php?page=wpjobportal_country&wpjobportallt=formcountry',
                            'text' => esc_html(__('Add New','wp-job-portal')) .' '. esc_html(__('Country','wp-job-portal'))
                        );
                    $link[] = array(
                            'link' => 'admin.php?page=wpjobportal_city&wpjobportallt=loadaddressdata',
                            'text' => esc_html(__('Load Address Data','wp-job-portal'))
                        );
                    WPJOBPORTALlayout::getNoRecordFound($msg,$link);
                }
            ?>
        </div>
    </div>
</div>
