<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
* @param js-job optional
* Filter's FrontEnd
*/
?>
<?php
    $wpjobportal_html = '';
    switch ($wpjobportal_layout) {
	    case 'myjobfilter':

            $wpjobportal_html.= '<div class="wjportal-filter">';
            $wpjobportal_html.=   WPJOBPORTALformfield::select('sortbycombo', $wpjobportal_sortbylist, isset(wpjobportal::$_data['filter']['sortby']) ? wpjobportal::$_data['filter']['sortby'] : null,esc_html(__("Default",'wp-job-portal')),array('onchange'=>'sortbychanged()'));
            $wpjobportal_html.='</div>';
            $wpjobportal_html.= '<div class="wjportal-filter-image">';
            $wpjobportal_html.= '<a href='.esc_url( wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'myjobs','sortby' => wpjobportal::$_sortlinks['newest'], 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()))) .' class='.esc_attr($wpjobportal_select).'>';
            $wpjobportal_html.= '<img src='.esc_url(WPJOBPORTAL_PLUGIN_URL) . "includes/images/" . $wpjobportal_img .' alt='.esc_html(__('sort','wp-job-portal')).'>';
            $wpjobportal_html.= '</a>
                  </div>';
            break;
        case 'myjobapplfilter':

                $wpjobportal_html.= '<div class="wjportal-filter-wrp">';
                $wpjobportal_html.= '   <div class="wjportal-filter">';
                $wpjobportal_html.=         WPJOBPORTALformfield::select('sorting', $wpjobportal_sortbylist, isset(wpjobportal::$_data['combosort']) ? wpjobportal::$_data['combosort'] : null,esc_html(__("Default",'wp-job-portal')),array('onchange'=>'changeCombo()'));
                $wpjobportal_html.='    </div>';
                $wpjobportal_html.= '   <div class="wjportal-filter-image">';
                $wpjobportal_data_sortby = '';
                if (isset(wpjobportal::$_data['sortby'])) {
                    $wpjobportal_data_sortby = wpjobportal::$_data['sortby'];
                }
                $wpjobportal_html .= '<a class="sort-icon" href="#" data-image1='. esc_attr($wpjobportal_image1).' data-image2='. esc_attr($wpjobportal_image2).' data-sortby='.$wpjobportal_data_sortby.'><img id="sortingimage" src='.  esc_url($wpjobportal_image).'></a>';
                $wpjobportal_html .= '</div>';
                $wpjobportal_html.= ' </div>';
        break;
        case 'sortby':?>
        <div id="resume-list-navebar" class="wjportal-filter-wrp">
            <div class="wjportal-filter">
                <?php echo wp_kses(WPJOBPORTALformfield::select('sorting', $wpjobportal_sortbylist, isset(wpjobportal::$_data['combosort']) ? wpjobportal::$_data['combosort'] : null,esc_html(__("Default",'wp-job-portal')),array('onchange'=>'changeCombo()')),WPJOBPORTAL_ALLOWED_TAGS); ?>
            </div>
        <div class="wjportal-filter-image">
            <a class="sort-icon" href="#" data-image1="<?php echo esc_attr($wpjobportal_image1); ?>" data-image2="<?php echo esc_attr($wpjobportal_image2); ?>" data-sortby="<?php echo esc_attr(wpjobportal::$_data['sortby']); ?>"><img id="sortingimage" src="<?php echo esc_url($wpjobportal_image); ?>" /></a>
        </div>
    </div>
    <?php
    if (wpjobportal::$_data[0]['applied'] != null or wpjobportal::$_data[0]['hits'] != null) { ?>
        <div class="wjportal-view-job-count">
            <span class="wjportal-view-job-txt">
                <?php echo esc_html(__('Job View', 'wp-job-portal')) . ': ' . esc_html(wpjobportal::$_data[0]['hits']) . ' / ' . esc_html(__('Applied', 'wp-job-portal')) . ': ' . esc_html(wpjobportal::$_data[0]['applied']) ?>
            </span>
        </div>
    <?php }
    }
    echo wp_kses($wpjobportal_html, WPJOBPORTAL_ALLOWED_TAGS);
?>
