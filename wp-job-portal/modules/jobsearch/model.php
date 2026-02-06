<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALjobSearchModel {

    function getSearchJobs_Widget($title, $wpjobportal_showtitle, $wpjobportal_fieldtitle, $wpjobportal_category, $wpjobportal_jobtype, $wpjobportal_jobstatus, $wpjobportal_salaryrange, $shift, $duration, $wpjobportal_startpublishing, $wpjobportal_stoppublishing, $wpjobportal_company, $wpjobportal_address, $columnperrow, $wpjobportal_layout = 'vertical', $wpjobportal_show_adv_button = true, $use_icons_for_buttons = false, $wpjobportal_field_custom_class = '',$wpjobportal_show_labels = 1,$wpjobportal_show_placeholders = 0) {
        // new variables
        //$wpjobportal_layout = 'vertical', $wpjobportal_show_adv_button = false, $use_icons_for_buttons = false, $custom_css_classes = '', $wpjobportal_field_custom_class = ''

        // Count how many fields are enabled
        $wpjobportal_enabled_fields = array_filter([
            $wpjobportal_fieldtitle,
            $wpjobportal_category,
            $wpjobportal_jobtype,
            $wpjobportal_company,
            $wpjobportal_address
        ]);

        $wpjobportal_count = 0;
        foreach ($wpjobportal_enabled_fields as $wpjobportal_enabled) {
            if ($wpjobportal_enabled) {
                $wpjobportal_count++;
            }
        }
        $wpjobportal_visible_field_count = $wpjobportal_count;
        // to handle button widths ( mainly for horizental style and advnce search diasble case for less then four fields)
        $button_wrap_class = '';
        // Set widths
        if ($wpjobportal_layout === 'vertical') {
            $wpjobportal_field_width = '100';
            $button_style = '100';
        } else {
            if ($wpjobportal_visible_field_count > 3) {
                $columns = $columnperrow; //
                $wpjobportal_field_width = round(100 / $columns, 2);
                $button_style = $wpjobportal_field_width;
            } else {
                $button_style = $wpjobportal_show_adv_button ? 25 : 15;
                $button_wrap_class = $wpjobportal_show_adv_button ? '' : 'wpjobportal-search-btn-full-width';// to handle button widths ( mainly for horizental style and advnce search diasble case for less then four fields)
                $wpjobportal_field_columns = $columnperrow; // avoid divide-by-zero
                $wpjobportal_field_width = round((100 - $button_style) / $wpjobportal_field_columns, 2);
            }
        }
        if(!function_exists('wpjobportal_renderCurrentFieldJP')){
            function wpjobportal_renderCurrentFieldJP($title_str, $wpjobportal_field_html, $wpjobportal_field_width) {
                $current_html = '<div class="wjportal-form-row " style="width:' . esc_attr($wpjobportal_field_width) . '%;">
                    <div class="wjportal-form-tit">' . esc_html($title_str) . '</div>
                    <div class="wjportal-form-val">' . wp_kses($wpjobportal_field_html, WPJOBPORTAL_ALLOWED_TAGS) . '</div>
                </div>';
                return $current_html;
            }
        }


        $wpjobportal_layout_class = $wpjobportal_layout == 'horizontal' ? 'wjportal-form-horizontal' : 'wjportal-form-vertical';

        $wpjobportal_html = '<div id="wpjobportal_mod_wrapper" class="wjportal-search-mod wjportal-form-mod ' . esc_attr($wpjobportal_layout_class) . '">';

        // if ($wpjobportal_showtitle == 1 && $title != '') {
        //     $wpjobportal_html .= '<div id="wpjobportal-mod-heading" class="wjportal-mod-heading">' . esc_html($title) . '</div>';
        // }

        $wpjobportal_html .= '<form class="job_form wjportal-form" id="job_form" method="post" action="' . esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobs', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageidForWidgets()))) . '">';


        if ($wpjobportal_fieldtitle == 1) {
            $title = '';
            $placeholder = '';
            if($wpjobportal_show_labels == 1){
                $title = esc_html(__('Job Title', 'wp-job-portal'));
            }
            if($wpjobportal_show_placeholders == 1){
                $placeholder = esc_html(__('Job Title', 'wp-job-portal'));
            }
            $wpjobportal_value = WPJOBPORTALformfield::text('jobtitle', '', array('class' => 'inputbox'.' '.$wpjobportal_field_custom_class, 'placeholder' => $placeholder));
            $wpjobportal_html .= wpjobportal_renderCurrentFieldJP($title, $wpjobportal_value, $wpjobportal_field_width);
        }

        if ($wpjobportal_category == 1) {
            $title = '';
            $placeholder = '';
            if($wpjobportal_show_labels == 1){
                $title = esc_html(__('Job Category', 'wp-job-portal'));
            }
            if($wpjobportal_show_placeholders == 1){
                $placeholder = esc_html(__('Select Job Category', 'wp-job-portal'));
            }
            $wpjobportal_value = WPJOBPORTALformfield::select('category[]', WPJOBPORTALincluder::getJSModel('category')->getCategoriesForCombo(), isset(wpjobportal::$_data['filter']['category']) ? wpjobportal::$_data['filter']['category'] : '', $placeholder, array('class' => 'inputbox'.' '.$wpjobportal_field_custom_class));
            $wpjobportal_html .= wpjobportal_renderCurrentFieldJP($title, $wpjobportal_value, $wpjobportal_field_width);
        }

        if ($wpjobportal_jobtype == 1) {
            $title = '';
            $placeholder = '';
            if($wpjobportal_show_labels == 1){
                $title = esc_html(__('Job Type', 'wp-job-portal'));
            }
            if($wpjobportal_show_placeholders == 1){
                $placeholder = esc_html(__('Select Job Type', 'wp-job-portal'));
            }
            $wpjobportal_value = WPJOBPORTALformfield::select('jobtype[]', WPJOBPORTALincluder::getJSModel('jobtype')->getJobTypeForCombo(), isset(wpjobportal::$_data['filter']['jobtype']) ? wpjobportal::$_data['filter']['jobtype'] : '', $placeholder, array('class' => 'inputbox'.' '.$wpjobportal_field_custom_class));
            $wpjobportal_html .= wpjobportal_renderCurrentFieldJP($title, $wpjobportal_value, $wpjobportal_field_width);
        }

        if ($wpjobportal_jobstatus == 1) {
            $title = '';
            $placeholder = '';
            if($wpjobportal_show_labels == 1){
                $title = esc_html(__('Job Status', 'wp-job-portal'));
            }
            if($wpjobportal_show_placeholders == 1){
                $placeholder = esc_html(__('Select Job Status', 'wp-job-portal'));
            }
            $wpjobportal_value = WPJOBPORTALformfield::select('jobstatus[]', WPJOBPORTALincluder::getJSModel('jobstatus')->getJobStatusForCombo(), isset(wpjobportal::$_data['filter']['jobstatus']) ? wpjobportal::$_data['filter']['jobstatus'] : '', $placeholder, array('class' => 'inputbox'.' '.$wpjobportal_field_custom_class));
            $wpjobportal_html .= wpjobportal_renderCurrentFieldJP($title, $wpjobportal_value, $wpjobportal_field_width);
        }

        if ($duration == 1) {
            $title = '';
            $placeholder = '';
            if($wpjobportal_show_labels == 1){
                $title = esc_html(__('Duration', 'wp-job-portal'));
            }
            if($wpjobportal_show_placeholders == 1){
                $placeholder = esc_html(__('Duration', 'wp-job-portal'));
            }
            $wpjobportal_value = WPJOBPORTALformfield::text('duration', isset(wpjobportal::$_data['filter']['duration']) ? wpjobportal::$_data['filter']['duration'] : '', array('class' => 'inputbox'.' '.$wpjobportal_field_custom_class, 'placeholder' => $placeholder));
            $wpjobportal_html .= wpjobportal_renderCurrentFieldJP($title, $wpjobportal_value, $wpjobportal_field_width);
        }

        if ($wpjobportal_company == 1) {
            $title = '';
            $placeholder = '';
            if($wpjobportal_show_labels == 1){
                $title = esc_html(__('Company', 'wp-job-portal'));
            }
            if($wpjobportal_show_placeholders == 1){
                $placeholder = esc_html(__('Select Company', 'wp-job-portal'));
            }
            $wpjobportal_value = WPJOBPORTALformfield::select('company[]', WPJOBPORTALincluder::getJSModel('company')->getCompaniesForCombo(), isset(wpjobportal::$_data['filter']['company']) ? wpjobportal::$_data['filter']['company'] : '', $placeholder, array('class' => 'inputbox'.' '.$wpjobportal_field_custom_class));
            $wpjobportal_html .= wpjobportal_renderCurrentFieldJP($title, $wpjobportal_value, $wpjobportal_field_width);
        }

        if ($wpjobportal_address == 1) {
            $title = '';
            $placeholder = '';
            if($wpjobportal_show_labels == 1){
                $title = esc_html(__('City', 'wp-job-portal'));
            }
            if($wpjobportal_show_placeholders == 1){
                $placeholder = esc_html(__('City', 'wp-job-portal'));
            }
            $wpjobportal_value = WPJOBPORTALformfield::text('city', isset(wpjobportal::$_data['filter']['city']) ? wpjobportal::$_data['filter']['city'] : '', array('class' => 'inputbox wpjobportal-job-search-widget-city-field', 'placeholder' => $placeholder));
            $wpjobportal_html .= wpjobportal_renderCurrentFieldJP($title, $wpjobportal_value, $wpjobportal_field_width);
        }


        if ($wpjobportal_salaryrange == 1) {
            // $title = esc_html(__('Salary Range', 'wp-job-portal'));
            // $wpjobportal_value  = WPJOBPORTALformfield::select('currencyid', WPJOBPORTALincluder::getJSModel('currency')->getCurrencyForCombo(), isset(wpjobportal::$_data[0]['filter']->currencyid) ? wpjobportal::$_data[0]['filter']->currencyid : '', esc_html(__('Select','wp-job-portal')) . ' ' . esc_html(__('Currency', 'wp-job-portal')), array('class' => 'inputbox sal'.' '.$wpjobportal_field_custom_class));
            // $wpjobportal_value .= WPJOBPORTALformfield::select('salaryrangestart', WPJOBPORTALincluder::getJSModel('salaryrange')->getJobStartSalaryRangeForCombo(), isset(wpjobportal::$_data[0]['filter']->salaryrange) ? wpjobportal::$_data[0]['filter']->salaryrange : '', esc_html(__('Select','wp-job-portal')) .' '. esc_html(__('Salary Range','wp-job-portal')) .' '. esc_html(__('Start', 'wp-job-portal')), array('class' => 'inputbox sal'.' '.$wpjobportal_field_custom_class));
            // $wpjobportal_value .= WPJOBPORTALformfield::select('salaryrangeend', WPJOBPORTALincluder::getJSModel('salaryrange')->getJobEndSalaryRangeForCombo(), isset(wpjobportal::$_data[0]['filter']->salaryrange) ? wpjobportal::$_data[0]['filter']->salaryrange : '', esc_html(__('Select','wp-job-portal')) .' '. esc_html(__('Salary Range','wp-job-portal')) .' '. esc_html(__('End', 'wp-job-portal')), array('class' => 'inputbox sal'.' '.$wpjobportal_field_custom_class));
            // $wpjobportal_value .= WPJOBPORTALformfield::select('salaryrangetype', WPJOBPORTALincluder::getJSModel('salaryrangetype')->getSalaryRangeTypesForCombo(), isset(wpjobportal::$_data[0]['filter']->salaryrangetype) ? wpjobportal::$_data[0]['filter']->salaryrangetype : '', esc_html(__('Select','wp-job-portal')) .' '. esc_html(__('Salary Range Type', 'wp-job-portal')), array('class' => 'inputbox sal'.' '.$wpjobportal_field_custom_class));
            // $wpjobportal_html .= wpjobportal_renderCurrentFieldJP($title, $wpjobportal_value, $wpjobportal_field_width);
        }

        if ($wpjobportal_startpublishing == 1) {
            // $title = esc_html(__('Start Publishing', 'wp-job-portal'));
            // $wpjobportal_value = WPJOBPORTALformfield::date('startpublishing', isset(wpjobportal::$_data['filter']['startpublishing']) ? wpjobportal::$_data['filter']['startpublishing'] : '', array('class' => 'inputbox'.' '.$wpjobportal_field_custom_class));
            // $wpjobportal_html .= wpjobportal_renderCurrentFieldJP($title, $wpjobportal_value, $wpjobportal_field_width);
        }

        if ($wpjobportal_stoppublishing == 1) {
            // $title = esc_html(__('Stop Publishing', 'wp-job-portal'));
            // $wpjobportal_value = WPJOBPORTALformfield::date('stoppublishing', isset(wpjobportal::$_data['filter']['stoppublishing']) ? wpjobportal::$_data['filter']['stoppublishing'] : '', array('class' => 'inputbox'.' '.$wpjobportal_field_custom_class));
            // $wpjobportal_html .= wpjobportal_renderCurrentFieldJP($title, $wpjobportal_value, $wpjobportal_field_width);
        }

        // Buttons
        $wpjobportal_search_label = $use_icons_for_buttons ? ' <i class="fa fa-search"></i> ' : esc_html(__('Search Job', 'wp-job-portal'));
        $adv_label = $use_icons_for_buttons ? ' <i class="fa fa-cogs"></i> ' : esc_html(__('Advance Search', 'wp-job-portal'));

        $wpjobportal_html .= '<div class="wjportal-form-btn-row '.esc_attr($button_wrap_class).' " style="width:' . $button_style . '%;"> ';
                if($wpjobportal_show_labels == 1){
                    $wpjobportal_html .='    <div class="wjportal-form-tit">&nbsp;</div>';
                }
        $wpjobportal_html .='
                        <button type="submit" class="wjportal-filter-search-btn">
                            ' . $wpjobportal_search_label . '
                        </button>
            ' . ( ($wpjobportal_show_adv_button) ? '<a class="anchor wjportal-form-btn wjportal-form-adv-srch-btn" href="' . esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobsearch', 'wpjobportallt'=>'jobsearch', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageidForWidgets()))) . '">' . $adv_label . '</a>' : '') . '
        </div>';

        $wpjobportal_html .= '
            <input type="hidden" id="issearchform" name="issearchform" value="1"/>
            <input type="hidden" id="WPJOBPORTAL_form_search" name="WPJOBPORTAL_form_search" value="WPJOBPORTAL_SEARCH"/>
            <input type="hidden" id="wpjobportallay" name="wpjobportallay" value="jobs"/>
        </form>
        </div>';

        wp_register_script( 'wpjobportal-inline-handle', '' );
        wp_enqueue_script( 'wpjobportal-inline-handle' );
        $wpjobportal_inline_js_script = '
            function getTokenInputWidget() {
                var cityArray = "' . esc_url_raw(admin_url("admin.php?page=wpjobportal_city&action=wpjobportaltask&task=getaddressdatabycityname")) . '";
                jQuery(".wpjobportal-job-search-widget-city-field").tokenInput(cityArray, {
                    theme: "wpjobportal",
                    preventDuplicates: true,
                    hintText: "' . esc_html(__('Type In A Search Term', 'wp-job-portal')) . '",
                    noResultsText: "' . esc_html(__('No Results', 'wp-job-portal')) . '",
                    searchingText: "' . esc_html(__('Searching', 'wp-job-portal')) . '"
                });
            }
            jQuery(document).ready(function(){
                getTokenInputWidget();
            });
        ';
        wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );

        return $wpjobportal_html;
    }

    function getAISearchJobs_Widget($title, $wpjobportal_showtitle, $wpjobportal_layout = 'vertical', $wpjobportal_show_adv_button = true, $use_icons_for_buttons = false, $wpjobportal_field_custom_class = '',$wpjobportal_show_labels = 1,$wpjobportal_show_placeholders = 0, $wpjobportal_label_value = '', $placeholder_value = '') {
        // to handle button widths ( mainly for horizental style and advnce search diasble case for less then four fields)
        $button_wrap_class = '';
        // Set widths
        if ($wpjobportal_layout === 'vertical') {
            $wpjobportal_field_width = '100';
            $button_style = '100';
        } else {
            $button_style = $wpjobportal_show_adv_button ? 25 : 15;
            $button_wrap_class = $wpjobportal_show_adv_button ? '' : 'wpjobportal-search-btn-full-width';// to handle button widths ( mainly for horizental style and advnce search diasble case for less then four fields)
            $wpjobportal_field_width = round((100 - $button_style) , 2);
        }
        if(!function_exists('wpjobportal_renderCurrentFieldJP')){
            function wpjobportal_renderCurrentFieldJP($title_str, $wpjobportal_field_html, $wpjobportal_field_width) {
                $current_html = '<div class="wjportal-form-row " style="width:' . esc_attr($wpjobportal_field_width) . '%;">
                                    <div class="wjportal-form-tit">' . esc_html($title_str) . '</div>
                                    <div class="wjportal-form-val">' . wp_kses($wpjobportal_field_html, WPJOBPORTAL_ALLOWED_TAGS) . '</div>
                                </div>';
                return $current_html;
            }
        }


        $wpjobportal_layout_class = $wpjobportal_layout == 'horizontal' ? 'wjportal-form-horizontal' : 'wjportal-form-vertical';

        $wpjobportal_html = '<div id="wpjobportal_mod_wrapper" class="wjportal-search-mod wjportal-form-mod ' . esc_attr($wpjobportal_layout_class) . '">';

        $wpjobportal_html .= '<form class="job_form wjportal-form" id="job_form" method="post" action="' . esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobs', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageidForWidgets()))) . '">';


        if (1 == 1) {
            $title = '';
            $placeholder = '';
            if($wpjobportal_show_labels == 1){
                //$title = esc_html(__('AI Search Job', 'wp-job-portal'));
                $title = esc_html($wpjobportal_label_value);
            }
            if($wpjobportal_show_placeholders == 1){
                $placeholder = esc_html($placeholder_value);
            }
            $wpjobportal_value = WPJOBPORTALformfield::text('aijobsearcch', '', array('class' => 'inputbox'.' '.$wpjobportal_field_custom_class, 'placeholder' => $placeholder));
            $wpjobportal_html .= wpjobportal_renderCurrentFieldJP($title, $wpjobportal_value, $wpjobportal_field_width);
        }

        // Buttons
        $wpjobportal_search_label = $use_icons_for_buttons ? ' <i class="fa fa-search"></i> ' : esc_html(__('Search Job', 'wp-job-portal'));
        $adv_label = $use_icons_for_buttons ? ' <i class="fa fa-cogs"></i> ' : esc_html(__('Advance Search', 'wp-job-portal'));

        $wpjobportal_html .= '<div class="wjportal-form-btn-row '.esc_attr($button_wrap_class).' " style="width:' . $button_style . '%;"> ';
                if($wpjobportal_show_labels == 1){
                    $wpjobportal_html .='    <div class="wjportal-form-tit">&nbsp;</div>';
                }
        $wpjobportal_html .='
                    <button type="submit" class="wjportal-filter-search-btn">
                        ' . $wpjobportal_search_label . '
                    </button>
            ' . ( ($wpjobportal_show_adv_button) ? '<a class="anchor wjportal-form-btn wjportal-form-adv-srch-btn" href="' . esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobsearch', 'wpjobportallt'=>'jobsearch', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageidForWidgets()))) . '">' . $adv_label . '</a>' : '') . '
        </div>';

        $wpjobportal_html .= '
            <input type="hidden" id="issearchform" name="issearchform" value="1"/>
            <input type="hidden" id="WPJOBPORTAL_form_search" name="WPJOBPORTAL_form_search" value="WPJOBPORTAL_SEARCH"/>
            <input type="hidden" id="wpjobportallay" name="wpjobportallay" value="jobs"/>
        </form>
        </div>';

        wp_register_script( 'wpjobportal-inline-handle', '' );
        wp_enqueue_script( 'wpjobportal-inline-handle' );
        $wpjobportal_inline_js_script = '
            function getTokenInputWidget() {
                var cityArray = "' . esc_url_raw(admin_url("admin.php?page=wpjobportal_city&action=wpjobportaltask&task=getaddressdatabycityname")) . '";
                jQuery(".wpjobportal-job-search-widget-city-field").tokenInput(cityArray, {
                    theme: "wpjobportal",
                    preventDuplicates: true,
                    hintText: "' . esc_html(__('Type In A Search Term', 'wp-job-portal')) . '",
                    noResultsText: "' . esc_html(__('No Results', 'wp-job-portal')) . '",
                    searchingText: "' . esc_html(__('Searching', 'wp-job-portal')) . '"
                });
            }
            jQuery(document).ready(function(){
                getTokenInputWidget();
            });
        ';
        wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );

        return $wpjobportal_html;
    }


    function getJobSearchOptions() {
        wpjobportal::$_data[2] = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldsOrderingforSearch(2);
    }

    function getMessagekey(){
        $wpjobportal_key = 'jobsearch';if(wpjobportal::$_common->wpjp_isadmin()){$wpjobportal_key = 'admin_'.$wpjobportal_key;}return $wpjobportal_key;
    }
}

?>
