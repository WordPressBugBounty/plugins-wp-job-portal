<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

// Creating the widget
class WPJOBPORTALCOREjobsbycategory_widget extends WP_Widget {

    function __construct() {
        parent::__construct(
                // Base ID of your widget
                'WPJOBPORTALjobsbycategory_widget',
                // Widget name will appear in UI
                __('Jobs By Categories Widget', 'wp-job-portal'),
                // Widget description
                array('description' => __('Show jobs by categories form jobs database', 'wp-job-portal'))
        );
    }

    // Creating widget front-end
    // This is where the action happens
    public function widget($args, $instance) {

        $title         = apply_filters('widget_title', isset($instance['title']) ? $instance['title'] : '');
        $showtitle     = (!empty($instance['showtitle']) && $instance['showtitle'] == 1) ? 1 : 0;
        $maximumrecords = isset($instance['maximumrecords']) ? $instance['maximumrecords'] : 0;
        $haverecords   = !empty($instance['haverecords']) ? 1 : 0;
        $showallcats   = !empty($instance['showallcats']) ? 1 : 0;
        $columnperrow  = isset($instance['columnperrow']) ? $instance['columnperrow'] : 1;

        // Color settings
        $color1 = isset($instance['titlecolor']) ? $instance['titlecolor'] : '';
        $color2 = isset($instance['backgroundcolor']) ? $instance['backgroundcolor'] : '';
        $color3 = isset($instance['bordercolor']) ? $instance['bordercolor'] : '';

        // before and after widget arguments are defined by themes
        echo wp_kses($args['before_widget'], WPJOBPORTAL_ALLOWED_TAGS);
        if (!empty($title) && $showtitle == 1)
            echo wp_kses($args['before_title'] . $title . $args['after_title'], WPJOBPORTAL_ALLOWED_TAGS);

        // This is where you run the code and display the output
        //Frontend HTML starts -----------

        $classname = 'category' . uniqid();
        wpjobportal::$_data['categories_widget'] = WPJOBPORTALincluder::getJSModel('job')->getJobsBycategory_Widget($showallcats, $haverecords, $maximumrecords);
        if (locate_template('wp-job-portal/widget-categories.php', 1, 0)) {
            $defaulthtml = false;
        }else{
            $html = WPJOBPORTALincluder::getJSModel('wpjobportalwidgets')->listModuleByJobcatOrType(wpjobportal::$_data['categories_widget'], $classname, $showtitle, $title, $columnperrow, 2 );
            echo wp_kses($html, WPJOBPORTAL_ALLOWED_TAGS);
            wpjobportal::wpjobportal_addStyleSheets();
            $echo_style = WPJOBPORTALincluder::getJSModel('wpjobportalwidgets')->prepareStyleForBlocks($classname, $color1, $color2, $color3);
            echo wp_kses($echo_style, WPJOBPORTAL_ALLOWED_TAGS);
        }
        //Frontend HTML ends -------------
        // before and after widget arguments are defined by themes
        echo wp_kses($args['after_widget'], WPJOBPORTAL_ALLOWED_TAGS);
    }

    // Widget Backend
    public function form($instance) {

        $title = (isset($instance['title'])) ? $instance['title'] : __('Jobs By Categories', 'wp-job-portal');
        $showallcats = (isset($instance['showallcats'])) ? $instance['showallcats'] : 2;
        $showtitle = (isset($instance['showtitle'])) ? $instance['showtitle'] : 1;
        $maximumrecords = (isset($instance['maximumrecords'])) ? $instance['maximumrecords'] : 20;
        $haverecords = (isset($instance['haverecords'])) ? $instance['haverecords'] : 1;
        $columnperrow = (isset($instance['columnperrow'])) ? $instance['columnperrow'] : 3;
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
                <option value="1" <?php if (esc_attr($showtitle) == 1) echo "selected"; ?>><?php echo esc_html__('Show', 'wp-job-portal'); ?></option>
                <option value="2" <?php if (esc_attr($showtitle) == 2) echo "selected"; ?>><?php echo esc_html__('Hide', 'wp-job-portal'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('maximumrecords')); ?>"><?php echo esc_html__('Show Maximum records', 'wp-job-portal'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('maximumrecords')); ?>" name="<?php echo esc_attr($this->get_field_name('maximumrecords')); ?>" type="text" value="<?php echo esc_attr($maximumrecords); ?>" />
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('showallcats')); ?>"><?php echo esc_html__('Show All Categories', 'wp-job-portal'); ?></label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('showallcats')); ?>" name="<?php echo esc_attr($this->get_field_name('showallcats')); ?>">
                <option value="1" <?php if (esc_attr($showallcats) == 1) echo "selected"; ?>><?php echo esc_html__('Yes', 'wp-job-portal'); ?></option>
                <option value="2" <?php if (esc_attr($showallcats) == 2) echo "selected"; ?>><?php echo esc_html__('No', 'wp-job-portal'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('haverecords')); ?>"><?php echo esc_html__('Show only record that have jobs', 'wp-job-portal'); ?></label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('haverecords')); ?>" name="<?php echo esc_attr($this->get_field_name('haverecords')); ?>">
                <option value="1" <?php if (esc_attr($haverecords) == 1) echo "selected"; ?>><?php echo esc_html__('Yes', 'wp-job-portal'); ?></option>
                <option value="2" <?php if (esc_attr($haverecords) == 2) echo "selected"; ?>><?php echo esc_html__('No', 'wp-job-portal'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('columnperrow')); ?>"><?php echo esc_html__('Column per row', 'wp-job-portal'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('columnperrow')); ?>" name="<?php echo esc_attr($this->get_field_name('columnperrow')); ?>" type="text" value="<?php echo esc_attr($columnperrow); ?>" />
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
        $instance['showallcats'] = (!empty($new_instance['showallcats'])) ? wpjobportalphplib::wpJP_strip_tags($new_instance['showallcats']) : '';
        $instance['showtitle'] = (!empty($new_instance['showtitle'])) ? wpjobportalphplib::wpJP_strip_tags($new_instance['showtitle']) : '';
        $instance['maximumrecords'] = (!empty($new_instance['maximumrecords'])) ? wpjobportalphplib::wpJP_strip_tags($new_instance['maximumrecords']) : '';
        $instance['haverecords'] = (!empty($new_instance['haverecords'])) ? wpjobportalphplib::wpJP_strip_tags($new_instance['haverecords']) : '';
        $instance['columnperrow'] = (!empty($new_instance['columnperrow'])) ? wpjobportalphplib::wpJP_strip_tags($new_instance['columnperrow']) : '';
        $instance['titlecolor'] = (!empty($new_instance['titlecolor'])) ? wpjobportalphplib::wpJP_strip_tags($new_instance['titlecolor']) : '';
        $instance['backgroundcolor'] = (!empty($new_instance['backgroundcolor'])) ? wpjobportalphplib::wpJP_strip_tags($new_instance['backgroundcolor']) : '';
        $instance['bordercolor'] = (!empty($new_instance['bordercolor'])) ? wpjobportalphplib::wpJP_strip_tags($new_instance['bordercolor']) : '';

        return $instance;
    }

}

// Class wpb_widget ends here
// Register and load the widget
function WPJOBPORTALCOREjobsbycategory_load_widget() {
    register_widget('WPJOBPORTALCOREjobsbycategory_widget');
}

add_action('widgets_init', 'WPJOBPORTALCOREjobsbycategory_load_widget');
?>
