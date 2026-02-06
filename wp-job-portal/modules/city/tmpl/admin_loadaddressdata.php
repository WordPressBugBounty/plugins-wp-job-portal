<?php
if (!defined('ABSPATH'))
die('Restricted Access');
wp_enqueue_script('wpjobportal-res-tables', WPJOBPORTAL_PLUGIN_URL . 'includes/js/responsivetable.js');
if(!isset($_GET['case_is'])){
    $_GET['case_is'] = 1;
}
?>
<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper">
    <!-- left menu -->
	<div id="wpjobportaladmin-leftmenu">
        <?php  WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <?php
            $wpjobportal_msgkey = WPJOBPORTALincluder::getJSModel('city')->getMessagekey();
            WPJOBPORTALMessages::getLayoutMessage($wpjobportal_msgkey);
        ?>
        <!-- top bar -->
        <div id="wpjobportal-wrapper-top">
            <div id="wpjobportal-wrapper-top-left">
                <div id="wpjobportal-breadcrumbs">
                    <ul>
                        <li>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal')); ?>" title="<?php echo esc_attr__("dashboard","wp-job-portal"); ?>">
                                <?php echo esc_html__("Dashboard","wp-job-portal"); ?>
                            </a>
                        </li>
                        <li><?php echo esc_html__("Load Address Data","wp-job-portal"); ?></li>
                    </ul>
                </div>
            </div>
            <div id="wpjobportal-wrapper-top-right">
                <div id="wpjobportal-config-btn">
                    <a href="admin.php?page=wpjobportal_configuration" title="<?php echo esc_attr__("configuration","wp-job-portal"); ?>">
                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/config.png">
                   </a>
                </div>
                <div id="wpjobportal-help-btn" class="wpjobportal-help-btn">
                    <a href="admin.php?page=wpjobportal&wpjobportallt=help" title="<?php echo esc_attr__("help","wp-job-portal"); ?>">
                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/help.png">
                   </a>
                </div>
                <div id="wpjobportal-vers-txt">
                    <?php echo esc_html__("Version","wp-job-portal").': '; ?>
                    <span class="wpjobportal-ver"><?php echo esc_html(WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue('versioncode')); ?></span>
                </div>
            </div>
        </div>
        <!-- top head -->
        <div id="wpjobportal-head">

            <h1 class="wpjobportal-head-text">
                <?php echo esc_html__("Load Address Data", "wp-job-portal"); ?>
            </h1>
            <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_city&wpjobportallt=locationnamesettings')); ?>" class="wpjobportal-add-link button" title="<?php echo esc_attr__("Location Name Settings", "wp-job-portal"); ?>">
                <?php echo esc_html__("Location Name Settings", "wp-job-portal"); ?>
            </a>
        </div>
        <?php
        $wpjobportal_default_free ='checked="checked" ';
        $wpjobportal_default_pro = 'disabled="disabled"';//
        $wpjobportal_show_buy_addon_message = 1;
        $wpjobportal_extraa_class = 'wpjobportal-wrn-cls';
        if(in_array('addressdata', wpjobportal::$_active_addons) && $_GET['case_is'] == 1){
            $wpjobportal_default_free ='';
            $wpjobportal_default_pro ='checked="checked" ';
            $wpjobportal_show_buy_addon_message = 0;
            $wpjobportal_extraa_class = '';
        }
        ?>
        <!-- page content -->
        <div id="wpjobportal-admin-wrapper">
            <form id="wpjobportal-list-form" class="wpjobportal-form" enctype="multipart/form-data" method="post" action="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_city')); ?>">
                <div class="wpjobportal-city-data-form-wrap wpjobportal-city-data-form-wrap-left">
                    <div class="wpjobportal-form-wrapper">
                        <div class="wpjobportal-form-title">
                            <?php echo esc_html__("Country", "wp-job-portal"). ': '; ?>
                        </div>
                        <div class="wpjobportal-form-value">
                            <?php echo wp_kses(WPJOBPORTALformfield::select('country_code', WPJOBPORTALincluder::getJSModel('country')->getCountriesForComboForCityImport(), ''),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        </div>
                    </div>
                    <div class="wpjobportal-form-wrapper">
                        <div class="wpjobportal-form-title">
                            <?php echo esc_html__("Cities Data To Import", "wp-job-portal"). ': '; ?>
                        </div>
                        <div class="wpjobportal-form-value">
                            <span class="wpjobportal-form-radio-field" >
                                <input type="radio" name="data_to_import" id="data_to_import1"  <?php echo esc_html($wpjobportal_default_free);?> value="free" />
                                <label for="data_to_import1"><?php echo esc_html__("Free Data", "wp-job-portal"); ?>
                                </label>
                            </span>
                            <span class="wpjobportal-form-radio-field  <?php echo esc_attr($wpjobportal_extraa_class);?>">
                                <input type="radio" name="data_to_import" id="data_to_import2" <?php echo esc_html($wpjobportal_default_pro);?> value="pro" />
                                <label for="data_to_import2"><?php echo esc_html__("Paid Data", "wp-job-portal"); ?>
                                </label>
                            </span>
                            <div class="wpjobportal-form-citycount-wrap" >
                                <span class="wpjobportal-form-radio-field-first" >
                                    <span class="wpjobportal-city-data-free-label" >
                                    </span>
                                </span>
                                <span class="wpjobportal-form-radio-field-second" >
                                    <span class="wpjobportal-city-data-pro-label" >
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php
                        $wpjobportal_name_preference = get_option("wpjobportal_location_name_preference");
                    ?>
                    <div class="wpjobportal-form-wrapper">
                        <div class="wpjobportal-form-title">
                            <?php echo esc_html__("City Name Preferences", "wp-job-portal"). ': '; ?>
                        </div>
                        <div class="wpjobportal-form-value">
                            <span class="wpjobportal-form-radio-field wpjobportal-form-radio-field-first">
                                <input type="radio" name="name_preference" id="name_preference1" <?php if($wpjobportal_name_preference != 2) {?> checked="checked" <?php } ?> value="1" />
                                <label for="name_preference1"><?php echo esc_html__("International Name", "wp-job-portal"); ?></label>
                            </span>
                            <span class="wpjobportal-form-radio-field wpjobportal-form-radio-field-second">
                                <input type="radio" name="name_preference" id="name_preference2" <?php if($wpjobportal_name_preference == 2) {?> checked="checked" <?php } ?> value="2" />
                                <label for="name_preference2"><?php echo esc_html__("Native Name", "wp-job-portal"); ?></label>
                            </span>
                            <span id="loadaddressdata_city_name_msg">
                                <?php echo esc_html__("To enhance the user experience, do you prefer displaying city names in English or in their native language.", "wp-job-portal"); ?>
                            </span>
                        </div>
                    </div>

                    <div class="wpjobportal-form-wrapper">
                        <div class="wpjobportal-form-title">
                            <?php echo esc_html__("Existing Data", "wp-job-portal"). ': '; ?>
                        </div>
                        <div class="wpjobportal-form-value wpjobportal-load-address-data-erase-data-radios">
                            <span class="wpjobportal-form-radio-field wpjobportal-form-radio-field-first">
                                <input type="radio" name="keepdata" id="keepdata1"  checked="checked" value="1" />
                                <label for="keepdata1"><?php echo esc_html__("Keep Data", "wp-job-portal"); ?></label>
                            </span>
                            <span class="wpjobportal-form-radio-field wpjobportal-form-radio-field-second">
                                <input type="radio" name="keepdata" id="keepdata2" value="2" />
                                <label for="keepdata2"><?php echo esc_html__("Erase Selected Country", "wp-job-portal"); ?></label>
                                <span class="wpjobportal-form-erase-data-country-name" ></span>
                            </span>
                            <span class="wpjobportal-form-radio-field wpjobportal-form-radio-field-third">
                                <input type="radio" name="keepdata" id="keepdata3" value="3" />
                                <label for="keepdata3"><?php echo esc_html__("Erase All Data", "wp-job-portal"); ?></label>
                            </span>
                            <span id="loadaddressdata_city_name_msg_warn">
                                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/import-city-warning-icon.png">
                                <p>
                                    <?php echo esc_html__("Selecting 'Erase Data' will remove all existing cities from your database. If you choose this option all city data will be permanently deleted.", "wp-job-portal"); ?>
                                </p>
                            </span>
                        </div>
                    </div>
                </div>
                    <?php if($wpjobportal_show_buy_addon_message == 1){ ?>
                        <div class="wpjobportaladmin-add-on-page-wrp wpjobportal-city-data-form-wrap-right wpjobportal-city-data-load-data-box-css">
                            <div class="add-on-page-cnt">
                                <div class="add-on-list">
                                    <div class="add-on-item address-data">
                                        <div class="add-on-name"><?php echo esc_html(__('Address Data Addon To Get All The Cities And Towns','wp-job-portal')); ?></div>
                                        <img class="add-on-img" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/addon-images/address-data-load-img.png" alt="<?php echo esc_attr(__('Address Data','wp-job-portal')); ?>" />
                                        <div class="add-on-txt"><?php echo esc_html(__('WP Job Portal offers a feature for users to see address data for states, cities or both. Admin will upload that file.','wp-job-portal')); ?></div>
                                        <a title="<?php echo esc_attr(__('buy now','wp-job-portal')); ?>" href="https://wpjobportal.com/product/address-data/" class="add-on-btn"><?php echo esc_html(__('buy now','wp-job-portal')); ?></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                <div class="wpjobportal-form-button">
                    <input class="button wpjobportal-form-save-btn" type="submit" name="submit_app" id="submitbutton" value="<?php echo esc_attr__("Load Address Data", "wp-job-portal"); ?>" onclick="return validate_form(document.adminForm)" />
                </div>
                <div class="wpjobportal-city-data-form-wrap wpjobportal-city-data-form-wrap-left">
                    <div class="wpjobportal-city-data-sample" >
                        <div class="wpjobportal-city-data-sample-heading" >
                            <?php echo esc_html__("Sample Data", "wp-job-portal"); ?>
                        </div>
                        <div class="wpjobportal-city-data-table-wrap csl-frst-wdth" >
                            <table class="wpjobportal-city-data-sample-data" >
                                <thead>
                                    <tr>
                                        <th><?php echo esc_html__("International Name", "wp-job-portal"); ?></th>
                                        <th><?php echo esc_html__("Native Name", "wp-job-portal"); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Dubai</td>
                                        <td>دبي</td> <!-- Arabic for Dubai -->
                                    </tr>
                                    <tr>
                                        <td>Tokyo</td>
                                        <td>東京</td> <!-- Japanese for Tokyo -->
                                    </tr>
                                    <tr>
                                        <td>Frankfurt</td>
                                        <td>Frankfurt am Main</td> <!-- Same in German -->
                                    </tr>
                                    <tr>
                                        <td>Mexico City</td>
                                        <td>Ciudad de México</td> <!-- Spanish for Mexico City -->
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="wpjobportal-city-data-table-wrap csl-scnd-wdth" >
                            <table class="wpjobportal-city-data-sample-data" >
                                <thead>
                                    <tr>
                                        <th><?php echo esc_html__("International Name", "wp-job-portal"); ?></th>
                                        <th><?php echo esc_html__("Native Name", "wp-job-portal"); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Vienna</td>
                                        <td>Wien</td> <!-- German for Vienna -->
                                    </tr>
                                    <tr>
                                        <td>Florence</td>
                                        <td>Firenze</td> <!-- Italian for Florence -->
                                    </tr>
                                    <tr>
                                        <td>Warsaw</td>
                                        <td>Warszawa</td> <!-- Polish for Warsaw -->
                                    </tr>

                                    <tr>
                                        <td>Athens</td>
                                        <td>Αθήνα</td> <!-- Greek for Athens -->
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('form_request', 'wpjobportal'), WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('task', 'loadaddressdata'), WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('action', 'city_loadaddressdata'), WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('_wpnonce', wp_create_nonce('wpjobportal_address_data_nonce')), WPJOBPORTAL_ALLOWED_TAGS); ?>
            </form>
        </div>
    </div>
</div>
<?php
$wpjobportal_cities_data_count_array = array();
$wpjobportal_cities_data_count_array['ae'] = array("free" => 13, "pro" => 110);
$wpjobportal_cities_data_count_array['af'] = array("free" => 40, "pro" => 311);
$wpjobportal_cities_data_count_array['al'] = array("free" => 13, "pro" => 63);
$wpjobportal_cities_data_count_array['am'] = array("free" => 3, "pro" => 48);
$wpjobportal_cities_data_count_array['ao'] = array("free" => 21, "pro" => 164);
$wpjobportal_cities_data_count_array['ar'] = array("free" => 85, "pro" => 574);
$wpjobportal_cities_data_count_array['at'] = array("free" => 6, "pro" => 239);
$wpjobportal_cities_data_count_array['au'] = array("free" => 97, "pro" => 1231);
$wpjobportal_cities_data_count_array['az'] = array("free" => 25, "pro" => 106);
$wpjobportal_cities_data_count_array['ba'] = array("free" => 11, "pro" => 112);
$wpjobportal_cities_data_count_array['bb'] = array("free" => 1, "pro" => 5);
$wpjobportal_cities_data_count_array['bd'] = array("free" => 70, "pro" => 487);
$wpjobportal_cities_data_count_array['be'] = array("free" => 15, "pro" => 386);
$wpjobportal_cities_data_count_array['bf'] = array("free" => 3, "pro" => 132);
$wpjobportal_cities_data_count_array['bg'] = array("free" => 27, "pro" => 258);
$wpjobportal_cities_data_count_array['bh'] = array("free" => 4, "pro" => 20);
$wpjobportal_cities_data_count_array['bi'] = array("free" => 17, "pro" => 126);
$wpjobportal_cities_data_count_array['bj'] = array("free" => 25, "pro" => 229);
$wpjobportal_cities_data_count_array['bm'] = array("free" => 1, "pro" => 2);
$wpjobportal_cities_data_count_array['bn'] = array("free" => 1, "pro" => 9);
$wpjobportal_cities_data_count_array['bo'] = array("free" => 52, "pro" => 100);
$wpjobportal_cities_data_count_array['br'] = array("free" => 347, "pro" => 3132);
$wpjobportal_cities_data_count_array['bs'] = array("free" => 5, "pro" => 23);
$wpjobportal_cities_data_count_array['bt'] = array("free" => 2, "pro" => 46);
$wpjobportal_cities_data_count_array['bw'] = array("free" => 8, "pro" => 35);
$wpjobportal_cities_data_count_array['by'] = array("free" => 22, "pro" => 218);
$wpjobportal_cities_data_count_array['bz'] = array("free" => 3, "pro" => 17);
$wpjobportal_cities_data_count_array['ca'] = array("free" => 194, "pro" => 1726);
$wpjobportal_cities_data_count_array['cd'] = array("free" => 56, "pro" => 566);
$wpjobportal_cities_data_count_array['cf'] = array("free" => 21, "pro" => 181);
$wpjobportal_cities_data_count_array['cg'] = array("free" => 10, "pro" => 85);
$wpjobportal_cities_data_count_array['ch'] = array("free" => 9, "pro" => 171);
$wpjobportal_cities_data_count_array['ci'] = array("free" => 25, "pro" => 161);
$wpjobportal_cities_data_count_array['cl'] = array("free" => 48, "pro" => 322);
$wpjobportal_cities_data_count_array['cm'] = array("free" => 47, "pro" => 369);
$wpjobportal_cities_data_count_array['cn'] = array("free" => 2813, "pro" => 23714);
$wpjobportal_cities_data_count_array['co'] = array("free" => 62, "pro" => 1059);
$wpjobportal_cities_data_count_array['cr'] = array("free" => 15, "pro" => 78);
$wpjobportal_cities_data_count_array['cu'] = array("free" => 34, "pro" => 273);
$wpjobportal_cities_data_count_array['cv'] = array("free" => 2, "pro" => 25);
$wpjobportal_cities_data_count_array['cy'] = array("free" => 8, "pro" => 44);
$wpjobportal_cities_data_count_array['cz'] = array("free" => 9, "pro" => 521);
$wpjobportal_cities_data_count_array['de'] = array("free" => 88, "pro" => 2375);
$wpjobportal_cities_data_count_array['dj'] = array("free" => 1, "pro" => 13);
$wpjobportal_cities_data_count_array['dk'] = array("free" => 10, "pro" => 112);
$wpjobportal_cities_data_count_array['do'] = array("free" => 39, "pro" => 325);
$wpjobportal_cities_data_count_array['dz'] = array("free" => 52, "pro" => 653);
$wpjobportal_cities_data_count_array['ec'] = array("free" => 26, "pro" => 244);
$wpjobportal_cities_data_count_array['ee'] = array("free" => 2, "pro" => 49);
$wpjobportal_cities_data_count_array['eg'] = array("free" => 108, "pro" => 332);
$wpjobportal_cities_data_count_array['er'] = array("free" => 3, "pro" => 45);
$wpjobportal_cities_data_count_array['es'] = array("free" => 98, "pro" => 1053);
$wpjobportal_cities_data_count_array['et'] = array("free" => 34, "pro" => 693);
$wpjobportal_cities_data_count_array['fi'] = array("free" => 21, "pro" => 521);
$wpjobportal_cities_data_count_array['fj'] = array("free" => 3, "pro" => 22);
$wpjobportal_cities_data_count_array['fk'] = array("free" => 1, "pro" => 1);
$wpjobportal_cities_data_count_array['fr'] = array("free" => 56, "pro" => 1218);
$wpjobportal_cities_data_count_array['ga'] = array("free" => 16, "pro" => 40);
$wpjobportal_cities_data_count_array['gb'] = array("free" => 77, "pro" => 1649);
$wpjobportal_cities_data_count_array['ge'] = array("free" => 11, "pro" => 101);
$wpjobportal_cities_data_count_array['gh'] = array("free" => 13, "pro" => 527);
$wpjobportal_cities_data_count_array['gi'] = array("free" => 1, "pro" => 1);
$wpjobportal_cities_data_count_array['gl'] = array("free" => 5, "pro" => 17);
$wpjobportal_cities_data_count_array['gm'] = array("free" => 1, "pro" => 29);
$wpjobportal_cities_data_count_array['gn'] = array("free" => 15, "pro" => 187);
$wpjobportal_cities_data_count_array['gq'] = array("free" => 9, "pro" => 30);
$wpjobportal_cities_data_count_array['gr'] = array("free" => 54, "pro" => 425);
$wpjobportal_cities_data_count_array['gt'] = array("free" => 18, "pro" => 285);
$wpjobportal_cities_data_count_array['gw'] = array("free" => 3, "pro" => 44);
$wpjobportal_cities_data_count_array['gy'] = array("free" => 11, "pro" => 107);
$wpjobportal_cities_data_count_array['hn'] = array("free" => 22, "pro" => 299);
$wpjobportal_cities_data_count_array['hr'] = array("free" => 14, "pro" => 129);
$wpjobportal_cities_data_count_array['ht'] = array("free" => 28, "pro" => 120);
$wpjobportal_cities_data_count_array['hu'] = array("free" => 19, "pro" => 348);
$wpjobportal_cities_data_count_array['id'] = array("free" => 122, "pro" => 842);
$wpjobportal_cities_data_count_array['ie'] = array("free" => 5, "pro" => 258);
$wpjobportal_cities_data_count_array['il'] = array("free" => 39, "pro" => 123);
$wpjobportal_cities_data_count_array['im'] = array("free" => 1, "pro" => 4);
$wpjobportal_cities_data_count_array['in'] = array("free" => 543, "pro" => 4351);
$wpjobportal_cities_data_count_array['iq'] = array("free" => 58, "pro" => 424);
$wpjobportal_cities_data_count_array['ir'] = array("free" => 77, "pro" => 1632);
$wpjobportal_cities_data_count_array['is'] = array("free" => 1, "pro" => 44);
$wpjobportal_cities_data_count_array['it'] = array("free" => 103, "pro" => 1313);
$wpjobportal_cities_data_count_array['jm'] = array("free" => 6, "pro" => 33);
$wpjobportal_cities_data_count_array['jo'] = array("free" => 19, "pro" => 70);
$wpjobportal_cities_data_count_array['jp'] = array("free" => 801, "pro" => 1445);
$wpjobportal_cities_data_count_array['ke'] = array("free" => 16, "pro" => 301);
$wpjobportal_cities_data_count_array['kg'] = array("free" => 14, "pro" => 70);
$wpjobportal_cities_data_count_array['kh'] = array("free" => 27, "pro" => 202);
$wpjobportal_cities_data_count_array['km'] = array("free" => 3, "pro" => 24);
$wpjobportal_cities_data_count_array['kp'] = array("free" => 27, "pro" => 270);
$wpjobportal_cities_data_count_array['kr'] = array("free" => 94, "pro" => 1369);
$wpjobportal_cities_data_count_array['kw'] = array("free" => 28, "pro" => 28);
$wpjobportal_cities_data_count_array['ky'] = array("free" => 2, "pro" => 5);
$wpjobportal_cities_data_count_array['kz'] = array("free" => 25, "pro" => 221);
$wpjobportal_cities_data_count_array['la'] = array("free" => 18, "pro" => 123);
$wpjobportal_cities_data_count_array['lb'] = array("free" => 9, "pro" => 95);
$wpjobportal_cities_data_count_array['lk'] = array("free" => 33, "pro" => 397);
$wpjobportal_cities_data_count_array['lr'] = array("free" => 1, "pro" => 46);
$wpjobportal_cities_data_count_array['ls'] = array("free" => 1, "pro" => 23);
$wpjobportal_cities_data_count_array['lt'] = array("free" => 5, "pro" => 359);
$wpjobportal_cities_data_count_array['lu'] = array("free" => 1, "pro" => 15);
$wpjobportal_cities_data_count_array['lv'] = array("free" => 10, "pro" => 81);
$wpjobportal_cities_data_count_array['ly'] = array("free" => 43, "pro" => 217);
$wpjobportal_cities_data_count_array['ma'] = array("free" => 53, "pro" => 332);
$wpjobportal_cities_data_count_array['mc'] = array("free" => 1, "pro" => 1);
$wpjobportal_cities_data_count_array['md'] = array("free" => 17, "pro" => 72);
$wpjobportal_cities_data_count_array['me'] = array("free" => 7, "pro" => 39);
$wpjobportal_cities_data_count_array['mg'] = array("free" => 8, "pro" => 123);
$wpjobportal_cities_data_count_array['mh'] = array("free" => 1, "pro" => 3);
$wpjobportal_cities_data_count_array['mk'] = array("free" => 15, "pro" => 37);
$wpjobportal_cities_data_count_array['ml'] = array("free" => 47, "pro" => 582);
$wpjobportal_cities_data_count_array['mm'] = array("free" => 55, "pro" => 520);
$wpjobportal_cities_data_count_array['mn'] = array("free" => 25, "pro" => 269);
$wpjobportal_cities_data_count_array['mr'] = array("free" => 40, "pro" => 92);
$wpjobportal_cities_data_count_array['mt'] = array("free" => 2, "pro" => 31);
$wpjobportal_cities_data_count_array['mu'] = array("free" => 5, "pro" => 20);
$wpjobportal_cities_data_count_array['mv'] = array("free" => 5, "pro" => 23);
$wpjobportal_cities_data_count_array['mw'] = array("free" => 5, "pro" => 73);
$wpjobportal_cities_data_count_array['mx'] = array("free" => 167, "pro" => 2274);
$wpjobportal_cities_data_count_array['my'] = array("free" => 65, "pro" => 431);
$wpjobportal_cities_data_count_array['mz'] = array("free" => 21, "pro" => 362);
$wpjobportal_cities_data_count_array['na'] = array("free" => 9, "pro" => 41);
$wpjobportal_cities_data_count_array['ne'] = array("free" => 52, "pro" => 174);
$wpjobportal_cities_data_count_array['ng'] = array("free" => 65, "pro" => 767);
$wpjobportal_cities_data_count_array['ni'] = array("free" => 19, "pro" => 166);
$wpjobportal_cities_data_count_array['nl'] = array("free" => 35, "pro" => 319);
$wpjobportal_cities_data_count_array['no'] = array("free" => 20, "pro" => 144);
$wpjobportal_cities_data_count_array['np'] = array("free" => 22, "pro" => 199);
$wpjobportal_cities_data_count_array['nz'] = array("free" => 20, "pro" => 196);
$wpjobportal_cities_data_count_array['om'] = array("free" => 33, "pro" => 120);
$wpjobportal_cities_data_count_array['pa'] = array("free" => 9, "pro" => 114);
$wpjobportal_cities_data_count_array['pe'] = array("free" => 35, "pro" => 689);
$wpjobportal_cities_data_count_array['pg'] = array("free" => 3, "pro" => 143);
$wpjobportal_cities_data_count_array['ph'] = array("free" => 150, "pro" => 1408);
$wpjobportal_cities_data_count_array['pk'] = array("free" => 149, "pro" => 917);
$wpjobportal_cities_data_count_array['pl'] = array("free" => 66, "pro" => 974);
$wpjobportal_cities_data_count_array['pt'] = array("free" => 157, "pro" => 735);
$wpjobportal_cities_data_count_array['py'] = array("free" => 18, "pro" => 249);
$wpjobportal_cities_data_count_array['qa'] = array("free" => 3, "pro" => 14);
$wpjobportal_cities_data_count_array['ro'] = array("free" => 67, "pro" => 349);
$wpjobportal_cities_data_count_array['rs'] = array("free" => 55, "pro" => 191);
$wpjobportal_cities_data_count_array['ru'] = array("free" => 188, "pro" => 2621);
$wpjobportal_cities_data_count_array['rw'] = array("free" => 10, "pro" => 94);
$wpjobportal_cities_data_count_array['sa'] = array("free" => 55, "pro" => 277);
$wpjobportal_cities_data_count_array['sb'] = array("free" => 2, "pro" => 16);
$wpjobportal_cities_data_count_array['sc'] = array("free" => 1, "pro" => 1);
$wpjobportal_cities_data_count_array['sd'] = array("free" => 69, "pro" => 292);
$wpjobportal_cities_data_count_array['se'] = array("free" => 23, "pro" => 323);
$wpjobportal_cities_data_count_array['sg'] = array("free" => 2, "pro" => 2);
$wpjobportal_cities_data_count_array['si'] = array("free" => 4, "pro" => 69);
$wpjobportal_cities_data_count_array['sk'] = array("free" => 8, "pro" => 158);
$wpjobportal_cities_data_count_array['sl'] = array("free" => 6, "pro" => 65);
$wpjobportal_cities_data_count_array['sm'] = array("free" => 1, "pro" => 2);
$wpjobportal_cities_data_count_array['sn'] = array("free" => 17, "pro" => 244);
$wpjobportal_cities_data_count_array['so'] = array("free" => 31, "pro" => 152);
$wpjobportal_cities_data_count_array['sr'] = array("free" => 1, "pro" => 9);
$wpjobportal_cities_data_count_array['st'] = array("free" => 1, "pro" => 12);
$wpjobportal_cities_data_count_array['sv'] = array("free" => 68, "pro" => 208);
$wpjobportal_cities_data_count_array['sy'] = array("free" => 25, "pro" => 231);
$wpjobportal_cities_data_count_array['sz'] = array("free" => 2, "pro" => 18);
$wpjobportal_cities_data_count_array['td'] = array("free" => 70, "pro" => 322);
$wpjobportal_cities_data_count_array['tg'] = array("free" => 8, "pro" => 53);
$wpjobportal_cities_data_count_array['th'] = array("free" => 31, "pro" => 1018);
$wpjobportal_cities_data_count_array['tj'] = array("free" => 7, "pro" => 99);
$wpjobportal_cities_data_count_array['tl'] = array("free" => 1, "pro" => 61);
$wpjobportal_cities_data_count_array['tm'] = array("free" => 57, "pro" => 126);
$wpjobportal_cities_data_count_array['tn'] = array("free" => 20, "pro" => 144);
$wpjobportal_cities_data_count_array['to'] = array("free" => 1, "pro" => 21);
$wpjobportal_cities_data_count_array['tr'] = array("free" => 81, "pro" => 978);
$wpjobportal_cities_data_count_array['tt'] = array("free" => 4, "pro" => 34);
$wpjobportal_cities_data_count_array['tw'] = array("free" => 10, "pro" => 254);
$wpjobportal_cities_data_count_array['tz'] = array("free" => 21, "pro" => 236);
$wpjobportal_cities_data_count_array['ua'] = array("free" => 46, "pro" => 1197);
$wpjobportal_cities_data_count_array['ug'] = array("free" => 24, "pro" => 221);
$wpjobportal_cities_data_count_array['us'] = array("free" => 1333, "pro" => 9102);
$wpjobportal_cities_data_count_array['uy'] = array("free" => 41, "pro" => 158);
$wpjobportal_cities_data_count_array['uz'] = array("free" => 48, "pro" => 438);
$wpjobportal_cities_data_count_array['ve'] = array("free" => 186, "pro" => 1305);
$wpjobportal_cities_data_count_array['vn'] = array("free" => 94, "pro" => 913);
$wpjobportal_cities_data_count_array['vu'] = array("free" => 3, "pro" => 17);
$wpjobportal_cities_data_count_array['ws'] = array("free" => 1, "pro" => 29);
$wpjobportal_cities_data_count_array['ye'] = array("free" => 15, "pro" => 257);
$wpjobportal_cities_data_count_array['za'] = array("free" => 35, "pro" => 781);
$wpjobportal_cities_data_count_array['zm'] = array("free" => 6, "pro" => 116);
$wpjobportal_cities_data_count_array['zw'] = array("free" => 8, "pro" => 98);

wp_register_script( 'wpjobportal-inline-handle', '' );
wp_enqueue_script( 'wpjobportal-inline-handle' );

$wpjobportal_inline_js_script = "
    var citis_data_count = ". wp_json_encode($wpjobportal_cities_data_count_array).";
      jQuery('#country_code').change(function() {
          // Get the selected value from the dropdown
          var selectedCountryCode = jQuery(this).val();
          setRecordNumbers(selectedCountryCode);
    });

      jQuery(document).ready(function() {
          // Get the selected value from the dropdown
          var selectedCountryCode = jQuery('#country_code').val();
          setRecordNumbers(selectedCountryCode);
      });

      function setRecordNumbers(combo_value){
          var selectedCountryCode = combo_value;

          // Access the relevant data from the JSON array
          var selectedData = citis_data_count[selectedCountryCode];

          // Extract the 'free' and 'pro' values
          var free_count = selectedData ? selectedData.free : 'Not available';
          var pro_count = selectedData ? selectedData.pro : 'Not available';

          // display relvent values in HTML elements
          if(free_count != 'Not available'){
              jQuery('.wpjobportal-city-data-free-label').text('('+free_count+' '+\"". esc_html(__('Records','wp-job-portal'))."\"+')');
              jQuery('.wpjobportal-city-data-free-label').text(\"". esc_html(__('Free Version Contains','wp-job-portal'))." \"+free_count+' '+\"". esc_html(__('Records','wp-job-portal'))."\");
          }else{
              alert(\"".  esc_html(__("No Records Found","wp-job-portal")). "\");
              jQuery('.wpjobportal-city-data-free-label').text('('+\"". esc_html(__('No Records','wp-job-portal'))."\"+')');
          }

          if(pro_count != 'Not available'){
              jQuery('.wpjobportal-city-data-pro-label').text(\"". esc_html(__('Paid Version Contains','wp-job-portal'))." \" +pro_count+' '+\"". esc_html(__('Records','wp-job-portal'))."\");
          }else{
              jQuery('.wpjobportal-city-data-pro-label').text('('+\"". esc_html(__('No Records','wp-job-portal')) ."\"+')');
          }
          selectedLabelText = jQuery('#country_code').find('option:selected').text();
          jQuery('.wpjobportal-form-erase-data-country-name').text(selectedLabelText);
      }
    ";
wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
?>
