<?php
if (!defined('ABSPATH')) exit;

class WPJOBPORTALCORE_Resume_Listing_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'wp_resume_listing_widget',
            __('Resume Listings', 'wp-job-portal'),
            array('description' => __('Display resumes with simplified options.', 'wp-job-portal'))
        );
    }

    public function widget($args, $instance) {
        echo wp_kses_post($args['before_widget']);

        $settings = [
            'typeofresume'     => isset($instance['typeofresume']) ? $instance['typeofresume'] : 1,
            'showtitle'        => !empty($instance['showtitle']),
            'applicationtitle' => !empty($instance['applicationtitle']),
            'name'             => !empty($instance['name']),
            'category'         => !empty($instance['category']),
            'jobtype'          => !empty($instance['jobtype']),
            'experience'       => !empty($instance['experience']),
            'available'        => !empty($instance['available']),
            'gender'           => !empty($instance['gender']),
            'nationality'      => !empty($instance['nationality']),
            'location'         => !empty($instance['location']),
            'posted'           => !empty($instance['posted']),
            'resumephoto'      => !empty($instance['resumephoto']),
            'view_resume_button'      => !empty($instance['view_resume_button']),
            'noofresume'       => isset($instance['noofresume']) ? $instance['noofresume'] : 5,
            'displaystyle'     => isset($instance['displaystyle']) ? $instance['displaystyle'] : 'list',
            'photoheight'      => isset($instance['photoheight']) ? $instance['photoheight'] : 50,
            'photowidth'       => isset($instance['photowidth']) ? $instance['photowidth'] : 50,
            'columns'          => isset($instance['columns']) ? $instance['columns'] : 1,
            'labelstyle'       => isset($instance['labelstyle']) ? $instance['labelstyle'] : 2,
        ];

        if ($settings['showtitle'] && !empty($instance['title'])) {
            echo wp_kses_post($args['before_title']) . esc_html($instance['title']) . wp_kses_post($args['after_title']);
        }

        $resumes = WPJOBPORTALincluder::getJSModel('resume')->getResumes_Widget($settings['typeofresume'], $settings['noofresume']);
        wpjobportal::$wpjobportal_data['resumes_widget'] = $resumes;

        $template_files = [
            "wp-job-portal/widget-resume.php",
            "wp-job-portal/widget-resumes.php"
        ];
        $defaulthtml = !locate_template($template_files, true, false);

        if ($defaulthtml) {
            $html = WPJOBPORTALincluder::getJSModel('wpjobportalwidgets')->wpjobportalRenderResumesWidgets(
                $resumes,
                $settings['displaystyle'],
                (int)$settings['columns'],
                $settings['applicationtitle'],
                $settings['resumephoto'],
                $settings['name'],
                $settings['category'],
                $settings['jobtype'],
                $settings['experience'],
                $settings['available'],
                $settings['gender'],
                $settings['nationality'],
                $settings['location'],
                $settings['posted'],
                (int)$settings['photowidth'],
                (int)$settings['photoheight'],
                (int)$settings['labelstyle'],
                [], // field order
                0,   // elementor_call
                0, // view all button
                $settings['view_resume_button']  // view resume button

            );
            echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built by the wpjobportalwidgets model, which escapes each value.
            wpjobportal::wpjobportal_addStyleSheets();
        }

        echo wp_kses_post($args['after_widget']);
    }

    public function form($instance) {
        $defaults = array(
            'title'             => __('Latest Resumes', 'wp-job-portal'),
            'typeofresume'      => 1,
            'noofresume'        => 5,
            'displaystyle'      => 'list',
            'showtitle'         => 1,
            'applicationtitle'  => 1,
            'name'              => 1,
            'category'          => 1,
            'jobtype'           => 1,
            'experience'        => 1,
            'available'         => 1,
            'gender'            => 1,
            'nationality'       => 1,
            'location'          => 1,
            'posted'            => 1,
            'resumephoto'       => 1,
            'photoheight'       => 50,
            'photowidth'        => 50,
            'columns'           => 1,
            'labelstyle'        => 2
        );
        $instance = wp_parse_args((array) $instance, $defaults);

        $fields = [
            'showtitle' => ['label' => __('Show Title', 'wp-job-portal'), 'type' => 'checkbox'],
            'title' => ['label' => __('Title', 'wp-job-portal'), 'type' => 'text'],
            'typeofresume' => [
                'label' => __('Type of Resume', 'wp-job-portal'),
                'type' => 'select',
                'options' => [
                    '1' => __('Newest Resumes', 'wp-job-portal'),
                    '2' => __('Top Resumes', 'wp-job-portal'),
                    '4' => __('Featured Resumes', 'wp-job-portal')
                ]
            ],
            'noofresume' => ['label' => __('Number Of Resumes', 'wp-job-portal'), 'type' => 'number'],
            'displaystyle' => [
                'label' => __('Display Style', 'wp-job-portal'),
                'type' => 'select',
                'options' => [
                    'list' => __('List', 'wp-job-portal'),
                    'grid' => __('Card', 'wp-job-portal'),
                    //'card' => __('Card', 'wp-job-portal')
                ]
            ],
            'columns' => [
                'label' => __('Number of Columns', 'wp-job-portal'),
                'type' => 'select',
                'options' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4'
                ]
            ],
            'labelstyle' => [
                'label' => __('Show Labels for Values', 'wp-job-portal'),
                'type' => 'select',
                'options' => [
                    '1' => __('Text', 'wp-job-portal'),
                    '2' => __('Icons', 'wp-job-portal'),
                    '3' => __('None', 'wp-job-portal')
                ]
            ],
            'applicationtitle' => ['label' => __('Application Title', 'wp-job-portal'), 'type' => 'checkbox'],
            'name' => ['label' => __('Name', 'wp-job-portal'), 'type' => 'checkbox'],
            'category' => ['label' => __('Category', 'wp-job-portal'), 'type' => 'checkbox'],
            'jobtype' => ['label' => __('Job Type', 'wp-job-portal'), 'type' => 'checkbox'],
            //'experience' => ['label' => __('Experience', 'wp-job-portal'), 'type' => 'checkbox'],
            //'available' => ['label' => __('Available', 'wp-job-portal'), 'type' => 'checkbox'],
            //'gender' => ['label' => __('Gender', 'wp-job-portal'), 'type' => 'checkbox'],
            'nationality' => ['label' => __('Nationality', 'wp-job-portal'), 'type' => 'checkbox'],
            'location' => ['label' => __('Location', 'wp-job-portal'), 'type' => 'checkbox'],
            'posted' => ['label' => __('Posted Date', 'wp-job-portal'), 'type' => 'checkbox'],
            'resumephoto' => ['label' => __('Photo', 'wp-job-portal'), 'type' => 'checkbox'],
            'view_resume_button' => ['label' => __('View Resume Button', 'wp-job-portal'), 'type' => 'checkbox'],
            'photoheight' => ['label' => __('Photo Height', 'wp-job-portal'), 'type' => 'number'],
            'photowidth' => ['label' => __('Photo Width', 'wp-job-portal'), 'type' => 'number'],
        ];

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
                    echo '<input id="' . esc_attr($id) . '" name="' . esc_attr($name_attr) . '" type="checkbox" value="1" ' . checked($value, 1, false) . '> ';
                    echo esc_html($field['label']);
                    echo "</label>";
                    break;

                case 'select':
                    echo '<label for="' . esc_attr($id) . '">' . esc_html($field['label']) . ':</label><br>';
                    echo '<select class="widefat" id="' . esc_attr($id) . '" name="' . esc_attr($name_attr) . '">';
                    foreach ($field['options'] as $opt_val => $opt_label) {
                        echo '<option value="' . esc_attr($opt_val) . '" ' . selected($value, $opt_val, false) . '>' . esc_html($opt_label) . '</option>';
                    }
                    echo "</select>";
                    break;
            }
            echo '</p>';
        }
    }

    // public function update($new_instance, $old_instance) {
    //     $instance = [];
    //     $checkbox_fields = [
    //         'showtitle', 'applicationtitle', 'name', 'category', 'jobtype',
    //         'experience', 'available', 'gender', 'nationality', 'location',
    //         'posted', 'resumephoto'
    //     ];

    //     foreach ($new_instance as $key => $value) {
    //         if (in_array($key, $checkbox_fields)) {
    //             $instance[$key] = !empty($value) ? 1 : 0;
    //         } elseif (in_array($key, ['photoheight', 'photowidth', 'columns', 'labelstyle', 'noofresume', 'typeofresume'])) {
    //             $instance[$key] = intval($value);
    //         } else {
    //             $instance[$key] = sanitize_text_field($value);
    //         }
    //     }

    //     return $instance;
    // }

    public function update($new_instance, $old_instance) {
        $instance = $old_instance;

        // Checkbox fields (true/false)
        $instance['showtitle']        = !empty($new_instance['showtitle']) ? 1 : 0;
        $instance['applicationtitle'] = !empty($new_instance['applicationtitle']) ? 1 : 0;
        $instance['name']             = !empty($new_instance['name']) ? 1 : 0;
        $instance['category']         = !empty($new_instance['category']) ? 1 : 0;
        $instance['jobtype']          = !empty($new_instance['jobtype']) ? 1 : 0;
        $instance['experience']       = !empty($new_instance['experience']) ? 1 : 0;
        $instance['available']        = !empty($new_instance['available']) ? 1 : 0;
        $instance['gender']           = !empty($new_instance['gender']) ? 1 : 0;
        $instance['nationality']      = !empty($new_instance['nationality']) ? 1 : 0;
        $instance['location']         = !empty($new_instance['location']) ? 1 : 0;
        $instance['posted']           = !empty($new_instance['posted']) ? 1 : 0;
        $instance['resumephoto']      = !empty($new_instance['resumephoto']) ? 1 : 0;
        $instance['view_resume_button']      = !empty($new_instance['view_resume_button']) ? 1 : 0;

        // Integer fields
        $instance['photoheight']      = isset($new_instance['photoheight']) ? intval($new_instance['photoheight']) : 50;
        $instance['photowidth']       = isset($new_instance['photowidth']) ? intval($new_instance['photowidth']) : 50;
        $instance['columns']          = isset($new_instance['columns']) ? intval($new_instance['columns']) : 1;
        $instance['noofresume']       = isset($new_instance['noofresume']) ? intval($new_instance['noofresume']) : 5;
        $instance['typeofresume']     = isset($new_instance['typeofresume']) ? intval($new_instance['typeofresume']) : 1;
        $instance['displaystyle']     = isset($new_instance['displaystyle']) ? $new_instance['displaystyle'] : 'list';

        // Text field
        $instance['title']           = sanitize_text_field($new_instance['title']);
        $instance['labelstyle']       = sanitize_text_field($new_instance['labelstyle']);

        return $instance;
    }

}

function wpjobportalcore_register_resume_listing_widget() {
    register_widget('WPJOBPORTALCORE_Resume_Listing_Widget');
}
add_action('widgets_init', 'wpjobportalcore_register_resume_listing_widget');
