<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
    /**
    *
    */
    ?>
<?php
    $wpjobportal_html = '';
    switch ($wpjobportal_layout) {
    	case 'comp-sort':
    		$wpjobportal_html.='<div id="wpjobportal-page-quick-actions">
    			        <label class="wpjobportal-page-quick-act-btn" onclick="return highlightAll();" for="selectall">
    			        	<input type="checkbox" name="selectall" id="selectall" value="">
			        		'. esc_html(__('Select All', 'wp-job-portal')) .'
		        		</label>
    			        <a class="wpjobportal-page-quick-act-btn multioperation" message="'. WPJOBPORTALMessages::getMSelectionEMessage().'" confirmmessage="'. esc_html(__('Are you sure to delete','wp-job-portal')).' ?'.'" data-for="remove" href="#" title="'.esc_attr(__('delete', 'wp-job-portal')).'">
    			        	<img src='. esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/control_panel/dashboard/forced-delete.png alt="'.esc_attr(__('delete', 'wp-job-portal')).'">
    			        	'. esc_html(__('Delete', 'wp-job-portal')) .'
			        	</a>';
    			        $wpjobportal_image1 = esc_url(WPJOBPORTAL_PLUGIN_URL) . "includes/images/control_panel/dashboard/sorting-white-1.png";
    			        $wpjobportal_image2 = esc_url(WPJOBPORTAL_PLUGIN_URL) . "includes/images/control_panel/dashboard/sorting-white-2.png";
    			        if (wpjobportal::$_data['sortby'] == 1) {
    			            $wpjobportal_image = $wpjobportal_image1;
    			        } else {
    			            $wpjobportal_image = $wpjobportal_image2;
    			        }
    			        $wpjobportal_html.='<div class="wpjobportal-sorting-wrp">
    			            	<span class="wpjobportal-sort-text">
    			            		'.esc_html(__('Sort by', 'wp-job-portal')).':
			            		</span>
    			            	<span class="wpjobportal-sort-field">
    			            		'.WPJOBPORTALformfield::select('sorting', $wpjobportal_categoryarray, wpjobportal::$_data['combosort'], '', array('class' => 'inputbox', 'onchange' => 'changeCombo();')).'
			            		</span>
    			            	<a class="wpjobportal-sort-icon sort-icon" href="#" data-image1='.$wpjobportal_image1.' data-image2='. $wpjobportal_image2.' data-sortby='. wpjobportal::$_data['sortby'].'>
    			            		<img id="sortingimage" src='. $wpjobportal_image.' alt="'.esc_attr(__('sort','wp-job-portal')).'">
			            		</a>
    			        	</div>
    			    </div>';
		break;
	}
    echo wp_kses($wpjobportal_html, WPJOBPORTAL_ALLOWED_TAGS);
?>
