<?php
if (!defined('ABSPATH'))
die('Restricted Access');
?>
<div class="wjportal-main-up-wrapper">
<?php
$wpjobportal_msgkey = WPJOBPORTALincluder::getJSModel('job')->getMessagekey();
WPJOBPORTALMessages::getLayoutMessage($wpjobportal_msgkey);

//WPJOBPORTALbreadcrumbs::getBreadcrumbs();
//include_once(WPJOBPORTAL_PLUGIN_PATH . 'includes/header.php');
?>
<div class="wjportal-main-wrapper wjportal-clearfix">
    <?php if (wpjobportal::$_error_flag == null) { ?>
        <div class="wjportal-page-header">
            <?php
                WPJOBPORTALincluder::getTemplate('templates/pagetitle',array('wpjobportal_module' => 'jobbycatagory','wpjobportal_layout'=>'jobbycatagory'));
            ?>
        </div>
        <div id="wjportal-popup-background"></div>
        <div id="wjportal-listpopup" class="wjportal-popup-wrp wjportal-job-by-catg-popup">
            <div class="wjportal-popup-cnt">
                <img id="wjportal-popup-close-btn" alt="popup cross" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/popup-close.png">
                <div class="wjportal-popup-title">
                    <span class="wjportal-popup-title2"></span>
                </div>
                <div class="wjportal-popup-contentarea"></div>
            </div>
        </div>
        <div id="wpjobportal-wrapper" class="wjportal-by-categories-main-wrp wjportal-job-by-categories-wrp">
            <?php
                $wpjobportal_number = 3;
                if(wpjobportal::$_data['config']['categories_colsperrow'] != ''){ // to handle float value in configuration
                    $wpjobportal_number = ceil(wpjobportal::$_data['config']['categories_colsperrow']);
                }
                if ($wpjobportal_number < 1 || $wpjobportal_number > 100) {
                    $wpjobportal_number = 3; // by default set to 3
                }
                $wpjobportal_width = 100 / $wpjobportal_number;
                $wpjobportal_count = 0;
                if (isset(wpjobportal::$_data[0]) && !empty(wpjobportal::$_data[0])) {
                    foreach (wpjobportal::$_data[0] AS $wpjobportal_jobsByCategories) {
                        if (($wpjobportal_count % $wpjobportal_number) == 0) {
                            if ($wpjobportal_count == 0)
                                echo '<div class="wjportal-by-categories-row-wrp">';
                            else
                                echo '</div><div class="wjportal-by-categories-row-wrp">';
                        }
                        ?>
                        <div class="wjportal-by-category-wrp" style="width: calc(<?php echo esc_attr($wpjobportal_width); ?>% - 10px);" data-id="<?php echo esc_attr($wpjobportal_jobsByCategories->aliasid); ?>">
                            <a href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobs', 'category'=>$wpjobportal_jobsByCategories->aliasid))); ?>">
                                <div class="wjportal-by-category-item">
                                    <span class="wjportal-by-category-item-title">
                                        <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_jobsByCategories->cat_title)); ?>
                                    </span>
                                    <?php if(wpjobportal::$_data['config']['categories_numberofjobs'] == 1){ ?>
                                        <span class="wjportal-by-category-item-number"><?php echo '(' . esc_html($wpjobportal_jobsByCategories->totaljobs + $wpjobportal_jobsByCategories->total_sub_jobs) . ')'; ?></span>
                                    <?php } ?>
                                </div>
                            </a>
                            <?php
                                $wpjobportal_config_array = WPJOBPORTALincluder::getJSModel('configuration')->getConfigByFor('category');
                                $wpjobportal_subcategory_limit = 3;
                                if($wpjobportal_config_array['subcategory_limit'] != ''){ // to handle float value in configuration
                                    $wpjobportal_subcategory_limit = ceil($wpjobportal_config_array['subcategory_limit']);
                                }
                                if (!empty($wpjobportal_jobsByCategories->subcat)) {
                                    $wpjobportal_html = '<div class="wjportal-by-sub-catagory" style="display:none;">';
                                    $wpjobportal_subcount = 0;
                                    foreach ($wpjobportal_jobsByCategories->subcat AS $cat) {
                                        //$wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'resumes', 'category'=>$cat->aliasid));
                                        $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobs', 'category'=>$cat->aliasid));
                                        $wpjobportal_html .= '  <div class="wjportal-by-category-wrp" style="width:100%;">
                                                        <a href="' . esc_url($wpjobportal_link) . '">
                                                            <div class="wjportal-by-category-item">
                                                                <span class="wjportal-by-category-item-title">' . wpjobportal::wpjobportal_getVariableValue($cat->cat_title) . '</span>';
                                                                if($wpjobportal_config_array['categories_numberofjobs'] == 1){
                                                                    $wpjobportal_html .= '<span class="wjportal-by-category-item-number">(' . $cat->totaljobs . ')</span>';
                                                                }
                                        $wpjobportal_html .=    '       </div>
                                                        </a>
                                                    </div>';
                                        $wpjobportal_subcount++;
                                    }
                                    if ($wpjobportal_subcount >= $wpjobportal_subcategory_limit) {
                                        $wpjobportal_html .= '  <div class="wjportal-by-category-item-btn-wrp">
                                                        <a href="#" class="wjportal-by-category-item-btn" onclick="getPopupAjax(\'' . $wpjobportal_jobsByCategories->aliasid . '\', \'' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_jobsByCategories->cat_title) . '\');">' . __('Show More', 'wp-job-portal') . '</a>
                                                    </div>';
                                    }
                                    $wpjobportal_html .= '</div>';
                                    echo wp_kses($wpjobportal_html, WPJOBPORTAL_ALLOWED_TAGS);
                                }
                            ?>
                        </div>
                        <?php
                        $wpjobportal_count++;
                    }
                    echo '</div>';
                } else {
                    WPJOBPORTALlayout::getNoRecordFound();
                }
            ?>
        </div>
    <?php
    } else {
        if(wpjobportal::$_error_flag_message !=''){
            echo wp_kses_post(wpjobportal::$_error_flag_message);
        }
    }
    ?>
</div>
</div>
