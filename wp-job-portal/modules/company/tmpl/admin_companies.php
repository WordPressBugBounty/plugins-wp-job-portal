<?php
    if (!defined('ABSPATH'))
        die('Restricted Access');
    wp_enqueue_script('jquery-ui-datepicker');
    $wpjobportal_protocol = is_ssl() ? "https://" : "http://";
    wp_enqueue_style('jquery-ui-css', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/jquery-ui-smoothness.css', array(), wpjobportal_asset_version('includes/css/jquery-ui-smoothness.css'));
    if(!WPJOBPORTALincluder::getTemplate('templates/admin/header',array('wpjobportal_module' => 'company'))){
        return;
    }

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
        jQuery(document).ready(function () {
            jQuery('a.sort-icon').click(function (e) {
                e.preventDefault();
                changeSortBy();
            });
            // featured tag
            jQuery('.featurednew').hover(function () {
                jQuery(this).find('span.featurednew-onhover').show();
            }, function () {
                jQuery(this).find('.featurednew-onhover').fadeOut('slow');
            });
            jQuery('.custom_date').datepicker({dateFormat: '". esc_js($wpjobportal_js_scriptdateformat)."'});
            jQuery('div.wpjobportal-company-list').each(function () {
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
            jQuery('div#full_background,img#popup_cross').click(function () {
                closePopup();
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

        function highlight(id) {
            if (jQuery('div#company_' + id + ' div span input:checked').length > 0) {
                showBorder(id);
            } else {
                hideBorder(id);
            }
        }
        function showBorder(id) {
            jQuery('div#company_' + id).addClass('blue');
        }
        function hideBorder(id) {
            jQuery('div#company_' + id).removeClass('blue');
        }
        function highlightAll() {
            if (jQuery('span.selector input').is(':checked') == false) {
                jQuery('span.selector').css('display', 'none');
                jQuery('div.wpjobportal-company-list').removeClass('blue');
            }
            if (jQuery('span.selector input').is(':checked') == true) {
                jQuery('span.selector').css('display', 'block');
                jQuery('div.wpjobportal-company-list').addClass('blue');
            }
        }

        function changeButton(cid, specialtype) {
            var non = jQuery('#featuredwpnonce').val();
            var html = '<a href=\"admin.php?page=wpjobportal_featuredcompany&task=removefeaturedcompany&action=wpjobportaltask&wpjobportal-cb[]=' + cid + '&_wpnonce='+non+'\" class=\"wpjobportal-company-act-btn\" title=\"". esc_html(__('Remove Featured', 'wp-job-portal'))."\">". esc_html(__('Remove Featured', 'wp-job-portal'))."</a>';
            jQuery('a.' + specialtype + '_' + cid).replaceWith(html);
        }

        function addBadgeToObject(cid, specialtype, expiry) {
            var html = '';
            html = '<span class=\"featurednew wpjobportal-featured-tag-icon-wrp\" data-id=\"' + cid + '\">';
            html += '<span id=\"badge_featured\" class=\"wpjobportal-featured-tag-icon\">\"". esc_html(__('Featured', 'wp-job-portal'))."\"</span>';
            html += '<span class=\"featurednew-onhover wpjobportal-featured-hover-wrp\" id=\"gold' + cid + '\" style=\"display: none;\">';
            html += \"". esc_html(__('Expiry Date', 'wp-job-portal')).":\"  + expiry;
            html += '</span>';
            html += '</span>';
            jQuery('div#company_' + cid).find('div#item-data div.wpjobportal-company-list-top-wrp').append(html);
            changeButton(cid,specialtype);
        }

        function resetFrom() {
            document.getElementById('searchcompany').value = '';
            document.getElementById('status').value = '';
            document.getElementById('datestart').value = '';
            document.getElementById('dateend').value = '';
            if (jQuery('#featured1').prop('checked') == true) {
                jQuery('#featured1').prop('checked',false);
            }
            document.getElementById('wpjobportalform').submit();
        }

        function changeSortBy() {
            var value = jQuery('a.sort-icon').attr('data-sortby');
            var img = '';
            if (value == 1) {
                value = 2;
                img = jQuery('a.sort-icon').attr('data-image2');
            } else {
                img = jQuery('a.sort-icon').attr('data-image1');
                value = 1;
            }
            jQuery('img#sortingimage').attr('src', img);
            jQuery('input#sortby').val(value);
            jQuery('form#wpjobportalform').submit();
        }
        function changeCombo() {
            jQuery('input#sorton').val(jQuery('select#sorting').val());
            changeSortBy();
        }

    ";
    wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );

    ?>
<?php
/*
 * Companies › All companies (design 4, built from the Jobs list's parts). The
 * script block above is the 2.7 list's, unchanged: the Feature add-on's row
 * action still relies on it. Classic screen: admin_companies_classic.php.
 */
WPJOBPORTAL_Admin_List_UI::enqueue();
WPJOBPORTALincluder::getTemplate('templates/admin/header', array('wpjobportal_module' => 'company'));

$wpjobportal_list = wpjobportal::$_data['companylist'];
$wpjobportal_f = $wpjobportal_list['filter'];
$wpjobportal_counts = $wpjobportal_list['counts'];
$wpjobportal_opts = $wpjobportal_list['options'];
$wpjobportal_link = function ($args = array()) use ($wpjobportal_f) {
    $wpjobportal_current = $wpjobportal_f;
    if ($wpjobportal_current['view'] === 'all') {
        $wpjobportal_current['view'] = '';
    }
    if ($wpjobportal_current['sort'] === 'name|1') {
        $wpjobportal_current['sort'] = '';
    }
    return WPJOBPORTAL_Admin_List_UI::url('wpjobportal_company', $wpjobportal_current, $args);
};
$wpjobportal_here = $wpjobportal_link(array('pagenum' => WPJOBPORTALpagination::$_currentpage > 1 ? WPJOBPORTALpagination::$_currentpage : null));
$wpjobportal_has_featured = in_array('featuredcompany', wpjobportal::$_active_addons, true);
$wpjobportal_has_departments = in_array('departments', wpjobportal::$_active_addons, true);
$wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
$wpjobportal_filters_on = ($wpjobportal_f['q'] !== '' || $wpjobportal_f['loc'] || $wpjobportal_f['owner']);
$wpjobportal_more_on = (int) ($wpjobportal_f['from'] !== '' || $wpjobportal_f['to'] !== '');
$wpjobportal_nonce = wp_create_nonce('wpjobportal_company_nonce');
$wpjobportal_views = array(
    'all' => array(__('All', 'wp-job-portal'), ''),
    'approved' => array(__('Approved', 'wp-job-portal'), '#059669'),
    'waiting' => array(__('Waiting for approval', 'wp-job-portal'), '#d97706'),
    'rejected' => array(__('Rejected', 'wp-job-portal'), '#dc2626'),
);
if ($wpjobportal_has_featured) {
    $wpjobportal_views['featured'] = array(__('Featured', 'wp-job-portal'), '#ca8a04');
}
$wpjobportal_task_url = function ($task, $args) use ($wpjobportal_nonce, $wpjobportal_here) {
    return add_query_arg(array_map('rawurlencode', array_merge(array('page' => 'wpjobportal_company', 'action' => 'wpjobportaltask', 'task' => $task, '_wpnonce' => $wpjobportal_nonce, 'returnto' => $wpjobportal_here), $args)), admin_url('admin.php'));
};
$wpjobportal_more = array(
    __('Company form fields', 'wp-job-portal') => admin_url('admin.php?page=wpjobportal_fieldordering&ff=1'),
);
if ($wpjobportal_has_departments) {
    $wpjobportal_more[__('Departments', 'wp-job-portal')] = admin_url('admin.php?page=wpjobportal_departments');
}
$wpjobportal_more[__('Companies waiting for approval', 'wp-job-portal')] = admin_url('admin.php?page=wpjobportal_company&wpjobportallt=companiesqueue');
$wpjobportal_more[__('Import companies', 'wp-job-portal')] = admin_url('admin.php?page=wpjobportal_thirdpartyimport');
$wpjobportal_statuses = array(1 => array('ok', __('Approved', 'wp-job-portal')), 0 => array('wait', __('Waiting for approval', 'wp-job-portal')), -1 => array('bad', __('Rejected', 'wp-job-portal')), 3 => array('wait', __('Pending payment', 'wp-job-portal')));
?>
<div id="wpjobportaladmin-wrapper" class="wjpl-page">
    <div id="wpjobportaladmin-leftmenu">
        <?php WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <div class="wjpl" data-delete-confirm="<?php echo esc_attr(__('Are you sure to delete', 'wp-job-portal') . ' ?'); ?>">
            <?php WPJOBPORTAL_Admin_List_UI::top_bar(array(array('label' => __('Companies', 'wp-job-portal')))); ?>
            <?php
            WPJOBPORTAL_Admin_List_UI::title_row(
                __('Companies', 'wp-job-portal'),
                /* translators: 1: number of companies, 2: number waiting for approval */
                sprintf(_n('%1$s company · %2$s waiting for approval', '%1$s companies · %2$s waiting for approval', $wpjobportal_counts['all'], 'wp-job-portal'), number_format_i18n($wpjobportal_counts['all']), number_format_i18n($wpjobportal_counts['waiting'])),
                $wpjobportal_more,
                array('label' => __('Add New Company', 'wp-job-portal'), 'url' => admin_url('admin.php?page=wpjobportal_company&wpjobportallt=formcompany'))
            );
            ?>
            <section class="wjpl-card" aria-label="<?php echo esc_attr(__('Companies', 'wp-job-portal')); ?>">
                <form class="wjpl-filters" method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
                    <input type="hidden" name="page" value="wpjobportal_company">
                    <?php if ($wpjobportal_f['view'] !== 'all') : ?><input type="hidden" name="view" value="<?php echo esc_attr($wpjobportal_f['view']); ?>"><?php endif; ?>
                    <div class="wjpl-tb1">
                        <?php WPJOBPORTAL_Admin_List_UI::chips($wpjobportal_views, $wpjobportal_counts, $wpjobportal_f['view'], function ($key) use ($wpjobportal_link) {
                            return $wpjobportal_link(array('view' => $key === 'all' ? null : $key));
                        }); ?>
                        <?php WPJOBPORTAL_Admin_List_UI::sort_select(array(
                            'name|1' => __('Name A–Z', 'wp-job-portal'),
                            'name|-1' => __('Name Z–A', 'wp-job-portal'),
                            'created|-1' => __('Newest first', 'wp-job-portal'),
                            'created|1' => __('Oldest first', 'wp-job-portal'),
                            'loc|1' => __('Location A–Z', 'wp-job-portal'),
                            'jobs|-1' => __('Most jobs', 'wp-job-portal'),
                        ), $wpjobportal_f['sort']); ?>
                    </div>
                    <div class="wjpl-selbar" role="toolbar" aria-label="<?php echo esc_attr(__('Bulk actions', 'wp-job-portal')); ?>" hidden>
                        <b><span class="wjpl-selcount">0</span> <?php echo esc_html(__('selected', 'wp-job-portal')); ?></b>
                        <button class="wjpl-sb" type="button" data-wjpl-bulk="approve"><?php echo esc_html(__('Approve', 'wp-job-portal')); ?></button>
                        <button class="wjpl-sb" type="button" data-wjpl-bulk="reject"><?php echo esc_html(__('Reject', 'wp-job-portal')); ?></button>
                        <button class="wjpl-sb wjpl-del" type="button" data-wjpl-bulk="delete"><?php echo esc_html(__('Delete', 'wp-job-portal')); ?></button>
                        <button class="wjpl-sb wjpl-clear" type="button"><?php echo esc_html(__('Clear selection', 'wp-job-portal')); ?></button>
                    </div>
                    <div class="wjpl-tb2">
                        <div class="wjpl-search">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                            <input class="wjpl-inp" type="search" name="q" value="<?php echo esc_attr($wpjobportal_f['q']); ?>" placeholder="<?php echo esc_attr(__('Search name, website, email, owner or location', 'wp-job-portal')); ?>" aria-label="<?php echo esc_attr(__('Search companies', 'wp-job-portal')); ?>">
                        </div>
                        <select class="wjpl-sel" name="loc" data-wjpl-autosubmit aria-label="<?php echo esc_attr(__('Location', 'wp-job-portal')); ?>">
                            <option value=""><?php echo esc_html(__('All locations', 'wp-job-portal')); ?></option>
                            <?php foreach ($wpjobportal_opts['locations'] as $wpjobportal_o) : ?>
                                <option value="<?php echo esc_attr($wpjobportal_o->id); ?>"<?php selected((int) $wpjobportal_f['loc'], (int) $wpjobportal_o->id); ?>><?php echo esc_html($wpjobportal_o->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select class="wjpl-sel" name="owner" data-wjpl-autosubmit aria-label="<?php echo esc_attr(__('Owner', 'wp-job-portal')); ?>">
                            <option value=""><?php echo esc_html(__('All owners', 'wp-job-portal')); ?></option>
                            <?php foreach ($wpjobportal_opts['owners'] as $wpjobportal_o) : ?>
                                <option value="<?php echo esc_attr($wpjobportal_o->id); ?>"<?php selected((int) $wpjobportal_f['owner'], (int) $wpjobportal_o->id); ?>><?php echo esc_html($wpjobportal_o->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span></span>
                        <span class="wjpl-tb2-acts">
                            <button class="wjpl-btn wjpl-btn-pri" type="submit"><?php echo esc_html(__('Search', 'wp-job-portal')); ?></button>
                            <a class="wjpl-linkbtn" href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_company')); ?>"><?php echo esc_html(__('Reset', 'wp-job-portal')); ?></a>
                        </span>
                        <?php if ($wpjobportal_filters_on || $wpjobportal_more_on) : ?>
                            <div class="wjpl-hint"><?php
                                /* translators: 1: companies shown, 2: all companies */
                                echo wp_kses(sprintf(__('Showing <b>%1$s</b> of %2$s companies.', 'wp-job-portal'), number_format_i18n($wpjobportal_list['total']), number_format_i18n($wpjobportal_counts['all'])), array('b' => array()));
                            ?></div>
                        <?php endif; ?>
                    </div>
                    <details class="wjpl-tb3"<?php echo $wpjobportal_more_on ? ' open' : ''; ?>>
                        <summary class="wjpl-linkbtn"><?php echo esc_html(__('More filters', 'wp-job-portal')); ?><?php if ($wpjobportal_more_on) : ?><span class="wjpl-n"><?php echo esc_html($wpjobportal_more_on); ?></span><?php endif; ?></summary>
                        <div class="wjpl-tb3-grid">
                            <label><?php echo esc_html(__('Created between', 'wp-job-portal')); ?>
                                <span class="wjpl-two">
                                    <input class="wjpl-inp wjpl-plain" type="date" name="from" value="<?php echo esc_attr($wpjobportal_f['from']); ?>" aria-label="<?php echo esc_attr(__('Created from', 'wp-job-portal')); ?>">
                                    <input class="wjpl-inp wjpl-plain" type="date" name="to" value="<?php echo esc_attr($wpjobportal_f['to']); ?>" aria-label="<?php echo esc_attr(__('Created to', 'wp-job-portal')); ?>">
                                </span>
                            </label>
                        </div>
                    </details>
                </form>

                <form class="wjpl-bulkform" id="wpjobportal-list-form" method="post" action="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_company')); ?>">
                    <input type="hidden" name="form_request" value="wpjobportal">
                    <input type="hidden" name="task" value="bulkcompanies">
                    <input type="hidden" name="bulkop" value="">
                    <input type="hidden" name="deleteall" value="0">
                    <input type="hidden" name="returnto" value="<?php echo esc_attr($wpjobportal_here); ?>">
                    <input type="hidden" name="_wpnonce" value="<?php echo esc_attr($wpjobportal_nonce); ?>">
                    <?php // Read by the Feature add-on's popup, as on the 2.7 list. ?>
                    <?php echo wp_kses(WPJOBPORTALformfield::hidden('creditid', ''), WPJOBPORTAL_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(WPJOBPORTALformfield::hidden('package', 'featuredcompany'), WPJOBPORTAL_ALLOWED_TAGS); ?>
                    <div class="wjpl-tw">
                    <?php if (!empty($wpjobportal_list['rows'])) : ?>
                        <table class="wjpl-table">
                            <thead><tr>
                                <th class="wjpl-c-cb"><input class="wjpl-cb wjpl-all" type="checkbox" aria-label="<?php echo esc_attr(__('Select all companies', 'wp-job-portal')); ?>"></th>
                                <?php
                                $wpjobportal_th = function ($key, $label, $class = '') use ($wpjobportal_f, $wpjobportal_link) {
                                    $wpjobportal_parts = explode('|', $wpjobportal_f['sort']);
                                    $wpjobportal_on = $wpjobportal_parts[0] === $key;
                                    $wpjobportal_next = ($wpjobportal_on && $wpjobportal_parts[1] === '1') ? '-1' : '1';
                                    echo '<th class="' . esc_attr($class) . '"' . ($wpjobportal_on ? ' aria-sort="' . ($wpjobportal_parts[1] === '1' ? 'ascending' : 'descending') . '"' : '') . '><a href="' . esc_url($wpjobportal_link(array('sort' => $key . '|' . $wpjobportal_next))) . '">' . esc_html($label) . ($wpjobportal_on ? '<span aria-hidden="true">' . ($wpjobportal_parts[1] === '1' ? ' ▲' : ' ▼') . '</span>' : '') . '</a></th>';
                                };
                                $wpjobportal_th('name', __('Company', 'wp-job-portal'));
                                echo '<th class="wjpl-col-cat">' . esc_html(__('Owner', 'wp-job-portal')) . '</th>';
                                $wpjobportal_th('loc', __('Location', 'wp-job-portal'), 'wjpl-col-sal');
                                $wpjobportal_th('jobs', __('Jobs', 'wp-job-portal'), 'wjpl-num');
                                $wpjobportal_th('created', __('Created', 'wp-job-portal'), 'wjpl-col-posted');
                                echo '<th>' . esc_html(__('Status', 'wp-job-portal')) . '</th>';
                                ?>
                            </tr></thead>
                            <tbody>
                            <?php foreach ($wpjobportal_list['rows'] as $wpjobportal_company) :
                                $wpjobportal_id = (int) $wpjobportal_company->id;
                                $wpjobportal_edit = admin_url('admin.php?page=wpjobportal_company&wpjobportallt=formcompany&wpjobportalid=' . $wpjobportal_id);
                                $wpjobportal_jobs_url = admin_url('admin.php?page=wpjobportal_job&company=' . $wpjobportal_id);
                                $wpjobportal_logo = '';
                                if (!empty($wpjobportal_company->logofilename)) {
                                    $wpjobportal_wpdir = wp_upload_dir();
                                    $wpjobportal_logo = $wpjobportal_wpdir['baseurl'] . '/' . WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory') . '/data/employer/comp_' . $wpjobportal_id . '/logo/' . $wpjobportal_company->logofilename;
                                }
                                $wpjobportal_initials = strtoupper(implode('', array_map(function ($w) { return function_exists('mb_substr') ? mb_substr($w, 0, 1) : substr($w, 0, 1); }, array_slice(preg_split('/\s+/', trim((string) $wpjobportal_company->name), -1, PREG_SPLIT_NO_EMPTY), 0, 2))));
                                $wpjobportal_location = $wpjobportal_company->city !== '' ? WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($wpjobportal_company->city) : '';
                                $wpjobportal_status = isset($wpjobportal_statuses[(int) $wpjobportal_company->status]) ? $wpjobportal_statuses[(int) $wpjobportal_company->status] : array('closed', (string) $wpjobportal_company->status);
                                $wpjobportal_feature_waiting = $wpjobportal_has_featured && (int) $wpjobportal_company->isfeaturedcompany === 0;
                                $wpjobportal_site = preg_replace('#^https?://#i', '', (string) $wpjobportal_company->url);
                                ?>
                                <tr id="company_<?php echo esc_attr($wpjobportal_id); ?>">
                                    <td class="wjpl-c-cb"><input class="wjpl-cb wjpl-pick" type="checkbox" name="wpjobportal-cb[]" value="<?php echo esc_attr($wpjobportal_id); ?>" aria-label="<?php echo esc_attr(sprintf(/* translators: %s: company name */ __('Select %s', 'wp-job-portal'), $wpjobportal_company->name)); ?>"></td>
                                    <td class="wjpl-c-main">
                                        <div class="wjpl-item">
                                            <?php if ($wpjobportal_logo !== '') : ?>
                                                <img class="wjpl-logo" src="<?php echo esc_url($wpjobportal_logo); ?>" alt="">
                                            <?php else : ?>
                                                <span class="wjpl-logo" aria-hidden="true"><?php echo esc_html($wpjobportal_initials !== '' ? $wpjobportal_initials : '—'); ?></span>
                                            <?php endif; ?>
                                            <div class="wjpl-item-body">
                                                <a class="wjpl-title" href="<?php echo esc_url($wpjobportal_edit); ?>"><?php echo esc_html($wpjobportal_company->name); ?></a>
                                                <?php if ((int) $wpjobportal_company->featurednow) : ?>
                                                    <span class="wjpl-badge wjpl-b-feat" title="<?php echo esc_attr(sprintf(/* translators: %s: date */ __('Featured until %s', 'wp-job-portal'), date_i18n($wpjobportal_dateformat, strtotime((string) $wpjobportal_company->endfeatureddate)))); ?>"><?php echo esc_html(__('Featured', 'wp-job-portal')); ?></span>
                                                <?php endif; ?>
                                                <?php if ($wpjobportal_feature_waiting) : ?><span class="wjpl-badge wjpl-b-wait"><?php echo esc_html(__('Featured request waiting', 'wp-job-portal')); ?></span><?php endif; ?>
                                                <div class="wjpl-meta wjpl-meta-plain">
                                                    <?php
                                                    $wpjobportal_bits = array();
                                                    if ($wpjobportal_site !== '') { $wpjobportal_bits[] = esc_html($wpjobportal_site); }
                                                    if (!empty($wpjobportal_company->contactemail)) { $wpjobportal_bits[] = esc_html($wpjobportal_company->contactemail); }
                                                    echo $wpjobportal_bits ? implode(' · ', $wpjobportal_bits) : '<span class="wjpl-muted">' . esc_html(__('No website or email', 'wp-job-portal')) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- each part escaped above
                                                    ?>
                                                </div>
                                                <div class="wjpl-ra" id="for_ajax_only_<?php echo esc_attr($wpjobportal_id); ?>">
                                                    <?php if ((int) $wpjobportal_company->status === 0) : ?>
                                                        <a class="wjpl-ok" href="<?php echo esc_url($wpjobportal_task_url('approveQueueCompany', array('id' => $wpjobportal_id))); ?>"><?php echo esc_html(__('Approve', 'wp-job-portal')); ?></a>
                                                        <a class="wjpl-del" href="<?php echo esc_url($wpjobportal_task_url('rejectQueueCompany', array('id' => $wpjobportal_id))); ?>"><?php echo esc_html(__('Reject', 'wp-job-portal')); ?></a>
                                                    <?php endif; ?>
                                                    <?php if ($wpjobportal_feature_waiting) : ?>
                                                        <a class="wjpl-ok" href="<?php echo esc_url($wpjobportal_task_url('approveQueueFeaturedCompany', array('id' => $wpjobportal_id))); ?>"><?php echo esc_html(__('Approve feature', 'wp-job-portal')); ?></a>
                                                        <a class="wjpl-del" href="<?php echo esc_url($wpjobportal_task_url('rejectQueueFeatureCompany', array('id' => $wpjobportal_id))); ?>"><?php echo esc_html(__('Reject feature', 'wp-job-portal')); ?></a>
                                                    <?php endif; ?>
                                                    <a href="<?php echo esc_url($wpjobportal_edit); ?>"><?php echo esc_html(__('Edit', 'wp-job-portal')); ?></a>
                                                    <?php if ((int) $wpjobportal_company->status === 1) : ?>
                                                        <a href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme' => 'company', 'wpjobportallt' => 'viewcompany', 'wpjobportalid' => $wpjobportal_company->aliasid, 'wpjobportalpageid' => wpjobportal::wpjobportal_getPageid()))); ?>" target="_blank" rel="noopener"><?php echo esc_html(__('View', 'wp-job-portal')); ?></a>
                                                    <?php endif; ?>
                                                    <a href="<?php echo esc_url($wpjobportal_jobs_url); ?>"><?php echo esc_html(sprintf(/* translators: %s: number of jobs */ __('Jobs (%s)', 'wp-job-portal'), number_format_i18n($wpjobportal_company->totaljobs))); ?></a>
                                                    <?php if ($wpjobportal_has_departments) : ?>
                                                        <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_departments&wpjobportallt=departments&companyid=' . $wpjobportal_id)); ?>"><?php echo esc_html(sprintf(/* translators: %s: number of departments */ __('Departments (%s)', 'wp-job-portal'), number_format_i18n($wpjobportal_company->totaldepartments))); ?></a>
                                                    <?php endif; ?>
                                                    <?php
                                                    // Add-on actions, as on the 2.7 list: Feature / Remove featured.
                                                    $wpjobportal_company->isfeaturedcompany = (int) $wpjobportal_company->isfeaturedcompany;
                                                    do_action('wpjobportal_addons_control_company_admin', $wpjobportal_company);
                                                    ?>
                                                    <a class="wjpl-del" href="#" data-wjpl-row="delete" data-id="<?php echo esc_attr($wpjobportal_id); ?>"><?php echo esc_html(__('Delete', 'wp-job-portal')); ?></a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="wjpl-col-cat" data-l="<?php echo esc_attr(__('Owner', 'wp-job-portal')); ?>"><?php echo $wpjobportal_company->ownername !== '' && $wpjobportal_company->ownername !== null ? esc_html($wpjobportal_company->ownername) : '<span class="wjpl-muted">—</span>'; ?><div class="wjpl-small"><?php echo esc_html(__('Employer', 'wp-job-portal')); ?></div></td>
                                    <td class="wjpl-col-sal" data-l="<?php echo esc_attr(__('Location', 'wp-job-portal')); ?>"><?php echo $wpjobportal_location !== '' ? esc_html($wpjobportal_location) : '<span class="wjpl-warn">' . esc_html(__('No location', 'wp-job-portal')) . '</span>'; ?></td>
                                    <td class="wjpl-num" data-l="<?php echo esc_attr(__('Jobs', 'wp-job-portal')); ?>">
                                        <a class="wjpl-apps<?php echo (int) $wpjobportal_company->totaljobs ? '' : ' wjpl-zero'; ?>" href="<?php echo esc_url($wpjobportal_jobs_url); ?>"><?php echo esc_html(number_format_i18n($wpjobportal_company->totaljobs)); ?></a>
                                        <div class="wjpl-small"><?php
                                            /* translators: 1: open jobs, 2: applications */
                                            echo esc_html(sprintf(__('%1$s open · %2$s applications', 'wp-job-portal'), number_format_i18n((int) $wpjobportal_company->openjobs), number_format_i18n((int) $wpjobportal_company->totalapps)));
                                        ?></div>
                                    </td>
                                    <td class="wjpl-col-posted wjpl-nowrap" data-l="<?php echo esc_attr(__('Created', 'wp-job-portal')); ?>"><?php echo esc_html(date_i18n($wpjobportal_dateformat, strtotime((string) $wpjobportal_company->created))); ?><div class="wjpl-small"><?php echo esc_html(sprintf(/* translators: %s: number of views */ _n('%s profile view', '%s profile views', (int) $wpjobportal_company->hits, 'wp-job-portal'), number_format_i18n((int) $wpjobportal_company->hits))); ?></div></td>
                                    <td data-l="<?php echo esc_attr(__('Status', 'wp-job-portal')); ?>"><span class="wjpl-badge wjpl-b-<?php echo esc_attr($wpjobportal_status[0]); ?>"><?php echo esc_html($wpjobportal_status[1]); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php elseif ($wpjobportal_list['total'] > 0) : ?>
                        <?php WPJOBPORTAL_Admin_List_UI::empty_state(__('Nothing on this page', 'wp-job-portal'), __('This list has fewer pages now.', 'wp-job-portal'), array(__('Go to the first page', 'wp-job-portal') => $wpjobportal_link(array('pagenum' => null)))); ?>
                    <?php elseif ($wpjobportal_counts['all'] === 0) : ?>
                        <?php WPJOBPORTAL_Admin_List_UI::empty_state(__('No companies yet', 'wp-job-portal'), __('Companies you or your employers add appear here.', 'wp-job-portal'), array(__('Add New Company', 'wp-job-portal') => admin_url('admin.php?page=wpjobportal_company&wpjobportallt=formcompany'))); ?>
                    <?php else : ?>
                        <?php WPJOBPORTAL_Admin_List_UI::empty_state(__('No companies match these filters', 'wp-job-portal'), __('Try fewer words, or reset the filters.', 'wp-job-portal'), array(__('Reset filters', 'wp-job-portal') => admin_url('admin.php?page=wpjobportal_company'))); ?>
                    <?php endif; ?>
                    </div>
                </form>
                <?php WPJOBPORTAL_Admin_List_UI::footer($wpjobportal_list['total'], __('companies', 'wp-job-portal'), function ($page) use ($wpjobportal_link) {
                    return $wpjobportal_link(array('pagenum' => $page > 1 ? $page : null));
                }); ?>
            </section>

            <dialog id="wjpl-delete" class="wjpl-dialog" aria-labelledby="wjpl-delete-title">
                <h2 id="wjpl-delete-title"><?php echo esc_html(__('Delete companies?', 'wp-job-portal')); ?></h2>
                <p><?php
                    /* translators: %s: number of selected companies (filled in by script) */
                    echo wp_kses(sprintf(__('%s selected company(ies) will be deleted. Companies that still have jobs or departments are kept unless you also tick the box below.', 'wp-job-portal'), '<b class="wjpl-dlg-n">1</b>'), array('b' => array('class' => array())));
                ?></p>
                <label class="wjpl-ck"><input class="wjpl-cb" type="checkbox" name="wjpl-deleteall" value="1"><?php echo esc_html(__('Also delete everything about these companies (their jobs, departments and applications). This cannot be undone.', 'wp-job-portal')); ?></label>
                <div class="wjpl-dlg-acts">
                    <button type="button" class="wjpl-btn wjpl-btn-sec wjpl-dlg-cancel"><?php echo esc_html(__('Cancel', 'wp-job-portal')); ?></button>
                    <button type="button" class="wjpl-btn wjpl-btn-danger wjpl-dlg-ok"><?php echo esc_html(__('Delete', 'wp-job-portal')); ?></button>
                </div>
            </dialog>
        </div>
    </div>
</div>
