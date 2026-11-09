<?php
    if (!defined('ABSPATH')) die('Restricted Access');
    wp_enqueue_script('jquery-ui-datepicker');
    $wpjobportal_company = isset(wpjobportal::$_data[0]) ? wpjobportal::$_data[0] : null;
    $wpjobportal_fields = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldsOrderingforForm(WPJOBPORTAL_COMPANY);
    if(!WPJOBPORTALincluder::getTemplate('templates/admin/header',array('wpjobportal_module' => 'company'))) {
        return;
    }
    wp_enqueue_style('jquery-ui-css', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/jquery-ui-smoothness.css', array(), wpjobportal_asset_version('includes/css/jquery-ui-smoothness.css'));
?>
<!-- main wrapper (design 4: fields in the Forms & fields order, approval and saving in a side panel) -->
<?php
$wpjobportal_isedit = $wpjobportal_company && isset($wpjobportal_company->id) && (int) $wpjobportal_company->id > 0;
$wpjobportal_formfields = (array) WPJOBPORTALincluder::getTemplate('company/form-fields', array(
    'wpjobportal_company' => $wpjobportal_company,
    'fields' => $wpjobportal_fields
));
$wpjobportal_foldable = array('facebook_link', 'youtube_link', 'twiter_link', 'linkedin_link');
WPJOBPORTAL_Admin_Form_UI::scripts('company_form');
$wpjobportal_token = WPJOBPORTALincluder::getJSModel('common')->getUniqueIdForTransient();
$wpjobportal_backlink = get_transient('current_user_token_company_'.$wpjobportal_token) ? admin_url('admin.php?page=wpjobportal_company&wpjobportal_restore_results='.$wpjobportal_token) : admin_url('admin.php?page=wpjobportal_company');
$wpjobportal_show_status = isset($wpjobportal_fields['status']) && isset($wpjobportal_fields['status']->published) && $wpjobportal_fields['status']->published == 1;
?>
<div id="wpjobportaladmin-wrapper" class="wjpl-page">
    <!-- left menu -->
    <div id="wpjobportaladmin-leftmenu">
        <?php WPJOBPORTALincluder::getTemplate('templates/admin/leftmenue',array('wpjobportal_module' => 'company')); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <div class="wjpl wjpl-formpage">
            <?php
            WPJOBPORTAL_Admin_List_UI::top_bar(array(
                array('label' => __('Companies', 'wp-job-portal'), 'url' => $wpjobportal_backlink),
                array('label' => $wpjobportal_isedit ? __('Edit company', 'wp-job-portal') : __('Add New Company', 'wp-job-portal')),
            ));
            $wpjobportal_more = array();
            if ($wpjobportal_isedit) {
                $wpjobportal_more[__('View on site', 'wp-job-portal')] = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme' => 'company', 'wpjobportallt' => 'viewcompany', 'wpjobportalid' => (int) $wpjobportal_company->id));
                $wpjobportal_more[__('Jobs of this company', 'wp-job-portal')] = admin_url('admin.php?page=wpjobportal_job&company=' . (int) $wpjobportal_company->id);
            }
            WPJOBPORTAL_Admin_List_UI::title_row(
                $wpjobportal_isedit ? __('Edit company', 'wp-job-portal') : __('Add New Company', 'wp-job-portal'),
                $wpjobportal_isedit ? (string) $wpjobportal_company->name : __('Fields marked * are required.', 'wp-job-portal'),
                $wpjobportal_more,
                array()
            );
            if ($wpjobportal_isedit) :
                $wpjobportal_approval = array(1 => array('ok', __('Approved', 'wp-job-portal')), 0 => array('wait', __('Waiting for approval', 'wp-job-portal')), -1 => array('bad', __('Rejected', 'wp-job-portal')), 3 => array('wait', __('Pending payment', 'wp-job-portal')));
                $wpjobportal_a = isset($wpjobportal_approval[(int) $wpjobportal_company->status]) ? $wpjobportal_approval[(int) $wpjobportal_company->status] : null;
                $wpjobportal_jobs = (int) wpjobportal::$_db->get_var('SELECT COUNT(id) FROM `' . wpjobportal::$_db->prefix . 'wj_portal_jobs` WHERE companyid = ' . (int) $wpjobportal_company->id);
                $wpjobportal_owner = (int) $wpjobportal_company->uid ? (string) wpjobportal::$_db->get_var('SELECT CONCAT_WS(" ", first_name, last_name) FROM `' . wpjobportal::$_db->prefix . 'wj_portal_users` WHERE id = ' . (int) $wpjobportal_company->uid) : '';
                ?>
                <div class="wjpl-formmeta">
                    <?php if ($wpjobportal_a) : ?><span class="wjpl-badge wjpl-b-<?php echo esc_attr($wpjobportal_a[0]); ?>"><?php echo esc_html($wpjobportal_a[1]); ?></span><?php endif; ?>
                    <?php if (trim($wpjobportal_owner) !== '') : ?><span><?php echo esc_html(sprintf(/* translators: %s: person */ __('Owner: %s', 'wp-job-portal'), $wpjobportal_owner)); ?></span><?php endif; ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_job&company=' . (int) $wpjobportal_company->id)); ?>"><?php echo esc_html(sprintf(/* translators: %s: number of jobs */ _n('%s job', '%s jobs', $wpjobportal_jobs, 'wp-job-portal'), number_format_i18n($wpjobportal_jobs))); ?></a>
                </div>
            <?php endif; ?>
            <form id="company_form" class="wpjobportal-form wpjp-ds-form wjpl-form" method="post" enctype="multipart/form-data" action="<?php echo esc_url_raw(admin_url("admin.php?page=wpjobportal_company&task=savecompany")); ?>">
                <div class="wjpl-form-main">
                    <section class="wjpl-card wjpl-fcard" aria-labelledby="wjpl-fs-company">
                        <h2 id="wjpl-fs-company"><?php echo WPJOBPORTAL_Admin_Form_UI::icon('<path d="M4 21V5l8-3v19M12 9h8v12M7 8h2M7 12h2M7 16h2M15 13h2M15 17h2"/>'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed SVG icon ?><?php echo esc_html(__('Company details', 'wp-job-portal')); ?></h2>
                        <div class="wjpl-fgrid">
                            <?php
                            // The owner is chosen when the company is added (as in 2.7, first).
                            echo wp_kses(WPJOBPORTALformfield::hidden('uid', $wpjobportal_company ? $wpjobportal_company->uid : null),WPJOBPORTAL_ALLOWED_TAGS);
                            if (!$wpjobportal_company && $wpjobportal_fields['uid']->published == 1) {
                                wpjobportal::$wpjobportal_data['admin_form_company'] = 1;
                                WPJOBPORTAL_Admin_Form_UI::field(array(
                                    'wpjobportal_field' => $wpjobportal_fields['uid'],
                                    'title' => __('Owner', 'wp-job-portal'),
                                    'wpjobportal_content' => WPJOBPORTALincluder::getTemplateHtml('templates/admin/user-selection')
                                ));
                            }
                            foreach ($wpjobportal_formfields as $wpjobportal_formfield) {
                                WPJOBPORTAL_Admin_Form_UI::field($wpjobportal_formfield, $wpjobportal_company, $wpjobportal_foldable);
                            }
                            ?>
                        </div>
                    </section>
                </div>
                <aside class="wjpl-form-side">
                    <section class="wjpl-card wjpl-fcard wjpl-publish" aria-labelledby="wjpl-fs-publishing">
                        <h2 id="wjpl-fs-publishing"><?php echo WPJOBPORTAL_Admin_Form_UI::icon('<circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/>'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed SVG icon ?><?php echo esc_html(__('Publishing', 'wp-job-portal')); ?></h2>
                        <?php if ($wpjobportal_show_status) : ?>
                            <div class="wjpl-fgrid">
                                <?php
                                $wpjobportal_status = array(
                                    (object) array('id' => 0, 'text' => esc_html(__('Waiting for approval', 'wp-job-portal'))),
                                    (object) array('id' => 1, 'text' => esc_html(__('Approved', 'wp-job-portal'))),
                                    (object) array('id' => -1, 'text' => esc_html(__('Rejected', 'wp-job-portal'))),
                                    (object) array('id' => 3, 'text' => esc_html(__('Pending payment', 'wp-job-portal')))
                                );
                                WPJOBPORTAL_Admin_Form_UI::field(array(
                                    'wpjobportal_field' => $wpjobportal_fields['status'],
                                    'title' => __('Approval status', 'wp-job-portal'),
                                    'wpjobportal_description' => __('Only approved companies show on the site.', 'wp-job-portal'),
                                    'wpjobportal_content' => WPJOBPORTALformfield::select('status', $wpjobportal_status, $wpjobportal_company ? $wpjobportal_company->status : 1, esc_html(__('Select Status', 'wp-job-portal')), array('class' => 'inputbox one wpjobportal-form-select-field'))
                                ));
                                ?>
                            </div>
                        <?php endif; ?>
                        <div class="wpjobportal-form-button wjpl-form-acts">
                            <?php
                            if (isset(wpjobportal::$_data[0]->id) || (!WPJOBPORTAL_Monetisation::usable('credits')) ) {
                                echo wp_kses(WPJOBPORTALformfield::submitbutton('save', esc_html(__('Save','wp-job-portal')) .' '. esc_html(__('Company', 'wp-job-portal')), array('class' => 'button wpjobportal-form-save-btn')),WPJOBPORTAL_ALLOWED_TAGS);// validate_url() was only checked in edit case
                            }else{
                                // handle the case of package not defined for employer role
                                $wpjobportal_subType = wpjobportal::$_config->getConfigValue('submission_type');
                                if($wpjobportal_subType == 3){
                                    $wpjobportal_no_package_needed = 0;
                                    $wpjobportal_result = WPJOBPORTALincluder::getJSModel('credits')->checkIfPackageDefinedForRole(1); // 1 is for employer
                                    if($wpjobportal_result == 0){ // 0 means no package found. so allow the action.
                                        echo wp_kses(WPJOBPORTALformfield::submitbutton('save', esc_html(__('Save','wp-job-portal')) .' '. esc_html(__('Company', 'wp-job-portal')), array('class' => 'button wpjobportal-form-save-btn')),WPJOBPORTAL_ALLOWED_TAGS);// validate_url() was only checked in edit case
                                    }else{
                                        echo wp_kses(WPJOBPORTALformfield::button('save', esc_html(__('Save','wp-job-portal')) .' '. esc_html(__('Company', 'wp-job-portal')), array('class' => 'button wpjobportal-form-save-btn', 'credit_userid' => '', 'onclick' => "wpjobportalformpopupAdmin('add_company','company_form');")),WPJOBPORTAL_ALLOWED_TAGS);
                                    }
                                }else{
                                    echo wp_kses(WPJOBPORTALformfield::button('save', esc_html(__('Save','wp-job-portal')) .' '. esc_html(__('Company', 'wp-job-portal')), array('class' => 'button wpjobportal-form-save-btn', 'credit_userid' => '', 'onclick' => "wpjobportalformpopupAdmin('add_company','company_form');")),WPJOBPORTAL_ALLOWED_TAGS);
                                }
                            }
                            ?>
                            <a id="form-cancel-button" class="wpjobportal-form-cancel-btn" href="<?php echo esc_url($wpjobportal_backlink); ?>" title="<?php echo esc_attr(__('cancel', 'wp-job-portal')); ?>">
                                <?php echo esc_html(__('Cancel', 'wp-job-portal')); ?>
                            </a>
                        </div>
                    </section>
                </aside>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('payment', ''),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('id', $wpjobportal_company ? esc_html($wpjobportal_company->id) : ''),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('form_request', 'wpjobportal'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('package', 'companies'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('upakid', ''),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('startfeatureddate', isset(wpjobportal::$_data[0]->startfeatureddate) ? esc_html(wpjobportal::$_data[0]->startfeatureddate) : ''),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('serverstatus', isset(wpjobportal::$_data[0]->serverstatus) ? esc_html(wpjobportal::$_data[0]->serverstatus) : ''),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('serverid', isset(wpjobportal::$_data[0]->serverid) ? esc_html(wpjobportal::$_data[0]->serverid) : ''),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('user-popup-title-text', esc_html(__('Select','wp-job-portal')) .' '. esc_html(__('User', 'wp-job-portal'))),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('isadmin', '1'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('_wpnonce', esc_html(wp_create_nonce('wpjobportal_company_nonce'))),WPJOBPORTAL_ALLOWED_TAGS); ?>
            </form>
        </div>
    </div>
</div>

<?php
    wp_register_script( 'wpjobportal-inline-handle', '', array(), WPJOBPORTAL_VERSION, false );
    wp_enqueue_script( 'wpjobportal-inline-handle' );

    $wpjobportal_inline_js_script = "
        jQuery(document).ready(function ($) {
            //Date Format
            $('.custom_date').datepicker({dateFormat: '". wpjobportal::$_common->getCalendarDateFormat()."'});
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
        });

        function checkUrl(obj) {
            if (!obj.value.match(/^http[s]?\:\/\//))
                obj.value = 'http://' + obj.value;
        }

        jQuery('body').on('click', 'img#wjportal-form-delete-image', function(e){
            jQuery('.wjportal-form-image-wrp').hide();
            jQuery('input#photo').val('').clone(true);
            jQuery('span.wjportal-form-upload-btn-wrp-txt').text('');
            var id =  jQuery('input[name=id]').val();
            removeLogo(id);
        });

        function validate_url() {
            var value = jQuery('#url').val();
            if (typeof value != 'undefined') {
                if (value != '') {
                    if (value.match(/^(http|https|ftp)\:\/\/\w+([\.\-]\w+)*\.\w{2,4}(\:\d+)*([\/\.\-\?\&\%\#]\w+)*\/?$/i) ||
                            value.match(/^mailto\:\w+([\.\-]\w+)*\@\w+([\.\-]\w+)*\.\w{2,4}$/i))
                    {
                        return true;
                    }
                    else {
                        jQuery('#url').addClass('invalid');
                        alert(\"". esc_html(__("Enter Correct Company Site", 'wp-job-portal'))."\");
                        return false;
                    }
                }
            }
            return true;
        }

        function selectPackage(packageid){
            jQuery('.package-div').css('border','1px solid #ccc');
            jQuery('#package-div-'+packageid).addClass('pkg-selected');
            jQuery('#wpjobportal_packageid').val(packageid);
            jQuery('#upakid').val(packageid);
            jQuery('.pkg-item').removeClass('pkg-selected');
            jQuery('#package-div-'+packageid).addClass('pkg-selected');
            jQuery('#pkg-disabled-btn').removeAttr('disabled');
            jQuery('.proceed-without-paying').removeClass('disabled-btn');
            if (jQuery('#package-div-'+packageid).hasClass('pkg-selected')) {
                jQuery('.proceed-without-paying').addClass('disabled-btn');
            }
        }

        function removeLogo(id) {
            var ajaxurl = \"". esc_url_raw(admin_url('admin-ajax.php')) ."\";
            jQuery.post(ajaxurl, {action: 'wpjobportal_ajax', wpjobportalme: 'company', task: 'deletecompanylogo', companyid: id, '_wpnonce':'". esc_attr(wp_create_nonce("delete-company-logo"))."'}, function (data) {
                if (data) {
                    jQuery('img#comp_logo').css('display', 'none');
                    jQuery('form#company_form').append('<input type=\"hidden\" name=\"company_logo_deleted\" value=\"1\"/>');
                } else {
                    jQuery('div.logo-container').append(\"<span style='color:Red;'>". esc_html(__('Error Deleting Logo', 'wp-job-portal'))."\");
                }
            });
        }

            jQuery('body').on('click', '#logo', function(e){
                jQuery('input#logo').change(function(){
                    var srcimage = jQuery('img.rs_photo');
                    readURL(this, srcimage);
                });
            });

        function readURL(input, srcimage) {
            if (input.files && input.files[0]) {
                var fileext = input.files[0].name.split('.').pop().trim().toLowerCase();
                var filesize = (input.files[0].size / 1024);
                var allowedsize = ". WPJOBPORTALincluder::getJSModel('common')->configAsJsNumber('company_logofilezize', 2048).";
                var allowedExt = '". wpjobportal::$_config->getConfigurationByConfigName('image_file_type')."';
                allowedExt = allowedExt.split(',').map(function(ext) {
                    return ext.trim().toLowerCase().replace(/^\.+/, '');
                }).filter(Boolean);
                if (jQuery.inArray(fileext, allowedExt) != - 1){
                    if (allowedsize > filesize){
                        //New Library
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            jQuery('#rs_photo').attr('src', e.target.result);
                            jQuery('.wjportal-form-image-wrp').show();
                            jQuery('.wjportal-form-upload-btn-wrp-txt').html(input.files[0].name);
                            jQuery('img#wjportal-form-delete-image').on('click',function(){
                                jQuery('.wjportal-form-image-wrp').hide();
                                jQuery('input#logo').val('').clone(true);
                                jQuery('span.wjportal-form-upload-btn-wrp-txt').text('');
                            });
                       }
                        reader.readAsDataURL(input.files[0]);
                    } else{
                        jQuery('input#logo').replaceWith(jQuery('input#logo').val('').clone(true));
                        alert(\"". esc_html(__("File size is greater then allowed file size", 'wp-job-portal'))."\");
                    }
                } else{
                    jQuery('input#logo').replaceWith(jQuery('input#logo').val('').clone(true));
                    alert('". esc_html(__("This file type is not allowed", 'wp-job-portal'))."');
                }
            }
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
                    noResultsText: \"".esc_html(__("No Results", 'wp-job-portal'))."\",
                    searchingText: \"". esc_html(__("Searching", 'wp-job-portal'))."\",
                    // tokenLimit: 1,
                    prePopulate: multicities,";
                    $wpjobportal_newtyped_cities = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('newtyped_cities');
                    if ($wpjobportal_newtyped_cities == 1) {
                        $wpjobportal_inline_js_script .= "
                        onResult: function (item) {
                            if (jQuery.isEmptyObject(item)) {
                                return [{id: 0, name: jQuery('tester').text()}];
                            } else {
                                //add the item at the top of the dropdown
                                item.unshift({id: 0, name: jQuery('tester').text()});
                                return item;
                            }
                        },
                        onAdd: function (item) {
                            if (item.id > 0) {
                                return;
                            }
                            console.log('test');
                            if (item.name.search(',') == -1) {
                                var input = jQuery('tester').text();
                                alert(\"". esc_html(__("Location Format Is Not Correct Please Enter City In This Format City Name Country Name Or City Name State Name Country Name", 'wp-job-portal'))."\");
                                jQuery('#city').tokenInput('remove', item);
                                return false;
                            } else {
                                var ajaxurl = \"". esc_url_raw(admin_url('admin-ajax.php')) ."\";
                                jQuery.post(ajaxurl, {action: 'wpjobportal_ajax', wpjobportalme: 'city', task: 'savetokeninputcity', citydata: jQuery('tester').text(), '_wpnonce':'". esc_attr(wp_create_nonce("save-token-input-city"))."'}, function (data) {
                                    if (data) {
                                        try {
                                            var value = jQuery.parseJSON(data);
                                            jQuery('#city').tokenInput('remove', item);
                                            jQuery('#city').tokenInput('add', {id: value.id, name: value.name});
                                        }
                                        catch (err) {
                                            jQuery('#city').tokenInput('remove', item);
                                            alert(data);
                                        }
                                    }
                                });
                            }
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
                        onResult: function (item) {
                            if (jQuery.isEmptyObject(item)) {
                                return [{id: 0, name: jQuery('tester').text()}];
                            } else {
                                //add the item at the top of the dropdown
                                item.unshift({id: 0, name: jQuery('tester').text()});
                                return item;
                            }
                        },
                        onAdd: function (item) {
                            if (item.id > 0) {
                                return;
                            }
                            if (item.name.search(',') == -1) {
                                var input = jQuery('tester').text();
                                alert(\"". esc_html(__("Location Format Is Not Correct Please Enter City In This Format City Name Country Name Or City Name State Name Country Name", 'wp-job-portal'))."\");
                                jQuery('#city').tokenInput('remove', item);
                                return false;
                            } else {
                                var ajaxurl = \"". esc_url_raw(admin_url('admin-ajax.php')) ."\";
                                jQuery.post(ajaxurl, {action: 'wpjobportal_ajax', wpjobportalme: 'city', task: 'savetokeninputcity', citydata: jQuery('tester').text(), '_wpnonce':'". esc_attr(wp_create_nonce("save-token-input-city"))."'}, function (data) {
                                    if (data) {
                                        try {
                                            var value = jQuery.parseJSON(data);
                                            jQuery('#city').tokenInput('remove', item);
                                            jQuery('#city').tokenInput('add', {id: value.id, name: value.name});
                                        }
                                        catch (err) {
                                            jQuery('#city').tokenInput('remove', item);
                                            alert(data);
                                        }
                                    }
                                });
                            }
                        }";
                    } 
                    $wpjobportal_inline_js_script .= "
                });
            }
        }
        ";
    wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
?>
