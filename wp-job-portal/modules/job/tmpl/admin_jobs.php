<?php
    if (!defined('ABSPATH'))
        die('Restricted Access');
    wp_enqueue_script('jquery-ui-datepicker');
    $wpjobportal_protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    
    wp_enqueue_style('wp-jquery-ui-dialog');
    wp_enqueue_style('jquery-ui-css', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/jquery-ui-smoothness.css');

    $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
    if ($wpjobportal_dateformat == 'm/d/Y' || $wpjobportal_dateformat == 'd/m/y' || $wpjobportal_dateformat == 'm/d/y' || $wpjobportal_dateformat == 'd/m/Y') {
        $wpjobportal_dash = '/';
    } else {
        $wpjobportal_dash = '-';
    }
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

    wp_register_script( 'wpjobportal-inline-handle', '' );
    wp_enqueue_script( 'wpjobportal-inline-handle' );

    $wpjobportal_inline_js_script = "
        function makeExpiry() {
            //start Approval queue jquery
            jQuery('.goldnew').hover(function () {
                jQuery(this).find('.goldnew-onhover').show();
            }, function () {
                jQuery(this).find('span.goldnew-onhover').fadeOut('slow');
            });
            jQuery('.featurednew').hover(function () {
                jQuery(this).find('span.featurednew-onhover').show();
            }, function () {
                jQuery(this).find('.featurednew-onhover').fadeOut('slow');
            });
            //end approval queue jquery
        }

        function selectPackage(packageid){
            jQuery('#package-div-'+packageid).addClass('pkg-selected');
            jQuery('#wpjobportal_packageid').val(packageid);
            jQuery('#upakid').val(packageid);
            jQuery('#pkg-disabled-btn').removeAttr('disabled');
            jQuery('.pkg-item').removeClass('pkg-selected');
            jQuery('#package-div-'+packageid).addClass('pkg-selected');
            jQuery('.proceed-without-paying').removeClass('disabled-btn');
            if (jQuery('#package-div-'+packageid).hasClass('pkg-selected')) {
                jQuery('.proceed-without-paying').addClass('disabled-btn');
            }
        }

        jQuery(document).ready(function () {
            jQuery('div#full_background,img#popup_cross').click(function () {
                closePopup();
            });
            makeExpiry();
            jQuery('.custom_date').datepicker({dateFormat: '". esc_js($wpjobportal_js_scriptdateformat)."'});
            jQuery('div.wpjobportal-jobs-list').each(function () {
                jQuery('div#' + this.id).hover(function () {
                    jQuery('div#' + this.id + ' div span.selector').show();
                }, function () {
                    if (jQuery('div#' + this.id + ' div span.selector input:checked').length > 0) {
                        jQuery('div#' + this.id + ' div span.selector').show();
                    } else {
                        jQuery('div#' + this.id + ' div span.selector').hide();
                    }
                });
            });
            jQuery('span#showhidefilter').click(function (e) {
                e.preventDefault();
                var img2 = '". esc_url(WPJOBPORTAL_PLUGIN_URL) . "includes/images/filter-up.png';
                var img1 = '". esc_url(WPJOBPORTAL_PLUGIN_URL) . "includes/images/filter-down.png';
                if (jQuery('.default-hidden').is(':visible')) {
                    jQuery(this).find('img').attr('src', img1);
                } else {
                    jQuery(this).find('img').attr('src', img2);
                }
                jQuery('.default-hidden').toggle();
                var height = jQuery(this).height();
                var imgheight = jQuery(this).find('img').height();
                var currenttop = (height - imgheight) / 2;
                jQuery(this).find('img').css('top', currenttop);
            });
        });

        function highlight(id) {
            if (jQuery('div#job_' + id + ' div span input:checked').length > 0) {
                showBorder(id);
            } else {
                hideBorder(id);
            }
        }
        function showBorder(id) {
            jQuery('div#job_' + id).addClass('blue');
        }
        function hideBorder(id) {
            jQuery('div#job_' + id).removeClass('blue');
        }
        function highlightAll() {
            if (jQuery('span.selector input').is(':checked') == false) {
                jQuery('span.selector').css('display', 'none');
                jQuery('div.wpjobportal-jobs-list').removeClass('blue');
            }
            if (jQuery('span.selector input').is(':checked') == true) {
                jQuery('span.selector').css('display', 'block');
                jQuery('div.wpjobportal-jobs-list').addClass('blue');
            }
        }

        function addBadgeToObject(cid, specialtype, expiry) {
            var html = '';
            html = '<span class=\"featurednew wpjobportal-featured-tag-icon-wrp\" data-id=\"' + cid + '\">';
            html += '<span id=\"badge_featured\" class=\"wpjobportal-featured-tag-icon\">". esc_html(__('Featured', 'wp-job-portal'))."<i class=\"fa fa-star\"></i></span>';
            html += '<span class=\"featurednew-onhover wpjobportal-featured-hover-wrp\" id=\"gold' + cid + '\" style=\"display: none;\">';
            html += \"". esc_html(__('Expiry Date', 'wp-job-portal'))." : \" + expiry;
            html += '</span>';
            html += '</span>';
            jQuery('div#job_' + cid).find('div#item-data div.wpjobportal-jobs-list-top-wrp').append(html);
            changeButton(cid,specialtype);
        }

        function showHideThings(listid, specialtype, status) {
            if (specialtype == 'featured') {
                if (status == 1) {
                    jQuery('div#for_ajax_only_' + listid + ' ' + 'a#js_feature').show();
                    jQuery('div#for_ajax_only_' + listid + ' ' + 'a#js_feature_not').hide();
                    jQuery('#job_' + listid + ' ' + '#badge_featured').show();
                } else {
                    jQuery('div#for_ajax_only_' + listid + ' ' + 'a#js_feature').hide();
                    jQuery('div#for_ajax_only_' + listid + ' ' + 'a#js_feature_not').show();
                    jQuery('#job_' + listid + ' ' + '#badge_featured').hide();
                }
            }
        }

        function copyJob(jobsid) {
            var ajaxurl = \"". esc_url_raw(admin_url('admin-ajax.php')) ."\";
            if (jobsid) {
                jQuery('#js_ajax_pleasewait').show();
            }
            jQuery.post(ajaxurl, {action: 'wpjobportal_ajax', wpjobportalme: 'job', task: 'makeJobCopyAjax', jobid: jobsid, '_wpnonce':'". esc_attr(wp_create_nonce("make-job-copy-ajax"))."'}, function (data) {
                if (data) {
                    jQuery('#js_ajax_pleasewait').hide();
                    if (data == 'copied') {
                        jQuery('p#js_jobcopid').slideDown();
                        setTimeout(function () {
                            location.reload();
                        }, 700);
                    }
                }
            });
        }

        function resetFrom() {
            document.getElementById('location').value = '';
            document.getElementById('searchtitle').value = '';
            document.getElementById('searchcompany').value = '';
            document.getElementById('searchjobcategory').value = '';
            document.getElementById('searchjobtype').value = '';
            document.getElementById('status').value = '';
            document.getElementById('datestart').value = '';
            document.getElementById('dateend').value = '';
            //jQuery('#gold1').prop('checked', false);
            jQuery('#featured1').prop('checked', false);
            document.getElementById('wpjobportalform').submit();
        }

        function changeButton(cid, specialtype) {
            var non = jQuery('#featuredwpnonce').val();
            var html = '<a href=\"admin.php?page=wpjobportal_featuredjob&task=removefeaturedjob&action=wpjobportaltask&wpjobportal-cb[]=' + cid + '&_wpnonce='+non+'\" class=\"wpjobportal-jobs-act-btn\" title=\"". esc_html(__('remove featured', 'wp-job-portal'))."\">". esc_html(__('Remove Featured', 'wp-job-portal'))."</a>';
            jQuery('a.' + specialtype + '_' + cid).replaceWith(html);
        }
    ";
    wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
?>

<?php
    $wpjobportal_categoryarray = array(
        (object) array('id' => 1, 'text' => esc_html(__('Job Title', 'wp-job-portal'))),
        (object) array('id' => 2, 'text' => esc_html(__('Company Name', 'wp-job-portal'))),
        (object) array('id' => 3, 'text' => esc_html(__('Category', 'wp-job-portal'))),
        (object) array('id' => 5, 'text' => esc_html(__('Location', 'wp-job-portal'))),
        (object) array('id' => 7, 'text' => esc_html(__('Status', 'wp-job-portal'))),
        (object) array('id' => 4, 'text' => esc_html(__('Job Type', 'wp-job-portal'))),
        (object) array('id' => 6, 'text' => esc_html(__('Created', 'wp-job-portal')))
    );
    WPJOBPORTALincluder::getTemplate('templates/admin/header', array('wpjobportal_module' => 'job'));
    $wpjobportal_jobs = isset(wpjobportal::$_data[0][0]) ? wpjobportal::$_data[0][0] :'';
?>
<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper">
    <!-- left menu -->
    <div id="wpjobportaladmin-leftmenu">
        <?php  WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
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
                        <li><?php echo esc_html(__('Jobs','wp-job-portal')); ?></li>
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
        <?php
            WPJOBPORTALincluder::getTemplate('templates/admin/pagetitle', array('wpjobportal_module' => 'job','wpjobportal_layouts' => 'joblist'));
        ?>
        <!-- page content -->
        <div id="wpjobportal-admin-wrapper" class="p0 bg-n bs-n">
            <div id="js_ajax_pleasewait" style="display:none;">
                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/spinning-wheel.gif"/>
            </div>
            <p id="js_jobcopid" style="display:none;">
                <?php echo esc_html(__('Job Copied Successfully', 'wp-job-portal')); ?>
            </p>
            <!-- quick actions -->
            <?php
                WPJOBPORTALincluder::getTemplate('job/views/admin/multioperation',array(
                    'wpjobportal_categoryarray' => $wpjobportal_categoryarray,
                    'wpjobportal_job' => $wpjobportal_jobs
                ));
            ?>
            <!-- filter form -->
            <form class="wpjobportal-filter-form" name="wpjobportalform" id="wpjobportalform" method="post" action="<?php echo esc_url_raw(admin_url("admin.php?page=wpjobportal_job")); ?>">
                <?php WPJOBPORTALincluder::getTemplate('job/views/admin/filter',array('wpjobportal_layout' => 'jobfilter'));?>
            </form>
            <?php
                if (!empty(wpjobportal::$_data[0])) {
                    ?>
                    <form id="wpjobportal-list-form" method="post" action="<?php echo esc_url_raw(admin_url("admin.php?page=wpjobportal_job")); ?>">
                        <?php
                            $wpjobportal_data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
                            foreach (wpjobportal::$_data[0] AS $wpjobportal_job) {
                                //View's For Main Div
                                WPJOBPORTALincluder::getTemplate('job/views/admin/joblist',array(
                                    'wpjobportal_job' => $wpjobportal_job,
                                    'wpjobportal_layout' => 'control',
                                    'wpjobportal_logo' => 'logo'
                                ));
                            }
                        ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('action', 'job_remove'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('task', ''),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('form_request', 'wpjobportal'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('callfrom', 1),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('_wpnonce', esc_html(wp_create_nonce('wpjobportal_job_nonce'))),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('featuredwpnonce', esc_html(wp_create_nonce('delete-featured-job'))),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('upakid', ''),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('package', 'featuredjob'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                    </form>
                    <?php
                    if (wpjobportal::$_data[1]) {
                        if(!WPJOBPORTALincluder::getTemplate('templates/admin/pagination',array('wpjobportal_module' => 'job','pagination' => wpjobportal::$_data[1]))){
                            return;
                        }
                    }
                } else {
                    $wpjobportal_msg = esc_html(__('No record found','wp-job-portal'));
                    $wpjobportal_link[] = array(
                                'link' => 'admin.php?page=wpjobportal_job&wpjobportallt=formjob',
                                'text' => esc_html(__('Add New','wp-job-portal')) .' '. esc_html(__('Job','wp-job-portal'))
                            );
                    WPJOBPORTALlayout::getNoRecordFound($wpjobportal_msg,$wpjobportal_link);
                }
            ?>
        </div>
    </div>
</div>
