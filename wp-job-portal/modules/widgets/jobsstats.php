<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

// Creating the widget
class WPJOBPORTALCOREjobsstats_widget extends WP_Widget {

    function __construct() {
        parent::__construct(
                // Base ID of your widget
                'WPJOBPORTALjobsstats_widget',
                // Widget name will appear in UI
                __('Jobs stats', 'wp-job-portal'),
                // Widget description
                array('description' => __('Jobs stats from WP Job Portal database', 'wp-job-portal'),)
        );
    }

    // Creating widget front-end
    // This is where the action happens
    public function widget($args, $instance) {
        // old code was casuing error
        $title       = apply_filters('widget_title', isset($instance['title']) ? $instance['title'] : '');
        $show_title  = !empty($instance['showtitle']) ? 1 : 0;
        $showtitle  = !empty($instance['showtitle']) ? 1 : 0;
        $employer    = isset($instance['employer']) ? $instance['employer'] : 0;
        $jobseeker   = isset($instance['jobseeker']) ? $instance['jobseeker'] : 0;
        $jobs        = isset($instance['jobs']) ? $instance['jobs'] : 0;
        $companies   = isset($instance['companies']) ? $instance['companies'] : 0;
        $activejobs  = isset($instance['activejobs']) ? $instance['activejobs'] : 0;
        $resumes     = isset($instance['resumes']) ? $instance['resumes'] : 0;
        $todaystats  = isset($instance['todaystats']) ? $instance['todaystats'] : 0;

        $color1 = isset($instance['titlecolor']) ? $instance['titlecolor'] : '';
        $color2 = isset($instance['backgroundcolor']) ? $instance['backgroundcolor'] : '';
        $color3 = isset($instance['bordercolor']) ? $instance['bordercolor'] : '';

        // before and after widget arguments are defined by themes
        echo wp_kses($args['before_widget'], WPJOBPORTAL_ALLOWED_TAGS);
        if (!empty($title) && $showtitle == 1)
            echo wp_kses($args['before_title'] . $title . $args['after_title'], WPJOBPORTAL_ALLOWED_TAGS);

        // This is where you run the code and display the output
        //Frontend HTML starts

        $classname = 'stats' . uniqid();
        wpjobportal::$_data['stats_widget'] = WPJOBPORTALincluder::getJSModel('common')->getJobsStats_Widget($classname, $title, $showtitle, $employer, $jobseeker, $jobs, $companies, $activejobs, $resumes, $todaystats);

        if (locate_template('wp-job-portal/widget-stats.php', 1, 0)) {
            $defaulthtml = false;
        }else{
            $modules_html = WPJOBPORTALincluder::getJSModel('common')->listModuleJobsStats($classname, $title, $showtitle, $employer, $jobseeker, $jobs, $companies, $activejobs, $resumes, $todaystats,wpjobportal::$_data['stats_widget']);
            echo wp_kses($modules_html, WPJOBPORTAL_ALLOWED_TAGS);
            wpjobportal::wpjobportal_addStyleSheets();
            $echo_style = WPJOBPORTALincluder::getJSModel('wpjobportalwidgets')->prepareStyleForStats($classname, $color1, $color2, $color3);
            echo wp_kses($echo_style, WPJOBPORTAL_ALLOWED_TAGS);
        }
        //Frontend HTML ends -------------
        // before and after widget arguments are defined by themes
        echo wp_kses($args['after_widget'], WPJOBPORTAL_ALLOWED_TAGS);
    }

    // Widget Backend
    public function form($instance) {
        $title = (isset($instance['title'])) ? $instance['title'] : __('Stats', 'wp-job-portal');
        $showtitle = (isset($instance['showtitle'])) ? $instance['showtitle'] : 1;
        $employer = (isset($instance['employer'])) ? $instance['employer'] : 1;
        $jobseeker = (isset($instance['jobseeker'])) ? $instance['jobseeker'] : 1;
        $jobs = (isset($instance['jobs'])) ? $instance['jobs'] : 1;
        $companies = (isset($instance['companies'])) ? $instance['companies'] : 1;
        $activejobs = (isset($instance['activejobs'])) ? $instance['activejobs'] : 1;
        $resumes = (isset($instance['resumes'])) ? $instance['resumes'] : 1;
        $todaystats = (isset($instance['todaystats'])) ? $instance['todaystats'] : 1;

        $titlecolor = (isset($instance['titlecolor'])) ? $instance['titlecolor'] : '';
        $backgroundcolor = (isset($instance['backgroundcolor'])) ? $instance['backgroundcolor'] : '';
        $bordercolor = (isset($instance['bordercolor'])) ? $instance['bordercolor'] : '';
        ?>
        <!-- widgets admin form options -->
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php echo esc_html__('Title', 'wp-job-portal'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('showtitle')); ?>"><?php echo esc_html__('Show Title', 'wp-job-portal'); ?></label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('showtitle')); ?>" name="<?php echo esc_attr($this->get_field_name('showtitle')); ?>">
                <option value="0" <?php if (esc_attr($showtitle) == 0) echo "selected"; ?>><?php echo esc_html__('Hide', 'wp-job-portal'); ?></option>
                <option value="1" <?php if (esc_attr($showtitle) == 1) echo "selected"; ?>><?php echo esc_html__('Show', 'wp-job-portal'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('employer')); ?>"><?php echo esc_html__('Employer', 'wp-job-portal'); ?></label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('employer')); ?>" name="<?php echo esc_attr($this->get_field_name('employer')); ?>">
                <option value="0" <?php if (esc_attr($employer) == 0) echo "selected"; ?>><?php echo esc_html__('Hide', 'wp-job-portal'); ?></option>
                <option value="1" <?php if (esc_attr($employer) == 1) echo "selected"; ?>><?php echo esc_html__('Show', 'wp-job-portal'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('jobseeker')); ?>"><?php echo esc_html__('Job Seeker', 'wp-job-portal'); ?></label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('jobseeker')); ?>" name="<?php echo esc_attr($this->get_field_name('jobseeker')); ?>">
                <option value="0" <?php if (esc_attr($jobseeker) == 0) echo "selected"; ?>><?php echo esc_html__('Hide', 'wp-job-portal'); ?></option>
                <option value="1" <?php if (esc_attr($jobseeker) == 1) echo "selected"; ?>><?php echo esc_html__('Show', 'wp-job-portal'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('jobs')); ?>"><?php echo esc_html__('Jobs', 'wp-job-portal'); ?></label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('jobs')); ?>" name="<?php echo esc_attr($this->get_field_name('jobs')); ?>">
                <option value="0" <?php if (esc_attr($jobs) == 0) echo "selected"; ?>><?php echo esc_html__('Hide', 'wp-job-portal'); ?></option>
                <option value="1" <?php if (esc_attr($jobs) == 1) echo "selected"; ?>><?php echo esc_html__('Show', 'wp-job-portal'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('activejobs')); ?>"><?php echo esc_html__('Active Jobs', 'wp-job-portal'); ?></label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('activejobs')); ?>" name="<?php echo esc_attr($this->get_field_name('activejobs')); ?>">
                <option value="0" <?php if (esc_attr($activejobs) == 0) echo "selected"; ?>><?php echo esc_html__('Hide', 'wp-job-portal'); ?></option>
                <option value="1" <?php if (esc_attr($activejobs) == 1) echo "selected"; ?>><?php echo esc_html__('Show', 'wp-job-portal'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('companies')); ?>"><?php echo esc_html__('Companies', 'wp-job-portal'); ?></label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('companies')); ?>" name="<?php echo esc_attr($this->get_field_name('companies')); ?>">
                <option value="0" <?php if (esc_attr($companies) == 0) echo "selected"; ?>><?php echo esc_html__('Hide', 'wp-job-portal'); ?></option>
                <option value="1" <?php if (esc_attr($companies) == 1) echo "selected"; ?>><?php echo esc_html__('Show', 'wp-job-portal'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('resumes')); ?>"><?php echo esc_html__('Resumes', 'wp-job-portal'); ?></label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('resumes')); ?>" name="<?php echo esc_attr($this->get_field_name('resumes')); ?>">
                <option value="0" <?php if (esc_attr($resumes) == 0) echo "selected"; ?>><?php echo esc_html__('Hide', 'wp-job-portal'); ?></option>
                <option value="1" <?php if (esc_attr($resumes) == 1) echo "selected"; ?>><?php echo esc_html__('Show', 'wp-job-portal'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('todaystats')); ?>"><?php echo esc_html__('Today','wp-job-portal') .' '. esc_html__('Stats', 'wp-job-portal'); ?></label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('todaystats')); ?>" name="<?php echo esc_attr($this->get_field_name('todaystats')); ?>">
                <option value="0" <?php if (esc_attr($todaystats) == 0) echo "selected"; ?>><?php echo esc_html__('Hide', 'wp-job-portal'); ?></option>
                <option value="1" <?php if (esc_attr($todaystats) == 1) echo "selected"; ?>><?php echo esc_html__('Show', 'wp-job-portal'); ?></option>
            </select>
        </p>
        <p>
            <label class="wpjobportalwidefat" for="<?php echo esc_attr($this->get_field_id('titlecolor')); ?>"><?php echo esc_html__('Title color', 'wp-job-portal'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('titlecolor')); ?>" name="<?php echo esc_attr($this->get_field_name('titlecolor')); ?>" type="text" value="<?php echo esc_attr($titlecolor); ?>" />
        </p>
        <p>
            <label class="wpjobportalwidefat" for="<?php echo esc_attr($this->get_field_id('backgroundcolor')); ?>"><?php echo esc_html__('Background Color', 'wp-job-portal'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('backgroundcolor')); ?>" name="<?php echo esc_attr($this->get_field_name('backgroundcolor')); ?>" type="text" value="<?php echo esc_attr($backgroundcolor); ?>" />
        </p>
        <p>
            <label class="wpjobportalwidefat" for="<?php echo esc_attr($this->get_field_id('bordercolor')); ?>"><?php echo esc_html__('Border color', 'wp-job-portal'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('bordercolor')); ?>" name="<?php echo esc_attr($this->get_field_name('bordercolor')); ?>" type="text" value="<?php echo esc_attr($bordercolor); ?>" />
        </p>
        <?php
    }

    // Updating widget replacing old instances with new
    public function update($new_instance, $old_instance) {
        $instance = array();

        $instance['title'] = (!empty($new_instance['title'])) ? wpjobportalphplib::wpJP_strip_tags($new_instance['title']) : '';
        $instance['showtitle'] = (!empty($new_instance['showtitle'])) ? wpjobportalphplib::wpJP_strip_tags($new_instance['showtitle']) : '';
        $instance['employer'] = (!empty($new_instance['employer'])) ? wpjobportalphplib::wpJP_strip_tags($new_instance['employer']) : '';
        $instance['jobseeker'] = (!empty($new_instance['jobseeker'])) ? wpjobportalphplib::wpJP_strip_tags($new_instance['jobseeker']) : '';
        $instance['jobs'] = (!empty($new_instance['jobs'])) ? wpjobportalphplib::wpJP_strip_tags($new_instance['jobs']) : '';
        $instance['companies'] = (!empty($new_instance['companies'])) ? wpjobportalphplib::wpJP_strip_tags($new_instance['companies']) : '';
        $instance['activejobs'] = (!empty($new_instance['activejobs'])) ? wpjobportalphplib::wpJP_strip_tags($new_instance['activejobs']) : '';
        $instance['resumes'] = (!empty($new_instance['resumes'])) ? wpjobportalphplib::wpJP_strip_tags($new_instance['resumes']) : '';
        $instance['todaystats'] = (!empty($new_instance['todaystats'])) ? wpjobportalphplib::wpJP_strip_tags($new_instance['todaystats']) : '';

        $instance['titlecolor'] = (!empty($new_instance['titlecolor'])) ? wpjobportalphplib::wpJP_strip_tags($new_instance['titlecolor']) : '';
        $instance['backgroundcolor'] = (!empty($new_instance['backgroundcolor'])) ? wpjobportalphplib::wpJP_strip_tags($new_instance['backgroundcolor']) : '';
        $instance['bordercolor'] = (!empty($new_instance['bordercolor'])) ? wpjobportalphplib::wpJP_strip_tags($new_instance['bordercolor']) : '';

        return $instance;
    }

}

// Class wpb_widget ends here
// Register and load the widget
function WPJOBPORTALCOREjobsstats_load_widget() {
    register_widget('WPJOBPORTALCOREjobsstats_widget');
}

add_action('widgets_init', 'WPJOBPORTALCOREjobsstats_load_widget');
?>
