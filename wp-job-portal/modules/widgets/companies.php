<?php
if (!defined('ABSPATH')) {
    die('Restricted Access');
}

class WPJOBPORTALCORE_Company_Listing_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'wp_company_listing_widget',
            __('Company Listings', 'wp-job-portal'),
            array('description' => __('Display company listings with full customization.', 'wp-job-portal'))
        );
    }

    public function widget($args, $instance) {
        echo wp_kses_post($args['before_widget']);

        $settings = [
            'show_title'         => isset($instance['show_title']) ? (bool)$instance['show_title'] : true,
            'company_type'       => isset($instance['company_type']) ? $instance['company_type'] : 1,
            'show_company_name'  => isset($instance['show_company_name']) ? (bool)$instance['show_company_name'] : true,
            'show_logo'          => isset($instance['show_logo']) ? (bool)$instance['show_logo'] : true,
            'show_category'      => FALSE,
            //'show_category'    => isset($instance['show_category']) ? (bool)$instance['show_category'] : true,
            'show_location'      => isset($instance['show_location']) ? (bool)$instance['show_location'] : true,
            'show_posted'        => isset($instance['show_posted']) ? (bool)$instance['show_posted'] : true,
            'num_records'        => isset($instance['num_records']) ? $instance['num_records'] : 5,
            'layout'             => isset($instance['layout']) ? $instance['layout'] : 'list',
            'columns'            => isset($instance['columns']) ? $instance['columns'] : 1,
            'logo_width'         => isset($instance['logo_width']) ? $instance['logo_width'] : 150,
            'logo_height'        => isset($instance['logo_height']) ? $instance['logo_height'] : 90,
            'labels_for_values'  => isset($instance['labels_for_values']) ? $instance['labels_for_values'] : 0,
            'field_order'        => ['location', 'category', 'posted'],
            'show_view_button'        => isset($instance['show_view_button']) ? (bool)$instance['show_view_button'] : true,
            'show_noofjobs'        => isset($instance['show_noofjobs']) ? (bool)$instance['show_noofjobs'] : true
            //'field_order'        => isset($instance['field_order']) ? explode(',', $instance['field_order']) : ['location', 'category', 'posted']
        ];

        if ( $settings['show_title'] && !empty($instance['title'])) {
            echo wp_kses_post($args['before_title']) . esc_html($instance['title']) . wp_kses_post($args['after_title']);
        }

        // Get companies
        $companies = WPJOBPORTALincluder::getJSModel('company')->getCompanies_Widget($settings['company_type'], $settings['num_records']);
        wpjobportal::$_data['companies_widget'] = $companies;

        // Locate custom template
        $template_files = [
            "wp-job-portal/widget-featuredcompanies.php",
            'wp-job-portal/widget-companies.php'
        ];
        $defaulthtml = !locate_template($template_files, true, false);

        if ($defaulthtml && !empty($companies)) {
            $html = WPJOBPORTALincluder::getJSModel('wpjobportalwidgets')->wpjobportalRenderCompaniesTemplate($companies, $settings['layout'], $settings['columns'], $settings['show_company_name'], $settings['show_category'], $settings['show_location'], $settings['show_posted'], $settings['show_logo'], $settings['logo_width'], $settings['logo_height'], $settings['labels_for_values'], $settings['field_order'], $settings['show_view_button'], $settings['show_noofjobs']);

            echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built by the wpjobportalwidgets model, which escapes each value.
            wpjobportal::wpjobportal_addStyleSheets();
        }

        echo wp_kses_post($args['after_widget']);
    }

    public function form($instance) {
        $defaults = array(
            'show_title'         => 1,
            'title'              => __('Featured Companies', 'wp-job-portal'),
            'company_type'       => 1,
            'show_company_name'  => 1,
            'show_logo'          => 1,
            'show_category'     => 1,
            'show_location'     => 1,
            'show_posted'       => 1,
            'num_records'        => 5,
            'layout'            => 'list',
            'columns'           => 1,
            'logo_width'         => 150,
            'logo_height'        => 90,
            'labels_for_values'  => 0,
            'show_view_button'  => 1,
            'show_noofjobs'  => 1,
            'field_order'       => 'location,category,posted'
        );
        $instance = wp_parse_args((array) $instance, $defaults);

        $fields = array(
            'show_title' => ['label' => __('Show Title', 'wp-job-portal'), 'type' => 'checkbox'],
            'title' => ['label' => __('Title', 'wp-job-portal'), 'type' => 'text'],
            'company_type' => [
                'label' => __('Company Type', 'wp-job-portal'),
                'type' => 'select',
                'options' => [
                    '1' => __('Latest Companies', 'wp-job-portal'),
                    '2' => __('Featured Companies', 'wp-job-portal')
                ]
            ],
            'show_company_name' => ['label' => __('Show Company Name', 'wp-job-portal'), 'type' => 'checkbox'],
            'show_logo' => ['label' => __('Show Company Logo', 'wp-job-portal'), 'type' => 'checkbox'],
            //'show_category' => ['label' => __('Show Category', 'wp-job-portal'), 'type' => 'checkbox'],
            'show_location' => ['label' => __('Show Location', 'wp-job-portal'), 'type' => 'checkbox'],
            'show_posted' => ['label' => __('Show Posted Date', 'wp-job-portal'), 'type' => 'checkbox'],
            'show_view_button' => ['label' => __('Show View Company Button', 'wp-job-portal'), 'type' => 'checkbox'],
            'show_noofjobs' => ['label' => __('Show Number Of Jobs', 'wp-job-portal'), 'type' => 'checkbox'],
            'num_records' => ['label' => __('Number Of Companies', 'wp-job-portal'), 'type' => 'number', 'min' => 1, 'max' => 50],
            'layout' => [
                'label' => __('Layout', 'wp-job-portal'),
                'type' => 'select',
                'options' => [
                    'list' => __('List', 'wp-job-portal'),
                    'grid' => __('Grid', 'wp-job-portal')
                ]
            ],
            'columns' => [
                'label' => __('Columns', 'wp-job-portal'),
                'type' => 'select',
                'options' => [
                    '1' => 1,
                    '2' => 2,
                    '3' => 3,
                    '4' => 4
                ]
            ],
            'logo_width' => ['label' => __('Logo Width (px)', 'wp-job-portal'), 'type' => 'number', 'min' => 50, 'max' => 300],
            'logo_height' => ['label' => __('Logo Height (px)', 'wp-job-portal'), 'type' => 'number', 'min' => 50, 'max' => 300],
            'labels_for_values' => [
                'label' => __('Field Labels Display', 'wp-job-portal'),
                'type' => 'select',
                'options' => [
                    '0' => __('None', 'wp-job-portal'),
                    '1' => __('Text', 'wp-job-portal'),
                    '2' => __('Icons', 'wp-job-portal')
                ]
            ],
            // 'field_order' => [
            //     'label' => __('Field Order (comma separated)', 'wp-job-portal'),
            //     'type' => 'text',
            //     'desc' => __('Available fields: location, category, posted', 'wp-job-portal')
            // ]
        );

        foreach ($fields as $name => $field) {
            $value = isset($instance[$name]) ? $instance[$name] : '';
            $id = $this->get_field_id($name);
            $name_attr = $this->get_field_name($name);

            echo '<p style="margin-top:10px;">';
            switch ($field['type']) {
                case 'text':
                    echo '<label for="' . esc_attr($id) . '">' . esc_html($field['label']) . ':</label><br>';
                    echo '<input class="widefat" id="' . esc_attr($id) . '" name="' . esc_attr($name_attr) . '" type="text" value="' . esc_attr($value) . '">';
                    if (isset($field['desc'])) {
                        echo '<small>' . esc_html($field['desc']) . '</small>';
                    }
                    break;

                case 'number':
                    echo '<label for="' . esc_attr($id) . '">' . esc_html($field['label']) . ':</label><br>';
                    echo '<input class="widefat" id="' . esc_attr($id) . '" name="' . esc_attr($name_attr) . '" type="number"' . (isset($field['min']) ? ' min="' . esc_attr($field['min']) . '"' : '') . (isset($field['max']) ? ' max="' . esc_attr($field['max']) . '"' : '') . ' value="' . esc_attr($value) . '">';
                    break;

                case 'checkbox':
                    echo '<label for="' . esc_attr($id) . '">';
                    echo '<input id="' . esc_attr($id) . '" name="' . esc_attr($name_attr) . '" type="checkbox" ' . checked($value, 1, false) . ' value="1">';
                    echo esc_html($field['label']);
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
        $instance = $old_instance;

        $instance['show_title'] = !empty($new_instance['show_title']) ? 1 : 0;
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['company_type'] = absint($new_instance['company_type']);
        $instance['show_company_name'] = !empty($new_instance['show_company_name']) ? 1 : 0;
        $instance['show_logo'] = !empty($new_instance['show_logo']) ? 1 : 0;
        $instance['show_category'] = !empty($new_instance['show_category']) ? 1 : 0;
        $instance['show_location'] = !empty($new_instance['show_location']) ? 1 : 0;
        $instance['show_posted'] = !empty($new_instance['show_posted']) ? 1 : 0;
        $instance['num_records'] = absint($new_instance['num_records']);
        $instance['layout'] = sanitize_text_field($new_instance['layout']);
        $instance['columns'] = absint($new_instance['columns']);
        $instance['logo_width'] = absint($new_instance['logo_width']);
        $instance['logo_height'] = absint($new_instance['logo_height']);
        $instance['labels_for_values'] = absint($new_instance['labels_for_values']);
        $instance['show_view_button'] = !empty($new_instance['show_view_button']) ? 1 : 0;
        $instance['show_noofjobs'] = !empty($new_instance['show_noofjobs']) ? 1 : 0;


        // Validate field order
        if (!empty($new_instance['field_order'])) {
            $valid_fields = ['location', 'category', 'posted'];
            $submitted_fields = array_map('trim', explode(',', $new_instance['field_order']));
            $filtered_fields = array_intersect($submitted_fields, $valid_fields);
            $instance['field_order'] = implode(',', $filtered_fields);
        } else {
            $instance['field_order'] = 'location,category,posted';
        }

        return $instance;
    }
}

function wpjobportalcore_register_company_listing_widget() {
    register_widget('WPJOBPORTALCORE_Company_Listing_Widget');
}
add_action('widgets_init', 'wpjobportalcore_register_company_listing_widget');
