<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
    wp_enqueue_script('jquery-ui-datepicker');
     if ( !WPJOBPORTALincluder::getTemplate('templates/admin/header', array('wpjobportal_module' => 'job')) ) {
        return;
    }
    $wpjobportal_protocol = is_ssl() ? "https://" : "http://";
    wp_enqueue_style('jquery-ui-css', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/jquery-ui-smoothness.css', array(), wpjobportal_asset_version('includes/css/jquery-ui-smoothness.css'));

    $wpjobportal_config = wpjobportal::$_configuration;
    if ($wpjobportal_config['date_format'] == 'm/d/Y' || $wpjobportal_config['date_format'] == 'd/m/y' || $wpjobportal_config['date_format'] == 'm/d/y' || $wpjobportal_config['date_format'] == 'd/m/Y') {
        $wpjobportal_dash = '/';
    } else {
        $wpjobportal_dash = '-';
    }
    $wpjobportal_dateformat = $wpjobportal_config['date_format'];
    $wpjobportal_firstdash = wpjobportalphplib::wpJP_strpos($wpjobportal_dateformat, $wpjobportal_dash, 0);
    $wpjobportal_firstvalue = wpjobportalphplib::wpJP_substr($wpjobportal_dateformat, 0, $wpjobportal_firstdash);
    $wpjobportal_firstdash = $wpjobportal_firstdash + 1;
    $wpjobportal_seconddash = wpjobportalphplib::wpJP_strpos($wpjobportal_dateformat, $wpjobportal_dash, $wpjobportal_firstdash);
    $wpjobportal_secondvalue = wpjobportalphplib::wpJP_substr($wpjobportal_dateformat, $wpjobportal_firstdash, $wpjobportal_seconddash - $wpjobportal_firstdash);
    $wpjobportal_seconddash = $wpjobportal_seconddash + 1;
    $wpjobportal_thirdvalue = wpjobportalphplib::wpJP_substr($wpjobportal_dateformat, $wpjobportal_seconddash, wpjobportalphplib::wpJP_strlen($wpjobportal_dateformat) - $wpjobportal_seconddash);
    $wpjobportal_js_dateformat = '%' . $wpjobportal_firstvalue . $wpjobportal_dash . '%' . $wpjobportal_secondvalue . $wpjobportal_dash . '%' . $wpjobportal_thirdvalue;
    $wpjobportal_js_scriptdateformat = $wpjobportal_firstvalue . $wpjobportal_dash . $wpjobportal_secondvalue . $wpjobportal_dash . $wpjobportal_thirdvalue;
    $wpjobportal_js_scriptdateformat = wpjobportalphplib::wpJP_str_replace('Y', 'yy', $wpjobportal_js_scriptdateformat);
    ?>
<style>
    div#map_container{
    width: 100%;
    height:<?php echo esc_attr(wpjobportal::$_configuration['mapheight']) . 'px'; ?>;
    }
</style>
<?php
    $wpjobportal_lists = array();
    $wpjobportal_defaultCategory = wpjobportal::$_common->getDefaultValue('categories');
    $wpjobportal_defaultJobtype = wpjobportal::$_common->getDefaultValue('jobtypes');
    $wpjobportal_defaultJobstatus = wpjobportal::$_common->getDefaultValue('jobstatus');
    $wpjobportal_defaultSalaryrangeType = wpjobportal::$_common->getDefaultValue('salaryrangetypes');
    $wpjobportal_defaultCareerlevels = wpjobportal::$_common->getDefaultValue('careerlevels');
    $wpjobportal_job = isset(wpjobportal::$_data[0]) ? wpjobportal::$_data[0] : null;
    ?>
<?php
    wp_register_script( 'wpjobportal-inline-handle', '', array(), WPJOBPORTAL_VERSION, false );
    wp_enqueue_script( 'wpjobportal-inline-handle' );

    $wpjobportal_inline_js_script = "
        var companycall = 0;
        jQuery(document).ready(function () {
            jQuery('select#companyid').on('change', function() {
                companycall = companycall + 1;
                var companyid = this.value;
                getdepartments('departmentid', companyid);
            });
            jQuery('select#companyid').change();
            jQuery('div#full_background,img#popup_cross').click(function (e) {
                jQuery('div#popup_main').slideUp('slow', function () {
                    jQuery('div#full_background').hide();
                });
            });
        });




        var companycall = 0;
        jQuery(document).ready(function () {
            jQuery('select#companyid').on('change', function() {
                companycall = companycall + 1;
                var companyid = this.value;
                jQuery.post(\"". esc_url_raw(admin_url('admin-ajax.php'))."\",{action:'wpjobportal_ajax',wpjobportalme:'user',task:'getUserIdByCompanyid',companyid:companyid, '_wpnonce':'". esc_attr(wp_create_nonce("get-user-id-by-company-id"))."'},function(data){
                    if(data){
                        jQuery('input.wpjobportal-form-save-btn').attr('credit_userid',data);
                    }
                });
                getdepartments('departmentid', companyid);
            });
            jQuery('select#companyid').change();
            jQuery('div#full_background,img#popup_cross').click(function (e) {
                jQuery('div#popup_main').slideUp('slow', function () {
                    jQuery('div#full_background').hide();
                });
            });
        });


        function selectPackage(packageid){
            jQuery('.package-div').css('border','1px solid #ccc');
            jQuery('#package-div-'+packageid).addClass('pkg-selected');
            jQuery('.pkg-item').removeClass('pkg-selected');
            jQuery('#package-div-'+packageid).addClass('pkg-selected');
            jQuery('#wpjobportal_packageid').val(packageid);
            jQuery('#upakid').val(packageid);
            jQuery('#pkg-disabled-btn').removeAttr('disabled');
            jQuery('.proceed-without-paying').removeClass('disabled-btn');
            if (jQuery('#package-div-'+packageid).hasClass('pkg-selected')) {
                jQuery('.proceed-without-paying').addClass('disabled-btn');
            }
        }
    ";
    wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
?>
<!-- main wrapper (design 4: fields in section cards in the order set in Forms & fields, publishing in a side panel) -->
<?php
$wpjobportal_isedit = isset(wpjobportal::$_data[0]->id) && (int) wpjobportal::$_data[0]->id > 0;
$wpjobportal_isque = WPJOBPORTALrequest::getVar('isqueue','get','');
// Which card each field sits in; within a card the Forms & fields order is kept.
// The fields appear exactly in the order set in Forms & fields; nothing is regrouped.
// Optional fields that take room or are rarely set start folded, in their own place.
$wpjobportal_icon = array('WPJOBPORTAL_Admin_Form_UI', 'icon');
$wpjobportal_foldable = array('map', 'tags', 'metakeywords', 'metadescription');
// Two fields used to read "Status": the hiring status and, in the side panel, the approval status.
$wpjobportal_titles = array('jobstatus' => __('Hiring status', 'wp-job-portal'));
$wpjobportal_formfields = (array) WPJOBPORTALincluder::getTemplate('job/form-fields', array(
    'wpjobportal_job' => $wpjobportal_job
));
$wpjobportal_field = function ($wpjobportal_formfield) use ($wpjobportal_job, $wpjobportal_foldable, $wpjobportal_titles) {
    WPJOBPORTAL_Admin_Form_UI::field($wpjobportal_formfield, $wpjobportal_job, $wpjobportal_foldable, $wpjobportal_titles);
};
WPJOBPORTAL_Admin_Form_UI::scripts('job_form');
$wpjobportal_token = WPJOBPORTALincluder::getJSModel('common')->getUniqueIdForTransient();
$wpjobportal_backlink = get_transient('current_user_token_job_'.$wpjobportal_token) ? admin_url('admin.php?page=wpjobportal_job&wpjobportal_restore_results='.$wpjobportal_token) : admin_url('admin.php?page=wpjobportal_job');
$wpjobportal_status = array((object) array('id' => 0, 'text' => esc_html(__('Waiting for approval', 'wp-job-portal'))), (object) array('id' => 1, 'text' => esc_html(__('Approved', 'wp-job-portal'))), (object) array('id' => -1, 'text' => esc_html(__('Rejected', 'wp-job-portal'))), (object) array('id' => 3, 'text' => esc_html(__('Pending payment', 'wp-job-portal'))));
?>
<div id="wpjobportaladmin-wrapper" class="wjpl-page">
    <!-- left menu -->
    <div id="wpjobportaladmin-leftmenu">
        <?php  WPJOBPORTALincluder::getTemplate('templates/admin/leftmenue',array('wpjobportal_module' => 'job')); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <div class="wjpl wjpl-formpage">
            <?php
            WPJOBPORTAL_Admin_List_UI::top_bar(array(
                array('label' => __('Jobs', 'wp-job-portal'), 'url' => $wpjobportal_backlink),
                array('label' => $wpjobportal_isedit ? __('Edit job', 'wp-job-portal') : __('Add New Job', 'wp-job-portal')),
            ));
            $wpjobportal_more = array();
            if ($wpjobportal_isedit) {
                $wpjobportal_more[__('View on site', 'wp-job-portal')] = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme' => 'job', 'wpjobportallt' => 'viewjob', 'wpjobportalid' => (int) wpjobportal::$_data[0]->id));
                $wpjobportal_more[__('Applications', 'wp-job-portal')] = admin_url('admin.php?page=wpjobportal_jobapply&wpjobportallt=jobappliedresume&jobid=' . (int) wpjobportal::$_data[0]->id);
            }
            WPJOBPORTAL_Admin_List_UI::title_row(
                $wpjobportal_isedit ? __('Edit job', 'wp-job-portal') : __('Add New Job', 'wp-job-portal'),
                $wpjobportal_isedit ? (string) wpjobportal::$_data[0]->title : __('Fields marked * are required.', 'wp-job-portal'),
                $wpjobportal_more,
                array()
            );
            ?>
            <?php if ($wpjobportal_isedit) :
                $wpjobportal_j = wpjobportal::$_data[0];
                $wpjobportal_approval = array(1 => array('ok', __('Approved', 'wp-job-portal')), 0 => array('wait', __('Waiting for approval', 'wp-job-portal')), -1 => array('bad', __('Rejected', 'wp-job-portal')), 3 => array('wait', __('Pending payment', 'wp-job-portal')));
                $wpjobportal_a = isset($wpjobportal_approval[(int) $wpjobportal_j->status]) ? $wpjobportal_approval[(int) $wpjobportal_j->status] : null;
                $wpjobportal_hiring = isset($wpjobportal_j->jobstatus) ? (string) wpjobportal::$_db->get_var('SELECT title FROM `' . wpjobportal::$_db->prefix . 'wj_portal_jobstatus` WHERE id = ' . (int) $wpjobportal_j->jobstatus) : '';
                $wpjobportal_apps = (int) wpjobportal::$_db->get_var('SELECT COUNT(id) FROM `' . wpjobportal::$_db->prefix . 'wj_portal_jobapply` WHERE jobid = ' . (int) $wpjobportal_j->id);
                ?>
                <div class="wjpl-formmeta">
                    <?php if ($wpjobportal_a) : ?><span class="wjpl-badge wjpl-b-<?php echo esc_attr($wpjobportal_a[0]); ?>"><?php echo esc_html($wpjobportal_a[1]); ?></span><?php endif; ?>
                    <?php if ($wpjobportal_hiring !== '') : ?><span class="wjpl-badge wjpl-b-closed"><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_hiring)); ?></span><?php endif; ?>
                    <?php if (!empty($wpjobportal_j->stoppublishing)) : ?><span><?php echo esc_html(sprintf(/* translators: %s: date */ __('Closes %s', 'wp-job-portal'), date_i18n(wpjobportal::$_configuration['date_format'], strtotime((string) $wpjobportal_j->stoppublishing)))); ?></span><?php endif; ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_jobapply&wpjobportallt=jobappliedresume&jobid=' . (int) $wpjobportal_j->id)); ?>"><?php echo esc_html(sprintf(/* translators: %s: number of applications */ _n('%s application', '%s applications', $wpjobportal_apps, 'wp-job-portal'), number_format_i18n($wpjobportal_apps))); ?></a>
                </div>
            <?php endif; ?>
            <?php if (isset(wpjobportal::$_data[0]->msg) AND wpjobportal::$_data[0]->msg != '') { ?>
                <div class="wjpl-flash wjpl-flash-bad" role="alert"><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue(wpjobportal::$_data[0]->msg)); ?></div>
            <?php } ?>
            <form id="job_form" class="wpjobportal-form wpjp-ds-form wjpl-form" method="post" enctype="multipart/form-data" action="<?php echo esc_url_raw(admin_url("admin.php?page=wpjobportal_job&task=savejob")); ?>">
                <div class="wjpl-form-main">
                    <section class="wjpl-card wjpl-fcard" aria-labelledby="wjpl-fs-details">
                        <h2 id="wjpl-fs-details"><?php echo $wpjobportal_icon('<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5h6v2"/>'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed SVG icon ?><?php echo esc_html(__('Job details', 'wp-job-portal')); ?></h2>
                        <div class="wjpl-fgrid">
                            <?php foreach ($wpjobportal_formfields as $wpjobportal_formfield) { $wpjobportal_field($wpjobportal_formfield); } ?>
                        </div>
                    </section>
                </div>
                <aside class="wjpl-form-side">
                    <section class="wjpl-card wjpl-fcard wjpl-publish" aria-labelledby="wjpl-fs-publishing">
                        <h2 id="wjpl-fs-publishing"><span class="wjpl-fcard-i" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/></svg></span><?php echo esc_html(__('Publishing', 'wp-job-portal')); ?></h2>
                        <div class="wjpl-fgrid">
                            <?php
                            $wpjobportal_field(array(
                                'title' => esc_html(__('Approval status', 'wp-job-portal')),
                                'wpjobportal_content' => WPJOBPORTALformfield::select('status', $wpjobportal_status, isset(wpjobportal::$_data[0]->status) ? wpjobportal::$_data[0]->status : 1, esc_html(__('Select Status', 'wp-job-portal')), array('class' => 'inputbox one wpjobportal-form-select-field')),
                                'wpjobportal_description' => __('Only approved jobs show on the site.', 'wp-job-portal'),
                            ));
                            ?>
                        </div>
                        <div class="wpjobportal-form-button wjpl-form-acts">
                            <?php
                                if (isset(wpjobportal::$_data[0]->id) || (!WPJOBPORTAL_Monetisation::usable('credits')) ) {
                                echo wp_kses(WPJOBPORTALformfield::submitbutton('save', esc_html(__('Save','wp-job-portal')) .' '. esc_html(__('Job', 'wp-job-portal')), array('class' => 'button wpjobportal-form-save-btn')),WPJOBPORTAL_ALLOWED_TAGS);
                            }else{
                                // handle the case of package not defined for employer role
                                $wpjobportal_subType = wpjobportal::$_config->getConfigValue('submission_type');
                                if($wpjobportal_subType == 3){
                                    $wpjobportal_no_package_needed = 0;
                                    $wpjobportal_result = WPJOBPORTALincluder::getJSModel('credits')->checkIfPackageDefinedForRole(1); // 1 is for employer
                                    if($wpjobportal_result == 0){ // 0 means no package found. so allow the action.
                                        echo wp_kses(WPJOBPORTALformfield::submitbutton('save', esc_html(__('Save','wp-job-portal')) .' '. esc_html(__('Job', 'wp-job-portal')), array('class' => 'button wpjobportal-form-save-btn')),WPJOBPORTAL_ALLOWED_TAGS);
                                    }else{
                                        echo wp_kses(WPJOBPORTALformfield::button('save', esc_html(__('Save','wp-job-portal')) .' '. esc_html(__('Job', 'wp-job-portal')), array('class' => 'button wpjobportal-form-save-btn','credit_userid' => '', 'onclick' => "wpjobportalformpopupAdmin('add_job','job_form');")),WPJOBPORTAL_ALLOWED_TAGS);
                                    }
                                }else{
                                    echo wp_kses(WPJOBPORTALformfield::button('save', esc_html(__('Save','wp-job-portal')) .' '. esc_html(__('Job', 'wp-job-portal')), array('class' => 'button wpjobportal-form-save-btn','credit_userid' => '', 'onclick' => "wpjobportalformpopupAdmin('add_job','job_form');")),WPJOBPORTAL_ALLOWED_TAGS);
                                }
                            }
                            ?>
                            <a id="form-cancel-button" class="wpjobportal-form-cancel-btn" href="<?php echo esc_url($wpjobportal_backlink); ?>" title="<?php echo esc_attr(__('cancel', 'wp-job-portal')); ?>">
                                <?php echo esc_html(__('Cancel', 'wp-job-portal')); ?>
                            </a>
                        </div>
                    </section>
                </aside>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('isqueue', $wpjobportal_isque != '' ? 1 : 0),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('id', isset(wpjobportal::$_data[0]->id) ? esc_html(wpjobportal::$_data[0]->id) : ''),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('default_longitude', esc_html(wpjobportal::$_configuration['default_longitude'])),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('default_latitude', esc_html(wpjobportal::$_configuration['default_latitude'])),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <input type="hidden" id="edit_longitude" name="edit_longitude" value="<?php echo  isset(wpjobportal::$_data[0]->longitude) ? esc_attr(wpjobportal::$_data[0]->longitude) : ''; ?>"/>
                <input type="hidden" id="edit_latitude" name="edit_latitude" value="<?php echo  isset(wpjobportal::$_data[0]->latitude) ? esc_attr(wpjobportal::$_data[0]->latitude) : ''; ?>"/>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('action', 'job_savejob'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('form_request', 'wpjobportal'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('isadmin', '1'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('payment', ''),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('creditid', ''),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('upakid', ''),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('package', 'job'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('_wpnonce', esc_html(wp_create_nonce('wpjobportal_job_nonce'))),WPJOBPORTAL_ALLOWED_TAGS); ?>
            </form>
        </div>
        <!-- Script Pro -->
        <?php
            $wpjobportal_mapfield = null;
            foreach(wpjobportal::$_data[2] AS $wpjobportal_key => $wpjobportal_value){
                $wpjobportal_value = (array) $wpjobportal_value;
                if(in_array('map', $wpjobportal_value)){
                    $wpjobportal_mapfield = $wpjobportal_key;
                    break;
                }
            }
            $wpjobportal_mappingservice = '';// to handle min fields error
            if($wpjobportal_mapfield):
                $wpjobportal_mapfield = wpjobportal::$_data[2][$wpjobportal_mapfield];
                $wpjobportal_mappingservice = wpjobportal::$_config->getConfigValue('mappingservice');
                if($wpjobportal_mapfield->published == 1){ ?>
                    <style>
                        div#map_container{border:2px solid #fff;}
                    </style>
                    <?php if($wpjobportal_mappingservice == "gmap"){ ?>
                        <?php $wpjobportal_protocol = is_ssl() ? "https://" : "http://";
                        wp_register_script ( 'wpjp-google-map-js', WPJOBPORTAL_Google_Maps::script_url(), array(), WPJOBPORTAL_VERSION, false );
                        wp_enqueue_script ( 'wpjp-google-map-js' );?>
                    <?php } elseif ($wpjobportal_mappingservice == "osm") {
                        wp_enqueue_script( 'wpjp-osm-js', esc_url(WPJOBPORTAL_PLUGIN_URL).'/includes/js/ol.min.js', array(), wpjobportal_asset_version('includes/js/ol.min.js'), false );
                        wp_enqueue_style( 'wpjp-osm-css', esc_url(WPJOBPORTAL_PLUGIN_URL).'/includes/css/ol.min.css', array(), wpjobportal_asset_version('includes/css/ol.min.css') );
                    ?>
                    <?php } ?>
                    <?php
                        $wpjobportal_inline_js_script = "
                            var map = null;
                            var markers = [];
                            var latlang_marker_array = [];
                            function addMarker(latlang,cityid = 0){
                                var marker = new google.maps.Marker({
                                    position: latlang,
                                    map: map,
                                    draggable: true,
                                });
                                marker.setMap(map);
                                map.setCenter(latlang);
                                // cityid is to identify the marker neds to be removed.
                                if(cityid != 0){
                                    marker.cityid = cityid;
                                    markers.push(marker);
                                }
                                // this array is for newly added city whoose marker may need to be removed.
                                latlang_marker_array[latlang] = marker;
                                //..

                                marker.addListener('dblclick', function() {
                                    deleteMarker(marker);
                                });
                                if(document.getElementById('latitude').value == ''){
                                    document.getElementById('latitude').value = marker.position.lat();
                                }else if(document.getElementById('latitude').value != marker.position.lat()){ // was adding same corrdinates twice
                                    document.getElementById('latitude').value += ',' + marker.position.lat();
                                }
                                if(document.getElementById('longitude').value == ''){
                                    document.getElementById('longitude').value = marker.position.lng();
                                }else if(document.getElementById('longitude').value != marker.position.lng()){ // was adding same corrdinates twice
                                    document.getElementById('longitude').value += ',' + marker.position.lng();
                                }
                            }

                            function deleteMarker(marker){ // this fucntion completely remves markr and thier lat lang values from text field
                                var latitude = document.getElementById('latitude').value;
                                latitude = latitude.replace(','+marker.position.lat(), '');
                                latitude = latitude.replace(marker.position.lat()+',', '');
                                latitude = latitude.replace(marker.position.lat(), '');
                                document.getElementById('latitude').value = latitude;
                                var longitude = document.getElementById('longitude').value;
                                longitude = longitude.replace(','+marker.position.lng(), '');
                                longitude = longitude.replace(marker.position.lng()+',', '');
                                longitude = longitude.replace(marker.position.lng(), '');
                                document.getElementById('longitude').value = longitude;
                                marker.setMap(null);
                                return;
                            }

                            function identifyMarkerForDelete(t_item){// this fucntion identifies the marker assiciated with token input value that has been removed.
                                var id = t_item.id;
                                // this code is when lat lang are added from data base cities
                                for (var i = 0; i < markers.length; i++) {
                                    if (markers[i].cityid == id) {
                                        //Remove the marker from Map
                                        //markers[i].setMap(null);
                                        deleteMarker(markers[i]);
                                        //Remove the marker from array.
                                        markers.splice(i, 1);
                                        return;
                                    }
                                }
                                // this code is for when lat lang belonged to newely added city
                                if( t_item.latitude != undefined && t_item.latitude != '' && t_item.latitude != 0){
                                    var markerLatlng = new google.maps.LatLng(t_item.latitude, t_item.longitude);
                                    deleteMarker(latlang_marker_array[markerLatlng]);
                                    markers.splice(markerLatlng, 1);
                                }
                            }

                            function addMarkerOnMap(location){
                                if( location.latitude != undefined && location.latitude != '' && location.latitude != 0){// code is for adding a marker from data base lat lang.
                                    var latlng = new google.maps.LatLng(String(location.latitude), String(location.longitude));
                                    if(map != null){
                                        addMarker(latlng,location.id);
                                    } else {
                                        //alert(\"". esc_html(__('Something went wrong','wp-job-portal')).":\");
                                    }
                                }else{ // this code for adding a marker from location name. // this code is redundant but leaving it here
                                    var geocoder =  new google.maps.Geocoder();
                                    geocoder.geocode( { 'address': location.name}, function(results, status) {
                                        var latlng = new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng());
                                        if (status == google.maps.GeocoderStatus.OK) {
                                            if(map != null){
                                                addMarker(latlng,location.id);
                                            }
                                        } else {
                                            
                                        }
                                    });
                                }
                                return;
                            }

                            function loadMap() {
                                var default_latitude = document.getElementById('default_latitude').value;
                                var default_longitude = document.getElementById('default_longitude').value;
                                var latitude = document.getElementById('edit_latitude').value;
                                var longitude = document.getElementById('edit_longitude').value;
                                var isdefaultvalue = true;
                                if (latitude != '' && longitude != '') {
                                    default_latitude = latitude;
                                    default_longitude = longitude;
                                    isdefaultvalue = false;
                                }

                                var latlng = new google.maps.LatLng(document.getElementById('default_latitude').value, document.getElementById('default_longitude').value);
                                zoom = 8;
                                var myOptions = {
                                    zoom: zoom,
                                    center: latlng,
                                    scrollwheel: false,
                                    mapTypeId: google.maps.MapTypeId.ROADMAP
                                };
                                map = new google.maps.Map(document.getElementById('map_container'), myOptions);
                                default_latitude = default_latitude.split(',');
                                if(default_latitude instanceof Array){
                                    default_longitude = default_longitude.split(',');
                                    for (i = 0; i < default_latitude.length; i++) {
                                        var latlng = new google.maps.LatLng(default_latitude[i], default_longitude[i]);
                                        if(isdefaultvalue == false)
                                            addMarker(latlng);
                                    }
                                }else{
                                    var latlng = new google.maps.LatLng(default_latitude, default_longitude);
                                    if(isdefaultvalue == false)
                                        addMarker(latlng);
                                }
                                google.maps.event.addListener(map, 'click', function (e) {
                                    var latLng = new google.maps.LatLng(e.latLng.lat(), e.latLng.lng());
                                    geocoder = new google.maps.Geocoder();
                                    geocoder.geocode({'latLng': latLng}, function (results, status) {
                                    if (status == google.maps.GeocoderStatus.OK) {
                                        addMarker(results[0].geometry.location);
                                    } else {
                                        alert(\"". esc_html(__("Geocode was not successful for the following reason", 'wp-job-portal')).": \" + status);
                                    }
                                    });
                                });
                            }
                        ";
                        wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );

                    ?>
            <?php } ?>
        <?php endif; ?>
        <?php
            $wpjobportal_inline_js_script = "
                var lmap = {
                     map:null,
                     marker:null,
                     init: function(){
                         this.toggleMap();
                         jQuery('#showmap1').on('change',lmap.toggleMap);
                     },
                     toggleMap: function(){
                         if(!jQuery('#showmap1').length || jQuery('#showmap1:checked').val()){
                             lmap.showMap();
                         }else{
                             lmap.hideMap();
                         }
                     },
                     showMap: function(){
                         jQuery('#map-latlng-wrap').show();
                         if(!this.map){
                             this.loadMap();
                         }
                     },
                     hideMap: function(){
                         jQuery('#map-latlng-wrap').hide();
                     },
                    loadMap: function(){ ";

                        if($wpjobportal_mappingservice == "osm"){
                            $wpjobportal_inline_js_script .= "
                            var default_latitude = parseFloat(document.getElementById('default_latitude').value);
                            var default_longitude = parseFloat(document.getElementById('default_longitude').value);
                            lmap.map = new ol.Map({
                                target: 'map_container',
                                controls: ol.control.defaults().extend([new ol.control.FullScreen()]),
                                layers: [
                                    new ol.layer.Tile({
                                        source: new ol.source.OSM()
                                    })
                                ],
                                view: new ol.View({
                                    center: ol.proj.fromLonLat([default_longitude,default_latitude]),
                                    zoom: 14,
                                }),
                            });
                            lmap.map.addEventListener('click',function(event){
                                lmap.addMarker(ol.proj.transform(event.coordinate, 'EPSG:3857', 'EPSG:4326'));
                            });";
                        }
                        $wpjobportal_inline_js_script .= "
                    },
                    addMarker: function(latlang){
                        if(!lmap.map){
                             return false;
                        }";
                        if($wpjobportal_mappingservice == "osm"){
                            $wpjobportal_inline_js_script .= "
                            if(lmap.marker){
                                lmap.map.removeLayer(lmap.marker);
                            }
                            lmap.marker = osmAddMarker(lmap.map, latlang);
                            lmap.map.getView().setCenter(ol.proj.transform(latlang, 'EPSG:4326', 'EPSG:3857'));
                            document.getElementById('latitude').value = latlang[1];
                            document.getElementById('longitude').value = latlang[0]; ";
                        }
                        $wpjobportal_inline_js_script .= "
                    }
                 };
                jQuery(document).ready(function ($) {
                    lmap.init();
                    /*Add Mark Up to OSM MAP*/ ";

                    
                        if($wpjobportal_mappingservice == 'osm'){
                            if(isset($wpjobportal_job) && !empty($wpjobportal_job->longitude) && !empty($wpjobportal_job->latitude)) {
                                    // Security: emit numbers; the stored values used to be concatenated raw into a JS string.
                                    $wpjobportal_inline_js_script .= "lmap.addMarker([". (float) $wpjobportal_job->longitude .",". (float) $wpjobportal_job->latitude ."]); ";
                             }
                        } 
                        $wpjobportal_inline_js_script .= "
                        /*job apply link start*/
                        if (jQuery('input#jobapplylink1').is(':checked')){
                            jQuery('div#input-text-joblink').show();
                        }
                        jQuery('input#jobapplylink1').click(function(){
                            if (jQuery(this).is(':checked')){
                                jQuery('div#input-text-joblink').show();
                            } else{
                                jQuery('div#input-text-joblink').hide();
                            }
                        });

                        /*job apply link end*/
                        $('.custom_date').datepicker({dateFormat: '". esc_js($wpjobportal_js_scriptdateformat)."'});
                            $.validate();
                            //Token Input
                            ";
                            $wpjobportal_multicities = "var multicities = '';";
                            if(isset(wpjobportal::$_data[0]->multicity)) 
                                if(!(wpjobportal::$_data[0]->multicity == "[]")) 
                                    $wpjobportal_multicities = "var multicities = ". wpjobportal::$_data[0]->multicity.";";
                            
                            $wpjobportal_inline_js_script .= $wpjobportal_multicities;
                            $wpjobportal_inline_js_script .= "
                            getTokenInput(multicities);

                            //tags
                            ";
                            if(in_array('tag',wpjobportal::$_active_addons)){
                                $wpjobportal_jobtags = "var jobtags = '';";
                                if(isset(wpjobportal::$_data[0]->jobtags)) 
                                    if(!(wpjobportal::$_data[0]->jobtags == "[]")) 
                                        $wpjobportal_jobtags = "var jobtags = ". wpjobportal::$_data[0]->jobtags.";";
                                
                                $wpjobportal_inline_js_script .= $wpjobportal_jobtags;
                                $wpjobportal_inline_js_script .= "
                                getTokenInputTags(jobtags);";
                            }
                            $wpjobportal_inline_js_script .= "
                            var map_obj = document.getElementById('map_container');
                            ";
                            if($wpjobportal_mappingservice == 'gmap'){
                                $wpjobportal_inline_js_script .= "
                                if (typeof map_obj !== 'undefined' && map_obj !== null) {
                                    window.onload = loadMap();
                                } ";
                            }
                            $wpjobportal_inline_js_script .= "
                        });
                        ";
                    wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
                        

                        $wpjobportal_inline_js_script = "
                        function getdepartments(src, val){ 
                            ";
                            if(in_array('departments', wpjobportal::$_active_addons)){ 
                                $wpjobportal_inline_js_script .= "
                                if(companycall > 1){
                                    var ajaxurl = \"" .esc_html(admin_url('admin-ajax.php')) ."\";
                                    jQuery.post(ajaxurl, {action: 'wpjobportal_ajax', wpjobportalme: 'departments', task: 'listdepartments', val: val, '_wpnonce':'". esc_attr(wp_create_nonce("list-departments"))."'}, function(data){
                                        if (data){
                                            jQuery('#' + src).html(data); //retuen value
                                        }
                                    });
                                } ";
                            }
                            $wpjobportal_inline_js_script .= "
                        }

                        function hideShowRange(hideSrc, showSrc, showLink, hideLink, showName, showVal){
                            jQuery('#' + hideSrc).toggle();
                            jQuery('#' + showSrc).toggle();
                            jQuery('#' + showLink).toggle();
                            jQuery('#' + hideLink).toggle();
                        }

                        function getTokenInputTags(multitags) {

                            var tagArray = '". esc_url_raw(admin_url("admin.php?page=wpjobportal_tag&tagfor=1&action=wpjobportaltask&task=gettagsbytagname"))."';
                            jQuery('#tags').tokenInput(tagArray, {
                                theme: 'wpjobportal',
                                preventDuplicates: true,
                                hintText: \"". esc_html(__('Type In A Search Term', 'wp-job-portal')) ."\",
                                noResultsText: \"". esc_html(__('No Results', 'wp-job-portal')). "\",
                                searchingText: \"". esc_html(__('Searching', 'wp-job-portal')) ."\",
                                tokenLimit: 5,
                                prePopulate: multitags, ";
                                $wpjobportal_newtyped_tags = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('newtyped_tags');
                                if ($wpjobportal_newtyped_tags == 1) {
                                    $wpjobportal_inline_js_script .= "
                                    onResult: function(item) {
                                        if (jQuery.isEmptyObject(item)){
                                            return [{id: '', name: jQuery('tester').text()}];
                                        } else {
                                            //add the item at the top of the dropdown
                                            item.unshift({id: '', name: jQuery('tester').text()});
                                            return item;
                                        }
                                    },
                                    onAdd: function(item) {
                                        if (item.id != ''){return; }
                                        var ajaxurl = \"". esc_url_raw(admin_url('admin-ajax.php')) ."\";
                                        jQuery.post(ajaxurl, {action: 'wpjobportal_ajax', wpjobportalme: 'tag', task: 'saveTokenInputTag', tagdata: jQuery('tester').text(), '_wpnonce':'". esc_attr(wp_create_nonce("save-token-input-tag"))."'}, function(data){
                                            if (data){
                                            try {
                                                var value = jQuery.parseJSON(data);
                                                jQuery('#tags').tokenInput('remove', item);
                                                jQuery('#tags').tokenInput('add', {id: value.id, name: value.name});
                                            }
                                            catch (err) {
                                                jQuery('#tags').tokenInput('remove', item);
                                                        alert(data);
                                                }
                                            }
                                        });
                                    }";
                                }
                                $wpjobportal_inline_js_script .= "
                            });
                        }

            ";
            wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
                
                $wpjobportal_inline_js_script = "
                    function getTokenInput(multicities) {
                        var cityArray = '". esc_url_raw(admin_url("admin.php?page=wpjobportal_city&action=wpjobportaltask&task=getaddressdatabycityname"))."';
                        var city = jQuery('#cityforedit').val();
                        if (city != '') {
                            jQuery('#city').tokenInput(cityArray, {
                                theme: 'wpjobportal',
                                preventDuplicates: true,
                                hintText: \"". esc_html(__("Type In A Search Term", 'wp-job-portal'))."\",
                                noResultsText: \"". esc_html(__("No Results", 'wp-job-portal'))."\",
                                searchingText: \"". esc_html(__("Searching", 'wp-job-portal'))."\",
                                // tokenLimit: 1,
                                prePopulate: multicities,";
                                $wpjobportal_newtyped_cities = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('newtyped_cities');
                                if ($wpjobportal_newtyped_cities == 1) {
                                    $wpjobportal_inline_js_script .= "
                                    onResult: function(item) {
                                        if (jQuery.isEmptyObject(item)){
                                            return [{id:0, name: jQuery('tester').text()}];
                                        } else {
                                            //add the item at the top of the dropdown
                                            item.unshift({id:0, name: jQuery('tester').text()});
                                            return item;
                                        }
                                    },
                                    onAdd: function(item) {
                                        if (item.id > 0){";
                                            
                                            if($wpjobportal_mapfield):
                                                if($wpjobportal_mapfield->published == 1 && $wpjobportal_mappingservice != "osm"){
                                                    $wpjobportal_inline_js_script .= "addMarkerOnMap(item);";
                                                } 
                                            endif; 
                                            $wpjobportal_inline_js_script .= "
                                                return;
                                        }
                                        if (item.name.search(',') == - 1) {
                                            var input = jQuery('tester').text();
                                            alert (\"". esc_html(__("Location Format Is Not Correct Please Enter City In This Format City Name Country Name Or City Name State Name Country Name", 'wp-job-portal'))."\");
                                            jQuery('#city').tokenInput('remove', item);
                                            return false;
                                        } else {
                                            var ajaxurl = \"". esc_url_raw(admin_url('admin-ajax.php'))."\";
                                            var location_data =  jQuery('tester').text();
                                                //alert(new_loction_lat);
                                                var n_latitude;
                                                var n_longitude;
                                                var geocoder =  new google.maps.Geocoder();
                                                geocoder.geocode( { 'address': location_data}, function(results, status) {
                                                    if (status == google.maps.GeocoderStatus.OK) {
                                                        n_latitude = results[0].geometry.location.lat();
                                                        n_longitude = results[0].geometry.location.lng();
                                                    } else {
                                                        alert(\"". esc_html(__('Something went wrong','wp-job-portal')).":\"+status);
                                                    }
                                                });
                                                setTimeout(function(){ // timout is required to make sure that lat lang has value.
                                                    jQuery.post(ajaxurl, {action: 'wpjobportal_ajax', wpjobportalme: 'city', task: 'savetokeninputcity', citydata: location_data,latitude:n_latitude ,longitude:n_longitude , '_wpnonce':'". esc_attr(wp_create_nonce("save-token-input-city"))."'}, function(data){
                                                        if (data){
                                                            try {
                                                                var value = jQuery.parseJSON(data);
                                                                jQuery('#city').tokenInput('remove', item);
                                                                jQuery('#city').tokenInput('add', {id: value.id, name: value.name,latitude:value.latitude, longitude:value.longitude});
                                                            }
                                                            catch (err) {
                                                                jQuery('#city').tokenInput('remove', item);
                                                                alert(data);
                                                            }
                                                        }
                                                    });
                                                },1500);
                                            }
                                    },
                                    onDelete: function(item){ ";
                                        if($wpjobportal_mappingservice != "osm"){
                                            $wpjobportal_inline_js_script .= "identifyMarkerForDelete(item);// delete marker associted with this token input value.";
                                        }
                                    $wpjobportal_inline_js_script .= "
                                        }";
                                }
                                $wpjobportal_inline_js_script .= "
                            });
                        } else {
                            jQuery('#city').tokenInput(cityArray, {
                                theme: 'wpjobportal',
                                preventDuplicates: true,
                                hintText: \"". esc_html(__("Type In A Search Term", 'wp-job-portal'))."\",
                                noResultsText: \"". esc_html(__("No Results", 'wp-job-portal'))."\",
                                searchingText: \"". esc_html(__("Searching", 'wp-job-portal'))."\",
                                // tokenLimit: 1,";
                                $wpjobportal_newtyped_cities = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('newtyped_cities');
                                if ($wpjobportal_newtyped_cities == 1) {
                                    $wpjobportal_inline_js_script .= "
                                    onResult: function(item) {
                                        if (jQuery.isEmptyObject(item)){
                                            return [{id:0, name: jQuery('tester').text()}];
                                        } else {
                                            //add the item at the top of the dropdown
                                            item.unshift({id:0, name: jQuery('tester').text()});
                                            return item;
                                        }
                                    },
                                    onAdd: function(item) {
                                        if (item.id > 0){
                                            ";
                                                if($wpjobportal_mapfield):
                                                    if($wpjobportal_mapfield->published == 1 && $wpjobportal_mappingservice != "osm"){
                                                        $wpjobportal_inline_js_script .= "addMarkerOnMap(item);";
                                                    }
                                                endif;
                                                $wpjobportal_inline_js_script .= "
                                            return;
                                        }
                                        if (item.name.search(',') == - 1) {
                                            var input = jQuery('tester').text();
                                            alert (\"". esc_html(__("Location Format Is Not Correct Please Enter City In This Format City Name Country Name Or City Name State Name Country Name", 'wp-job-portal'))."\");
                                            jQuery('#city').tokenInput('remove', item);
                                            return false;
                                        } else{
                                            var ajaxurl = \"". esc_url_raw(admin_url('admin-ajax.php')) ."\";
                                            var location_data =  jQuery('tester').text();
                                                //alert(new_loction_lat);
                                                var n_latitude;
                                                var n_longitude;
                                                var geocoder =  new google.maps.Geocoder();
                                                geocoder.geocode( { 'address': location_data}, function(results, status) {
                                                    if (status == google.maps.GeocoderStatus.OK) {
                                                        n_latitude = results[0].geometry.location.lat();
                                                        n_longitude = results[0].geometry.location.lng();
                                                    } else {
                                                        alert(\"". esc_html(__('Something went wrong','wp-job-portal')).":\"+status);
                                                    }
                                                });
                                                setTimeout(function(){ // timout is required to make sure that lat lang has value.
                                                    jQuery.post(ajaxurl, {action: 'wpjobportal_ajax', wpjobportalme: 'city', task: 'savetokeninputcity', citydata: location_data,latitude:n_latitude ,longitude:n_longitude , '_wpnonce':'". esc_attr(wp_create_nonce("save-token-input-city"))."'}, function(data){
                                                        if (data){
                                                            try {
                                                                var value = jQuery.parseJSON(data);
                                                                jQuery('#city').tokenInput('remove', item);
                                                                jQuery('#city').tokenInput('add', {id: value.id, name: value.name,latitude:value.latitude, longitude:value.longitude});
                                                            }
                                                            catch (err) {
                                                                jQuery('#city').tokenInput('remove', item);
                                                                alert(data);
                                                            }
                                                        }
                                                    });
                                                },1000);
                                        }
                                    },
                                    onDelete: function(item){
                                        identifyMarkerForDelete(item);// delete marker associted with this token input value.
                                    }";
                                }
                                $wpjobportal_inline_js_script .= "
                            });
                        }
                    }
                    ";
            wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
        
        ?>
 
    </div>
</div>
