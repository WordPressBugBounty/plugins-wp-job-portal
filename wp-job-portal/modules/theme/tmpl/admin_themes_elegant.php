<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
wp_enqueue_script('iris');
$rand = random_int(1,999);
wp_enqueue_style('wpjobportal-style1', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/theme-interface.css');


wp_enqueue_script('jquery');

wp_enqueue_style('wpjobportal-elegantdesign-vars', esc_url(ELEGANTDESIGN_PLUGIN_URL).'includes/css/elegantdesignvariables.css');
wp_enqueue_style('wpjobportal-elegantdesign', esc_url(ELEGANTDESIGN_PLUGIN_URL).'includes/css/elegantdesign.css');
wp_enqueue_style('wpjobportal-elegantdesign-overides', esc_url(ELEGANTDESIGN_PLUGIN_URL).'includes/css/elegant_elementor_overrides.css');

wp_enqueue_style( 'wpb-google-fonts', 'https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap', false );
wp_enqueue_style('wpjobportal-style-tablet', esc_url(ELEGANTDESIGN_PLUGIN_URL) . 'includes/css/elegantdesign_tablet.css',array(),'1.1.1','(max-width: 782px)');
wp_enqueue_style('wpjobportal-style-mobile-landscape', esc_url(ELEGANTDESIGN_PLUGIN_URL) . 'includes/css/elegantdesign_mobile.css',array(),'1.1.1','(max-width: 650px)');
wp_enqueue_style('wpjobportal-style-mobile', esc_url(ELEGANTDESIGN_PLUGIN_URL) . 'includes/css/elegantdesign_oldmobile.css',array(),'1.1.1','(max-width: 480px)');
if (is_rtl()) {
    wp_register_style('wpjobportal-elegantdesign-style-rtl', esc_url(ELEGANTDESIGN_PLUGIN_URL).'includes/css/elegantdesign_rtl.css');
    wp_enqueue_style('wpjobportal-elegantdesign-style-rtl');
}

?>
<div id="wpjobportaladmin-wrapper">
    <div id="wpjobportaladmin-leftmenu">
        <?php WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <div id="wpjobportal-wrapper-top">
            <div id="wpjobportal-wrapper-top-left">
                <div id="wpjobportal-breadcrumbs">
                    <ul>
                        <li>
                            <a href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal')); ?>"
                                title="<?php echo esc_attr(__('dashboard', 'wp-job-portal')); ?>">
                                <?php echo esc_html(__('Dashboard', 'wp-job-portal')); ?>
                            </a>
                        </li>
                        <li><?php echo esc_html(__('Colors', 'wp-job-portal')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="wpjobportal-wrapper-top-right">
                <div id="wpjobportal-config-btn">
                    <a href="admin.php?page=wpjobportal_configuration"
                        title="<?php echo esc_attr(__('configuration', 'wp-job-portal')); ?>">
                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/config.png">
                    </a>
                </div>
                <div id="wpjobportal-vers-txt">
                    <?php echo esc_html(__('Version', 'wp-job-portal')) . ': '; ?>
                    <span class="wpjobportal-ver"><?php echo esc_html(WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue('versioncode')); ?></span>
                </div>
            </div>
        </div>
        <div id="wpjobportal-head">
            <h1 class="wpjobportal-head-text">
                <?php echo esc_html(__('Colors', 'wp-job-portal')); ?>
            </h1>
        </div>
        <div id="wpjobportal-admin-wrapper" class="p0 wpjobportal-admin-themepge-wrapper">
            <div id="theme_heading">
                <div class="js_themes_presets">
                    <div class="wp-section-title">
                        <div class="wp-icon-box-txt-wrp">
                            <div class="wp-icon-box">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" data-lucide="swatch-book" class="lucide lucide-swatch-book">
                                    <path d="M11 17a4 4 0 0 1-8 0V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2Z"></path>
                                    <path d="M16.7 13H19a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2H7"></path>
                                    <path d="M 7 17h.01"></path>
                                    <path
                                        d="m11 8 2.3-2.3a2.4 2.4 0 0 1 3.404.004L18.6 7.6a2.4 2.4 0 0 1 .026 3.434L9.9 19.8">
                                    </path>
                                </svg>
                            </div>
                            <h2><?php echo esc_html(__('Presets', 'wp-job-portal')); ?></h2>
                        </div>

                        <span class="wpjb-box-styles"> 8 <?php echo esc_html(__('Styles', 'wp-job-portal')); ?></span>
                    </div>
                    <div class="theme_platte">
                        <div class="color_wrapper">
                            <div class="color 1" style="background:#36bc9a;"></div>
                            <div class="color 2" style="background:#333333;"></div>
                            <div class="color 3" style="background:#575757;"></div>
                            <a href="#" class="set_theme"><?php echo esc_html(__('Teal Slate', 'wp-job-portal')); ?></a>
                        </div>
                    </div>
                    <div class="theme_platte">
                        <div class="color_wrapper">
                            <div class="color 1" style="background:#e43039;"></div>
                            <div class="color 2" style="background:#940007;"></div>
                            <div class="color 3" style="background:#575757;"></div>
                            <a href="#" class="set_theme"><?php echo esc_html(__('Ruby Red', 'wp-job-portal')); ?></a>
                        </div>
                    </div>
                    <div class="theme_platte">
                        <div class="color_wrapper">
                            <div class="color 1" style="background:#3baeda;"></div>
                            <div class="color 2" style="background:#333333;"></div>
                            <div class="color 3" style="background:#575757;"></div>
                            <a href="#" class="set_theme"><?php echo esc_html(__('Ocean Cyan', 'wp-job-portal')); ?></a>
                        </div>
                    </div>
                    <div class="theme_platte">
                        <div class="color_wrapper">
                            <div class="color 1" style="background:#4d89dc;"></div>
                            <div class="color 2" style="background:#000000;"></div>
                            <div class="color 3" style="background:#575757;"></div>
                            <a href="#"
                                class="set_theme"><?php echo esc_html(__('Royal Azure', 'wp-job-portal')); ?></a>
                        </div>
                    </div>
                    <div class="theme_platte">
                        <div class="color_wrapper">
                            <div class="color 1" style="background:#8cc051;"></div>
                            <div class="color 2" style="background:#366600;"></div>
                            <div class="color 3" style="background:#575757;"></div>
                            <a href="#"
                                class="set_theme"><?php echo esc_html(__('Forest Lime', 'wp-job-portal')); ?></a>
                        </div>
                    </div>
                    <div class="theme_platte">
                        <div class="color_wrapper">
                            <div class="color 1" style="background:#db4453;"></div>
                            <div class="color 2" style="background:#80000d;"></div>
                            <div class="color 3" style="background:#575757;"></div>
                            <a href="#" class="set_theme"><?php echo esc_html(__('Rose Berry', 'wp-job-portal')); ?></a>
                        </div>
                    </div>
                    <div class="theme_platte">
                        <div class="color_wrapper">
                            <div class="color 1" style="background:#967bdc;"></div>
                            <div class="color 2" style="background:#391a8c;"></div>
                            <div class="color 3" style="background:#575757;"></div>
                            <a href="#"
                                class="set_theme"><?php echo esc_html(__('Deep Amethyst', 'wp-job-portal')); ?></a>
                        </div>
                    </div>
                    <div class="theme_platte">
                        <div class="color_wrapper">
                            <div class="color 1" style="background:#000000;"></div>
                            <div class="color 2" style="background:#120045;"></div>
                            <div class="color 3" style="background:#575757;"></div>
                            <a href="#"
                                class="set_theme"><?php echo esc_html(__('Midnight Mode', 'wp-job-portal')); ?></a>
                        </div>
                    </div>
                </div>
                <form action="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_theme&task=savetheme&action=task'), 'wpjobportal_theme_nonce')); ?>" method="POST" name="adminForm" id="adminForm">
                    <div class="wp-section-title wpjp-tuning-sect">
                        <div class="wp-icon-box-txt-wrp">
                            <div class="wp-icon-box pink">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" data-lucide="sliders-horizontal"
                                    class="lucide lucide-sliders-horizontal">
                                    <path d="M10 5H3"></path>
                                    <path d="M12 19H3"></path>
                                    <path d="M14 3v4"></path>
                                    <path d="M16 17v4"></path>
                                    <path d="M21 12h-9"></path>
                                    <path d="M21 19h-5"></path>
                                    <path d="M21 5h-7"></path>
                                    <path d="M8 10v4"></path>
                                    <path d="M8 12H3"></path>
                                </svg>
                            </div>
                            <h2><?php echo esc_html(__('Fine Tuning', 'wp-job-portal')); ?></h2>
                        </div>
                    </div>
                    <div class="color_portion">
                        <span class="color_title">
                            <?php echo esc_html(__('Primary Color', 'wp-job-portal')); ?>
                        </span>
                        <span class="color_wrp">
                            <input type="text" name="color1" id="color1" value="<?php echo esc_attr(wpjobportal::$_data[0]['color1']); ?>" style="background:<?php echo esc_attr(wpjobportal::$_data[0]['color1']); ?> !important;" />
                            <span class="color_wrp_img">
                                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/themes/colorpicker.png">
                            </span>
                        </span>
                    </div>
                    <div class="color_portion">
                        <span class="color_title">
                            <?php echo esc_html(__('Secondary Color', 'wp-job-portal')); ?></span>
                        <span class="color_wrp">
                            <input type="text" name="color2" id="color2" value="<?php echo esc_attr(wpjobportal::$_data[0]['color2']); ?>" style="background:<?php echo esc_attr(wpjobportal::$_data[0]['color2']); ?> !important;" />
                            <span class="color_wrp_img">
                                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/themes/colorpicker.png">
                            </span>
                        </span>
                    </div>
                    <div class="color_portion">
                        <span class="color_title">
                            <?php echo esc_html(__('Content Color', 'wp-job-portal')); ?>
                        </span>
                        <span class="color_wrp">
                            <input type="text" name="color3" id="color3" value="<?php echo esc_attr(wpjobportal::$_data[0]['color3']); ?>" style="background:<?php echo esc_attr(wpjobportal::$_data[0]['color3']); ?> !important;" />
                            <span class="color_wrp_img">
                                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/themes/colorpicker.png">
                            </span>
                        </span>
                    </div>
                    <input type="hidden" name="form_request" value="wpjobportal" />
                </form>
                <div class="wpjobportal-add-button-wrp">
                    <a class="wpjobportal-add-link button" id="saveColors" href="#"
                        title="<?php echo esc_attr(__('Save Colors', 'wp-job-portal')) ?>">
                        <?php echo esc_html(__('Save Colors', 'wp-job-portal')) ?>
                    </a>
                    <div class="wpjobportal-sugestion-alert-wrp">
                        <div class="wpjobportal-sugestion-alert">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="info" size="14" style="display:inline; vertical-align:middle;" class="lucide lucide-info"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>
                            <?php echo esc_html(__('Some changes may require clearing your cache to take effect.', 'wp-job-portal')) ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="js_effect_preview">
                <div class="js_preview-sidetitle">
                    <div class="js_preview-sidetitle_icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="36" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            data-lucide="monitor" style="color: var(--primary);" class="lucide lucide-monitor">
                            <rect width="20" height="14" x="2" y="3" rx="2"></rect>
                            <line x1="8" x2="16" y1="21" y2="21"></line>
                            <line x1="12" x2="12" y1="17" y2="21"></line>
                        </svg>
                    </div>
                    <?php echo esc_html(__('Live Preview', 'wp-job-portal')); ?>
                </div>

                <div class="wjportal-elegant-addon-main-up-wrapper">
                    <div class="wjportal-elegant-addon-main-wrapper wjportal-elegant-addon-clearfix">
                        <div class="wjportal-elegant-addon-page-header">
                            <div class="wjportal-elegant-addon-page-header-cnt">
                                <div class="wjportal-page-heading"><?php echo esc_html(__('My Jobs', 'wp-job-portal')); ?></div>
                            </div>
                            <div class="wjportal-elegant-addon-header-actions">
                                <div class="wjportal-elegant-addon-act-btn-wrp">
                                    <a class="wjportal-elegant-addon-act-btn" href="#">
                                        <img decoding="async" class="wjportal-elegant-addon-addjob-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/add.png" alt="Add">
                                        <?php echo esc_html(__('Add New Job', 'wp-job-portal')); ?>
                                    </a>
                                </div>
                                <div class="wjportal-elegant-addon-filter-wrp">
                                    <div class="wjportal-elegant-addon-filter">
                                        <select name="sorting" id="sorting">
                                            <option value=""><?php echo esc_html(__('Default', 'wp-job-portal')); ?></option>
                                            <option class="" value="1"><?php echo esc_html(__('Job Title', 'wp-job-portal')); ?></option>
                                            <option class="" value="2"><?php echo esc_html(__('Company Name', 'wp-job-portal')); ?></option>
                                            <option class="" value="3"><?php echo esc_html(__('Category', 'wp-job-portal')); ?></option>
                                            <option class="" value="5"><?php echo esc_html(__('Location', 'wp-job-portal')); ?></option>
                                            <option class="" value="7"><?php echo esc_html(__('Status', 'wp-job-portal')); ?></option>
                                            <option class="" value="4"><?php echo esc_html(__('Job Type', 'wp-job-portal')); ?></option>
                                            <option class="" selected="selected" value="6"><?php echo esc_html(__('Created', 'wp-job-portal')); ?></option>
                                            <option class="" value="8"><?php echo esc_html(__('Salary', 'wp-job-portal')); ?></option>
                                        </select>
                                    </div>
                                    <div class="wjportal-elegant-addon-filter-image">
                                        <a class="sort-icon" href="#" data-image1="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/sort-up.png" data-image2="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/sort-down.png" data-sortby="2">
                                            <img decoding="async" id="sortingimage" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/sort-down.png">
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="wjportal-elegant-addon-jobs-list-wrapper wjportal-elegant-addon-my-jobs-wrp">
                            <div id="job_form_preview">

                                <div class="wjportal-elegant-addon-jobs-list " data-boxid="job_1">
                                    <div class="wjportal-elegant-addon-jobs-list-top-wrp object_1">
                                        <div class="wjportal-elegant-addon-jobs-cnt-wrp">
                                            <div class="wjportal-elegant-addon-jobs-middle-wrp">
                                                <div class="wjportal-elegant-addon-jobs-data wjportal-elegant-addon-jobs-data-wrp">
                                                    <span class="wjportal-elegant-addon-item-status" style="background:#00a859;"><?php echo esc_html(__('Publish', 'wp-job-portal')); ?></span>
                                                    <a class="wjportal-elegant-addon-companyname" href="#"><?php echo esc_html(__('Buruj Solution', 'wp-job-portal')); ?></a>
                                                    <span class="wjportal-elegant-addon-jobs-location-text">
                                                        <img decoding="async" class="wjportal-elegant-addon-filter-search-field-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/loacation-g.png" title="Location" alt="Location">
                                                        <?php echo esc_html(__('Santa Barbara, California, United States', 'wp-job-portal')); ?>
                                                    </span>
                                                </div>
                                                <div class="wjportal-elegant-addon-jobs-data">
                                                    <span class="wjportal-elegant-addon-job-title">
                                                        <a href="#"><?php echo esc_html(__('PHP Developer', 'wp-job-portal')); ?></a>
                                                    </span>
                                                </div>
                                                <div class="wjportal-elegant-addon-jobs-keyvalue">
                                                    <span class="wjportal-elegant-addon-jobs-info">
                                                        <span class="wjportal-elegant-addon-job-type">
                                                            <img decoding="async" class="wjportal-elegant-addon-filter-search-field-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/type.png" title="Job Type" alt="Job Type">
                                                            <?php echo esc_html(__('Full-Time', 'wp-job-portal')); ?>
                                                        </span>
                                                    </span>
                                                    <span class="wjportal-elegant-addon-jobs-data-text">
                                                        <img decoding="async" class="wjportal-elegant-addon-filter-search-field-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/category.png" title="Category" alt="Category">
                                                        <?php echo esc_html(__('Computer/IT', 'wp-job-portal')); ?>
                                                    </span>
                                                    <span class="wjportal-elegant-addon-jobs-salary">
                                                        <img decoding="async" class="wjportal-elegant-addon-filter-search-field-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/salary.png" title="Salary" alt="Salary">
                                                        1,000 - 1,500 $ <span class="wjportal-elegant-addon-salary-type"> / <?php echo esc_html(__('Per Month', 'wp-job-portal')); ?></span>
                                                    </span>
                                                    <span class="wjportal-elegant-addon-posted-info">
                                                        <img decoding="async" class="wjportal-elegant-addon-filter-search-field-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/posted.png" title="Posted" alt="Posted">
                                                        <?php echo esc_html(__('1 week Ago', 'wp-job-portal')); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="wjportal-elegant-addon-jobs-logo">
                                            <a href="#">
                                                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/default_logo.png" alt="Company logo">
                                            </a>
                                        </div>
                                    </div>
                                    <div class="wjportal-elegant-addon-jobs-discription">
                                        <?php echo esc_html(__('Responsibilities Work closely with Project Managers and other members of the Development Team to both develop detailed specification documents with clear project deliverables and timelines, and to ensure timely completion of deliverables. Produce project estimates during sales process, including expertise required, total number of people required, total number of development hours required, etc. Attend client meetings during the sales process and during development. Work with clients and Project Managers to build and refine graphic designs for websites. Must have strong skills in Photoshop, Fireworks, or equivalent application(s). Convert raw images and layouts from a graphic designer into CSS/XHTML themes. Determine appropriate architecture, and other technical solutions, and make relevant recommendations to clients. Communicate to the Project Manager with efficiency and accuracy any progress and/or delays. Engage in outside-the-box thinking to provide high value-of-service to clients. Alert colleagues to emerging technologies or applications and the opportunities to integrate them into operations and activities. Be actively involved in and contribute regularly to the development community of the CMS of your choice. Develop innovative, reusable Web-based tools for activism and community building.', 'wp-job-portal')); ?>
                                    </div>
                                    <div class="wjportal-elegant-addon-jobs-list-btm-wrp wjportal-elegant-addon-btn-wrp-ai">
                                        <div class="wjportal-elegant-addon-myjobs-btn-wrp">
                                            <a class="wjportal-elegant-addon-jobs-colored-btn" title="Edit" href="#">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-black-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/edit-black.png" alt="Edit">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-white-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/edit-white.png" alt="Edit">
                                                <?php echo esc_html(__('Edit Job', 'wp-job-portal')); ?>
                                            </a>
                                            <span class="wjportal-elegant-addon-jobs-feature-wrp">
                                                <a href="#" class="wjportal-jobs-act-btn wjportal-list-act-btn-featured" title="Add featured">
                                                    <?php echo esc_html(__('Add Featured', 'wp-job-portal')); ?>
                                                </a>
                                            </span>
                                            <a class="wjportal-elegant-addon-jobs-act-btn" href="#">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-black-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/del-black.png" title="Delete" alt="Delete">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-white-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/del-white.png" title="Delete" alt="Delete">
                                                <?php echo esc_html(__('Delete Job', 'wp-job-portal')); ?>
                                            </a>

                                            <a class="wjportal-elegant-addon-jobs-act-btn wjportal-elegant-addon-jobs-apply-res" title="Resume" href="#">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-black-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/resume-black.png" alt="Resume">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-white-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/resume-white.png" alt="Resume">
                                                <?php echo esc_html(__('Resume (1)', 'wp-job-portal')); ?>
                                            </a>
                                            <a class="wjportal-elegant-addon-jobs-act-btn wjportal-elegant-addon-jobs-act-btn-ai-suggested-resumes" href="#">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-white-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/ai-button-icon-white.png" title="Suggested Resumes" alt="Suggested Resumes">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-black-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/ai-button-icon-white.png" title="Suggested Resumes" alt="Suggested Resumes">
                                                <?php echo esc_html(__('Suggested Resumes', 'wp-job-portal')); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="wjportal-elegant-addon-jobs-list " data-boxid="job_2">
                                    <div class="wjportal-elegant-addon-jobs-list-top-wrp object_2">
                                        <div class="wjportal-elegant-addon-jobs-cnt-wrp">
                                            <div class="wjportal-elegant-addon-jobs-middle-wrp">
                                                <div class="wjportal-elegant-addon-jobs-data wjportal-elegant-addon-jobs-data-wrp">
                                                    <span class="wjportal-elegant-addon-item-status" style="background:#00a859;"><?php echo esc_html(__('Publish', 'wp-job-portal')); ?></span>
                                                    <a class="wjportal-elegant-addon-companyname" href="#"><?php echo esc_html(__('Joom Sky', 'wp-job-portal')); ?></a>
                                                    <span class="wjportal-elegant-addon-jobs-location-text">
                                                        <img decoding="async" class="wjportal-elegant-addon-filter-search-field-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/loacation-g.png" title="Location" alt="Location">
                                                        <?php echo esc_html(__('Ventura, California, United States', 'wp-job-portal')); ?>
                                                    </span>
                                                </div>
                                                <div class="wjportal-elegant-addon-jobs-data">
                                                    <span class="wjportal-elegant-addon-job-title">
                                                        <a href="#"><?php echo esc_html(__('Android Developer', 'wp-job-portal')); ?></a>
                                                    </span>
                                                </div>
                                                <div class="wjportal-elegant-addon-jobs-keyvalue">
                                                    <span class="wjportal-elegant-addon-jobs-info">
                                                        <span class="wjportal-elegant-addon-job-type">
                                                            <img decoding="async" class="wjportal-elegant-addon-filter-search-field-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/type.png" title="Job Type" alt="Job Type">
                                                            <?php echo esc_html(__('Full-Time', 'wp-job-portal')); ?>
                                                        </span>
                                                    </span>
                                                    <span class="wjportal-elegant-addon-jobs-data-text">
                                                        <img decoding="async" class="wjportal-elegant-addon-filter-search-field-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/category.png" title="Category" alt="Category">
                                                        <?php echo esc_html(__('Computer/IT', 'wp-job-portal')); ?>
                                                    </span>
                                                    <span class="wjportal-elegant-addon-jobs-salary">
                                                        <img decoding="async" class="wjportal-elegant-addon-filter-search-field-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/salary.png" title="Salary" alt="Salary">
                                                        2,500 - 3,000 $ <span class="wjportal-elegant-addon-salary-type"> / <?php echo esc_html(__('Per Month', 'wp-job-portal')); ?></span>
                                                    </span>
                                                    <span class="wjportal-elegant-addon-posted-info">
                                                        <img decoding="async" class="wjportal-elegant-addon-filter-search-field-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/posted.png" title="Posted" alt="Posted">
                                                        <?php echo esc_html(__('1 week Ago', 'wp-job-portal')); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="wjportal-elegant-addon-jobs-logo">
                                            <a href="#">
                                                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/default_logo.png" alt="Company logo">
                                            </a>
                                        </div>
                                    </div>
                                    <div class="wjportal-elegant-addon-jobs-discription">
                                        <?php echo esc_html(__('Games developers are involved in the creation and production of games for personal computers, games consoles, social/online games, arcade games, tablets, mobile phones and other hand held devices. Their work involves either design (including art and animation) or programming. Games development is a fast-moving, multi-billion pound industry. The making of a game from concept to finished product can take up to three years and involve teams of up to 200 professionals. There are many stages, including creating and designing a game’s look and how it plays, animating characters and objects, creating audio, programming, localisation, testing and producing. The games developer job title covers a broad area of work and there are many specialisms within the industry. These include: quality assurance tester; programmer, with various specialisms such as network, engine, toolchain and artificial intelligence; audio engineer; artist, including concept artist, animator and 3D modeller; producer; editor; designer; special effects technician.', 'wp-job-portal')); ?>
                                    </div>
                                    <div class="wjportal-elegant-addon-jobs-list-btm-wrp wjportal-elegant-addon-btn-wrp-ai">
                                        <div class="wjportal-elegant-addon-myjobs-btn-wrp">
                                            <a class="wjportal-elegant-addon-jobs-colored-btn" title="Edit" href="#">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-black-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/edit-black.png" alt="Edit">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-white-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/edit-white.png" alt="Edit">
                                                <?php echo esc_html(__('Edit Job', 'wp-job-portal')); ?>
                                            </a>
                                            <span class="wjportal-elegant-addon-jobs-feature-wrp">
                                                <a href="#" class="wjportal-jobs-act-btn wjportal-list-act-btn-featured" title="Add featured">
                                                    <?php echo esc_html(__('Add Featured', 'wp-job-portal')); ?>
                                                </a>
                                            </span>
                                            <a class="wjportal-elegant-addon-jobs-act-btn" href="#">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-black-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/del-black.png" title="Delete" alt="Delete">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-white-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/del-white.png" title="Delete" alt="Delete">
                                                <?php echo esc_html(__('Delete Job', 'wp-job-portal')); ?>
                                            </a>

                                            <a class="wjportal-elegant-addon-jobs-act-btn wjportal-elegant-addon-jobs-apply-res" title="Resume" href="#">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-black-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/resume-black.png" alt="Resume">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-white-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/resume-white.png" alt="Resume">
                                                <?php echo esc_html(__('Resume (1)', 'wp-job-portal')); ?>
                                            </a>
                                            <a class="wjportal-elegant-addon-jobs-act-btn wjportal-elegant-addon-jobs-act-btn-ai-suggested-resumes" href="#">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-white-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/ai-button-icon-white.png" title="Suggested Resumes" alt="Suggested Resumes">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-black-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/ai-button-icon-white.png" title="Suggested Resumes" alt="Suggested Resumes">
                                                <?php echo esc_html(__('Suggested Resumes', 'wp-job-portal')); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="wjportal-elegant-addon-jobs-list " data-boxid="job_3">
                                    <div class="wjportal-elegant-addon-jobs-list-top-wrp object_3">
                                        <div class="wjportal-elegant-addon-jobs-cnt-wrp">
                                            <div class="wjportal-elegant-addon-jobs-middle-wrp">
                                                <div class="wjportal-elegant-addon-jobs-data wjportal-elegant-addon-jobs-data-wrp">
                                                    <span class="wjportal-elegant-addon-item-status" style="background:#00a859;"><?php echo esc_html(__('Publish', 'wp-job-portal')); ?></span>
                                                    <a class="wjportal-elegant-addon-companyname" href="#"><?php echo esc_html(__('Joom Shark', 'wp-job-portal')); ?></a>
                                                    <span class="wjportal-elegant-addon-jobs-location-text">
                                                        <img decoding="async" class="wjportal-elegant-addon-filter-search-field-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/loacation-g.png" title="Location" alt="Location">
                                                        <?php echo esc_html(__('Leona, Kansas, United States', 'wp-job-portal')); ?>
                                                    </span>
                                                </div>
                                                <div class="wjportal-elegant-addon-jobs-data">
                                                    <span class="wjportal-elegant-addon-job-title">
                                                        <a href="#"><?php echo esc_html(__('Accountant', 'wp-job-portal')); ?></a>
                                                    </span>
                                                </div>
                                                <div class="wjportal-elegant-addon-jobs-keyvalue">
                                                    <span class="wjportal-elegant-addon-jobs-info">
                                                        <span class="wjportal-elegant-addon-job-type">
                                                            <img decoding="async" class="wjportal-elegant-addon-filter-search-field-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/type.png" title="Job Type" alt="Job Type">
                                                            <?php echo esc_html(__('Full-Time', 'wp-job-portal')); ?>
                                                        </span>
                                                    </span>
                                                    <span class="wjportal-elegant-addon-jobs-data-text">
                                                        <img decoding="async" class="wjportal-elegant-addon-filter-search-field-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/category.png" title="Category" alt="Category">
                                                        <?php echo esc_html(__('Computer/IT', 'wp-job-portal')); ?>
                                                    </span>
                                                    <span class="wjportal-elegant-addon-jobs-salary">
                                                        <img decoding="async" class="wjportal-elegant-addon-filter-search-field-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/salary.png" title="Salary" alt="Salary">
                                                        4,000 - 4,500 $ <span class="wjportal-elegant-addon-salary-type"> / <?php echo esc_html(__('Per Month', 'wp-job-portal')); ?></span>
                                                    </span>
                                                    <span class="wjportal-elegant-addon-posted-info">
                                                        <img decoding="async" class="wjportal-elegant-addon-filter-search-field-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/posted.png" title="Posted" alt="Posted">
                                                        <?php echo esc_html(__('1 week Ago', 'wp-job-portal')); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="wjportal-elegant-addon-jobs-logo">
                                            <a href="#">
                                                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/default_logo.png" alt="Company logo">
                                            </a>
                                        </div>
                                    </div>
                                    <div class="wjportal-elegant-addon-jobs-discription">
                                        <?php echo esc_html(__('Accountant Job Duties: Prepares asset, liability, and capital account entries by compiling and analyzing account information. Documents financial transactions by entering account information. Recommends financial actions by analyzing accounting options. Summarizes current financial status by collecting information; preparing balance sheet, profit and loss statement, and other reports. Substantiates financial transactions by auditing documents. Maintains accounting controls by preparing and recommending policies and procedures. Guides accounting clerical staff by coordinating activities and answering questions. Reconciles financial discrepancies by collecting and analyzing account information. Secures financial information by completing data base backups. Maintains financial security by following internal controls. Prepares payments by verifying documentation, and requesting disbursements. Answers accounting procedure questions by researching and interpreting accounting policy and regulations. Complies with federal, state, and local financial legal requirements by studying existing and new legislation, enforcing adherence to requirements, and advising management on needed actions. Prepares special financial reports by collecting, analyzing, and summarizing account information and trends. Maintains customer confidence and protects operations by keeping financial information confidential. Maintains professional and technical knowledge by attending educational workshops; reviewing professional publications; establishing personal networks; participating in professional societies. Accomplishes the result by performing the duty. Contributes to team effort by accomplishing related results as needed.', 'wp-job-portal')); ?>
                                    </div>
                                    <div class="wjportal-elegant-addon-jobs-list-btm-wrp wjportal-elegant-addon-btn-wrp-ai">
                                        <div class="wjportal-elegant-addon-myjobs-btn-wrp">
                                            <a class="wjportal-elegant-addon-jobs-colored-btn" title="Edit" href="#">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-black-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/edit-black.png" alt="Edit">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-white-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/edit-white.png" alt="Edit">
                                                <?php echo esc_html(__('Edit Job', 'wp-job-portal')); ?>
                                            </a>
                                            <span class="wjportal-elegant-addon-jobs-feature-wrp">
                                                <a href="#" class="wjportal-jobs-act-btn wjportal-list-act-btn-featured" title="Add featured">
                                                    <?php echo esc_html(__('Add Featured', 'wp-job-portal')); ?>
                                                </a>
                                            </span>
                                            <a class="wjportal-elegant-addon-jobs-act-btn" href="#">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-black-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/del-black.png" title="Delete" alt="Delete">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-white-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/del-white.png" title="Delete" alt="Delete">
                                                <?php echo esc_html(__('Delete Job', 'wp-job-portal')); ?>
                                            </a>

                                            <a class="wjportal-elegant-addon-jobs-act-btn wjportal-elegant-addon-jobs-apply-res" title="Resume" href="#">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-black-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/resume-black.png" alt="Resume">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-white-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/resume-white.png" alt="Resume">
                                                <?php echo esc_html(__('Resume (1)', 'wp-job-portal')); ?>
                                            </a>
                                            <a class="wjportal-elegant-addon-jobs-act-btn wjportal-elegant-addon-jobs-act-btn-ai-suggested-resumes" href="#">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-white-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/ai-button-icon-white.png" title="Suggested Resumes" alt="Suggested Resumes">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-black-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/ai-button-icon-white.png" title="Suggested Resumes" alt="Suggested Resumes">
                                                <?php echo esc_html(__('Suggested Resumes', 'wp-job-portal')); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="wjportal-elegant-addon-jobs-list " data-boxid="job_4">
                                    <div class="wjportal-elegant-addon-jobs-list-top-wrp object_4">
                                        <div class="wjportal-elegant-addon-jobs-cnt-wrp">
                                            <div class="wjportal-elegant-addon-jobs-middle-wrp">
                                                <div class="wjportal-elegant-addon-jobs-data wjportal-elegant-addon-jobs-data-wrp">
                                                    <span class="wjportal-elegant-addon-item-status" style="background:#00a859;"><?php echo esc_html(__('Publish', 'wp-job-portal')); ?></span>
                                                    <a class="wjportal-elegant-addon-companyname" href="#"><?php echo esc_html(__('Sample Company', 'wp-job-portal')); ?></a>
                                                    <span class="wjportal-elegant-addon-jobs-location-text">
                                                        <img decoding="async" class="wjportal-elegant-addon-filter-search-field-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/loacation-g.png" title="Location" alt="Location">
                                                        <?php echo esc_html(__('Sheboygan, Wisconsin, United States', 'wp-job-portal')); ?>
                                                    </span>
                                                </div>
                                                <div class="wjportal-elegant-addon-jobs-data">
                                                    <span class="wjportal-elegant-addon-job-title">
                                                        <a href="#"><?php echo esc_html(__('Senior Software Engineer', 'wp-job-portal')); ?></a>
                                                    </span>
                                                </div>
                                                <div class="wjportal-elegant-addon-jobs-keyvalue">
                                                    <span class="wjportal-elegant-addon-jobs-info">
                                                        <span class="wjportal-elegant-addon-job-type">
                                                            <img decoding="async" class="wjportal-elegant-addon-filter-search-field-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/type.png" title="Job Type" alt="Job Type">
                                                            <?php echo esc_html(__('Full-Time', 'wp-job-portal')); ?>
                                                        </span>
                                                    </span>
                                                    <span class="wjportal-elegant-addon-jobs-data-text">
                                                        <img decoding="async" class="wjportal-elegant-addon-filter-search-field-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/category.png" title="Category" alt="Category">
                                                        <?php echo esc_html(__('Computer/IT', 'wp-job-portal')); ?>
                                                    </span>
                                                    <span class="wjportal-elegant-addon-jobs-salary">
                                                        <img decoding="async" class="wjportal-elegant-addon-filter-search-field-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/salary.png" title="Salary" alt="Salary">
                                                        4,500 $ <span class="wjportal-elegant-addon-salary-type"> / <?php echo esc_html(__('Per Month', 'wp-job-portal')); ?></span>
                                                    </span>
                                                    <span class="wjportal-elegant-addon-posted-info">
                                                        <img decoding="async" class="wjportal-elegant-addon-filter-search-field-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/posted.png" title="Posted" alt="Posted">
                                                        <?php echo esc_html(__('1 week Ago', 'wp-job-portal')); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="wjportal-elegant-addon-jobs-logo">
                                            <a href="#">
                                                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/default_logo.png" alt="Company logo">
                                            </a>
                                        </div>
                                    </div>
                                    <div class="wjportal-elegant-addon-jobs-discription">
                                        <?php echo esc_html(__('You might be responsible for the replacement of a whole system based on the specifications provided by an IT analyst but often you’ll work with ‘off the shelf’ software, modifying it and integrating it into the existing network. The skill in this is creating the code to link the systems together. You’ll also be responsible for: Reviewing current systems Presenting ideas for system improvements, including cost proposals Working closely with analysts, designers and staff Producing detailed specifications and writing the programme codes Testing the product in controlled, real situations before going live Preparation of training manuals for users Maintaining the systems once they are up and running', 'wp-job-portal')); ?>
                                    </div>
                                    <div class="wjportal-elegant-addon-jobs-list-btm-wrp wjportal-elegant-addon-btn-wrp-ai">
                                        <div class="wjportal-elegant-addon-myjobs-btn-wrp">
                                            <a class="wjportal-elegant-addon-jobs-colored-btn" title="Edit" href="#">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-black-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/edit-black.png" alt="Edit">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-white-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/edit-white.png" alt="Edit">
                                                <?php echo esc_html(__('Edit Job', 'wp-job-portal')); ?>
                                            </a>
                                            <span class="wjportal-elegant-addon-jobs-feature-wrp">
                                                <a href="#" class="wjportal-jobs-act-btn wjportal-list-act-btn-featured" title="Add featured">
                                                    <?php echo esc_html(__('Add Featured', 'wp-job-portal')); ?>
                                                </a>
                                            </span>
                                            <a class="wjportal-elegant-addon-jobs-act-btn" href="#">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-black-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/del-black.png" title="Delete" alt="Delete">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-white-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/del-white.png" title="Delete" alt="Delete">
                                                <?php echo esc_html(__('Delete Job', 'wp-job-portal')); ?>
                                            </a>

                                            <a class="wjportal-elegant-addon-jobs-act-btn wjportal-elegant-addon-jobs-apply-res" title="Resume" href="#">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-black-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/resume-black.png" alt="Resume">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-white-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/resume-white.png" alt="Resume">
                                                <?php echo esc_html(__('Resume (1)', 'wp-job-portal')); ?>
                                            </a>
                                            <a class="wjportal-elegant-addon-jobs-act-btn wjportal-elegant-addon-jobs-act-btn-ai-suggested-resumes" href="#">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-white-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/ai-button-icon-white.png" title="Suggested Resumes" alt="Suggested Resumes">
                                                <img decoding="async" class="wjportal-elegant-addon-myjobs-btn-black-icon" src="<?php echo esc_url(ELEGANTDESIGN_PLUGIN_URL); ?>includes/images/ai-button-icon-white.png" title="Suggested Resumes" alt="Suggested Resumes">
                                                <?php echo esc_html(__('Suggested Resumes', 'wp-job-portal')); ?>
                                            </a>
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
</div>

<?php
wp_register_script('wpjobportal-inline-handle', '');
wp_enqueue_script('wpjobportal-inline-handle');

$inline_js_script = "
        jQuery(document).ready(function () {
            makeColorPicker('" . esc_js(wpjobportal::$_data[0]['color1']) . "', '" . esc_js(wpjobportal::$_data[0]['color2']) . "', '" . esc_js(wpjobportal::$_data[0]['color3']) . "');

        function makeColorPicker(color1, color2, color3) {
            jQuery('input#color1').iris({
                color: color1,
                onShow: function (colpkr) {
                    jQuery(colpkr).fadeIn(500);
                    return false;
                },
                onHide: function (colpkr) {
                    jQuery(colpkr).fadeOut(500);
                    return false;
                },
                change: function (c_event, ui) {
                    var hex = ui.color.toString();
                    jQuery('.wjportal-elegant-addon-act-btn-wrp .wjportal-elegant-addon-act-btn').css('background-color', hex);
                    jQuery('.wjportal-elegant-addon-jobs-colored-btn').css('background-color', hex);
                    jQuery('.wjportal-elegant-addon-jobs-act-btn-ai-suggested-resumes').css('background-color', hex);
                    jQuery('.wjportal-breadcrumbs-firstlinks .wjportal-breadcrumbs-link').css('color', hex);
                    jQuery('.wjportal-elegant-addon-companyname').css('color', hex);
                    jQuery('.wjportal-jobs-act-btn.wjportal-list-act-btn-featured').css('color', hex);

                    jQuery('input#color1').attr('style', 'background-color: ' + hex + ' !important;');

                    jQuery('.wjportal-elegant-addon-act-btn-wrp .wjportal-elegant-addon-act-btn').hover(function () {
                        jQuery(this).css('background-color', jQuery('input#color2').val());
                    }, function () {
                        jQuery(this).css('background-color', jQuery('input#color1').val());
                    });
                    jQuery('.wjportal-elegant-addon-jobs-colored-btn').hover(function () {
                        jQuery(this).css('background-color', jQuery('input#color2').val());
                    }, function () {
                        jQuery(this).css('background-color', jQuery('input#color1').val());
                    });
                    jQuery('.wjportal-elegant-addon-jobs-act-btn-ai-suggested-resumes').hover(function () {
                        jQuery(this).css('background-color', jQuery('input#color2').val());
                    }, function () {
                        jQuery(this).css('background-color', jQuery('input#color1').val());
                    });
                    jQuery('.wjportal-elegant-addon-companyname').hover(function () {
                        jQuery(this).css('color', jQuery('input#color2').val());
                    }, function () {
                        jQuery(this).css('color', jQuery('input#color1').val());
                    });
                    jQuery('.wjportal-jobs-act-btn.wjportal-list-act-btn-featured').hover(function () {
                        jQuery(this).css('background-color', jQuery('input#color2').val());
                        jQuery(this).css('color', '#fff');
                    }, function () {
                        jQuery(this).css('background-color', 'transparent');
                        jQuery(this).css('color', jQuery('input#color1').val());
                    });
                }
            });
            jQuery('input#color2').iris({
                color: color2,
                onShow: function (colpkr) {
                    jQuery(colpkr).fadeIn(500);
                    return false;
                },
                onHide: function (colpkr) {
                    jQuery(colpkr).fadeOut(500);
                    return false;
                },
                change: function (c_event, ui) {
                    var hex = ui.color.toString();
                    jQuery('.wjportal-page-heading').css('color', hex);
                    jQuery('.wjportal-elegant-addon-job-title a').css('color', hex);
                    jQuery('.wjportal-elegant-addon-jobs-salary').css('color', hex);
                    jQuery('.wjportal-elegant-addon-jobs-act-btn').css('color', hex);

                    jQuery('input#color2').attr('style', 'background-color: ' + hex + ' !important;');

                    jQuery('.wjportal-elegant-addon-jobs-act-btn:not(.wjportal-elegant-addon-jobs-act-btn-ai-suggested-resumes)').hover(function () {
                        jQuery(this).css('background-color', jQuery('input#color2').val());
                        jQuery(this).css('color', '#fff');
                    }, function () {
                        jQuery(this).css('background-color', 'transparent');
                        jQuery(this).css('color', jQuery('input#color2').val());
                    });
                    // Re-apply hover for color1 elements to match new color2
                    jQuery('.wjportal-elegant-addon-act-btn-wrp .wjportal-elegant-addon-act-btn').hover(function () {
                        jQuery(this).css('background-color', jQuery('input#color2').val());
                    }, function () {
                        jQuery(this).css('background-color', jQuery('input#color1').val());
                    });
                    jQuery('.wjportal-elegant-addon-companyname').hover(function () {
                        jQuery(this).css('color', jQuery('input#color2').val());
                    }, function () {
                        jQuery(this).css('color', jQuery('input#color1').val());
                    });
                }
            });
            jQuery('input#color3').iris({
                color: color3,
                onShow: function (colpkr) {
                    jQuery(colpkr).fadeIn(500);
                    return false;
                },
                onHide: function (colpkr) {
                    jQuery(colpkr).fadeOut(500);
                    return false;
                },
                change: function (c_event, ui) {
                    var hex = ui.color.toString();
                    jQuery('.wjportal-breadcrumbs-lastlink').css('color', hex);
                    jQuery('.wjportal-elegant-addon-jobs-data-text').css('color', hex);
                    jQuery('.wjportal-elegant-addon-jobs-location-text').css('color', hex);
                    jQuery('.wjportal-elegant-addon-jobs-discription').css('color', hex);
                    jQuery('.wjportal-elegant-addon-posted-info').css('color', hex);
                    jQuery('.wjportal-elegant-addon-job-type').css('color', hex);

                    jQuery('.wjportal-elegant-addon-filter select').attr('style', 'color: ' + hex + ' !important');
                    jQuery('input#color3').attr('style', 'background-color: ' + hex + ' !important;');
                }
            });

            // Iris utility
            jQuery(document).click(function (e) {
                if (!jQuery(e.target).is('#color1, #color2, #color3')) {
                    jQuery('#color1, #color2, #color3').iris('hide');
                }
            });
            jQuery('#color1, #color2, #color3').click(function () {
                jQuery('#color1, #color2, #color3').iris('hide');
                jQuery(this).iris('show');
                return false;
            });
        }
        });
    ";
wp_add_inline_script('wpjobportal-inline-handle', $inline_js_script);

$inline_js_script2 = "
        jQuery(document).ready(function () {
            jQuery('#saveColors').click(function (e) {
                jQuery('form#adminForm').submit();
            });

            jQuery('a.set_theme').each(function () {
                jQuery(this).click(function (e) {
                    e.preventDefault();
                    var div = jQuery(this).parent();
                    var color1 = rgb2hex(jQuery(div.find('div.1')).css('background-color'));
                    var color2 = rgb2hex(jQuery(div.find('div.2')).css('background-color'));
                    var color3 = rgb2hex(jQuery(div.find('div.3')).css('background-color'));

                    jQuery('input#color1').attr('style', 'background-color: ' + color1 + ' !important;');
                    jQuery('input#color2').attr('style', 'background-color: ' + color2 + ' !important;');
                    jQuery('input#color3').attr('style', 'background-color: ' + color3 + ' !important;');
                    jQuery('input#color1').val(color1);
                    jQuery('input#color2').val(color2);
                    jQuery('input#color3').val(color3);

                    themeSelectionEffect();
                });
            });
        });

        function rgb2hex(rgb) {
            rgb = rgb.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+))?\)$/);
            function hex(x) {
                return ('0' + parseInt(x).toString(16)).slice(-2);
            }
            return '#' + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
        }

        function themeSelectionEffect() {
            var color1 = jQuery('input#color1').val();
            var color2 = jQuery('input#color2').val();
            var color3 = jQuery('input#color3').val();

            // Apply Color 1
            jQuery('.wjportal-elegant-addon-act-btn-wrp .wjportal-elegant-addon-act-btn').css('background-color', color1);
            jQuery('.wjportal-elegant-addon-jobs-colored-btn').css('background-color', color1);
            jQuery('.wjportal-elegant-addon-jobs-act-btn-ai-suggested-resumes').css('background-color', color1);
            jQuery('.wjportal-breadcrumbs-firstlinks .wjportal-breadcrumbs-link').css('color', color1);
            jQuery('.wjportal-elegant-addon-companyname').css('color', color1);
            jQuery('.wjportal-jobs-act-btn.wjportal-list-act-btn-featured').css('color', color1);

            // Apply Color 2
            jQuery('.wjportal-page-heading').css('color', color2);
            jQuery('.wjportal-elegant-addon-job-title a').css('color', color2);
            jQuery('.wjportal-elegant-addon-jobs-salary').css('color', color2);
            jQuery('.wjportal-elegant-addon-jobs-act-btn').css('color', color2);

            // Apply Color 3
            jQuery('.wjportal-breadcrumbs-lastlink').css('color', color3);
            jQuery('.wjportal-elegant-addon-jobs-data-text').css('color', color3);
            jQuery('.wjportal-elegant-addon-jobs-location-text').css('color', color3);
            jQuery('.wjportal-elegant-addon-jobs-discription').css('color', color3);
            jQuery('.wjportal-elegant-addon-posted-info').css('color', color3);
            jQuery('.wjportal-elegant-addon-job-type').css('color', color3);
            jQuery('.wjportal-elegant-addon-filter select').attr('style', 'color: ' + color3 + ' !important');

            // Re-apply Hover states
            jQuery('.wjportal-elegant-addon-act-btn-wrp .wjportal-elegant-addon-act-btn').hover(function () {
                jQuery(this).css('background-color', color2);
            }, function () {
                jQuery(this).css('background-color', color1);
            });
            jQuery('.wjportal-elegant-addon-jobs-colored-btn').hover(function () {
                jQuery(this).css('background-color', color2);
            }, function () {
                jQuery(this).css('background-color', color1);
            });
            jQuery('.wjportal-elegant-addon-jobs-act-btn-ai-suggested-resumes').hover(function () {
                jQuery(this).css('background-color', color2);
            }, function () {
                jQuery(this).css('background-color', color1);
            });
            jQuery('.wjportal-elegant-addon-companyname').hover(function () {
                jQuery(this).css('color', color2);
            }, function () {
                jQuery(this).css('color', color1);
            });
            jQuery('.wjportal-jobs-act-btn.wjportal-list-act-btn-featured').hover(function () {
                jQuery(this).css('background-color', color2);
                jQuery(this).css('color', '#fff');
            }, function () {
                jQuery(this).css('background-color', 'transparent');
                jQuery(this).css('color', color1);
            });
            jQuery('.wjportal-elegant-addon-jobs-act-btn:not(.wjportal-elegant-addon-jobs-act-btn-ai-suggested-resumes)').hover(function () {
                jQuery(this).css('background-color', color2);
                jQuery(this).css('color', '#fff');
            }, function () {
                jQuery(this).css('background-color', 'transparent');
                jQuery(this).css('color', color2);
            });
        }
    ";
wp_add_inline_script('wpjobportal-inline-handle', $inline_js_script2);
?>