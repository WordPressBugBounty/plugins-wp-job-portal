<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
?>
<div class="wjportal-main-up-wrapper">
<?php
if(!WPJOBPORTALincluder::getTemplate('templates/header',array('wpjobportal_module'=>'job'))){
    return;
}
?>   
    <div class="wjportal-main-wrapper wjportal-clearfix">
        <div class="wjportal-page-header">
            <?php
                WPJOBPORTALincluder::getTemplate('templates/pagetitle',array('wpjobportal_module'=>'job','wpjobportal_layout'=>'jobtype'));
            ?>
        </div>
        <div class="wjportal-by-type-wrp">
            <?php
                $wpjobportal_number = 3;
                if(wpjobportal::$_data['config']['jobtype_per_row'] != ''){ // to handle float value in configuration
                    $wpjobportal_number = ceil(wpjobportal::$_data['config']['jobtype_per_row']);
                }

                if ($wpjobportal_number < 1 || $wpjobportal_number > 100) {
                    $wpjobportal_number = 3; // by default set to 3
                }
                $wpjobportal_width = 100 / $wpjobportal_number;
                $wpjobportal_count = 0;
                if (isset(wpjobportal::$_data[0]) && !empty(wpjobportal::$_data[0])) {
                    foreach (wpjobportal::$_data[0] AS $wpjobportal_jobsBytype) {
                        if (($wpjobportal_count % $wpjobportal_number) == 0) {
                            if ($wpjobportal_count == 0)
                                echo '<div class="wjportal-type-row-wrapper">';
                            else
                                echo '</div><div class="wjportal-type-row-wrapper">';
                        }
                        ?>
                        <div class="wjportal-type-wrapper" style="width:<?php echo esc_attr($wpjobportal_width); ?>%;">
                            <a href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobs', 'jobtype'=>$wpjobportal_jobsBytype->alias))); ?>" title="<?php echo esc_attr(wpjobportal::wpjobportal_getVariableValue($wpjobportal_jobsBytype->title)); ?>">
                                <span class="wjportal-type-title">
                                    <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_jobsBytype->title)); ?>
                                </span>
                                <?php if(wpjobportal::$_data['config']['jobtype_numberofjobs']){ ?>
                                    <span class="wjportal-type-num">
                                        <?php echo esc_html($wpjobportal_jobsBytype->totaljobs); ?>
                                    </span>
                                <?php } ?>
                            </a>
                        </div>
                    <?php
                    $wpjobportal_count++;
                }
                echo '</div>';
                }
            else {
                WPJOBPORTALlayout::getNoRecordFound();?>
            <?php }
            ?>
        </div>
    </div>	
</div>
