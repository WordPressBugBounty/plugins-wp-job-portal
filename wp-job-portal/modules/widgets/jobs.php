<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
class WPJOBPORTALCORE_Job_Listing_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'wp_job_listing_widget',
            __('Job Listing', 'wp-job-portal'),
            array('description' => __('Display job listings with full customization.', 'wp-job-portal'))
        );
    }

    public function widget($args, $instance) {
        echo wp_kses_post($args['before_widget']);

        if (!empty($instance['title'])) {
            echo wp_kses_post($args['before_title']) . esc_html($instance['title']) . wp_kses_post($args['after_title']);
        }

        $settings = [
            'show_jobtitle'      => !empty($instance['show_jobtitle']),
            'show_company_name'  => !empty($instance['show_company_name']),
            'show_company_logo'  => !empty($instance['show_company_logo']),
            'show_job_category'  => !empty($instance['show_job_category']),
            'show_jobtype'       => !empty($instance['show_jobtype']),
            'show_salary'        => !empty($instance['show_salary']),
            'show_stoppublishing'=> !empty($instance['show_stoppublishing']),
            'show_careerlevel'   => !empty($instance['show_careerlevel']),
            'show_location'      => !empty($instance['show_location']),
            'show_posted'        => !empty($instance['show_posted']),
            'logo_width'         => isset($instance['logo_width']) ? $instance['logo_width'] : 100,
            'logo_height'        => isset($instance['logo_height']) ? $instance['logo_height'] : 100,
            'num_records'        => isset($instance['num_records']) ? $instance['num_records'] : 6,
            'layout'             => isset($instance['layout']) ? $instance['layout'] : 'list',
            'num_of_columns'     => isset($instance['num_of_columns']) ? $instance['num_of_columns'] : 1,
            'typeofjobs'         => isset($instance['typeofjobs']) ? $instance['typeofjobs'] : 1,
            'labels_for_values'  => isset($instance['labels_for_values']) ? $instance['labels_for_values'] : 0,
        ];

        // Determine job type slug
        $mod_map = [
            1 => 'newestjobs',
            2 => 'topjobs',
            3 => 'hotjobs',
            5 => 'featuredjobs'
        ];
        $mod = $mod_map[$settings['typeofjobs']] ?? 'jobs';

        // Get jobs
        $jobs = WPJOBPORTALincluder::getJSModel('job')->getJobs_Widget($settings['typeofjobs'], $settings['num_records']);
        wpjobportal::$_data['jobs_widget'] = $jobs;

        // Locate custom template
        $template_files = [
            "wp-job-portal/widget-{$mod}.php",
            'wp-job-portal/widget-jobs.php'
        ];
        $defaulthtml = !locate_template($template_files, true, false);

        if ($defaulthtml) {
            $html = WPJOBPORTALincluder::getJSModel('wpjobportalwidgets')->wpjobportalRenderJobsTemplate($jobs, $settings['layout'], $settings['num_of_columns'], $settings['show_jobtitle'], $settings['show_company_name'], $settings['show_location'], $settings['show_jobtype'], $settings['show_salary'], $settings['show_stoppublishing'], $settings['show_careerlevel'], $settings['show_posted'], $settings['show_job_category'], $settings['show_company_logo'], $settings['logo_width'], $settings['logo_height'], $settings['labels_for_values']);

            echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built by the wpjobportalwidgets model, which escapes each value.
            //echo wp_kses($html, WPJOBPORTAL_ALLOWED_TAGS);
            wpjobportal::wpjobportal_addStyleSheets();
        }

        echo wp_kses_post($args['after_widget']);
    }

    public function form($instance) {
        $defaults = array(
            'title'              => __('Latest Jobs', 'wp-job-portal'),
            'show_jobtitle'      => 1,
            'show_company_name'  => 1,
            'show_company_logo'  => 1,
            'show_job_category'  => 1,
            'show_jobtype'       => 1,
            'show_salary'        => 1,
            'show_stoppublishing'=> 1,
            'show_careerlevel'   => 1,
            'show_location'      => 1,
            'show_posted'        => 1,
            'show_salary'        => 1,
            'logo_width'         => 100,
            'logo_height'        => 100,
            'num_records'        => 5,
            'layout'             => 'list',
            'num_of_columns'     => 1,
            'typeofjobs'         => 1,
            'labels_for_values'  => 0,
        );
        $instance = wp_parse_args((array) $instance, $defaults);

        $fields = array(
            'title'              => ['label' => __('Title', 'wp-job-portal'), 'type' => 'text'],
            'typeofjobs'         => ['label' => __('Job Type', 'wp-job-portal'), 'type' => 'select', 'options' => [
                '1' => __('Newest Jobs', 'wp-job-portal'),
                '2' => __('Top Jobs', 'wp-job-portal'),
                '3' => __('Hot Jobs', 'wp-job-portal'),
                '5' => __('Featured Jobs', 'wp-job-portal'),
            ]],
            'show_jobtitle'      => ['label' => __('Show Job Title', 'wp-job-portal'), 'type' => 'checkbox'],
            'show_company_logo'  => ['label' => __('Show Company Logo', 'wp-job-portal'), 'type' => 'checkbox'],
            'show_company_name'  => ['label' => __('Show Company Name', 'wp-job-portal'), 'type' => 'checkbox'],
            'show_salary'        => ['label' => __('Show Salary', 'wp-job-portal'), 'type' => 'checkbox'],
            'show_location'      => ['label' => __('Show Location', 'wp-job-portal'), 'type' => 'checkbox'],
            'show_jobtype'       => ['label' => __('Show Job Type', 'wp-job-portal'), 'type' => 'checkbox'],
            'show_job_category'  => ['label' => __('Show Job Category', 'wp-job-portal'), 'type' => 'checkbox'],
            'show_careerlevel'   => ['label' => __('Show Career Level', 'wp-job-portal'), 'type' => 'checkbox'],
            'show_posted'        => ['label' => __('Show Posted Date', 'wp-job-portal'), 'type' => 'checkbox'],
            'show_stoppublishing'=> ['label' => __('Show Apply Before Date', 'wp-job-portal'), 'type' => 'checkbox'],
            'logo_width'         => ['label' => __('Logo Width (px)', 'wp-job-portal'), 'type' => 'number'],
            'logo_height'        => ['label' => __('Logo Height (px)', 'wp-job-portal'), 'type' => 'number'],
            'num_records'        => ['label' => __('Number Of Jobs to Show', 'wp-job-portal'), 'type' => 'number'],
            'layout'             => ['label' => __('Layout', 'wp-job-portal'), 'type' => 'select', 'options' => [
                'list' => __('List', 'wp-job-portal'),
                //'grid' => __('Grid', 'wp-job-portal'),
                'card' => __('Card', 'wp-job-portal')
            ]],
            'num_of_columns'     => ['label' => __('Columns per Row', 'wp-job-portal'), 'type' => 'select', 'options' => [
                '1' => 1, '2' => 2, '3' => 3, '4' => 4
            ]],
            'labels_for_values'             => ['label' => __('Labels For Values', 'wp-job-portal'), 'type' => 'select', 'options' => [
                '0' => __('None', 'wp-job-portal'),
                '1' => __('Text', 'wp-job-portal'),
                '2' => __('Icons', 'wp-job-portal')
            ]],
        );

        foreach ($fields as $name => $field) {
            $value = $instance[$name] ?? '';
            $id = $this->get_field_id($name);
            $name_attr = $this->get_field_name($name);


            echo '<p style="margin-top:10px;">';
            switch ($field['type']) {
                case 'text':
                case 'number':
                    echo '<label for="' . esc_attr($id) . '">' . esc_html($field['label']) . ':</label><br>';
                    echo '<input class="widefat" id="' . esc_attr($id) . '" name="' . esc_attr($name_attr) . '" type="' . esc_attr($field['type']) . '" value="' . esc_attr($value) . '">';
                    break;

                case 'checkbox':
                    echo '<label for="' . esc_attr($id) . '">';
                    echo '<input id="' . esc_attr($id) . '" name="' . esc_attr($name_attr) . '" type="checkbox" ' . checked($value, 1, false) . ' value="1">';
                    echo  esc_html($field['label']);
                    echo '</label>';
                    break;

                case 'select':
                    echo '<label for="' . esc_attr($id) . '">' . esc_html($field['label']) . ':</label><br>';
                    echo '<select class="widefat" id="' . esc_attr($id) . '" name="' . esc_attr($name_attr) . '">';
                    foreach ($field['options'] as $opt_val => $opt_label) {
                        echo '<option value="' . esc_attr($opt_val) . '" ' . selected($value, $opt_val, false) . '>' . esc_html($opt_label) . '</option>';
                    }
                    echo '</select>';
                    break;
            }

            echo '</p>';
        }
    }

    public function update($new_instance, $old_instance) {
        // $instance = [];

        // foreach ($new_instance as $key => $value) {
        //     $instance[$key] = in_array($key, [
        //         'show_jobtitle', 'show_company_name', 'show_company_logo',
        //         'show_job_category', 'show_jobtype', 'show_location', 'show_posted',
        //         'show_salary', 'show_stoppublishing', 'show_careerlevel'
        //     ]) ? (!empty($value) ? 1 : 0) : sanitize_text_field($value);
        // }

        // // echo '<pre>';print_r($instance);echo '</pre>';
        // // exit;

        $instance = $old_instance;

        $instance['show_jobtitle']       = !empty($new_instance['show_jobtitle']) ? 1 : 0;
        $instance['show_company_name']   = !empty($new_instance['show_company_name']) ? 1 : 0;
        $instance['show_company_logo']   = !empty($new_instance['show_company_logo']) ? 1 : 0;
        $instance['show_job_category']   = !empty($new_instance['show_job_category']) ? 1 : 0;
        $instance['show_jobtype']        = !empty($new_instance['show_jobtype']) ? 1 : 0;
        $instance['show_location']       = !empty($new_instance['show_location']) ? 1 : 0;
        $instance['show_posted']         = !empty($new_instance['show_posted']) ? 1 : 0;
        $instance['show_salary']         = !empty($new_instance['show_salary']) ? 1 : 0;
        $instance['show_stoppublishing'] = !empty($new_instance['show_stoppublishing']) ? 1 : 0;
        $instance['show_careerlevel']    = !empty($new_instance['show_careerlevel']) ? 1 : 0;

        // Add any other non-boolean fields here
        $instance['typeofjobs']       = !empty($new_instance['typeofjobs']) ? $new_instance['typeofjobs'] : 1;
        $instance['title']               = sanitize_text_field($new_instance['title']);
        $instance['num_records']         = absint($new_instance['num_records']);
        $instance['layout']              = sanitize_text_field($new_instance['layout']);
        $instance['columns']             = absint($new_instance['num_of_columns']);
        $instance['logo_width']               = sanitize_text_field($new_instance['logo_width']);
        $instance['logo_height']               = sanitize_text_field($new_instance['logo_height']);
        $instance['num_of_columns']             = absint($new_instance['num_of_columns']);
        $instance['labels_for_values']       = isset($new_instance['labels_for_values']) ? $new_instance['labels_for_values'] : 0;

        return $instance;
    }
}

function wpjobportalcore_register_job_listing_widget() {
    register_widget('WPJOBPORTALCORE_Job_Listing_Widget');
}
add_action('widgets_init', 'wpjobportalcore_register_job_listing_widget');
