<?php
    if (!defined('ABSPATH'))
        die('Restricted Access');
    wp_enqueue_script('wpjobportal-res-tables', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/responsivetable.js');
    require_once WPJOBPORTAL_PLUGIN_PATH.'includes/addon-updater/wpjobportalupdater.php';
    $WPJOBPORTAL_JOBPORTALUpdater  = new WPJOBPORTAL_JOBPORTALUpdater();
    $wpjobportal_cdnversiondata = $WPJOBPORTAL_JOBPORTALUpdater->getPluginVersionDataFromCDN();
    $wpjobportal_not_installed = array();

    $wpjobportal_addons = $wpjobportal_jssupportticket_addons = WPJOBPORTALincluder::getJSModel('wpjobportal')->getWPJPAddonsArray();

?>
<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper">
    <!-- left menu -->
    <div id="wpjobportaladmin-leftmenu">
        <?php  WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data" class="wpjpadmin-addons-list-data">
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
                        <li><?php echo esc_html(__('Addons Status','wp-job-portal')); ?></li>
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
        <?php WPJOBPORTALincluder::getTemplate('templates/admin/pagetitle',array('wpjobportal_module' => 'wpjobportal' , 'wpjobportal_layouts' => 'addonstatus')); ?>
        <!-- page content -->
        <div id="wpjobportal-admin-wrapper">
            <!-- admin addons status -->
            <div id="black_wrapper_translation"></div>
            <div id="jstran_loading">
                <img alt="<?php echo esc_attr(__('spinning wheel','wp-job-portal')); ?>" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/spinning-wheel.gif" />
            </div>
            <div class="wpjpadmin-addons-list-wrp">
                <?php
                $wpjobportal_installed_plugins = get_plugins();
                ?>
                <?php
                    foreach ($wpjobportal_addons as $wpjobportal_key1 => $wpjobportal_value1) {
                        $wpjobportal_matched = 0;
                        $wpjobportal_version = "";
                        foreach ($wpjobportal_installed_plugins as $wpjobportal_name => $wpjobportal_value) {
                            $wpjobportal_install_plugin_name = str_replace(".php","",basename($wpjobportal_name));
                            if($wpjobportal_key1 == $wpjobportal_install_plugin_name){
                                $wpjobportal_matched = 1;
                                $wpjobportal_version = $wpjobportal_value["Version"];
                                $wpjobportal_install_plugin_matched_name = $wpjobportal_install_plugin_name;
                            }
                        }
                        $wpjobportal_status = '';
                        if($wpjobportal_matched == 1){ //installed
                            $wpjobportal_name = $wpjobportal_key1;
                            $title = $wpjobportal_value1['title'];
                            $wpjobportal_img = str_replace("wp-job-portal-", "", $wpjobportal_key1).'.png';
                            $wpjobportal_cdnavailableversion = "";
                            foreach ($wpjobportal_cdnversiondata as $wpjobportal_cdnname => $wpjobportal_cdnversion) {
                                $wpjobportal_install_plugin_name_simple = str_replace("-", "", $wpjobportal_install_plugin_matched_name);
                                if($wpjobportal_cdnname == str_replace("-", "", $wpjobportal_install_plugin_matched_name)){
                                    if($wpjobportal_cdnversion > $wpjobportal_version){ // new version available
                                        $wpjobportal_status = 'update_available';
                                        $wpjobportal_cdnavailableversion = $wpjobportal_cdnversion;
                                    }else{
                                        $wpjobportal_status = 'updated';
                                    }
                                }    
                            }
                            WPJP_PrintAddoneStatus($wpjobportal_name, $title, $wpjobportal_img, $wpjobportal_version, $wpjobportal_status, $wpjobportal_cdnavailableversion);
                        }else{ // not installed
                            $wpjobportal_img = str_replace("wp-job-portal-", "", $wpjobportal_key1).'.png';
                            $wpjobportal_not_installed[] = array("name" => $wpjobportal_key1, "title" => $wpjobportal_value1['title'], "img" => $wpjobportal_img, "status" => 'not-installed', "version" => "---");
                        }
                    }
                    foreach ($wpjobportal_not_installed as $wpjobportal_notinstall_addon) {
                        WPJP_PrintAddoneStatus($wpjobportal_notinstall_addon["name"], $wpjobportal_notinstall_addon["title"], $wpjobportal_notinstall_addon["img"], $wpjobportal_notinstall_addon["version"], $wpjobportal_notinstall_addon["status"]);
                    }
                ?>
            </div>
        </div>
    </div>
</div>

<?php
function WPJP_PrintAddoneStatus($wpjobportal_name, $title, $wpjobportal_img, $wpjobportal_version, $wpjobportal_status, $wpjobportal_cdnavailableversion = ''){
    $wpjobportal_addoneinfo = WPJOBPORTALincluder::getJSModel('wpjobportal')->checkWPJPAddoneInfo($wpjobportal_name);
    if ($wpjobportal_status == 'update_available') {
        $wrpclass = 'wpjp-admin-addon-status wpjp-admin-addons-status-update-wrp';
        $wpjobportal_btnclass = 'wpjp-admin-addons-update-btn';
        $wpjobportal_btntxt = 'Update Now';
        $wpjobportal_btnlink = 'id="wpjp-admin-addons-update" data-for="'.esc_attr($wpjobportal_name).'"';
        $wpjobportal_msg = '<span id="wpjp-admin-addon-status-cdnversion">'.esc_html(__('New Update Version','wp-job-portal'));
        $wpjobportal_msg .= '<span>'." ".esc_html($wpjobportal_cdnavailableversion)." ".'</span>';
        $wpjobportal_msg .= esc_html(__('is Available','wp-job-portal')).'</span>';
    } elseif ($wpjobportal_status == 'expired') {
        $wrpclass = 'wpjp-admin-addon-status wpjp-admin-addons-status-expired-wrp';
        $wpjobportal_btnclass = 'wpjp-admin-addons-expired-btn';
        $wpjobportal_btntxt = 'Expired';
        $wpjobportal_btnlink = '';
        $wpjobportal_msg = '';
    } elseif ($wpjobportal_status == 'updated') {
        $wrpclass = 'wpjp-admin-addon-status';
        $wpjobportal_btnclass = '';
        $wpjobportal_btntxt = 'Updated';
        $wpjobportal_btnlink = '';
        $wpjobportal_msg = '';
    } else {
        $wrpclass = 'wpjp-admin-addon-status';
        $wpjobportal_btnclass = 'wpjp-admin-addons-buy-btn';
        $wpjobportal_btntxt = 'Buy Now';
        $wpjobportal_btnlink = 'href="https://wpjobportal.com/add-ons/"';
        $wpjobportal_msg = '';
    }
    $wpjobportal_html = '
    <div class="'.esc_attr($wrpclass).'" id="'.esc_attr($wpjobportal_name).'">
        <div class="wpjp-addon-status-image-wrp">
            <img alt="Addone image" src="'.esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/control_panel/dashboard/addons/'.esc_attr($wpjobportal_img).'" />
        </div>
        <div class="wpjp-admin-addon-status-title-wrp">
            <h2>'. esc_html($title) .'</h2>
            <a class="'. esc_attr($wpjobportal_addoneinfo["actionClass"]) .'" href="'. esc_url($wpjobportal_addoneinfo["url"]) .'">
                '. esc_html($wpjobportal_addoneinfo["action"]) .'
            </a>
            '. wp_kses($wpjobportal_msg, WPJOBPORTAL_ALLOWED_TAGS).'
        </div>
        <div class="wpjp-admin-addon-status-addonstatus-wrp">
            <span>'. esc_html(__('Status','wp-job-portal')) .': </span>
            <span class="wpjp-admin-adons-status-Active" href="#">
                '. esc_html($wpjobportal_addoneinfo["status"]) .'
            </span>
        </div>
        <div class="wpjp-admin-addon-status-addonsversion-wrp">
            <span id="wpjp-admin-addon-status-cversion">
                '. esc_html(__('Version','wp-job-portal')).': 
                <span>
                    '. esc_html($wpjobportal_version) .'
                </span>
            </span>
        </div>
        <div class="wpjp-admin-addon-status-addonstatusbtn-wrp">
            <a '. wp_kses($wpjobportal_btnlink, WPJOBPORTAL_ALLOWED_TAGS).' class="'.esc_attr($wpjobportal_btnclass).'">'. esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_btntxt)) .'</a>
        </div>
        <div class="wpjp-admin-addon-status-msg wpjp_admin_success">
            <img src="'. esc_url(WPJOBPORTAL_PLUGIN_URL) .'includes/images/success.png" />
            <span class="wpjp-admin-addon-status-msg-txt"></span>
        </div>
        <div class="wpjp-admin-addon-status-msg wpjp_admin_error">
            <img src="'. esc_url(WPJOBPORTAL_PLUGIN_URL) .'includes/images/error.png" />
            <span class="wpjp-admin-addon-status-msg-txt"></span>
        </div>
    </div>';
        echo wp_kses($wpjobportal_html, WPJOBPORTAL_ALLOWED_TAGS);
    }

?>

<script>
    jQuery(document).ready(function(){
        jQuery(document).on("click", "a#wpjp-admin-addons-update", function(){
            jsShowLoading();
            var dataFor = jQuery(this).attr("data-for");
            var cdnVer = jQuery('#'+ dataFor +' #wpjp-admin-addon-status-cdnversion span').text();
            var currentVer = jQuery('#'+ dataFor +' #wpjp-admin-addon-status-cversion span').text();
            var cdnVersion = cdnVer.trim();
            var currentVersion = currentVer.trim();
            jQuery.post(ajaxurl, {action: 'wpjobportal_ajax', wpjobportalme: 'wpjobportal', task: 'downloadandinstalladdonfromAjax', dataFor:dataFor, currentVersion:currentVersion, cdnVersion:cdnVersion, '_wpnonce':'<?php echo esc_js(wp_create_nonce("download-and-install-addon")); ?>'}, function (data) {
                if (data) {
                    jsHideLoading();
                    data = JSON.parse(data);
                    if(data['error']){
                        jQuery('#' + dataFor).css('background-color', '#fff');
                        jQuery('#' + dataFor).css('border-color', '#FF4F4E');
                        jQuery('#' + dataFor + ' .wpjp-admin-addon-status-title-wrp span').hide();
                        jQuery('#' + dataFor + ' .wpjp-admin-addon-status-msg.wpjp_admin_error').show();
                        jQuery('#' + dataFor + ' .wpjp-admin-addon-status-msg.wpjp_admin_error span.wpjp-admin-addon-status-msg-txt').html(data['error']);
                        jQuery('#' + dataFor + ' .wpjp-admin-addon-status-msg.wpjp_admin_error').slideDown('slow');
                    } else if(data['success']) {
                        jQuery('#' + dataFor).css('background-color', '#fff');
                        jQuery('#' + dataFor).css('border-color', '#0C6E45');
                        jQuery('#' + dataFor + ' a#wpjp-admin-addons-update').hide();
                        jQuery('#' + dataFor + ' .wpjp-admin-addon-status-title-wrp span').hide();
                        jQuery('#' + dataFor + ' .wpjp-admin-addon-status-msg.wpjp_admin_success').show();
                        jQuery('#' + dataFor + ' .wpjp-admin-addon-status-msg.wpjp_admin_success span.wpjp-admin-addon-status-msg-txt').html(data['success']);
                        jQuery('#' + dataFor + ' .wpjp-admin-addon-status-msg.wpjp_admin_success').slideDown('slow');
                    }
                }
            });
        });
    });
    function jsShowLoading(){
        jQuery('div#black_wrapper_translation').show();
        jQuery('div#jstran_loading').show();
    }

    function jsHideLoading(){
        jQuery('div#black_wrapper_translation').hide();
        jQuery('div#jstran_loading').hide();
    }
</script>
