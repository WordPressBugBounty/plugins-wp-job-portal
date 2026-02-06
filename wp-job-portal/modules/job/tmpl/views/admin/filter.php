<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
* @param Multiple Filter wp-job-portal
*/
?>
<?php
$wpjobportal_html ='';
switch ($wpjobportal_layout) {
	case 'jobfilter':
		$wpjobportal_html.=''.WPJOBPORTALformfield::text('searchtitle', wpjobportal::$_data['filter']['searchtitle'], array('class' => 'inputbox wpjobportal-form-input-field', 'placeholder' => esc_html(__('Title', 'wp-job-portal')))).'';
        $wpjobportal_html.=''. WPJOBPORTALformfield::text('searchcompany', wpjobportal::$_data['filter']['searchcompany'], array('class' => 'inputbox wpjobportal-form-input-field', 'placeholder' => esc_html(__('Company','wp-job-portal')) .' '. esc_html(__('Name', 'wp-job-portal')))).'';
        $wpjobportal_html.=''. WPJOBPORTALformfield::select('searchjobcategory', WPJOBPORTALincluder::getJSModel('category')->getCategoriesForCombo('kb'), wpjobportal::$_data['filter']['searchjobcategory'], esc_html(__('Select','wp-job-portal')) .' '. esc_html(__('Category', 'wp-job-portal')), array('class' => 'inputbox wpjobportal-form-select-field')).'';
        $wpjobportal_html.=''. WPJOBPORTALformfield::select('searchjobtype', WPJOBPORTALincluder::getJSModel('jobtype')->getJobtypeForCombo('kb'), wpjobportal::$_data['filter']['searchjobtype'], esc_html(__('Select','wp-job-portal')) .' '. esc_html(__('Job Type', 'wp-job-portal')), array('class' => 'inputbox wpjobportal-form-select-field')).'';

        $wpjobportal_html.=''. WPJOBPORTALformfield::submitbutton('btnsubmit', esc_html(__('Search', 'wp-job-portal')), array('class' => 'button wpjobportal-form-search-btn')).'';
        $wpjobportal_html.=''.WPJOBPORTALformfield::button('reset', esc_html(__('Reset', 'wp-job-portal')), array('class' => 'button wpjobportal-form-reset-btn', 'onclick' => 'resetFrom();')).'';
        $wpjobportal_html.=''.WPJOBPORTALformfield::button('advanced_search', esc_html(__('Advanced Search', 'wp-job-portal')), array('class' => 'button wpjobportal-toggle-filter-btn', 'onclick' => 'toggleFilterFields();', 'data-search-title'=>esc_html(__('Advanced Search', 'wp-job-portal')),'data-hide-title'=>esc_html(__('Less Options', 'wp-job-portal')))).'';

        $wpjobportal_html.=' <div class="wpjobportal-extra-filter-fields">';
        $wpjobportal_html.=''. WPJOBPORTALformfield::text('location', wpjobportal::$_data['filter']['location'], array('class' => 'inputbox wpjobportal-form-input-field', 'placeholder' => esc_html(__('Location', 'wp-job-portal')))).'';
        $wpjobportal_html.=''. WPJOBPORTALformfield::text('datestart', wpjobportal::$_data['filter']['datestart'], array('class' => 'custom_date wpjobportal-form-input-field', 'placeholder' => esc_html(__('Date Start', 'wp-job-portal')), 'autocomplete' => 'off')).'';
        $wpjobportal_html.=''. WPJOBPORTALformfield::text('dateend', wpjobportal::$_data['filter']['dateend'], array('class' => 'custom_date wpjobportal-form-input-field', 'placeholder' => esc_html(__('Date End', 'wp-job-portal')), 'autocomplete' => 'off')).'';
            if(empty($wpjobportal_show)){
                $wpjobportal_html.=''. WPJOBPORTALformfield::select('status', WPJOBPORTALincluder::getJSModel('common')->getListingStatus(), wpjobportal::$_data['filter']['status'], esc_html(__('Select Status', 'wp-job-portal')), array('class' => 'inputbox wpjobportal-form-select-field')).'';
            }

            if(in_array('featuredjob', wpjobportal::$_active_addons)){
                $wpjobportal_html .= '<div class="wpjobportal-form-checkbox-field">';
                        $wpjobportal_html .=  WPJOBPORTALformfield::checkbox('featured', array('1' => esc_html(__('Featured', 'wp-job-portal'))), isset(wpjobportal::$_data['filter']['featured']) ? wpjobportal::$_data['filter']['featured'] : 0, array('class' => 'checkbox'));
                $wpjobportal_html .= '</div>';
            }
        $wpjobportal_html.=' </div>';
       $wpjobportal_html.=''.WPJOBPORTALformfield::hidden('WPJOBPORTAL_form_search', 'WPJOBPORTAL_SEARCH').'';
       $wpjobportal_html.=''. WPJOBPORTALformfield::hidden('sortby', wpjobportal::$_data['sortby']).'';
       $wpjobportal_html.=''. WPJOBPORTALformfield::hidden('sorton', wpjobportal::$_data['sorton']).'';
        
  break;
}
echo wp_kses($wpjobportal_html, WPJOBPORTAL_ALLOWED_TAGS);
?>
