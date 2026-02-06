<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALjobssearchjobs_widget extends WP_Widget {

    function __construct() {
        parent::__construct(
            'WPJOBPORTALjobssearchjobs_widget',
            esc_html(__('Job Search', 'wp-job-portal')),
            array(
                'description' => esc_html(__('Search jobs form WP Job Portal database', 'wp-job-portal')),
                'show_instance_in_rest' => true,
            )
        );
    }

    public function widget($wpjobportal_args, $wpjobportal_instance) {
        $wpjobportal_defaults = [
            'title' => esc_html__('Search Job', 'wp-job-portal'),
            'showtitle' => 1,
            'jobtitle' => 1,
            'category' => 1,
            'jobtype' => 1,
            'jobstatus' => 0,
            'salaryrange' => 0,
            'shift' => 0,
            'duration' => 0,
            'company' => 1,
            'address' => 1,
            'columnperrow' => 1,
            'layout' => 'vertical',
            'show_adv_button' => 1,
            'use_icons_for_buttons' => 0,
            //'custom_css_classes' => '',
            'field_custom_class' => '',
            'show_labels' => 1,
            'show_placeholders' => 0,
        ];
        $wpjobportal_instance = wp_parse_args($wpjobportal_instance, $wpjobportal_defaults);

        echo wp_kses($wpjobportal_args['before_widget'], WPJOBPORTAL_ALLOWED_TAGS);
        if (!empty($wpjobportal_instance['title']) && $wpjobportal_instance['showtitle']) {
            echo wp_kses($wpjobportal_args['before_title'], WPJOBPORTAL_ALLOWED_TAGS) .
                 esc_html($wpjobportal_instance['title']) .
                 wp_kses($wpjobportal_args['after_title'], WPJOBPORTAL_ALLOWED_TAGS);
        }

        if (defined('REST_REQUEST') && REST_REQUEST) {
            ob_start();
        }

        if (!locate_template('wp-job-portal/widget-searchjobs.php', true, true)) {
            wpjobportal::wpjobportal_addStyleSheets();
            $wpjobportal_modules_html = WPJOBPORTALincluder::getJSModel('jobsearch')->getSearchJobs_Widget(
                $wpjobportal_instance['title'],
                $wpjobportal_instance['showtitle'],
                $wpjobportal_instance['jobtitle'],
                $wpjobportal_instance['category'],
                $wpjobportal_instance['jobtype'],
                $wpjobportal_instance['jobstatus'],
                $wpjobportal_instance['salaryrange'],
                $wpjobportal_instance['shift'],
                $wpjobportal_instance['duration'],
                0, // startpublishing
                0, // stoppublishing
                $wpjobportal_instance['company'],
                $wpjobportal_instance['address'],
                $wpjobportal_instance['columnperrow'],
                $wpjobportal_instance['layout'],
                $wpjobportal_instance['show_adv_button'],
                $wpjobportal_instance['use_icons_for_buttons'],
                $wpjobportal_instance['field_custom_class'],
                $wpjobportal_instance['show_labels'],
                $wpjobportal_instance['show_placeholders']
            );
            echo wp_kses($wpjobportal_modules_html,WPJOBPORTAL_ALLOWED_TAGS);
        }

        if (defined('REST_REQUEST') && REST_REQUEST) {
            return ob_get_clean();
        }

        echo wp_kses($wpjobportal_args['after_widget'], WPJOBPORTAL_ALLOWED_TAGS);
    }

    public function form($wpjobportal_instance) {
        $wpjobportal_instance = wp_parse_args((array) $wpjobportal_instance, array(
            'title' => '',
            'showtitle' => 1,
            'jobtitle' => 1,
            'category' => 1,
            'jobtype' => 1,
            'jobstatus' => 0,
            'salaryrange' => 0,
            'shift' => 0,
            'duration' => 0,
            'company' => 1,
            'address' => 1,
            'columnperrow' => 1,
            'layout' => 'vertical',
            'show_adv_button' => 1,
            'use_icons_for_buttons' => 0,
            'field_custom_class' => '',
            'show_labels' => 1,
            'show_placeholders' => 0,
        ));

        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php echo esc_html__('Title', 'wp-job-portal'); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($wpjobportal_instance['title']); ?>" />
        </p>

        <p>
            <input id="<?php echo esc_attr($this->get_field_id('showtitle')); ?>" name="<?php echo esc_attr($this->get_field_name('showtitle')); ?>" type="checkbox" value="1" <?php checked($wpjobportal_instance['showtitle'], 1); ?> />
            <label for="<?php echo esc_attr($this->get_field_id('showtitle')); ?>">
                <?php echo esc_html__('Show Title', 'wp-job-portal'); ?>
            </label>
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('layout')); ?>">
                <?php echo esc_html__('Layout', 'wp-job-portal'); ?>
            </label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('layout')); ?>" name="<?php echo esc_attr($this->get_field_name('layout')); ?>">
                <option value="vertical" <?php selected($wpjobportal_instance['layout'], 'vertical'); ?>>
                    <?php echo esc_html__('Vertical', 'wp-job-portal'); ?>
                </option>
                <option value="horizontal" <?php selected($wpjobportal_instance['layout'], 'horizontal'); ?>>
                    <?php echo esc_html__('Horizontal', 'wp-job-portal'); ?>
                </option>
            </select>
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('columnperrow')); ?>">
                <?php echo esc_html__('Columns per Row', 'wp-job-portal'); ?>
            </label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('columnperrow')); ?>" name="<?php echo esc_attr($this->get_field_name('columnperrow')); ?>">
                <option value="1" <?php if (esc_attr($wpjobportal_instance['columnperrow']) == 1) echo "selected"; ?>><?php echo esc_html(__('1 Column', 'wp-job-portal')); ?></option>
                <option value="2" <?php if (esc_attr($wpjobportal_instance['columnperrow']) == 2) echo "selected"; ?>><?php echo esc_html(__('2 Columns', 'wp-job-portal')); ?></option>
                <option value="3" <?php if (esc_attr($wpjobportal_instance['columnperrow']) == 3) echo "selected"; ?>><?php echo esc_html(__('3 Columns', 'wp-job-portal')); ?></option>
                <option value="4" <?php if (esc_attr($wpjobportal_instance['columnperrow']) == 4) echo "selected"; ?>><?php echo esc_html(__('4 Columns', 'wp-job-portal')); ?></option>
                <option value="5" <?php if (esc_attr($wpjobportal_instance['columnperrow']) == 5) echo "selected"; ?>><?php echo esc_html(__('5 Columns', 'wp-job-portal')); ?></option>
            </select>
        </p>

        <h3 style="margin-top:10px; font-weight: bold;">
            <?php echo esc_html__('Search Fields', 'wp-job-portal'); ?>
        </h3>

        <?php
        // Define fields to show as checkboxes
        $wpjobportal_checkbox_fields = array(
            'jobtitle' => __('Job Title', 'wp-job-portal'),
            'company' => __('Company', 'wp-job-portal'),
            'category' => __('Job Category', 'wp-job-portal'),
            'jobtype' => __('Job Type', 'wp-job-portal'),
            'address' => __('City', 'wp-job-portal'),
            'show_labels' => __('Show Labels', 'wp-job-portal'),
            'show_placeholders' => __('Show Placeholders', 'wp-job-portal'),
            'use_icons_for_buttons' => __('Use Icons for Buttons', 'wp-job-portal'),
            'show_adv_button' => __('Show Advanced Search Button', 'wp-job-portal'),
        );

        foreach ($wpjobportal_checkbox_fields as $wpjobportal_field => $wpjobportal_label): ?>
            <p>
                <input id="<?php echo esc_attr($this->get_field_id($wpjobportal_field)); ?>" name="<?php echo esc_attr($this->get_field_name($wpjobportal_field)); ?>" type="checkbox" value="1" <?php checked($wpjobportal_instance[$wpjobportal_field], 1); ?> />
                <label for="<?php echo esc_attr($this->get_field_id($wpjobportal_field)); ?>">
                    <?php echo esc_html($wpjobportal_label); ?>
                </label>
            </p>
        <?php endforeach; ?>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('field_custom_class')); ?>">
                <?php echo esc_html__('Custom Field Class', 'wp-job-portal'); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('field_custom_class')); ?>" name="<?php echo esc_attr($this->get_field_name('field_custom_class')); ?>" type="text" value="<?php echo esc_attr($wpjobportal_instance['field_custom_class']); ?>" />
        </p>
        <?php
    }

    public function update($wpjobportal_new_instance, $old_instance) {
        $wpjobportal_instance = array();

        // Text/select fields
        $wpjobportal_text_fields = array(
            'title', 'layout', 'columnperrow', 'field_custom_class'
        );
        foreach ($wpjobportal_text_fields as $wpjobportal_field) {
            $wpjobportal_instance[$wpjobportal_field] = !empty($wpjobportal_new_instance[$wpjobportal_field]) ? sanitize_text_field($wpjobportal_new_instance[$wpjobportal_field]) : '';
        }

        // Checkbox fields
        $wpjobportal_checkbox_fields = array(
            'showtitle', 'jobtitle', 'category', 'jobtype', 'jobstatus',
            'salaryrange', 'shift', 'duration', 'company', 'address',
            'show_adv_button', 'use_icons_for_buttons', 'show_labels', 'show_placeholders'
        );
        foreach ($wpjobportal_checkbox_fields as $wpjobportal_field) {
            $wpjobportal_instance[$wpjobportal_field] = !empty($wpjobportal_new_instance[$wpjobportal_field]) ? 1 : 0;
        }

        return $wpjobportal_instance;
    }
}

function WPJOBPORTALjobssearchjobs_load_widget() {
    register_widget('WPJOBPORTALjobssearchjobs_widget');
}
add_action('widgets_init', 'WPJOBPORTALjobssearchjobs_load_widget');