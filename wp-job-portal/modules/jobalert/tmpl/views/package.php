<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
/**
* @param WP JOB PORTAL---
* @param Packages Details For Job Alert 
*/
    
?>
<?php if(isset($packages)){ 
        if (wpjobportal::$wpjobportal_theme_chk == 1) { ?>
            <div class="wpj-jp-pkg-list">
                <div class="wpj-jp-pkg-list-top">
                    <div class="wpj-jp-pkg-list-title">
                        <h4 class="wpj-jp-pkg-list-title-txt">
                            <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($packages->title)); ?>
                        </h4>
                    </div>
                </div>
                <div class="wpj-jp-pkg-list-mid">
                    <?php if(isset($packages)){?>
                        <div class="wpj-jp-pkg-list-data">
                            <span class="wpj-jp-pkg-list-laebl">
                                <?php echo esc_html__("Total Job Alert","job-portal-theme")." : "; ?>
                            </span>
                            <?php echo $packages->jobalert==-1 ? esc_html__('Unlimited','job-portal-theme') : esc_html(wpjobportal::wpjobportal_getVariableValue( $packages->jobalert)); ?>
                        </div>
                        <div class="wpj-jp-pkg-list-data">
                            <span class="wpj-jp-pkg-list-laebl">
                                <?php echo esc_html__("Remaining Job Alert","job-portal-theme")." : "; ?>
                            </span>
                            <?php echo $packages->jobalert==-1 ? esc_html__('Unlimited','job-portal-theme') : esc_html(wpjobportal::wpjobportal_getVariableValue( $packages->remjobalert)); ?>
                        </div>
                    <?php } ?>
                </div>
                <div class="wpj-jp-pkg-list-btm">
                    <div class="wpj-jp-pkg-list-action-wrp">
                        <a class="wpj-jp-outline-btn" title="<?php echo esc_attr__('Change Package', "job-portal-theme"); ?>" href="<?php echo esc_url(add_query_arg('choosepackage', 1, wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobalert','wpjobportallt'=>'jobalert')))); ?>">
                            <?php echo esc_html__("Change Package", "job-portal-theme") ?>
                        </a>
                    </div>
                    <div class="wpj-jp-pkg-list-exp-date">
                        <?php echo esc_html__('Ends On','job-portal-theme').': '.esc_html(date_i18n(wpjobportal::$_configuration['date_format'],strtotime($packages->enddate))); ?>
                    </div>
                </div>
            </div>
        <?php } else { ?>
    <div class="wjportal-packages-list">
        <div class="wjportal-pkg-list-item">
            <div class="wjportal-pkg-list-item-top">
                <div class="wjportal-pkg-list-item-title">
                    <div class="wjportal-pkg-list-item-title-txt">
                        <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($packages->title)); ?>
                    </div>
                </div>
            </div>
            <div class="wjportal-pkg-list-item-mid">
                <?php if(isset($packages)){?>
                    <div class="wjportal-pkg-list-item-data">
                        <div class="wjportal-pkg-list-item-row">
                            <span class="wjportal-pkg-list-item-row-tit">
                                <?php echo esc_html(__('Total Job Alert','wp-job-portal')). ' : '; ?>
                            </span>
                            <span class="wjportal-pkg-list-item-row-val">
                                <?php echo ($packages->jobalert==-1 ? esc_html(__('Unlimited','wp-job-portal')) : esc_html(wpjobportal::wpjobportal_getVariableValue($packages->jobalert))); ?>
                            </span>
                        </div>
                        <div class="wjportal-pkg-list-item-row">
                            <span class="wjportal-pkg-list-item-row-tit">
                                <?php echo esc_html(__('Remaining Job Alert','wp-job-portal')). ' : '; ?>
                            </span>
                            <span class="wjportal-pkg-list-item-row-val">
                                <?php echo ($packages->jobalert==-1 ? esc_html(__('Unlimited','wp-job-portal')) : esc_html(wpjobportal::wpjobportal_getVariableValue($packages->remjobalert))); ?>
                            </span>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div class="wjportal-pkg-list-item-btm">
                <?php
                // dont show change package button if user only has 1 package
                if(WPJOBPORTALincluder::getJSModel('purchasehistory')->showChangePackageButtonToUser()){ ?>
                    <div class="wjportal-pkg-list-item-action-wrp">
                        <a href="<?php echo esc_url(add_query_arg('choosepackage', 1, wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobalert','wpjobportallt'=>'jobalert')))); ?>" class="wjportal-pkg-list-item-act-btn  wjportal-list-act-btn-change-package" title="<?php echo esc_attr(__('Change Package','wp-job-portal')); ?>">
                            <?php echo esc_html(__('Change Package','wp-job-portal')); ?>
                        </a>
                    </div>
                <?php } ?>
                <div class="wjportal-pkg-list-item-exp-date">
                    <?php echo esc_html(__('Ends On','wp-job-portal')).': '.esc_html(date_i18n(wpjobportal::$_configuration['date_format'],strtotime($packages->enddate))); ?> 
                </div>
            </div>
        </div>
    </div>
<?php } }?>
