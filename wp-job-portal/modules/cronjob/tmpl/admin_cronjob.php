<?php
    if (!defined('ABSPATH'))
        die('Restricted Access');
    wp_enqueue_script('jquery-ui-tabs');
    $wpjobportal_msgkey = WPJOBPORTALincluder::getJSModel('configuration')->getMessagekey();
    $wpjobportal_external_cron_url = '';
    if (!empty(wpjobportal::$_data[0]['ck'])) {
        $wpjobportal_external_cron_url = wpjobportal::wpjobportal_makeUrl(array(
            'wpjobportalme' => 'jobalert',
            'task' => 'sendjobalert',
            'action' => 'wpjobportaltask',
            'ck' => wpjobportal::$_data[0]['ck'],
            'wpjobportalpageid' => wpjobportal::wpjobportal_getPageid()
        ));
    }
    
    WPJOBPORTALMessages::getLayoutMessage($wpjobportal_msgkey);
?>
<script type="text/javascript">
    jQuery(document).ready(function () {
        // log issue
        // var wpjpconfigid = '<?php // echo esc_js(wpjobportal::$_data["wpjpconfigid"]) ?>';
        // if (wpjpconfigid == 'cron_setting') {
        //     jQuery('#cron_setting').css('display','inline-block');
        //     jQuery('#cj_setting').addClass('active');
        // }
    });
</script>
<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper">
    <!-- left menu -->
    <div id="wpjobportaladmin-leftmenu">
        <?php  WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <!-- top bar -->
        <div id="wpjobportal-wrapper-top">
            <div id="wpjobportal-wrapper-top-left">
                <div id="wpjobportal-breadcrumbs">
                    <ul>
                        <li>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal')); ?>" title="<?php echo esc_attr__('Dashboard','wp-job-portal'); ?>">
                                <?php echo esc_html__('Dashboard','wp-job-portal'); ?>
                            </a>
                        </li>
                        <li><?php echo esc_html__('Cron Job','wp-job-portal'); ?></li>
                    </ul>
                </div>
            </div>    
            <div id="wpjobportal-wrapper-top-right">
                <div id="wpjobportal-config-btn">
                    <a href="admin.php?page=wpjobportal_configuration" title="<?php echo esc_attr__('Configuration','wp-job-portal'); ?>">
                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/control_panel/dashboard/config.png'); ?>">
                   </a>
                </div>
                <div id="wpjobportal-help-btn" class="wpjobportal-help-btn">
                    <a href="admin.php?page=wpjobportal&wpjobportallt=help" title="<?php echo esc_attr__('Help','wp-job-portal'); ?>">
                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/control_panel/dashboard/help.png'); ?>">
                   </a>
                </div>
                <div id="wpjobportal-vers-txt">
                    <?php echo esc_html__('Version','wp-job-portal').': '; ?>
                    <span class="wpjobportal-ver"><?php echo esc_html(WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue('versioncode')); ?></span>
                </div>
            </div>    
        </div>
        <!-- top head -->
        <div id="wpjobportal-head"  class="wpjobportal-config-head">
            <h1 class="wpjobportal-head-text">
                <?php echo esc_html__('Cron Job', 'wp-job-portal'); ?>
            </h1>
        </div>
        <!-- page content -->
        <div id="wpjobportal-admin-wrapper" class="wpjobportal-config-main-wrapper">
            <div class="wpjobportal-configurations">
                <div class="wpjobportal-configurations-toggle">
                    <img alt="<?php echo esc_attr__('Menu','wp-job-portal'); ?>" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/control_panel/dashboard/admin-left-menu/menu.png'); ?>" />
                    <span class="jslm_text"><?php echo esc_html__('Select Configuration', 'wp-job-portal'); ?></span>
                </div>
                <div class="wpjobportal-right-content">
                    <div id="tabs" class="tabs">
                        <ul>
                            <li class="ui-tabs-active">
                                <a  href="#webcrown">
                                    <?php echo esc_html__('Webcrown.org', 'wp-job-portal'); ?>
                                </a>
                            </li>
                            <li>
                                <a href="#wget">
                                    <?php echo esc_html__('Wget', 'wp-job-portal'); ?>
                                </a> 
                            </li>
                            <li>
                                <a href="#curl">
                                    <?php echo esc_html__('CURL', 'wp-job-portal'); ?>
                                </a>
                            </li>
                            <li>
                                <a href="#phpscript">
                                    <?php echo esc_html__('PHP Script', 'wp-job-portal'); ?>
                                </a>
                            </li>
                            <li>
                                <a href="#url">
                                    <?php echo esc_html__('Website', 'wp-job-portal'); ?></a>
                            </li>
                        </ul>
                        <?php
                            $wpjobportal_array = array('even', 'odd');
                            $wpjobportal_k = 0;
                        ?>
                        <div id="cron_info_area">
                            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/info-icon.png'); ?>"> 
                            <?php echo esc_html__('Job alerts run daily through WordPress Cron. Configure an external cron only when the site has low traffic or WordPress Cron is disabled.', 'wp-job-portal'); ?>
                        </div>
                        <div class="tabInner">
                            <div id="webcrown" class="wpjobportal_gen_body">
                                <div id="cron_job">
                                    <span class="crown_text">
                                        <?php echo esc_html__('Configuration Of A Backup Job With', 'wp-job-portal').'Webcron.org'; ?>
                                    </span>
                                    <div id="cron_job_detail_wrapper" class="<?php echo esc_attr($wpjobportal_array[$wpjobportal_k]);$wpjobportal_k = 1 - $wpjobportal_k; ?>">
                                        <span class="crown_text_left">
                                            <?php echo esc_html__('Cron Job Name', 'wp-job-portal'); ?>
                                        </span>
                                        <span class="crown_text_right">
                                            <?php echo esc_html__('Log In To','wp-job-portal'). ' Webcron.org. '. esc_html__('In The Cron Area','wp-job-portal').', '.esc_html__('Click On The New Cron Button','wp-job-portal').'. '.esc_html__('Below You will Find What You Have To Enter At','wp-job-portal').' Webcron.orgs'. esc_html__('Interface', 'wp-job-portal'); ?>
                                        </span>
                                    </div>
                                    <div id="cron_job_detail_wrapper" class="<?php echo esc_attr($wpjobportal_array[$wpjobportal_k]);$wpjobportal_k = 1 - $wpjobportal_k; ?>">
                                        <span class="crown_text_left">
                                            <?php echo esc_html__('Timeout', 'wp-job-portal'); ?>
                                        </span>
                                        <span class="crown_text_right">
                                            <?php echo esc_html__('180 Seconds If The Backup Does Not Complete','wp-job-portal').', '.esc_html__('Increase It','wp-job-portal').'. '.esc_html__('Most Sites Will Work With A Setting Of 180 Or 600 Here','wp-job-portal').'. '.esc_html__('If You Have A Very Big Site Which Takes More Than 5 Minutes To Back Itself Up','wp-job-portal').', '.esc_html__('You Might Consider Using Akeeba Backup Professional And The Native Cli Cron Job Instead','wp-job-portal').', '.esc_html__('As Its Much More Cost-effective.', 'wp-job-portal'); ?>
                                        </span>
                                    </div>
                                    <div id="cron_job_detail_wrapper" class="<?php echo esc_attr($wpjobportal_array[$wpjobportal_k]);$wpjobportal_k = 1 - $wpjobportal_k; ?>">
                                        <span class="crown_text_left">
                                            <?php echo esc_html__('Url You Want To Execute', 'wp-job-portal'); ?>
                                        </span>
                                        <span class="crown_text_right">
                                            <?php echo esc_url($wpjobportal_external_cron_url); ?>
                                        </span>
                                    </div>
                                    <div id="cron_job_detail_wrapper" class="<?php echo esc_attr($wpjobportal_array[$wpjobportal_k]);$wpjobportal_k = 1 - $wpjobportal_k; ?>">
                                        <span class="crown_text_left">
                                            <?php echo esc_html__('Login', 'wp-job-portal'); ?>
                                        </span>
                                        <span class="crown_text_right">
                                            <?php echo esc_html__('Leave This Blank', 'wp-job-portal'); ?>
                                        </span>
                                    </div>
                                    <div id="cron_job_detail_wrapper" class="<?php echo esc_attr($wpjobportal_array[$wpjobportal_k]);$wpjobportal_k = 1 - $wpjobportal_k; ?>">
                                        <span class="crown_text_left">
                                            <?php echo esc_html__('Password', 'wp-job-portal'); ?>
                                        </span>
                                        <span class="crown_text_right">
                                            <?php echo esc_html__('Leave This Blank', 'wp-job-portal'); ?>
                                        </span>
                                    </div>
                                    <div id="cron_job_detail_wrapper" class="<?php echo esc_attr($wpjobportal_array[$wpjobportal_k]);$wpjobportal_k = 1 - $wpjobportal_k; ?>">
                                        <span class="crown_text_left">
                                            <?php echo esc_html__('Execution Time', 'wp-job-portal'); ?>
                                        </span>
                                        <span class="crown_text_right">
                                            <?php echo esc_html__('Thats The Grid Below The Other Options','wp-job-portal').'. '.esc_html__('Select When And How Often You Want Your Cron Job To Run.', 'wp-job-portal'); ?>
                                        </span>
                                    </div>
                                    <div id="cron_job_detail_wrapper" class="<?php echo esc_attr($wpjobportal_array[$wpjobportal_k]);$wpjobportal_k = 1 - $wpjobportal_k; ?>">
                                        <span class="crown_text_left">
                                            <?php echo esc_html__('Alerts', 'wp-job-portal'); ?>
                                        </span>
                                        <span class="crown_text_right">
                                            <?php echo esc_html__('If You Have Already Set Up Alert Methods In','wp-job-portal').' Webcron.orgs'. esc_html__('Interface','wp-job-portal').', '.esc_html__('We Recommend Choosing An Alert Method Here And Not Checking The only On Error So That You Always Get A Notification When The Backup Cron Job Runs','wp-job-portal').'. '.esc_html__('Finally Click On The Submit Button To Finish Setting Up Your Cron Job.', 'wp-job-portal'); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div id="wget" class="wpjobportal_gen_body">
                                <div id="cron_job">
                                    <span class="crown_text">
                                        <?php echo esc_html__('Cron Scheduling Using Wget Most Hosts', 'wp-job-portal'); ?>
                                    </span>
                                    <div id="cron_job_detail_wrapper" class="even">
                                        <span class="crown_text_right fullwidth">
                                            <?php echo esc_html('wget --max-redirect=10000 "' . esc_url_raw($wpjobportal_external_cron_url) . '" -O - 1>/dev/null 2>/dev/null'); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div id="curl" class="wpjobportal_gen_body">
                                <div id="cron_job">
                                    <span class="crown_text">
                                        <?php echo esc_html__('Cron Scheduling Using Curl siteground And A Few Other Hosts', 'wp-job-portal'); ?>
                                    </span>
                                    <div id="cron_job_detail_wrapper" class="even">
                                        <span class="crown_text_right fullwidth">
                                            <?php echo esc_html('curl "' . esc_url_raw($wpjobportal_external_cron_url) . '"'); ?><br><?php echo esc_html__('Or', 'wp-job-portal'); ?><br>
                                            <?php echo esc_html('curl -L --max-redirs 1000 -v "' . esc_url_raw($wpjobportal_external_cron_url) . '" 1>/dev/null 2>/dev/null'); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div id="phpscript" class="wpjobportal_gen_body">
                                <div id="cron_job">
                                    <span class="crown_text">
                                        <?php echo esc_html__('Custom Php Script To Run', 'wp-job-portal'); ?>
                                    </span>
                                    <div id="cron_job_detail_wrapper" class="even">
                                        <span class="crown_text_right fullwidth">
                                            <?php
                                                $wpjobportal_php_script = '$curl_handle = curl_init();' . "\n";
                                                $wpjobportal_php_script .= 'curl_setopt($curl_handle, CURLOPT_URL, \'' . esc_url_raw($wpjobportal_external_cron_url) . '\');' . "\n";
                                                $wpjobportal_php_script .= 'curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, true);' . "\n";
                                                $wpjobportal_php_script .= 'curl_setopt($curl_handle, CURLOPT_MAXREDIRS, 10);' . "\n";
                                                $wpjobportal_php_script .= 'curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);' . "\n";
                                                $wpjobportal_php_script .= '$buffer = curl_exec($curl_handle);' . "\n";
                                                $wpjobportal_php_script .= 'curl_close($curl_handle);' . "\n";
                                                $wpjobportal_php_script .= 'echo $buffer;';
                                                echo nl2br(esc_html($wpjobportal_php_script));
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div id="url" class="wpjobportal_gen_body">
                                <div id="cron_job">
                                    <span class="crown_text">
                                        <?php echo esc_html__('URL For Use With Your Own Scripts And Third Party Services', 'wp-job-portal'); ?>
                                    </span>
                                    <div id="cron_job_detail_wrapper" class="even">
                                        <span class="crown_text_right fullwidth">
                                            <?php echo esc_url($wpjobportal_external_cron_url); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script >
    jQuery(document).ready(function () {
         jQuery("#tabs").tabs();
    });
</script>

<style>

:root {
    /* Colors */
    --wjp-color-primary: #4f46e5;
    --wjp-color-primary-bg: #eef2ff;
    --wjp-color-success-text: #065f46;
    --wjp-color-success-bg: #d1fae5;

    /* Text */
    --wjp-text-dark: #1e293b;
    --wjp-text-medium: #475569;

    /* Backgrounds & Borders */
    --wjp-bg-body: #f8fafc;
    --wjp-bg-card: #ffffff;
    --wjp-bg-light: #f1f5f9;
    --wjp-border-color: #e2e8f0;

    /* Misc */
    --wjp-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
    --wjp-border-radius: 0.75rem;
    --wjp-border-radius-sm: 0.5rem;

    /* Fonts */
    --wjp-font-weight-medium: 500;
    --wjp-font-weight-semibold: 600;
    --wjp-font-size-sm: 0.875rem;
    --wjp-font-size-base: 1rem;
    --wjp-font-size-lg: 1.125rem;
}
    /* ==========================================================================
   WP Job Portal - Cron Job Settings Styles
   ========================================================================== */

/* Main Configurations Wrapper */
.wpjobportal-config-main-wrapper .wpjobportal-configurations {
    display: flex;
    gap: 2rem;
    align-items: flex-start;
}



.wpjobportal-configurations .wpjobportal-right-content {
    flex-grow: 1;
    min-width: 0;
}

/* Toggle Menu (Mobile View) */
.wpjobportal-configurations-toggle {
    display: none;
    align-items: center;
    gap: 10px;
    padding: 10px 15px;
    background-color: var(--wjp-bg-card);
    border: 1px solid var(--wjp-border-color);
    border-radius: var(--wjp-border-radius-sm);
    margin-bottom: 1rem;
    cursor: pointer;
    font-weight: var(--wjp-font-weight-medium);
    color: var(--wjp-text-dark);
}

/* Info Box */
#cron_info_area {
    display: flex;
    align-items: center;
    background-color: var(--wjp-color-success-bg);
    color: var(--wjp-color-success-text);
    padding: 1rem 1.5rem;
    border-radius: var(--wjp-border-radius-sm);
    margin-bottom: 1.5rem;
    border: 1px solid #a7f3d0;
    font-weight: var(--wjp-font-weight-medium);
    font-size: var(--wjp-font-size-base);
}

#cron_info_area img {
    margin-right: 12px;
    width: 24px;
    height: 24px;
}

/* Tabs Container */
div#tabs.tabs {
    background-color: var(--wjp-bg-card);
    border-radius: var(--wjp-border-radius);
    box-shadow: var(--wjp-shadow);
    border: 1px solid var(--wjp-border-color);
    padding: 2rem;
}

/* Tabs Navigation */
div#tabs > ul {
    list-style: none;
    padding: 0;
    margin: 0 0 2rem 0;
    display: flex;
    flex-wrap: wrap;
    border-bottom: 2px solid var(--wjp-border-color);
    gap: 0.5rem;
}

div#tabs > ul li {
    margin: 0;
    padding: 0;
}

div#tabs > ul li a {
    display: block;
    padding: 0.75rem 1.5rem;
    color: var(--wjp-text-medium);
    text-decoration: none;
    font-weight: var(--wjp-font-weight-medium);
    font-size: var(--wjp-font-size-base);
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    transition: all 0.2s ease;
    border-radius: var(--wjp-border-radius-sm) var(--wjp-border-radius-sm) 0 0;
}

div#tabs > ul li a:hover {
    color: var(--wjp-color-primary);
    background-color: var(--wjp-color-primary-bg);
}

div#tabs > ul li.ui-tabs-active a {
    color: var(--wjp-color-primary);
    border-bottom-color: var(--wjp-color-primary);
    background-color: transparent;
}

/* Tab Content Areas */
.wpjobportal_gen_body {
    animation: wjp-fade-in 0.3s ease-out;
}

#cron_job {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Headings inside Tabs */
span.crown_text {
    font-size: var(--wjp-font-size-lg);
    font-weight: var(--wjp-font-weight-semibold);
    color: var(--wjp-text-dark);
    display: block;
    margin-bottom: 0.5rem;
    border-bottom: 1px dashed var(--wjp-border-color);
    padding-bottom: 0.75rem;
}

/* Detail Rows */
#cron_job_detail_wrapper {
    display: flex;
    align-items: center;
    padding: 1.25rem;
    border-radius: var(--wjp-border-radius-sm);
    border: 1px solid var(--wjp-border-color);
    flex-wrap: wrap;
    gap: 1rem;
}

#cron_job_detail_wrapper.even {
    background-color: var(--wjp-bg-card);
}

#cron_job_detail_wrapper.odd {
    background-color: var(--wjp-bg-light);
}

/* Left & Right text blocks */
span.crown_text_left {
    flex: 0 0 250px;
    font-weight: var(--wjp-font-weight-semibold);
    color: var(--wjp-text-dark);
    font-size: var(--wjp-font-size-sm);
}

span.crown_text_right {
    flex: 1;
    color: var(--wjp-text-medium);
    word-break: break-all;
    background-color: var(--wjp-bg-body);
    padding: 0.75rem 1rem;
    border-radius: var(--wjp-border-radius-sm);
    border: 1px solid #d1d5db;
    font-family: monospace;
    font-size: var(--wjp-font-size-sm);
    line-height: 1.5;
}

span.crown_text_right.fullwidth {
    flex: 1 1 100%;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .wpjobportal-config-main-wrapper .wpjobportal-configurations {
        flex-direction: column;
        gap: 1rem;
    }

    .wpjobportal-configurations .wpjobportal-config-left-menu {
        width: 100%;
        display: none; /* Can be toggled with JS using the configurations-toggle */
    }

    .wpjobportal-configurations-toggle {
        display: flex;
    }

    div#tabs > ul li a {
        padding: 0.5rem 1rem;
        font-size: var(--wjp-font-size-sm);
    }

    div#tabs.tabs {
        padding: 1rem;
    }

    #cron_job_detail_wrapper {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    span.crown_text_left {
        flex: 1 1 100%;
        margin-bottom: 0.5rem;
    }
}
</style>
