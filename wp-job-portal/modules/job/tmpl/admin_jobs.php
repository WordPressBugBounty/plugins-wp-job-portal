<?php
    if (!defined('ABSPATH'))
        die('Restricted Access');
    wp_enqueue_script('jquery-ui-datepicker');
    $wpjobportal_protocol = is_ssl() ? "https://" : "http://";
    
    wp_enqueue_style('wp-jquery-ui-dialog');
    wp_enqueue_style('jquery-ui-css', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/jquery-ui-smoothness.css', array(), wpjobportal_asset_version('includes/css/jquery-ui-smoothness.css'));

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

    wp_register_script( 'wpjobportal-inline-handle', '', array(), WPJOBPORTAL_VERSION, false );
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
            var html = '<a href=\"admin.php?page=wpjobportal_featuredjob&task=removefeaturedjob&action=wpjobportaltask&wpjobportal-cb[]=' + cid + '&_wpnonce='+non+'\" class=\"wpjobportal-jobs-act-btn\" title=\"". esc_html(__('Remove Featured', 'wp-job-portal'))."\">". esc_html(__('Remove Featured', 'wp-job-portal'))."</a>';
            jQuery('a.' + specialtype + '_' + cid).replaceWith(html);
        }
    ";
    wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
?>
<?php
/*
 * Jobs › All jobs (design 4, the reference list). The script block above is
 * the 2.7 list's, unchanged: the Feature and Copy add-on actions in each row
 * still rely on it. Classic screen: admin_jobs_classic.php.
 */
WPJOBPORTAL_Admin_List_UI::enqueue();
WPJOBPORTALincluder::getTemplate('templates/admin/header', array('wpjobportal_module' => 'job'));

$wpjobportal_list = wpjobportal::$_data['joblist'];
$wpjobportal_f = $wpjobportal_list['filter'];
$wpjobportal_counts = $wpjobportal_list['counts'];
$wpjobportal_opts = $wpjobportal_list['options'];
$wpjobportal_page_slug = 'wpjobportal_job';
$wpjobportal_link = function ($args = array()) use ($wpjobportal_page_slug, $wpjobportal_f) {
    $wpjobportal_current = $wpjobportal_f;
    if ($wpjobportal_current['view'] === 'all') {
        $wpjobportal_current['view'] = '';
    }
    if ($wpjobportal_current['sort'] === 'posted|-1') {
        $wpjobportal_current['sort'] = '';
    }
    return WPJOBPORTAL_Admin_List_UI::url($wpjobportal_page_slug, $wpjobportal_current, $args);
};
$wpjobportal_here = $wpjobportal_link(array('pagenum' => WPJOBPORTALpagination::$_currentpage > 1 ? WPJOBPORTALpagination::$_currentpage : null));
$wpjobportal_has_featured = in_array('featuredjob', wpjobportal::$_active_addons, true);
$wpjobportal_has_enhancer = in_array('joblistingenhancer', wpjobportal::$_active_addons, true);
$wpjobportal_new_days = $wpjobportal_has_enhancer ? (int) wpjobportal::$_config->getConfigValue('job_new_badge_days') : 0;
$wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
$wpjobportal_filters_on = ($wpjobportal_f['q'] !== '' || $wpjobportal_f['cat'] || $wpjobportal_f['type'] || $wpjobportal_f['loc']);
$wpjobportal_more_on = (int) (bool) $wpjobportal_f['company'] + (int) ($wpjobportal_f['stat'] !== '') + (int) ($wpjobportal_f['from'] !== '' || $wpjobportal_f['to'] !== '') + (int) ($wpjobportal_f['featured'] !== '');
$wpjobportal_job_nonce = wp_create_nonce('wpjobportal_job_nonce');
$wpjobportal_views = array(
    'all' => array(__('All', 'wp-job-portal'), ''),
    'open' => array(__('Accepting applicants', 'wp-job-portal'), '#059669'),
    'closed' => array(__('Closed', 'wp-job-portal'), '#94a3b8'),
    'waiting' => array(__('Waiting for approval', 'wp-job-portal'), '#d97706'),
    'expired' => array(__('Expired', 'wp-job-portal'), '#dc2626'),
);
if ($wpjobportal_f['view'] === 'closing') { // W-20: reached from the Dashboard
    $wpjobportal_views['closing'] = array(__('Closing in 7 days', 'wp-job-portal'), '#d97706');
}
$wpjobportal_task_url = function ($task, $args) use ($wpjobportal_job_nonce, $wpjobportal_here) {
    return add_query_arg(array_map('rawurlencode', array_merge(array('page' => 'wpjobportal_job', 'action' => 'wpjobportaltask', 'task' => $task, '_wpnonce' => $wpjobportal_job_nonce, 'returnto' => $wpjobportal_here), $args)), admin_url('admin.php'));
};
?>
<div id="wpjobportaladmin-wrapper" class="wjpl-page">
    <div id="wpjobportaladmin-leftmenu">
        <?php WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <div class="wjpl" data-delete-confirm="<?php echo esc_attr(__('Are you sure to delete', 'wp-job-portal') . ' ?'); ?>">
            <?php WPJOBPORTAL_Admin_List_UI::top_bar(array(array('label' => __('Jobs', 'wp-job-portal')))); ?>
            <?php
            WPJOBPORTAL_Admin_List_UI::title_row(
                __('Jobs', 'wp-job-portal'),
                /* translators: 1: number of jobs, 2: number accepting applicants */
                sprintf(__('%1$s jobs · %2$s open', 'wp-job-portal'), number_format_i18n($wpjobportal_counts['all']), number_format_i18n($wpjobportal_counts['open'])),
                array(
                    __('Job form fields', 'wp-job-portal') => admin_url('admin.php?page=wpjobportal_fieldordering&ff=2'),
                    __('Job search fields', 'wp-job-portal') => admin_url('admin.php?page=wpjobportal_fieldordering&wpjobportallt=searchfields&ff=2'),
                    __('Apply settings', 'wp-job-portal') => admin_url('admin.php?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=job_apply'),
                    __('Approval queue', 'wp-job-portal') => admin_url('admin.php?page=wpjobportal_job&wpjobportallt=jobqueue'),
                    __('Import jobs', 'wp-job-portal') => admin_url('admin.php?page=wpjobportal_thirdpartyimport'),
                ),
                array('label' => __('Add New Job', 'wp-job-portal'), 'url' => admin_url('admin.php?page=wpjobportal_job&wpjobportallt=formjob'))
            );
            ?>
            <div id="js_ajax_pleasewait" style="display:none;"><img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/spinning-wheel.gif" alt=""/></div>
            <p id="js_jobcopid" class="wjpl-flash" style="display:none;"><?php echo esc_html(__('Job Copied Successfully', 'wp-job-portal')); ?></p>

            <section class="wjpl-card" aria-label="<?php echo esc_attr(__('Jobs', 'wp-job-portal')); ?>">
                <form class="wjpl-filters" method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
                    <input type="hidden" name="page" value="wpjobportal_job">
                    <?php if ($wpjobportal_f['view'] !== 'all') : ?><input type="hidden" name="view" value="<?php echo esc_attr($wpjobportal_f['view']); ?>"><?php endif; ?>
                    <div class="wjpl-tb1">
                        <?php WPJOBPORTAL_Admin_List_UI::chips($wpjobportal_views, $wpjobportal_counts, $wpjobportal_f['view'], function ($key) use ($wpjobportal_link) {
                            return $wpjobportal_link(array('view' => $key === 'all' ? null : $key));
                        }); ?>
                        <?php WPJOBPORTAL_Admin_List_UI::sort_select(array(
                            'posted|-1' => __('Newest first', 'wp-job-portal'),
                            'posted|1' => __('Oldest first', 'wp-job-portal'),
                            'exp|1' => __('Expiring soonest', 'wp-job-portal'),
                            'apps|-1' => __('Most applications', 'wp-job-portal'),
                            'title|1' => __('Title A–Z', 'wp-job-portal'),
                            'title|-1' => __('Title Z–A', 'wp-job-portal'),
                            'company|1' => __('Company A–Z', 'wp-job-portal'),
                            'cat|1' => __('Category A–Z', 'wp-job-portal'),
                            'loc|1' => __('Location A–Z', 'wp-job-portal'),
                            'type|1' => __('Job type A–Z', 'wp-job-portal'),
                            'stat|1' => __('Approval status', 'wp-job-portal'),
                        ), $wpjobportal_f['sort']); ?>
                    </div>
                    <div class="wjpl-selbar" role="toolbar" aria-label="<?php echo esc_attr(__('Bulk actions', 'wp-job-portal')); ?>" hidden>
                        <b><span class="wjpl-selcount">0</span> <?php echo esc_html(__('selected', 'wp-job-portal')); ?></b>
                        <button class="wjpl-sb" type="button" data-wjpl-bulk="approve"><?php echo esc_html(__('Approve', 'wp-job-portal')); ?></button>
                        <button class="wjpl-sb" type="button" data-wjpl-bulk="close"><?php echo esc_html(__('Close to new applicants', 'wp-job-portal')); ?></button>
                        <button class="wjpl-sb" type="button" data-wjpl-bulk="reopen"><?php echo esc_html(__('Reopen', 'wp-job-portal')); ?></button>
                        <button class="wjpl-sb wjpl-del" type="button" data-wjpl-bulk="delete"><?php echo esc_html(__('Delete', 'wp-job-portal')); ?></button>
                        <button class="wjpl-sb wjpl-clear" type="button"><?php echo esc_html(__('Clear selection', 'wp-job-portal')); ?></button>
                    </div>
                    <div class="wjpl-tb2">
                        <div class="wjpl-search">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                            <input class="wjpl-inp" type="search" name="q" value="<?php echo esc_attr($wpjobportal_f['q']); ?>" placeholder="<?php echo esc_attr(__('Search title, company, category or location', 'wp-job-portal')); ?>" aria-label="<?php echo esc_attr(__('Search jobs', 'wp-job-portal')); ?>">
                        </div>
                        <select class="wjpl-sel" name="cat" data-wjpl-autosubmit aria-label="<?php echo esc_attr(__('Category', 'wp-job-portal')); ?>">
                            <option value=""><?php echo esc_html(sprintf(/* translators: %s: number of categories */ __('All categories (%s)', 'wp-job-portal'), number_format_i18n(count($wpjobportal_opts['categories'])))); ?></option>
                            <?php foreach ($wpjobportal_opts['categories'] as $wpjobportal_o) : ?>
                                <option value="<?php echo esc_attr($wpjobportal_o->id); ?>"<?php selected((int) $wpjobportal_f['cat'], (int) $wpjobportal_o->id); ?>><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_o->title)); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select class="wjpl-sel" name="type" data-wjpl-autosubmit aria-label="<?php echo esc_attr(__('Job type', 'wp-job-portal')); ?>">
                            <option value=""><?php echo esc_html(__('All job types', 'wp-job-portal')); ?></option>
                            <?php foreach ($wpjobportal_opts['types'] as $wpjobportal_o) : ?>
                                <option value="<?php echo esc_attr($wpjobportal_o->id); ?>"<?php selected((int) $wpjobportal_f['type'], (int) $wpjobportal_o->id); ?>><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_o->title)); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select class="wjpl-sel" name="loc" data-wjpl-autosubmit aria-label="<?php echo esc_attr(__('Location', 'wp-job-portal')); ?>">
                            <option value=""><?php echo esc_html(__('All locations', 'wp-job-portal')); ?></option>
                            <?php foreach ($wpjobportal_opts['locations'] as $wpjobportal_o) : ?>
                                <option value="<?php echo esc_attr($wpjobportal_o->id); ?>"<?php selected((int) $wpjobportal_f['loc'], (int) $wpjobportal_o->id); ?>><?php echo esc_html($wpjobportal_o->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="wjpl-tb2-acts">
                            <button class="wjpl-btn wjpl-btn-pri" type="submit"><?php echo esc_html(__('Search', 'wp-job-portal')); ?></button>
                            <a class="wjpl-linkbtn" href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_job')); ?>"><?php echo esc_html(__('Reset', 'wp-job-portal')); ?></a>
                        </span>
                        <?php if ($wpjobportal_filters_on || $wpjobportal_more_on) : ?>
                            <div class="wjpl-hint"><?php
                                /* translators: 1: jobs shown, 2: all jobs */
                                echo wp_kses(sprintf(__('Showing <b>%1$s</b> of %2$s jobs.', 'wp-job-portal'), number_format_i18n($wpjobportal_list['total']), number_format_i18n($wpjobportal_counts['all'])), array('b' => array()));
                            ?></div>
                        <?php endif; ?>
                    </div>
                    <details class="wjpl-tb3"<?php echo $wpjobportal_more_on ? ' open' : ''; ?>>
                        <summary class="wjpl-linkbtn"><?php echo esc_html(__('More filters', 'wp-job-portal')); ?><?php if ($wpjobportal_more_on) : ?><span class="wjpl-n"><?php echo esc_html($wpjobportal_more_on); ?></span><?php endif; ?></summary>
                        <div class="wjpl-tb3-grid">
                            <label><?php echo esc_html(__('Company', 'wp-job-portal')); ?>
                                <select class="wjpl-sel" name="company" data-wjpl-autosubmit>
                                    <option value=""><?php echo esc_html(__('All companies', 'wp-job-portal')); ?></option>
                                    <?php foreach ($wpjobportal_opts['companies'] as $wpjobportal_o) : ?>
                                        <option value="<?php echo esc_attr($wpjobportal_o->id); ?>"<?php selected((int) $wpjobportal_f['company'], (int) $wpjobportal_o->id); ?>><?php echo esc_html($wpjobportal_o->name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label><?php echo esc_html(__('Approval status', 'wp-job-portal')); ?>
                                <select class="wjpl-sel" name="stat" data-wjpl-autosubmit>
                                    <option value=""><?php echo esc_html(__('Any status', 'wp-job-portal')); ?></option>
                                    <?php foreach (array('1' => __('Approved', 'wp-job-portal'), '0' => __('Waiting for approval', 'wp-job-portal'), '-1' => __('Rejected', 'wp-job-portal'), '3' => __('Pending payment', 'wp-job-portal')) as $wpjobportal_v => $wpjobportal_l) : ?>
                                        <option value="<?php echo esc_attr($wpjobportal_v); ?>"<?php selected($wpjobportal_f['stat'], (string) $wpjobportal_v); ?>><?php echo esc_html($wpjobportal_l); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label><?php echo esc_html(__('Posted between', 'wp-job-portal')); ?>
                                <span class="wjpl-two">
                                    <input class="wjpl-inp wjpl-plain" type="date" name="from" value="<?php echo esc_attr($wpjobportal_f['from']); ?>" aria-label="<?php echo esc_attr(__('Posted from', 'wp-job-portal')); ?>">
                                    <input class="wjpl-inp wjpl-plain" type="date" name="to" value="<?php echo esc_attr($wpjobportal_f['to']); ?>" aria-label="<?php echo esc_attr(__('Posted to', 'wp-job-portal')); ?>">
                                </span>
                            </label>
                            <?php if ($wpjobportal_has_featured) : ?>
                                <label class="wjpl-ck"><input class="wjpl-cb" type="checkbox" name="featured" value="1" data-wjpl-autosubmit<?php checked($wpjobportal_f['featured'], '1'); ?>><?php echo esc_html(__('Featured jobs only', 'wp-job-portal')); ?></label>
                            <?php endif; ?>
                        </div>
                    </details>
                </form>

                <form class="wjpl-bulkform" id="wpjobportal-list-form" method="post" action="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_job')); ?>">
                    <input type="hidden" name="form_request" value="wpjobportal">
                    <input type="hidden" name="task" value="bulkjobs">
                    <input type="hidden" name="bulkop" value="">
                    <input type="hidden" name="deleteall" value="0">
                    <input type="hidden" name="returnto" value="<?php echo esc_attr($wpjobportal_here); ?>">
                    <input type="hidden" name="_wpnonce" value="<?php echo esc_attr($wpjobportal_job_nonce); ?>">
                    <?php // Read by the Feature add-on's popup, as on the 2.7 list. ?>
                    <?php echo wp_kses(WPJOBPORTALformfield::hidden('featuredwpnonce', esc_html(wp_create_nonce('delete-featured-job'))), WPJOBPORTAL_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(WPJOBPORTALformfield::hidden('upakid', ''), WPJOBPORTAL_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(WPJOBPORTALformfield::hidden('package', 'featuredjob'), WPJOBPORTAL_ALLOWED_TAGS); ?>
                    <div class="wjpl-tw">
                    <?php if (!empty($wpjobportal_list['rows'])) : ?>
                        <table class="wjpl-table">
                            <thead><tr>
                                <th class="wjpl-c-cb"><input class="wjpl-cb wjpl-all" type="checkbox" aria-label="<?php echo esc_attr(__('Select all jobs', 'wp-job-portal')); ?>"></th>
                                <?php
                                $wpjobportal_th = function ($key, $label, $class = '') use ($wpjobportal_f, $wpjobportal_link) {
                                    $wpjobportal_parts = explode('|', $wpjobportal_f['sort']);
                                    $wpjobportal_on = $wpjobportal_parts[0] === $key;
                                    $wpjobportal_next = ($wpjobportal_on && $wpjobportal_parts[1] === '1') ? '-1' : '1';
                                    echo '<th class="' . esc_attr($class) . '"' . ($wpjobportal_on ? ' aria-sort="' . ($wpjobportal_parts[1] === '1' ? 'ascending' : 'descending') . '"' : '') . '><a href="' . esc_url($wpjobportal_link(array('sort' => $key . '|' . $wpjobportal_next))) . '">' . esc_html($label) . ($wpjobportal_on ? '<span aria-hidden="true">' . ($wpjobportal_parts[1] === '1' ? ' ▲' : ' ▼') . '</span>' : '') . '</a></th>';
                                };
                                $wpjobportal_th('title', __('Job', 'wp-job-portal'));
                                $wpjobportal_th('cat', __('Category · type', 'wp-job-portal'), 'wjpl-col-cat');
                                echo '<th class="wjpl-col-sal">' . esc_html(__('Salary', 'wp-job-portal')) . '</th>';
                                echo '<th>' . esc_html(__('Hiring status', 'wp-job-portal')) . '</th>';
                                $wpjobportal_th('apps', __('Applications', 'wp-job-portal'), 'wjpl-num');
                                $wpjobportal_th('posted', __('Posted', 'wp-job-portal'), 'wjpl-col-posted');
                                $wpjobportal_th('exp', __('Expires', 'wp-job-portal'));
                                ?>
                            </tr></thead>
                            <tbody>
                            <?php foreach ($wpjobportal_list['rows'] as $wpjobportal_job) :
                                $wpjobportal_id = (int) $wpjobportal_job->id;
                                $wpjobportal_closed = in_array((int) $wpjobportal_job->jobstatus, $wpjobportal_list['closed_statuses'], true);
                                $wpjobportal_today = date_i18n('Y-m-d');
                                $wpjobportal_featured_on = (int) $wpjobportal_job->isfeaturedjob === 1 && date_i18n('Y-m-d', strtotime((string) $wpjobportal_job->endfeatureddate)) >= $wpjobportal_today;
                                $wpjobportal_feature_waiting = $wpjobportal_has_featured && (int) $wpjobportal_job->isfeaturedjob === 0;
                                $wpjobportal_not_started = date_i18n('Y-m-d', strtotime((string) $wpjobportal_job->startpublishing)) > $wpjobportal_today;
                                $wpjobportal_edit = admin_url('admin.php?page=wpjobportal_job&wpjobportallt=formjob&wpjobportalid=' . $wpjobportal_id);
                                $wpjobportal_apps_url = admin_url('admin.php?page=wpjobportal_jobapply&wpjobportallt=jobappliedresume&jobid=' . $wpjobportal_id);
                                $wpjobportal_logo = '';
                                if (!empty($wpjobportal_job->logo)) {
                                    $wpjobportal_wpdir = wp_upload_dir();
                                    $wpjobportal_logo = $wpjobportal_wpdir['baseurl'] . '/' . WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory') . '/data/employer/comp_' . (int) $wpjobportal_job->companyid . '/logo/' . $wpjobportal_job->logo;
                                }
                                $wpjobportal_initials = strtoupper(implode('', array_map(function ($w) { return function_exists('mb_substr') ? mb_substr($w, 0, 1) : substr($w, 0, 1); }, array_slice(preg_split('/\s+/', trim((string) $wpjobportal_job->companyname), -1, PREG_SPLIT_NO_EMPTY), 0, 2))));
                                $wpjobportal_location = $wpjobportal_job->city !== '' ? WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($wpjobportal_job->city) : '';
                                $wpjobportal_salary = wpjobportal::$_common->getSalaryRangeView($wpjobportal_job->salarytype, $wpjobportal_job->salarymin, $wpjobportal_job->salarymax, $wpjobportal_job->currency);
                                $wpjobportal_days = (int) $wpjobportal_job->daysleft;
                                ?>
                                <tr id="job_<?php echo esc_attr($wpjobportal_id); ?>">
                                    <td class="wjpl-c-cb"><input class="wjpl-cb wjpl-pick" type="checkbox" name="wpjobportal-cb[]" value="<?php echo esc_attr($wpjobportal_id); ?>" aria-label="<?php echo esc_attr(sprintf(/* translators: %s: job title */ __('Select %s', 'wp-job-portal'), $wpjobportal_job->title)); ?>"></td>
                                    <td class="wjpl-c-main">
                                        <div class="wjpl-item">
                                            <?php if ($wpjobportal_logo !== '') : ?>
                                                <img class="wjpl-logo" src="<?php echo esc_url($wpjobportal_logo); ?>" alt="">
                                            <?php else : ?>
                                                <span class="wjpl-logo" aria-hidden="true"><?php echo esc_html($wpjobportal_initials !== '' ? $wpjobportal_initials : '—'); ?></span>
                                            <?php endif; ?>
                                            <div class="wjpl-item-body">
                                                <a class="wjpl-title" href="<?php echo esc_url($wpjobportal_edit); ?>"><?php echo esc_html($wpjobportal_job->title); ?></a>
                                                <?php
                                                $wpjobportal_badges = array();
                                                if ((int) $wpjobportal_job->status === 0) { $wpjobportal_badges[] = array('wait', __('Waiting for approval', 'wp-job-portal'), ''); }
                                                if ((int) $wpjobportal_job->status === -1) { $wpjobportal_badges[] = array('bad', __('Rejected', 'wp-job-portal'), ''); }
                                                if ((int) $wpjobportal_job->status === 3) { $wpjobportal_badges[] = array('wait', __('Pending payment', 'wp-job-portal'), ''); }
                                                if ((int) $wpjobportal_job->status === 1 && $wpjobportal_not_started) { $wpjobportal_badges[] = array('wait', __('Not published yet', 'wp-job-portal'), ''); }
                                                if ((int) $wpjobportal_job->isexpired) { $wpjobportal_badges[] = array('bad', __('Expired', 'wp-job-portal'), ''); }
                                                if ($wpjobportal_featured_on) {
                                                    /* translators: %s: date */
                                                    $wpjobportal_badges[] = array('feat', __('Featured', 'wp-job-portal'), sprintf(__('Featured until %s', 'wp-job-portal'), date_i18n($wpjobportal_dateformat, strtotime((string) $wpjobportal_job->endfeatureddate))));
                                                }
                                                if ($wpjobportal_feature_waiting) { $wpjobportal_badges[] = array('wait', __('Featured request waiting', 'wp-job-portal'), ''); }
                                                if ($wpjobportal_has_enhancer && !empty($wpjobportal_job->is_urgent)) { $wpjobportal_badges[] = array('bad', __('Urgent', 'wp-job-portal'), ''); }
                                                if ($wpjobportal_new_days > 0 && (current_time('timestamp') - strtotime((string) $wpjobportal_job->created)) <= $wpjobportal_new_days * DAY_IN_SECONDS) { $wpjobportal_badges[] = array('ok', __('New', 'wp-job-portal'), ''); }
                                                $wpjobportal_modes = array(1 => __('On-site', 'wp-job-portal'), 2 => __('Hybrid', 'wp-job-portal'), 3 => __('Remote', 'wp-job-portal'));
                                                if ($wpjobportal_has_enhancer && isset($wpjobportal_modes[(int) $wpjobportal_job->workplace_type])) { $wpjobportal_badges[] = array('info', $wpjobportal_modes[(int) $wpjobportal_job->workplace_type], ''); }
                                                foreach ($wpjobportal_badges as $wpjobportal_b) : ?>
                                                    <span class="wjpl-badge wjpl-b-<?php echo esc_attr($wpjobportal_b[0]); ?>"<?php echo $wpjobportal_b[2] !== '' ? ' title="' . esc_attr($wpjobportal_b[2]) . '"' : ''; ?>><?php echo esc_html($wpjobportal_b[1]); ?></span>
                                                <?php endforeach; ?>
                                                <div class="wjpl-meta">
                                                    <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_company&wpjobportallt=formcompany&wpjobportalid=' . (int) $wpjobportal_job->companyid)); ?>"><?php echo esc_html($wpjobportal_job->companyname); ?></a>
                                                    · <?php echo $wpjobportal_location !== '' ? esc_html($wpjobportal_location) : '<span class="wjpl-warn">' . esc_html(__('No location', 'wp-job-portal')) . '</span>'; ?>
                                                </div>
                                                <div class="wjpl-ra" id="for_ajax_only_<?php echo esc_attr($wpjobportal_id); ?>">
                                                    <?php if ((int) $wpjobportal_job->status === 0) : ?>
                                                        <a class="wjpl-ok" href="<?php echo esc_url($wpjobportal_task_url('approveQueueJob', array('id' => $wpjobportal_id))); ?>"><?php echo esc_html(__('Approve', 'wp-job-portal')); ?></a>
                                                        <a class="wjpl-del" href="<?php echo esc_url($wpjobportal_task_url('rejectQueueJob', array('id' => $wpjobportal_id))); ?>"><?php echo esc_html(__('Reject', 'wp-job-portal')); ?></a>
                                                    <?php endif; ?>
                                                    <?php if ($wpjobportal_feature_waiting) : ?>
                                                        <a class="wjpl-ok" href="<?php echo esc_url($wpjobportal_task_url('approveQueueFeaturedJob', array('id' => $wpjobportal_id))); ?>"><?php echo esc_html(__('Approve feature', 'wp-job-portal')); ?></a>
                                                        <a class="wjpl-del" href="<?php echo esc_url($wpjobportal_task_url('rejectQueueFeaturedJob', array('id' => $wpjobportal_id))); ?>"><?php echo esc_html(__('Reject feature', 'wp-job-portal')); ?></a>
                                                    <?php endif; ?>
                                                    <a href="<?php echo esc_url($wpjobportal_edit); ?>"><?php echo esc_html(__('Edit', 'wp-job-portal')); ?></a>
                                                    <?php if ((int) $wpjobportal_job->status === 1) : ?>
                                                        <a href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme' => 'job', 'wpjobportallt' => 'viewjob', 'wpjobportalid' => $wpjobportal_job->jobaliasid, 'wpjobportalpageid' => wpjobportal::wpjobportal_getPageid()))); ?>" target="_blank" rel="noopener"><?php echo esc_html(__('View', 'wp-job-portal')); ?></a>
                                                    <?php endif; ?>
                                                    <a href="<?php echo esc_url($wpjobportal_apps_url); ?>"><?php echo esc_html(sprintf(/* translators: %s: number of applications */ __('Applications (%s)', 'wp-job-portal'), number_format_i18n($wpjobportal_job->totalapps))); ?></a>
                                                    <?php if ($wpjobportal_closed) : ?>
                                                        <a href="#" data-wjpl-row="reopen" data-id="<?php echo esc_attr($wpjobportal_id); ?>"><?php echo esc_html(__('Reopen', 'wp-job-portal')); ?></a>
                                                    <?php else : ?>
                                                        <a href="#" data-wjpl-row="close" data-id="<?php echo esc_attr($wpjobportal_id); ?>"><?php echo esc_html(__('Close to new applicants', 'wp-job-portal')); ?></a>
                                                    <?php endif; ?>
                                                    <?php
                                                    // Add-on actions, as on the 2.7 list: Feature / Remove featured, Copy.
                                                    do_action('wpjobportal_addons_admin_feature_for_job', $wpjobportal_list['config'], $wpjobportal_job, !$wpjobportal_featured_on);
                                                    do_action('wpjobportal_addons_copyjob_credit_for_job', $wpjobportal_job);
                                                    ?>
                                                    <a class="wjpl-del" href="#" data-wjpl-row="delete" data-id="<?php echo esc_attr($wpjobportal_id); ?>"><?php echo esc_html(__('Delete', 'wp-job-portal')); ?></a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="wjpl-col-cat" data-l="<?php echo esc_attr(__('Category', 'wp-job-portal')); ?>"><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue((string) $wpjobportal_job->cat_title)); ?><div class="wjpl-small"><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue((string) $wpjobportal_job->jobtypetitle)); ?></div></td>
                                    <td class="wjpl-col-sal wjpl-nowrap" data-l="<?php echo esc_attr(__('Salary', 'wp-job-portal')); ?>"><?php
                                        if ($wpjobportal_salary !== '') {
                                            echo esc_html($wpjobportal_salary);
                                            if (((int) $wpjobportal_job->salarytype === 2 || (int) $wpjobportal_job->salarytype === 3) && !empty($wpjobportal_job->salaryrangetype)) {
                                                echo '<div class="wjpl-small">' . esc_html('/ ' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->salaryrangetype)) . '</div>';
                                            }
                                        } else {
                                            echo '<span class="wjpl-muted">—</span>';
                                        }
                                    ?></td>
                                    <td data-l="<?php echo esc_attr(__('Hiring status', 'wp-job-portal')); ?>">
                                        <span class="wjpl-badge <?php echo $wpjobportal_closed ? 'wjpl-b-closed' : 'wjpl-b-info'; ?>"><?php echo esc_html(($wpjobportal_closed && trim((string) $wpjobportal_job->hiringstatus) === '') ? __('Closed', 'wp-job-portal') : wpjobportal::wpjobportal_getVariableValue((string) $wpjobportal_job->hiringstatus)); /* Audit 7: the real status (e.g. On hold), not always "Closed" */ ?></span>
                                        <?php if ($wpjobportal_closed) : ?><div class="wjpl-small"><?php echo esc_html(__('to new applicants', 'wp-job-portal')); ?></div><?php endif; ?>
                                    </td>
                                    <td class="wjpl-num" data-l="<?php echo esc_attr(__('Applications', 'wp-job-portal')); ?>">
                                        <a class="wjpl-apps<?php echo (int) $wpjobportal_job->totalapps ? '' : ' wjpl-zero'; ?>" href="<?php echo esc_url($wpjobportal_apps_url); ?>"><?php echo esc_html(number_format_i18n($wpjobportal_job->totalapps)); ?></a>
                                        <?php if ((int) $wpjobportal_job->newapps) : ?><span class="wjpl-newpill" title="<?php echo esc_attr(__('In the Inbox', 'wp-job-portal')); ?>"><?php echo esc_html(sprintf(/* translators: %s: number */ __('%s new', 'wp-job-portal'), number_format_i18n($wpjobportal_job->newapps))); ?></span><?php endif; ?>
                                    </td>
                                    <td class="wjpl-col-posted wjpl-nowrap" data-l="<?php echo esc_attr(__('Posted', 'wp-job-portal')); ?>"><?php echo esc_html(date_i18n($wpjobportal_dateformat, strtotime((string) $wpjobportal_job->created))); ?></td>
                                    <td class="wjpl-nowrap" data-l="<?php echo esc_attr(__('Expires', 'wp-job-portal')); ?>">
                                        <?php echo esc_html(date_i18n($wpjobportal_dateformat, strtotime((string) $wpjobportal_job->stoppublishing))); ?>
                                        <div class="wjpl-small<?php echo $wpjobportal_days <= 7 ? ' wjpl-soon' : ''; ?>"><?php
                                            if ($wpjobportal_days < 0) {
                                                echo esc_html(__('expired', 'wp-job-portal'));
                                            } elseif ($wpjobportal_days === 0) {
                                                echo esc_html(__('today', 'wp-job-portal'));
                                            } else {
                                                /* translators: %s: number of days */
                                                echo esc_html(sprintf(_n('in %s day', 'in %s days', $wpjobportal_days, 'wp-job-portal'), number_format_i18n($wpjobportal_days)));
                                            }
                                        ?></div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php elseif ($wpjobportal_list['total'] > 0) : ?>
                        <?php WPJOBPORTAL_Admin_List_UI::empty_state(__('Nothing on this page', 'wp-job-portal'), __('This list has fewer pages now.', 'wp-job-portal'), array(__('Go to the first page', 'wp-job-portal') => $wpjobportal_link(array('pagenum' => null)))); ?>
                    <?php elseif ($wpjobportal_counts['all'] === 0) : ?>
                        <?php WPJOBPORTAL_Admin_List_UI::empty_state(__('No jobs yet', 'wp-job-portal'), __('Jobs you or your employers post appear here.', 'wp-job-portal'), array(__('Add New Job', 'wp-job-portal') => admin_url('admin.php?page=wpjobportal_job&wpjobportallt=formjob'))); ?>
                    <?php else : ?>
                        <?php WPJOBPORTAL_Admin_List_UI::empty_state(__('No jobs match these filters', 'wp-job-portal'), __('Try fewer words, or reset the filters.', 'wp-job-portal'), array(__('Reset filters', 'wp-job-portal') => admin_url('admin.php?page=wpjobportal_job'))); ?>
                    <?php endif; ?>
                    </div>
                </form>
                <?php WPJOBPORTAL_Admin_List_UI::footer($wpjobportal_list['total'], __('jobs', 'wp-job-portal'), function ($page) use ($wpjobportal_link) {
                    return $wpjobportal_link(array('pagenum' => $page > 1 ? $page : null));
                }); ?>
            </section>

            <dialog id="wjpl-delete" class="wjpl-dialog" aria-labelledby="wjpl-delete-title">
                <h2 id="wjpl-delete-title"><?php echo esc_html(__('Delete jobs?', 'wp-job-portal')); ?></h2>
                <p><?php
                    /* translators: %s: number of selected jobs (filled in by script) */
                    echo wp_kses(sprintf(__('%s selected job(s) will be deleted. Jobs that have applications are kept unless you also tick the box below.', 'wp-job-portal'), '<b class="wjpl-dlg-n">1</b>'), array('b' => array('class' => array())));
                ?></p>
                <label class="wjpl-ck"><input class="wjpl-cb" type="checkbox" name="wjpl-deleteall" value="1"><?php echo esc_html(__('Also delete everything about these jobs (applications and related records). This cannot be undone.', 'wp-job-portal')); ?></label>
                <div class="wjpl-dlg-acts">
                    <button type="button" class="wjpl-btn wjpl-btn-sec wjpl-dlg-cancel"><?php echo esc_html(__('Cancel', 'wp-job-portal')); ?></button>
                    <button type="button" class="wjpl-btn wjpl-btn-danger wjpl-dlg-ok"><?php echo esc_html(__('Delete', 'wp-job-portal')); ?></button>
                </div>
            </dialog>
        </div>
    </div>
</div>
