<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALwpjobportalwidgetsModel {

    function __construct() {

    }

    function listModuleJobs($wpjobportal_layoutName, $wpjobportal_jobs, $location, $wpjobportal_showtitle, $title, $listtype, $wpjobportal_noofjobs, $wpjobportal_category, $wpjobportal_subcategory, $wpjobportal_company, $wpjobportal_jobtype, $posteddate, $wpjobportal_theme, $wpjobportal_separator, $wpjobportal_moduleheight, $wpjobportal_jobsinrow, $wpjobportal_jobsinrowtab, $wpjobportal_jobmargintop, $wpjobportal_jobmarginleft, $wpjobportal_companylogo, $wpjobportal_logodatarow, $sliding, $wpjobportal_datacolumn, $speedTest, $slidingdirection, $consecutivesliding, $wpjobportal_jobheight, $wpjobportal_companylogowidth, $wpjobportal_companylogoheight) {
        $speed = 50;
        if(!is_numeric($speedTest)){
            $speedTest =0;
        }
        if ($speedTest < 5) {
            for ($wpjobportal_i = 5; $wpjobportal_i > $speedTest; $wpjobportal_i--)
                $speed += 10;
            if ($speed > 100)
                $speed = 100;
        }elseif ($speedTest > 5) {
            for ($wpjobportal_i = 5; $wpjobportal_i < $speedTest; $wpjobportal_i++)
                $speed -= 10;
            if ($speed < 10)
                $speed = 10;
        }
        $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];

        $wpjobportal_moduleName = $wpjobportal_layoutName;

        $wpjobportal_contentswrapperstart = '';
        $wpjobportal_contents = '';
        if ($wpjobportal_jobs) {
            if ($listtype == 0) { //list style
                $wpjobportal_contentswrapperstart .= '<div id="wpjobportal_module_wrapper" class="' . esc_attr($wpjobportal_moduleName) . '" style="height:' . esc_attr($wpjobportal_moduleheight) . 'px;" >';
                if ($wpjobportal_showtitle == 1) {

                    $wpjobportal_contentswrapperstart .= '
                        <div id="tp_heading" class="wjportal-mod-heading">
                            ' . esc_html($title) . '
                        </div>
                    ';
                }
                $wpjobportal_contentswrapperstart .= '<div id="wpjobportal_modulelist_titlebar" class="' . esc_attr($wpjobportal_moduleName) . '" ><span id="whiteback"></span>';
                //For desktop
                $wpjobportal_desktop_w = 1;
                if (($wpjobportal_company == 1 || $wpjobportal_company == 2 || $wpjobportal_company == 4 || $wpjobportal_company == 6) || ($wpjobportal_companylogo == 1 || $wpjobportal_companylogo == 2 || $wpjobportal_companylogo == 4 || $wpjobportal_companylogo == 6)) {
                    $wpjobportal_desktop_w++;
                }
                if ($wpjobportal_category == 1 || $wpjobportal_category == 2 || $wpjobportal_category == 3 || $wpjobportal_category == 5) {
                    $wpjobportal_desktop_w++;
                }
                if ($wpjobportal_jobtype == 1 || $wpjobportal_jobtype == 2 || $wpjobportal_jobtype == 3 || $wpjobportal_jobtype == 5) {
                    $wpjobportal_desktop_w++;
                }
                if ($posteddate == 1 || $posteddate == 2 || $posteddate == 3 || $posteddate == 5) {
                    $wpjobportal_desktop_w++;
                }
                if ($location == 1 || $location == 2 || $location == 3 || $location == 5) {
                    $wpjobportal_desktop_w++;
                }
                //For tablet
                $wpjobportal_tablet_w = 1;
                if (($wpjobportal_company == 1 || $wpjobportal_company == 2 || $wpjobportal_company == 4 || $wpjobportal_company == 6) || ($wpjobportal_companylogo == 1 || $wpjobportal_companylogo == 2 || $wpjobportal_companylogo == 4 || $wpjobportal_companylogo == 6)) {
                    $wpjobportal_tablet_w++;
                }
                if ($wpjobportal_category == 1 || $wpjobportal_category == 2 || $wpjobportal_category == 4 || $wpjobportal_category == 6) {
                    $wpjobportal_tablet_w++;
                }
                if ($wpjobportal_jobtype == 1 || $wpjobportal_jobtype == 2 || $wpjobportal_jobtype == 4 || $wpjobportal_jobtype == 6) {
                    $wpjobportal_tablet_w++;
                }
                if ($posteddate == 1 || $posteddate == 2 || $posteddate == 4 || $posteddate == 6) {
                    $wpjobportal_tablet_w++;
                }
                if ($location == 1 || $location == 2 || $location == 4 || $location == 6) {
                    $wpjobportal_tablet_w++;
                }
                //For mobile
                $mobile_w = 1;
                if (($wpjobportal_company == 1 || $wpjobportal_company == 2 || $wpjobportal_company == 4 || $wpjobportal_company == 6) || ($wpjobportal_companylogo == 1 || $wpjobportal_companylogo == 2 || $wpjobportal_companylogo == 4 || $wpjobportal_companylogo == 6)) {
                    $mobile_w++;
                }
                if ($wpjobportal_category == 1 || $wpjobportal_category == 3 || $wpjobportal_category == 4 || $wpjobportal_category == 7) {
                    $mobile_w++;
                }
                if ($wpjobportal_jobtype == 1 || $wpjobportal_jobtype == 3 || $wpjobportal_jobtype == 4 || $wpjobportal_jobtype == 7) {
                    $mobile_w++;
                }
                if ($posteddate == 1 || $posteddate == 3 || $posteddate == 4 || $posteddate == 7) {
                    $mobile_w++;
                }
                if ($location == 1 || $location == 3 || $location == 4 || $location == 7) {
                    $mobile_w++;
                }

                if ($wpjobportal_company != 0 || $wpjobportal_companylogo != 0) {
                    $wpjobportal_class = $this->getClasses($wpjobportal_companylogo);
                    $wpjobportal_class .= $this->getClasses($wpjobportal_company);
                    $wpjobportal_contentswrapperstart .= '<span id="wpjobportal_modulelist_titlebar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . esc_html(__('Company', 'wp-job-portal')) . '</span>';
                }
                $wpjobportal_contentswrapperstart .= '<span id="wpjobportal_modulelist_titlebar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' visible-all">' . esc_html(__('Title', 'wp-job-portal')) . '</span>';
                if ($wpjobportal_category != 0) {
                    $wpjobportal_class = $this->getClasses($wpjobportal_category);
                    $wpjobportal_contentswrapperstart .= '<span id="wpjobportal_modulelist_titlebar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . esc_html(__('Category', 'wp-job-portal')) . '</span>';
                }
                if ($wpjobportal_jobtype == 1) {
                    $wpjobportal_class = $this->getClasses($wpjobportal_jobtype);
                    $wpjobportal_contentswrapperstart .= '<span id="wpjobportal_modulelist_titlebar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . esc_html(__('Type', 'wp-job-portal')) . '</span>';
                }
                if ($location == 1) {
                    $wpjobportal_class = $this->getClasses($location);
                    $wpjobportal_contentswrapperstart .= '<span id="wpjobportal_modulelist_titlebar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . esc_html(__('Location', 'wp-job-portal')) . '</span>';
                }
                if ($posteddate == 1) {
                    $wpjobportal_class = $this->getClasses($posteddate);
                    $wpjobportal_contentswrapperstart .= '<span id="wpjobportal_modulelist_titlebar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . esc_html(__('Posted', 'wp-job-portal')) . '</span>';
                }
                $wpjobportal_contentswrapperstart .= '</div>';
                $wpjobportal_wpdir = wp_upload_dir();
                $wpjobportal_data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
                if (isset($wpjobportal_jobs)) {
                    foreach ($wpjobportal_jobs as $wpjobportal_job) {
                        $wpjobportal_contents .= '<div id="wpjobportal_modulelist_databar"><span id="whiteback"></span>';
                        if ($wpjobportal_company != 0 || $wpjobportal_companylogo != 0) {
                            $wpjobportal_class = $this->getClasses($wpjobportal_company);
                            $wpjobportal_class .= $this->getClasses($wpjobportal_companylogo);
                            $wpjobportal_contents .= '<span id="wpjobportal_modulelist_databar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">';
                            if ($wpjobportal_companylogo != 0) {
                                $wpjobportal_class = $this->getClasses($wpjobportal_companylogo);

                                $c_l = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_job->companyaliasid, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));

                                $wpjobportal_logo = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('employer');
                                if($wpjobportal_job->logofilename != ''){
                                    $wpjobportal_logo = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/employer/comp_' . $wpjobportal_job->companyid . '/logo/' . $wpjobportal_job->logofilename;
                                }
                                $wpjobportal_contents .= '<a href=' . esc_url($c_l) . '><img  src="' . esc_url($wpjobportal_logo) . '"  /></a>';
                            }
                            if ($wpjobportal_company != 0) {
                                $c_l = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_job->companyaliasid, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                                $wpjobportal_contents .= '<span id="themeanchor"><a class="anchor" href=' . esc_url($c_l) . '>' . esc_html($wpjobportal_job->companyname) . '</a></span>';
                            }
                            $wpjobportal_contents .= '</span>';
                        }
                        $an_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'viewjob', 'wpjobportalid'=>$wpjobportal_job->jobaliasid, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        $wpjobportal_contents .= '<span id="wpjobportal_modulelist_databar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' visible-all">
                                        <span id="themeanchor">
                                            <a class="anchor" href="' . esc_url($an_link) . '">
                                                ' . esc_html($wpjobportal_job->title) . '
                                            </a>
                                        </span>
                                        </span>';
                        if ($wpjobportal_category != 0) {
                            $wpjobportal_class = $this->getClasses($wpjobportal_category);
                            $wpjobportal_contents .= '<span id="wpjobportal_modulelist_databar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . esc_html($wpjobportal_job->cat_title) . '</span>';
                        }
                        if ($wpjobportal_jobtype != 0) {
                            $wpjobportal_class = $this->getClasses($wpjobportal_jobtype);
                            $wpjobportal_contents .= '<span id="wpjobportal_modulelist_databar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . $wpjobportal_job->jobtypetitle . '</span>';
                        }
                        if ($location != 0) {
                            $wpjobportal_class = $this->getClasses($location);
                            $wpjobportal_contents .= '<span id="wpjobportal_modulelist_databar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . $wpjobportal_job->location . '</span>';
                        }
                        if ($posteddate != 0) {
                            $wpjobportal_class = $this->getClasses($posteddate);
                            $wpjobportal_contents .= '<span id="wpjobportal_modulelist_databar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . date_i18n($wpjobportal_dateformat, strtotime($wpjobportal_job->created)) . '</span>';
                        }
                        $wpjobportal_contents .= '</div>';
                    }
                }

                if ($sliding == 1) { // Sliding is enable
                    $consectivecontent = '';
                    for ($wpjobportal_i = 0; $wpjobportal_i < $consecutivesliding; $wpjobportal_i++) {
                        $consectivecontent .= $wpjobportal_contents;
                    }

                    if ($slidingdirection == 1) { // UP
                        $wpjobportal_contents = '<marquee id="mod_hotwpjobportal"  style="height:' . esc_attr($wpjobportal_moduleheight) . 'px;" direction="up" scrolldelay="' . $speed . '" scrollamount="1" onmouseover="this.stop();" onmouseout="this.start()";>' . $consectivecontent . '</marquee>';
                    }
                }
                $wpjobportal_contentswrapperend = '</div>';
            } else { //box style
                $wpjobportal_jobwidthclass = "modjob" . $wpjobportal_jobsinrow;
                $wpjobportal_jobtabwidthclass = "modjobtab" . $wpjobportal_jobsinrowtab;
                $wpjobportal_contentswrapperstart .= '<div id="wpjobportal_module_wrapper" class="' . esc_attr($wpjobportal_moduleName) . '" >';
                if ($wpjobportal_showtitle == 1) {
                    $wpjobportal_contentswrapperstart .= '
                        <div id="tp_heading" class="wjportal-mod-heading">
                            ' . esc_html($title) . '
                        </div>
                    ';
                }
                $wpjobportal_inlineCSS = 'margin-top:' . esc_attr($wpjobportal_jobmargintop) . 'px;margin-left:' . esc_attr($wpjobportal_jobmarginleft) . 'px;';
                $wpjobportal_wpdir = wp_upload_dir();
                $wpjobportal_data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
                if (isset($wpjobportal_jobs)) {
                    foreach ($wpjobportal_jobs as $wpjobportal_job) {
                        $wpjobportal_contents .= '<div id="wpjobportal_module_wrap" class="' . esc_attr($wpjobportal_jobwidthclass) . ' ' . esc_attr($wpjobportal_jobtabwidthclass) . ' wjportal-job-mod">
                                      <div id="wpjobportal_module">';
                        $an_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'viewjob', 'wpjobportalid'=>$wpjobportal_job->jobaliasid, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        $wpjobportal_dataclass = 'data100';
                        if ($wpjobportal_companylogo != 0) {
                            $c_l = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_job->companyaliasid, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                            if ($wpjobportal_logodatarow == 1) { // Combine
                                $wpjobportal_logoclass = "comp40";
                                $wpjobportal_dataclass = "data60";
                                $wpjobportal_logocss = 'width:' . esc_attr($wpjobportal_companylogowidth) . 'px;';
                            } else {
                                $wpjobportal_logoclass = "comp100";
                                $wpjobportal_dataclass = "data100";
                                $wpjobportal_logocss = 'height:' . esc_attr($wpjobportal_companylogoheight) . 'px;';
                            }
                            $wpjobportal_logo = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('employer');
                            if($wpjobportal_job->logofilename != ''){
                                $wpjobportal_logo = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/employer/comp_' . $wpjobportal_job->companyid . '/logo/' . $wpjobportal_job->logofilename;
                            }

                            /*$wpjobportal_logoclass .= $this->getClasses($wpjobportal_companylogo);
                            $wpjobportal_contents .= '
                                                <div id="wpjobportal_module_data_fieldwrapper" class="' . $wpjobportal_logoclass . ' wjportal-job-logo" >
                                                    <a href=' . esc_url($c_l) . '><img  src="' . esc_url($wpjobportal_logo) . '" /></a>
                                                </div>
                                              ';*/
                        }
                        $wpjobportal_contents .= '<div class="wjportal-job-cont">';
                        $wpjobportal_contents .= '<div id="wpjobportal_module_heading" class="wjportal-job-data wjportal-job-title">
                                        <a class="wjportal-jobname" href="' . esc_url($an_link) . '">
                                            ' . esc_html($wpjobportal_job->title) . '
                                        </a>
                                      </div>';
                        $wpjobportal_contents .= '<div id="wpjobportal_module_data_fieldwrapper" class="' . esc_attr($wpjobportal_dataclass) . ' visible-all">';
                        $colwidthclass = esc_attr('modcolwidth') . esc_attr($wpjobportal_datacolumn);
                        if ($wpjobportal_company != 0) {
                            $wpjobportal_class = $this->getClasses($wpjobportal_company);
                            $c_l = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_job->companyaliasid, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                            $wpjobportal_contents .= '
                                                <div id="wpjobportal_module_data_fieldwrapper" class="' . esc_attr($colwidthclass) . esc_attr($wpjobportal_class) . ' wjportal-job-data">
                                                    <span id="wpjobportal_module_data_fieldtitle" class="wjportal-job-data-tit">' . esc_html(__('Company', 'wp-job-portal')) . ' : </span>
                                                    <span id="wpjobportal_module_data_fieldvalue" class="wjportal-job-data-val">
                                                        <a class="wjportal-compname" href=' . esc_url($c_l) . '>' . esc_html($wpjobportal_job->companyname) . '</a>
                                                    </span>
                                                </div>
                                              ';
                        }
                        if ($wpjobportal_category != 0) {
                            $wpjobportal_class = $this->getClasses($wpjobportal_category);
                            $wpjobportal_contents .= '
                                                <div id="wpjobportal_module_data_fieldwrapper" class="' . esc_attr($colwidthclass) . esc_attr($wpjobportal_class) . ' wjportal-job-data">
                                                    <span id="wpjobportal_module_data_fieldtitle" class="wjportal-job-data-tit">' . esc_html(__('Category', 'wp-job-portal')) . ' : </span>
                                                    <span id="wpjobportal_module_data_fieldvalue" class="wjportal-job-data-val">' . esc_html($wpjobportal_job->cat_title) . '</span>
                                                </div>
                                              ';
                        }
                        if ($wpjobportal_jobtype != 0) {
                            $wpjobportal_class = $this->getClasses($wpjobportal_jobtype);
                            $wpjobportal_contents .= '
                                                <div id="wpjobportal_module_data_fieldwrapper" class="' . esc_attr($colwidthclass) . esc_attr($wpjobportal_class) . ' wjportal-job-data">
                                                    <span id="wpjobportal_module_data_fieldtitle" class="wjportal-job-data-tit">' . esc_html(__('Type', 'wp-job-portal')) . ' : </span>
                                                    <span id="wpjobportal_module_data_fieldvalue" class="wjportal-job-data-val">' . $wpjobportal_job->jobtypetitle . '</span>
                                                </div>
                                              ';
                        }
                        if ($location != 0) {
                            $wpjobportal_contents .= '
                                                <div id="wpjobportal_module_data_fieldwrapper" class="' . esc_attr($colwidthclass) . esc_attr($wpjobportal_class) . ' wjportal-job-data">
                                                    <span id="wpjobportal_module_data_fieldtitle" class="wjportal-job-data-tit">' . esc_html(__('Location', 'wp-job-portal')) . ' : </span>
                                                    <span id="wpjobportal_module_data_fieldvalue" class="wjportal-job-data-val">' . $wpjobportal_job->location . '</span>
                                                </div>
                                              ';
                        }
                        if ($posteddate != 0) {
                            $wpjobportal_class = $this->getClasses($posteddate);
                            $wpjobportal_contents .= '
                                                <div id="wpjobportal_module_data_fieldwrapper" class="' . esc_attr($colwidthclass) . esc_attr($wpjobportal_class) . ' wjportal-job-data">
                                                    <span id="wpjobportal_module_data_fieldtitle" class="wjportal-job-data-tit">' . esc_html(__('Posted', 'wp-job-portal')) . ' : </span>
                                                    <span id="wpjobportal_module_data_fieldvalue" class="wjportal-job-data-val">' . date_i18n($wpjobportal_dateformat, strtotime($wpjobportal_job->created)) . '</span>
                                                </div>
                                              ';
                        }
                        $wpjobportal_contents .= '</div>
                                </div>
                            </div>
                            </div>';
                    }
                }
                $wpjobportal_contentswrapperend = '</div>';
            }
            return $wpjobportal_contentswrapperstart . $wpjobportal_contents . $wpjobportal_contentswrapperend;
        }
    }

    function getClasses($for) {
        $wpjobportal_class = '';
        switch ($for) {
            case 1: // Show all
                $wpjobportal_class = ' visible-all ';
                break;
            case 2: // Show desktop and tablet
                $wpjobportal_class = ' visible-desktop visible-tablet ';
                break;
            case 3: // Show desktop and mobile
                $wpjobportal_class = ' visible-desktop visible-mobile ';
                break;
            case 4: // Show tablet and mobile
                $wpjobportal_class = ' visible-tablet visible-mobile ';
                break;
            case 5: // Show desktop
                $wpjobportal_class = ' visible-desktop ';
                break;
            case 6: // Show tablet
                $wpjobportal_class = ' visible-tablet ';
                break;
            case 7: // Show mobile
                $wpjobportal_class = ' visible-mobile ';
                break;
        }
        return $wpjobportal_class;
    }

    function listModuleCompanies($wpjobportal_layoutName, $wpjobportal_companies, $wpjobportal_noofcompanies, $wpjobportal_category, $posteddate, $listtype, $wpjobportal_theme, $location, $wpjobportal_moduleheight, $wpjobportal_jobwidth, $wpjobportal_jobheight, $wpjobportal_jobfloat, $wpjobportal_jobmargintop, $wpjobportal_jobmarginleft, $wpjobportal_companylogo, $wpjobportal_companylogowidth, $wpjobportal_companylogoheight, $wpjobportal_datacolumn, $listtype_extra, $title, $wpjobportal_showtitle, $speedTest, $sliding, $slidingdirection, $consecutivesliding, $wpjobportal_resumesinrow, $wpjobportal_resumesinrowtab, $wpjobportal_logodatarow) {

        $speed = 50;
        if(!is_numeric($speedTest)){
            $speedTest = 0;
        }
        if ($speedTest < 5) {
            for ($wpjobportal_i = 5; $wpjobportal_i > $speedTest; $wpjobportal_i--)
                $speed += 10;
            if ($speed > 100)
                $speed = 100;
        }elseif ($speedTest > 5) {
            for ($wpjobportal_i = 5; $wpjobportal_i < $speedTest; $wpjobportal_i++)
                $speed -= 10;
            if ($speed < 10)
                $speed = 10;
        }
        $wpjobportal_moduleName = $wpjobportal_layoutName;
        $wpjobportal_contentswrapperstart = '';
        $wpjobportal_contents = '';

        $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
        $wpjobportal_data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
        if ($wpjobportal_companies) {
            if ($listtype == 0) { //list style
                $wpjobportal_contentswrapperstart .= '<div id="wpjobportal_module_wrapper" class="' . esc_attr($wpjobportal_moduleName) . '" style="height:' . esc_attr($wpjobportal_moduleheight) . 'px;" >';
                if ($wpjobportal_showtitle == 1) {

                    $wpjobportal_contentswrapperstart .= '
                        <div id="tp_heading" class="wjportal-mod-heading">
                            ' . esc_html($title) . '
                        </div>
                    ';
                }
                $wpjobportal_contentswrapperstart .= '<div id="wpjobportal_modulelist_titlebar" class="' . esc_attr($wpjobportal_moduleName) . '" ><span id="whiteback"></span>';
                //For desktop
                $wpjobportal_desktop_w = 1;
                if ($wpjobportal_noofcompanies == 1 || $wpjobportal_noofcompanies == 2 || $wpjobportal_noofcompanies == 4 || $wpjobportal_noofcompanies == 6) {
                    $wpjobportal_desktop_w++;
                }
                if ($wpjobportal_category == 1 || $wpjobportal_category == 2 || $wpjobportal_category == 4 || $wpjobportal_category == 6) {
                    $wpjobportal_desktop_w++;
                }
                if ($title == 1 || $title == 2 || $title == 3 || $title == 5) {
                    $wpjobportal_desktop_w++;
                }
                if ($location == 1 || $location == 2 || $location == 3 || $location == 5) {
                    $wpjobportal_desktop_w++;
                }
                if ($posteddate == 1 || $posteddate == 2 || $posteddate == 3 || $posteddate == 5) {
                    $wpjobportal_desktop_w++;
                }
                //For tablet
                $wpjobportal_tablet_w = 1;
                if ($wpjobportal_noofcompanies == 1 || $wpjobportal_noofcompanies == 2 || $wpjobportal_noofcompanies == 4 || $wpjobportal_noofcompanies == 6) {
                    $wpjobportal_tablet_w++;
                }
                if ($wpjobportal_category == 1 || $wpjobportal_category == 2 || $wpjobportal_category == 4 || $wpjobportal_category == 6) {
                    $wpjobportal_tablet_w++;
                }
                if ($title == 1 || $title == 2 || $title == 3 || $title == 5) {
                    $wpjobportal_tablet_w++;
                }
                if ($location == 1 || $location == 2 || $location == 3 || $location == 5) {
                    $wpjobportal_tablet_w++;
                }
                if ($posteddate == 1 || $posteddate == 2 || $posteddate == 3 || $posteddate == 5) {
                    $wpjobportal_tablet_w++;
                }
                //For mobile
                $mobile_w = 1;
                if ($wpjobportal_noofcompanies == 1 || $wpjobportal_noofcompanies == 2 || $wpjobportal_noofcompanies == 4 || $wpjobportal_noofcompanies == 6) {
                    $mobile_w++;
                }
                if ($wpjobportal_category == 1 || $wpjobportal_category == 2 || $wpjobportal_category == 4 || $wpjobportal_category == 6) {
                    $mobile_w++;
                }
                if ($title == 1 || $title == 2 || $title == 3 || $title == 5) {
                    $mobile_w++;
                }
                if ($location == 1 || $location == 2 || $location == 3 || $location == 5) {
                    $mobile_w++;
                }
                if ($posteddate == 1 || $posteddate == 2 || $posteddate == 3 || $posteddate == 5) {
                    $mobile_w++;
                }

                if ($wpjobportal_noofcompanies != 0) {
                    $wpjobportal_class = $this->getClasses($wpjobportal_noofcompanies);
                    $wpjobportal_contentswrapperstart .= '<span id="wpjobportal_modulelist_titlebar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . esc_html(__('Photo', 'wp-job-portal')) . '</span>';
                }
                if ($wpjobportal_category != 0) {
                    $wpjobportal_class = $this->getClasses($wpjobportal_category);
                    $wpjobportal_contentswrapperstart .= '<span id="wpjobportal_modulelist_titlebar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . esc_html(__('Category', 'wp-job-portal')) . '</span>';
                }
                if ($location != 0) {
                    $wpjobportal_class = $this->getClasses($location);
                    $wpjobportal_contentswrapperstart .= '<span id="wpjobportal_modulelist_titlebar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . esc_html(__('Location', 'wp-job-portal')) . '</span>';
                }
                if ($posteddate != 0) {
                    $wpjobportal_class = $this->getClasses($posteddate);
                    $wpjobportal_contentswrapperstart .= '<span id="wpjobportal_modulelist_titlebar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . esc_html(__('Posted', 'wp-job-portal')) . '</span>';
                }
                $wpjobportal_contentswrapperstart .= '</div>';
                $wpjobportal_wpdir = wp_upload_dir();
                if (isset($wpjobportal_companies)) {
                    foreach ($wpjobportal_companies as $wpjobportal_company) {
                        $wpjobportal_contents .= '<div id="wpjobportal_modulelist_databar"><span id="whiteback"></span>';
                        if ($wpjobportal_companylogo != 0) {
                            $wpjobportal_class = $this->getClasses($wpjobportal_companylogo);
                            $wpjobportal_contents .= '<span id="wpjobportal_modulelist_databar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">';
                            $c_l = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_company->companyaliasid, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));

                            $wpjobportal_logo = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('employer');
                            if($wpjobportal_company->logofilename != ''){
                                $wpjobportal_logo = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/employer/comp_' . $wpjobportal_company->id . '/logo/' . $wpjobportal_company->logofilename;
                            }

                            $wpjobportal_contents .= '<a href=' . esc_url($c_l) . '><img  src="' . esc_url($wpjobportal_logo) . '"  /></a>';
                            $wpjobportal_contents .= '</span>';
                        }
                        if ($title != 0) {
                            $wpjobportal_class = $this->getClasses($title);
                           $an_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_company->companyaliasid, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                            $wpjobportal_contents .= '<span id="wpjobportal_modulelist_databar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">
                                            <span id="themeanchor">
                                                <a class="anchor" href="' . esc_url($an_link) . '">
                                                    ' . esc_html($wpjobportal_company->title) . '
                                                </a>
                                            </span>
                                            </span>';
                        }
                        if ($wpjobportal_category != 0) {
                            $wpjobportal_class = $this->getClasses($wpjobportal_category);
                            $wpjobportal_contents .= '<span id="wpjobportal_modulelist_databar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . esc_html($wpjobportal_company->cat_title) . '</span>';
                        }
                        if ($location != 0) {
                            $wpjobportal_class = $this->getClasses($location);
                            $wpjobportal_contents .= '<span id="wpjobportal_modulelist_databar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . esc_html($wpjobportal_company->location) . '</span>';
                        }
                        if ($posteddate != 0) {
                            $wpjobportal_class = $this->getClasses($posteddate);
                            $wpjobportal_contents .= '<span id="wpjobportal_modulelist_databar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . esc_html(date_i18n($wpjobportal_dateformat, strtotime($wpjobportal_company->created))) . '</span>';
                        }
                        $wpjobportal_contents .= '</div>';
                    }
                }

                $wpjobportal_contentswrapperend = '</div>';
            } else { //box style
                $wpjobportal_jobwidthclass = "modjob" . esc_attr($wpjobportal_resumesinrow);
                $wpjobportal_jobtabwidthclass = "modjobtab" . esc_attr($wpjobportal_resumesinrowtab);
                //$wpjobportal_contentswrapperstart .= '<div id="wpjobportal_module_wrapper" class="' . esc_attr($wpjobportal_moduleName) . '" style="height:' . esc_attr($wpjobportal_moduleheight) . 'px;overflow:hidden;">';
                $wpjobportal_contentswrapperstart .= '<div id="wpjobportal_module_wrapper" class="' . esc_attr($wpjobportal_moduleName) . '" >';
                if ($wpjobportal_showtitle == 1) {
                    $wpjobportal_contentswrapperstart .= '
                                <div id="tp_heading" class="wjportal-mod-heading">
                                    ' . esc_html($title) . '
                                </div>
                    ';
                }
                $wpjobportal_inlineCSS = 'margin-top:' . esc_attr($wpjobportal_jobmargintop) . 'px;margin-left:' . esc_attr($wpjobportal_jobmarginleft) . 'px;';
                if (isset($wpjobportal_companies)) {
                    $wpjobportal_wpdir = wp_upload_dir();
                    foreach ($wpjobportal_companies as $wpjobportal_company) {
                        $wpjobportal_contents .= '<div id="wpjobportal_module_wrap" class="' . esc_attr($wpjobportal_jobwidthclass) . ' ' . esc_attr($wpjobportal_jobtabwidthclass) . ' wjportal-comp-mod ">
                                      <div id="wpjobportal_module">';
                        $an_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_company->companyaliasid, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        $wpjobportal_dataclass = 'data100';
                        if ($wpjobportal_companylogo != 0) {
                            $c_l = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_company->companyaliasid, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                            if ($wpjobportal_logodatarow == 1) { // Combine
                                $wpjobportal_logoclass = "comp40";
                                $wpjobportal_dataclass = "data60";
                                $wpjobportal_logocss = 'width:' . esc_attr($wpjobportal_companylogowidth) . 'px;';
                            } else {
                                $wpjobportal_logoclass = "comp100";
                                $wpjobportal_dataclass = "data100";
                                $wpjobportal_logocss = 'height:' . esc_attr($wpjobportal_companylogoheight) . 'px;';
                            }
                            $wpjobportal_logo = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('employer');
                            if($wpjobportal_company->logofilename != ''){
                                $wpjobportal_logo = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/employer/comp_' . $wpjobportal_company->id . '/logo/' . $wpjobportal_company->logofilename;
                            }

                            /*$wpjobportal_logoclass .= $this->getClasses($wpjobportal_companylogo);
                            $wpjobportal_contents .= '
                                                <div id="wpjobportal_module_data_fieldwrapper" class="' . $wpjobportal_logoclass . ' wjportal-comp-logo" >
                                                    <a href=' . esc_url($c_l) . '><img  src="' . esc_url($wpjobportal_logo) . '" style="' . $wpjobportal_logocss . 'display:block;margin:auto;" /></a>
                                                </div>
                                              ';*/
                        }
                        $wpjobportal_contents .= '<div class="wjportal-comp-cont">';
                        $wpjobportal_contents .= '<div id="wpjobportal_module_heading" class="wjportal-company-data wjportal-company-title">
                                        <a class="wjportal-companyname" href="' . esc_url($an_link) . '">
                                            ' . esc_html($wpjobportal_company->name) . '
                                        </a>
                                      </div>';
                        $wpjobportal_contents .= '<div id="wpjobportal_module_data_fieldwrapper" class="' . esc_attr($wpjobportal_dataclass) . ' visible-all ">';
                        $colwidthclass = 'modcolwidth' . esc_attr($wpjobportal_datacolumn);
                        if ($wpjobportal_category != 0) {
                            $wpjobportal_class = $this->getClasses($wpjobportal_category);
                            $wpjobportal_contents .= '
                                                <div id="wpjobportal_module_data_fieldwrapper" class="' . esc_attr($colwidthclass) . esc_attr($wpjobportal_class) . ' wjportal-company-data wjportal-company-catg">
                                                </div>
                                              ';
                        }
                        if ($location != 0) {
                            $wpjobportal_class = $this->getClasses($location);
                            $wpjobportal_contents .= '
                                                <div id="wpjobportal_module_data_fieldwrapper" class="' . esc_attr($colwidthclass) . esc_attr($wpjobportal_class) . ' wjportal-company-data wjportal-company-loc">
                                                    <span id="wpjobportal_module_data_fieldtitle" class="wjportal-company-data-tit">' . esc_html(__('Location', 'wp-job-portal')) . ' : </span>
                                                    <span id="wpjobportal_module_data_fieldvalue" class="wjportal-company-data-val">' . esc_html($wpjobportal_company->location) . '</span>
                                                </div>
                                              ';
                        }
                        if ($posteddate != 0) {
                            $wpjobportal_class = $this->getClasses($posteddate);
                            $wpjobportal_contents .= '
                                                <div id="wpjobportal_module_data_fieldwrapper" class="' . esc_attr($colwidthclass) . esc_attr($wpjobportal_class) . ' wjportal-company-data ">
                                                    <span id="wpjobportal_module_data_fieldtitle" class="wjportal-company-data-tit">' . esc_html(__('Posted', 'wp-job-portal')) . ' : </span>
                                                    <span id="wpjobportal_module_data_fieldvalue" class="wjportal-company-data-val">' . esc_html(date_i18n($wpjobportal_dateformat, strtotime($wpjobportal_company->created))) . '</span>
                                                </div>
                                              ';
                        }
                        $wpjobportal_contents .= '</div>
                                </div>
                            </div>
                        </div>';
                    }
                }
                $wpjobportal_contentswrapperend = '</div>';
            }

            return $wpjobportal_contentswrapperstart . $wpjobportal_contents . $wpjobportal_contentswrapperend;
        }
    }

    function listModuleResumes($wpjobportal_layoutName, $wpjobportal_resumes, $wpjobportal_noofresumes, $wpjobportal_applicationtitle, $wpjobportal_name, $wpjobportal_experience, $available, $wpjobportal_gender, $wpjobportal_nationality, $location, $wpjobportal_category, $wpjobportal_subcategory, $wpjobportal_jobtype, $posteddate, $wpjobportal_separator, $wpjobportal_moduleheight, $wpjobportal_resumeheight, $wpjobportal_resumemargintop, $wpjobportal_resumemarginleft, $wpjobportal_photowidth, $wpjobportal_photoheight, $wpjobportal_datacolumn, $listtype, $title, $wpjobportal_showtitle, $speedTest, $sliding, $consecutivesliding, $slidingdirection, $wpjobportal_resumephoto, $wpjobportal_resumesinrow, $wpjobportal_resumesinrowtab, $wpjobportal_logodatarow) {
        $speed = 50;
        if(!is_numeric($speedTest)){
            $speedTest = 0;
        }
        if ($speedTest < 5) {
            for ($wpjobportal_i = 5; $wpjobportal_i > $speedTest; $wpjobportal_i--)
                $speed += 10;
            if ($speed > 100)
                $speed = 100;
        }elseif ($speedTest > 5) {
            for ($wpjobportal_i = 5; $wpjobportal_i < $speedTest; $wpjobportal_i++)
                $speed -= 10;
            if ($speed < 10)
                $speed = 10;
        }

        $wpjobportal_moduleName = $wpjobportal_layoutName;

        $wpjobportal_contentswrapperstart = '';
        $wpjobportal_contents = '';

        $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
        $wpjobportal_data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');


        if ($wpjobportal_resumes) {
            if ($listtype == 0) { //list style
                $wpjobportal_contentswrapperstart .= '<div id="wpjobportal_module_wrapper" class="' . esc_attr($wpjobportal_moduleName) . '" style="height:' . esc_attr($wpjobportal_moduleheight) . 'px;" >';
                if ($wpjobportal_showtitle == 1) {
                    $wpjobportal_contentswrapperstart .= '
                        <div id="tp_heading" class="wjportal-mod-heading">
                            ' . esc_html($title) . '
                        </div>
                    ';
                }
                $wpjobportal_contentswrapperstart .= '<div id="wpjobportal_modulelist_titlebar" class="' . esc_attr($wpjobportal_moduleName) . '" ><span id="whiteback"></span>';
                //For desktop
                $wpjobportal_desktop_w = 1;
                if ($wpjobportal_resumephoto == 1 || $wpjobportal_resumephoto == 2 || $wpjobportal_resumephoto == 4 || $wpjobportal_resumephoto == 6) {
                    $wpjobportal_desktop_w++;
                }
                if ($wpjobportal_applicationtitle == 1 || $wpjobportal_applicationtitle == 2 || $wpjobportal_applicationtitle == 4 || $wpjobportal_applicationtitle == 6) {
                    $wpjobportal_desktop_w++;
                }
                if ($wpjobportal_name == 1 || $wpjobportal_name == 2 || $wpjobportal_name == 3 || $wpjobportal_name == 5) {
                    $wpjobportal_desktop_w++;
                }
                if ($wpjobportal_category == 1 || $wpjobportal_category == 2 || $wpjobportal_category == 3 || $wpjobportal_category == 5) {
                    $wpjobportal_desktop_w++;
                }
                if ($wpjobportal_jobtype == 1 || $wpjobportal_jobtype == 2 || $wpjobportal_jobtype == 3 || $wpjobportal_jobtype == 5) {
                    $wpjobportal_desktop_w++;
                }
                if ($wpjobportal_experience == 1 || $wpjobportal_experience == 2 || $wpjobportal_experience == 3 || $wpjobportal_experience == 5) {
                    $wpjobportal_desktop_w++;
                }
                if ($available == 1 || $available == 2 || $available == 3 || $available == 5) {
                    $wpjobportal_desktop_w++;
                }
                if ($wpjobportal_gender == 1 || $wpjobportal_gender == 2 || $wpjobportal_gender == 3 || $wpjobportal_gender == 5) {
                    $wpjobportal_desktop_w++;
                }
                if ($wpjobportal_nationality == 1 || $wpjobportal_nationality == 2 || $wpjobportal_nationality == 3 || $wpjobportal_nationality == 5) {
                    $wpjobportal_desktop_w++;
                }
                if ($location == 1 || $location == 2 || $location == 3 || $location == 5) {
                    $wpjobportal_desktop_w++;
                }
                if ($posteddate == 1 || $posteddate == 2 || $posteddate == 3 || $posteddate == 5) {
                    $wpjobportal_desktop_w++;
                }
                //For tablet
                $wpjobportal_tablet_w = 1;
                if ($wpjobportal_resumephoto == 1 || $wpjobportal_resumephoto == 2 || $wpjobportal_resumephoto == 4 || $wpjobportal_resumephoto == 6) {
                    $wpjobportal_tablet_w++;
                }
                if ($wpjobportal_applicationtitle == 1 || $wpjobportal_applicationtitle == 2 || $wpjobportal_applicationtitle == 4 || $wpjobportal_applicationtitle == 6) {
                    $wpjobportal_tablet_w++;
                }
                if ($wpjobportal_name == 1 || $wpjobportal_name == 2 || $wpjobportal_name == 3 || $wpjobportal_name == 5) {
                    $wpjobportal_tablet_w++;
                }
                if ($wpjobportal_category == 1 || $wpjobportal_category == 2 || $wpjobportal_category == 3 || $wpjobportal_category == 5) {
                    $wpjobportal_tablet_w++;
                }
                if ($wpjobportal_jobtype == 1 || $wpjobportal_jobtype == 2 || $wpjobportal_jobtype == 3 || $wpjobportal_jobtype == 5) {
                    $wpjobportal_tablet_w++;
                }
                if ($wpjobportal_experience == 1 || $wpjobportal_experience == 2 || $wpjobportal_experience == 3 || $wpjobportal_experience == 5) {
                    $wpjobportal_tablet_w++;
                }
                if ($available == 1 || $available == 2 || $available == 3 || $available == 5) {
                    $wpjobportal_tablet_w++;
                }
                if ($wpjobportal_gender == 1 || $wpjobportal_gender == 2 || $wpjobportal_gender == 3 || $wpjobportal_gender == 5) {
                    $wpjobportal_tablet_w++;
                }
                if ($wpjobportal_nationality == 1 || $wpjobportal_nationality == 2 || $wpjobportal_nationality == 3 || $wpjobportal_nationality == 5) {
                    $wpjobportal_tablet_w++;
                }
                if ($location == 1 || $location == 2 || $location == 3 || $location == 5) {
                    $wpjobportal_tablet_w++;
                }
                if ($posteddate == 1 || $posteddate == 2 || $posteddate == 3 || $posteddate == 5) {
                    $wpjobportal_tablet_w++;
                }
                //For mobile
                $mobile_w = 1;
                if ($wpjobportal_resumephoto == 1 || $wpjobportal_resumephoto == 2 || $wpjobportal_resumephoto == 4 || $wpjobportal_resumephoto == 6) {
                    $mobile_w++;
                }
                if ($wpjobportal_applicationtitle == 1 || $wpjobportal_applicationtitle == 2 || $wpjobportal_applicationtitle == 4 || $wpjobportal_applicationtitle == 6) {
                    $mobile_w++;
                }
                if ($wpjobportal_name == 1 || $wpjobportal_name == 2 || $wpjobportal_name == 3 || $wpjobportal_name == 5) {
                    $mobile_w++;
                }
                if ($wpjobportal_category == 1 || $wpjobportal_category == 2 || $wpjobportal_category == 3 || $wpjobportal_category == 5) {
                    $mobile_w++;
                }
                if ($wpjobportal_jobtype == 1 || $wpjobportal_jobtype == 2 || $wpjobportal_jobtype == 3 || $wpjobportal_jobtype == 5) {
                    $mobile_w++;
                }
                if ($wpjobportal_experience == 1 || $wpjobportal_experience == 2 || $wpjobportal_experience == 3 || $wpjobportal_experience == 5) {
                    $mobile_w++;
                }
                if ($available == 1 || $available == 2 || $available == 3 || $available == 5) {
                    $mobile_w++;
                }
                if ($wpjobportal_gender == 1 || $wpjobportal_gender == 2 || $wpjobportal_gender == 3 || $wpjobportal_gender == 5) {
                    $mobile_w++;
                }
                if ($wpjobportal_nationality == 1 || $wpjobportal_nationality == 2 || $wpjobportal_nationality == 3 || $wpjobportal_nationality == 5) {
                    $mobile_w++;
                }
                if ($location == 1 || $location == 2 || $location == 3 || $location == 5) {
                    $mobile_w++;
                }
                if ($posteddate == 1 || $posteddate == 2 || $posteddate == 3 || $posteddate == 5) {
                    $mobile_w++;
                }

                if ($wpjobportal_resumephoto != 0) {
                    $wpjobportal_class = $this->getClasses($wpjobportal_resumephoto);
                    $wpjobportal_contentswrapperstart .= '<span id="wpjobportal_modulelist_titlebar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . esc_html(__('Photo', 'wp-job-portal')) . '</span>';
                }
                if ($wpjobportal_applicationtitle != 0) {
                    $wpjobportal_contentswrapperstart .= '<span id="wpjobportal_modulelist_titlebar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' visible-all">' . esc_html(__('Application title', 'wp-job-portal')) . '</span>';
                }
                if ($wpjobportal_name != 0) {
                    $wpjobportal_class = $this->getClasses($wpjobportal_name);
                    $wpjobportal_contentswrapperstart .= '<span id="wpjobportal_modulelist_titlebar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . esc_html(__('Name', 'wp-job-portal')) . '</span>';
                }
                if ($wpjobportal_category != 0) {
                    $wpjobportal_class = $this->getClasses($wpjobportal_category);
                    $wpjobportal_contentswrapperstart .= '<span id="wpjobportal_modulelist_titlebar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . esc_html(__('Category', 'wp-job-portal')) . '</span>';
                }
                if ($wpjobportal_jobtype != 0) {
                    $wpjobportal_class = $this->getClasses($wpjobportal_jobtype);
                    $wpjobportal_contentswrapperstart .= '<span id="wpjobportal_modulelist_titlebar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . esc_html(__('Work preference', 'wp-job-portal')) . '</span>';
                }
                if ($wpjobportal_experience != 0) {
                    $wpjobportal_class = $this->getClasses($wpjobportal_experience);
                    $wpjobportal_contentswrapperstart .= '<span id="wpjobportal_modulelist_titlebar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . esc_html(__('Experience', 'wp-job-portal')) . '</span>';
                }
                if ($available != 0) {
                    $wpjobportal_class = $this->getClasses($available);
                    $wpjobportal_contentswrapperstart .= '<span id="wpjobportal_modulelist_titlebar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . esc_html(__('Available', 'wp-job-portal')) . '</span>';
                }
                if ($wpjobportal_gender != 0) {
                    $wpjobportal_class = $this->getClasses($wpjobportal_gender);
                    $wpjobportal_contentswrapperstart .= '<span id="wpjobportal_modulelist_titlebar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . esc_html(__('Gender', 'wp-job-portal')) . '</span>';
                }
                if ($wpjobportal_nationality != 0) {
                    $wpjobportal_class = $this->getClasses($wpjobportal_nationality);
                    $wpjobportal_contentswrapperstart .= '<span id="wpjobportal_modulelist_titlebar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . esc_html(__('Nationality', 'wp-job-portal')) . '</span>';
                }
                if ($location != 0) {
                    $wpjobportal_class = $this->getClasses($location);
                    $wpjobportal_contentswrapperstart .= '<span id="wpjobportal_modulelist_titlebar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . esc_html(__('Location', 'wp-job-portal')) . '</span>';
                }
                if ($posteddate != 0) {
                    $wpjobportal_class = $this->getClasses($posteddate);
                    $wpjobportal_contentswrapperstart .= '<span id="wpjobportal_modulelist_titlebar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . esc_html(__('Posted', 'wp-job-portal')) . '</span>';
                }
                $wpjobportal_contentswrapperstart .= '</div>';
                $wpjobportal_wpdir = wp_upload_dir();
                if (isset($wpjobportal_resumes)) {
                    foreach ($wpjobportal_resumes as $wpjobportal_resume) {
                        $wpjobportal_contents .= '<div id="wpjobportal_modulelist_databar"><span id="whiteback"></span>';
                        if ($wpjobportal_resumephoto != 0) {
                            $wpjobportal_class = $this->getClasses($wpjobportal_resumephoto);
                            $wpjobportal_contents .= '<span id="wpjobportal_modulelist_databar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">';

                            $c_l = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'viewresume', 'wpjobportalid'=>$wpjobportal_resume->resumealiasid, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));

                            $wpjobportal_logo = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('jobseeker');
                            if($wpjobportal_resume->photo != ''){
                                $wpjobportal_logo = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/jobseeker/resume_' . $wpjobportal_resume->resumeid . '/photo/' . $wpjobportal_resume->photo;
                            }

                            $wpjobportal_contents .= '<a href=' . esc_url($c_l) . '><img  src="' . esc_url($wpjobportal_logo) . '"  /></a>';
                            $wpjobportal_contents .= '</span>';
                        }
                        if ($wpjobportal_applicationtitle != 0) {
                            $wpjobportal_class = $this->getClasses($wpjobportal_applicationtitle);

                            $an_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'viewresume', 'wpjobportalid'=>$wpjobportal_resume->resumealiasid, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                            $wpjobportal_contents .= '<span id="wpjobportal_modulelist_databar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">
                                            <span id="themeanchor">
                                                <a class="anchor" href="' . esc_url($an_link) . '">
                                                    ' . esc_html($wpjobportal_resume->applicationtitle) . '
                                                </a>
                                            </span>
                                            </span>';
                        }
                        if ($wpjobportal_name != 0) {
                            $wpjobportal_class = $this->getClasses($wpjobportal_name);
                            $wpjobportal_contents .= '<span id="wpjobportal_modulelist_databar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . esc_html($wpjobportal_resume->name) . '</span>';
                        }
                        if ($wpjobportal_category != 0) {
                            $wpjobportal_class = $this->getClasses($wpjobportal_category);
                            $wpjobportal_contents .= '<span id="wpjobportal_modulelist_databar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . esc_html($wpjobportal_resume->cat_title) . '</span>';
                        }
                        if ($wpjobportal_jobtype != 0) {
                            $wpjobportal_class = $this->getClasses($wpjobportal_jobtype);
                            $wpjobportal_contents .= '<span id="wpjobportal_modulelist_databar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . esc_html($wpjobportal_resume->jobtypetitle) . '</span>';
                        }
                        if ($wpjobportal_experience != 0) {
                            $wpjobportal_class = $this->getClasses($wpjobportal_experience);
                            $wpjobportal_contents .= '<span id="wpjobportal_modulelist_databar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . esc_html($wpjobportal_resume->experiencetitle) . '</span>';
                        }
                        if ($available != 0) {
                            $wpjobportal_class = $this->getClasses($available);
                            $wpjobportal_resumeavail = ($wpjobportal_resume->available == 1) ? esc_html(__('Yes', 'wp-job-portal')) : esc_html(__('No', 'wp-job-portal'));
                            $wpjobportal_contents .= '<span id="wpjobportal_modulelist_databar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . esc_html($wpjobportal_resumeavail) . '</span>';
                        }
                        if ($wpjobportal_gender != 0) {
                            $wpjobportal_class = $this->getClasses($wpjobportal_gender);
                            $wpjobportal_resumegender = ($wpjobportal_resume->gender == 1) ? esc_html(__('Male', 'wp-job-portal')) : esc_html(__('Female', 'wp-job-portal'));
                            $wpjobportal_contents .= '<span id="wpjobportal_modulelist_databar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . esc_html($wpjobportal_resumegender) . '</span>';
                        }
                        if ($wpjobportal_nationality != 0) {
                            $wpjobportal_class = $this->getClasses($wpjobportal_nationality);
                            $wpjobportal_contents .= '<span id="wpjobportal_modulelist_databar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . esc_html($wpjobportal_resume->nationalityname) . '</span>';
                        }
                        if ($location != 0) {
                            $wpjobportal_class = $this->getClasses($location);
                            $wpjobportal_addlocation = JSModel::getJSModel('configurations')->getConfigValue('defaultaddressdisplaytype');
                            $wpjobportal_joblocation = !empty($wpjobportal_job->cityname) ? $wpjobportal_job->cityname : ' ';
                            switch ($wpjobportal_addlocation) {
                                case 'csc':
                                    $wpjobportal_joblocation .=!empty($wpjobportal_job->statename) ? ', ' . $wpjobportal_job->statename : '';
                                    $wpjobportal_joblocation .=!empty($wpjobportal_job->countryname) ? ', ' . $wpjobportal_job->countryname : '';
                                    break;
                                case 'cs':
                                    $wpjobportal_joblocation .=!empty($wpjobportal_job->statename) ? ', ' . $wpjobportal_job->statename : '';
                                    break;
                                case 'cc':
                                    $wpjobportal_joblocation .=!empty($wpjobportal_job->countryname) ? ', ' . $wpjobportal_job->countryname : '';
                                    break;
                            }
                            $wpjobportal_contents .= '<span id="wpjobportal_modulelist_databar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . esc_html($wpjobportal_joblocation) . '</span>';
                        }
                        if ($posteddate != 0) {
                            $wpjobportal_class = $this->getClasses($posteddate);
                            $wpjobportal_contents .= '<span id="wpjobportal_modulelist_databar" class="desktop_w-' . esc_attr($wpjobportal_desktop_w) . ' tablet_w-' . esc_attr($wpjobportal_tablet_w) . ' mobile_w-' . esc_attr($mobile_w) . ' ' . esc_attr($wpjobportal_class) . '">' . gmdate($wpjobportal_dateformat, strtotime($wpjobportal_resume->created)) . '</span>';
                        }
                        $wpjobportal_contents .= '</div>';
                    }
                }

                $wpjobportal_contentswrapperend = '</div>';
            } else { //box style
                $wpjobportal_jobwidthclass = "modjob" . esc_attr($wpjobportal_resumesinrow);
                $wpjobportal_jobtabwidthclass = "modjobtab" . esc_attr($wpjobportal_resumesinrowtab);
                //$wpjobportal_contentswrapperstart .= '<div id="wpjobportal_module_wrapper" class="' . esc_attr($wpjobportal_moduleName) . '" style="height:' . esc_attr($wpjobportal_moduleheight) . 'px;overflow:hidden;" >';
                $wpjobportal_contentswrapperstart .= '<div id="wpjobportal_module_wrapper" class="' . esc_attr($wpjobportal_moduleName) . '">';
                if ($wpjobportal_showtitle == 1) {
                    $wpjobportal_contentswrapperstart .= '
                        <div id="tp_heading" class="wjportal-mod-heading">
                            ' . esc_html($title) . '
                        </div>
                    ';
                }
                $wpjobportal_inlineCSS = 'margin-top:' . esc_attr($wpjobportal_resumemargintop) . 'px;margin-left:' . esc_attr($wpjobportal_resumemarginleft) . 'px;';
                if (isset($wpjobportal_resumes)) {
                    $wpjobportal_wpdir = wp_upload_dir();
                    foreach ($wpjobportal_resumes as $wpjobportal_resume) {
                        $wpjobportal_contents .= '<div id="wpjobportal_module_wrap" class="' . esc_attr($wpjobportal_jobwidthclass) . ' ' . esc_attr($wpjobportal_jobtabwidthclass) . ' wjportal-resume-mod">
                                      <div id="wpjobportal_module">';

                        $an_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'viewresume', 'wpjobportalid'=>$wpjobportal_resume->resumealiasid, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        $wpjobportal_dataclass = 'data100';
                        if ($wpjobportal_resumephoto != 0) {

                            $c_l = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'viewresume', 'wpjobportalid'=>$wpjobportal_resume->resumealiasid, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                            if ($wpjobportal_logodatarow == 1) { // Combine
                                $wpjobportal_logoclass = "comp40";
                                $wpjobportal_dataclass = "data60";
                                $wpjobportal_logocss = 'width:' . esc_attr($wpjobportal_photowidth) . 'px;';
                            } else {
                                $wpjobportal_logoclass = "comp100";
                                $wpjobportal_dataclass = "data100";
                                $wpjobportal_logocss = 'height:' . esc_attr($wpjobportal_photoheight) . 'px;';
                            }
                            $wpjobportal_logo = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('jobseeker');
                            if($wpjobportal_resume->photo != ''){
                                $wpjobportal_logo = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/jobseeker/resume_' . $wpjobportal_resume->resumeid . '/photo/' . $wpjobportal_resume->photo;
                            }
                            $wpjobportal_logoclass .= $this->getClasses($wpjobportal_resumephoto);
                            /*$wpjobportal_contents .= '
                                                <div id="wpjobportal_module_data_fieldwrapper" class="' . $wpjobportal_logoclass . ' wjportal-res-logo" >
                                                    <a href=' . esc_url($c_l) . '><img  src="' . esc_url($wpjobportal_logo) . '" /></a>
                                                </div>
                                              ';*/
                        }
                        $wpjobportal_contents .= '<div class="wjportal-res-cont">';
                        $wpjobportal_contents .= '<div id="wpjobportal_module_heading" class="wjportal-res-data wjportal-res-title">
                                        <a class="wjportal-res-name" href="' . esc_url($an_link) . '">
                                            ' . esc_html($wpjobportal_resume->name) . '
                                        </a>
                                      </div>';
                        $wpjobportal_contents .= '<div id="wpjobportal_module_data_fieldwrapper" class="' . esc_attr($wpjobportal_dataclass) . ' visible-all">';
                        $colwidthclass = 'modcolwidth' . esc_attr($wpjobportal_datacolumn);
                        if ($wpjobportal_applicationtitle != 0) {
                            $wpjobportal_class = $this->getClasses($wpjobportal_applicationtitle);

                            $an_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'viewresume', 'wpjobportalid'=>$wpjobportal_resume->resumealiasid, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                            $wpjobportal_contents .= '
                                                <div id="wpjobportal_module_data_fieldwrapper" class="' . esc_attr($colwidthclass) . esc_attr($wpjobportal_class) . ' wjportal-res-data">
                                                    <a class="wjportal-res-app" href=' . esc_url($an_link) . '>' . esc_html($wpjobportal_resume->applicationtitle) . '</a>
                                                </div>
                                              ';
                        }
                        /*if ($wpjobportal_name != 0) {
                            $wpjobportal_class = $this->getClasses($wpjobportal_name);

                            $c_l = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'viewresume', 'wpjobportalid'=>$wpjobportal_resume->resumealiasid, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                            $wpjobportal_contents .= '
                                                <div id="wpjobportal_module_data_fieldwrapper" class="' . esc_attr($colwidthclass) . esc_attr($wpjobportal_class) . ' wjportal-res-data">
                                                    <span id="wpjobportal_module_data_fieldtitle" class="wjportal-res-data-tit">' . esc_html(__('Name', 'wp-job-portal')) . ' : </span>
                                                    <span id="wpjobportal_module_data_fieldvalue" class="wjportal-res-data-val">
                                                        <a class="wjportal-res-name" href=' . esc_url($c_l) . '>' . $wpjobportal_resume->name . '</a></span>
                                                    </span>
                                                </div>
                                              ';
                        }*/
                        if ($wpjobportal_category != 0) {
                            $wpjobportal_class = $this->getClasses($wpjobportal_category);
                            $wpjobportal_contents .= '
                                                <div id="wpjobportal_module_data_fieldwrapper" class="' . esc_attr($colwidthclass) . esc_attr($wpjobportal_class) . ' wjportal-res-data">
                                                    <span id="wpjobportal_module_data_fieldtitle" class="wjportal-res-data-tit">' . esc_html(__('Category', 'wp-job-portal')) . ' : </span>
                                                    <span id="wpjobportal_module_data_fieldvalue" class="wjportal-res-data-val">' . esc_html($wpjobportal_resume->cat_title) . '</span>
                                                </div>
                                              ';
                        }
                        if ($wpjobportal_jobtype != 0) {
                            $wpjobportal_class = $this->getClasses($wpjobportal_jobtype);
                            $wpjobportal_contents .= '
                                                <div id="wpjobportal_module_data_fieldwrapper" class="' . esc_attr($colwidthclass) . esc_attr($wpjobportal_class) . ' wjportal-res-data">
                                                    <span id="wpjobportal_module_data_fieldtitle" class="wjportal-res-data-tit">' . esc_html(__('Type', 'wp-job-portal')) . ' : </span>
                                                    <span id="wpjobportal_module_data_fieldvalue" class="wjportal-res-data-val">' . esc_html($wpjobportal_resume->jobtypetitle) . '</span>
                                                </div>
                                              ';
                        }
                        /*if ($wpjobportal_experience != 0) {
                            $wpjobportal_class = $this->getClasses($wpjobportal_experience);
                            $wpjobportal_contents .= '
                                                <div id="wpjobportal_module_data_fieldwrapper" class="' . esc_attr($colwidthclass) . esc_attr($wpjobportal_class) . ' wjportal-res-data">
                                                    <span id="wpjobportal_module_data_fieldtitle" class="wjportal-res-data-tit">' . esc_html(__('Experience', 'wp-job-portal')) . ' : </span>
                                                    <span id="wpjobportal_module_data_fieldvalue" class="wjportal-res-data-val">' . esc_html($wpjobportal_resume->experiencetitle) . '</span>
                                                </div>
                                              ';
                        }
                        if ($available != 0) {
                            $wpjobportal_class = $this->getClasses($available);
                            $wpjobportal_resume->available = esc_html(__("No",'wp-job-portal'));
                            if($wpjobportal_resume->available == 1){
                                $wpjobportal_resume->available = esc_html(__("Yes",'wp-job-portal'));
                            }
                            $wpjobportal_contents .= '
                                                <div id="wpjobportal_module_data_fieldwrapper" class="' . esc_attr($colwidthclass) . esc_attr($wpjobportal_class) . ' wjportal-res-data">
                                                    <span id="wpjobportal_module_data_fieldtitle" class="wjportal-res-data-tit">' . esc_html(__('Available', 'wp-job-portal')) . ' : </span>
                                                    <span id="wpjobportal_module_data_fieldvalue" class="wjportal-res-data-val">' . esc_html($wpjobportal_resume->available) . '</span>
                                                </div>
                                              ';
                        }
                        if ($wpjobportal_gender != 0) {
                            $wpjobportal_class = $this->getClasses($wpjobportal_gender);
                            $wpjobportal_resumegender = ($wpjobportal_resume->gender == 1) ? esc_html(__('Male', 'wp-job-portal')) : esc_html(__('Female', 'wp-job-portal'));
                            $wpjobportal_contents .= '
                                                <div id="wpjobportal_module_data_fieldwrapper" class="' . esc_attr($colwidthclass) . esc_attr($wpjobportal_class) . ' wjportal-res-data">
                                                    <span id="wpjobportal_module_data_fieldtitle" class="wjportal-res-data-tit">' . esc_html(__('Gender', 'wp-job-portal')) . ' : </span>
                                                    <span id="wpjobportal_module_data_fieldvalue" class="wjportal-res-data-val">' . esc_html($wpjobportal_resumegender .) '</span>
                                                </div>
                                              ';
                        }*/
                        if ($wpjobportal_nationality != 0) {
                            $wpjobportal_class = $this->getClasses($wpjobportal_nationality);
                            $wpjobportal_contents .= '
                                                <div id="wpjobportal_module_data_fieldwrapper" class="' . esc_attr($colwidthclass) . esc_attr($wpjobportal_class) . ' wjportal-res-data">
                                                    <span id="wpjobportal_module_data_fieldtitle" class="wjportal-res-data-tit">' . esc_html(__('Nationality', 'wp-job-portal')) . ' : </span>
                                                    <span id="wpjobportal_module_data_fieldvalue" class="wjportal-res-data-val">' . esc_html($wpjobportal_resume->nationalityname) . '</span>
                                                </div>
                                              ';
                        }
                        if ($location != 0) {
                            $wpjobportal_contents .= '
                                                <div id="wpjobportal_module_data_fieldwrapper" class="' . esc_attr($colwidthclass) . esc_attr($wpjobportal_class) . ' wjportal-res-data">
                                                    <span id="wpjobportal_module_data_fieldtitle" class="wjportal-res-data-tit">' . esc_html(__('Location', 'wp-job-portal')) . ' : </span>
                                                    <span id="wpjobportal_module_data_fieldvalue" class="wjportal-res-data-val">' . esc_html($wpjobportal_resume->location) . '</span>
                                                </div>
                                              ';
                        }
                        if ($posteddate != 0) {
                            $wpjobportal_class = $this->getClasses($posteddate);
                            $wpjobportal_contents .= '
                                                <div id="wpjobportal_module_data_fieldwrapper" class="' . esc_attr($colwidthclass) . esc_attr($wpjobportal_class) . ' wjportal-res-data">
                                                    <span id="wpjobportal_module_data_fieldtitle" class="wjportal-res-data-tit">' . esc_html(__('Posted', 'wp-job-portal')) . ' : </span>
                                                    <span id="wpjobportal_module_data_fieldvalue" class="wjportal-res-data-val">' . esc_html(date_i18n($wpjobportal_dateformat, strtotime($wpjobportal_resume->created))) . '</span>
                                                </div>
                                              ';
                        }
                        $wpjobportal_contents .= '</div>
                                    </div>
                                </div>
                            </div>';
                    }
                }

                $wpjobportal_contentswrapperend = '</div>';
            }

            return $wpjobportal_contentswrapperstart . $wpjobportal_contents . $wpjobportal_contentswrapperend;
        }
    }

    function listModuleByJobcatOrType($wpjobportal_jobs, $wpjobportal_classname, $wpjobportal_showtitle, $title, $columnperrow, $wpjobportal_jobfor){

        if (! is_numeric($columnperrow)) {
            $columnperrow = 3;
        }
        if($columnperrow < 1){
            $columnperrow = 3;
        }

        $wpjobportal_width = 100 / $columnperrow;
        $wpjobportal_width = (int) $wpjobportal_width;


        $wpjobportal_html = '
            <div id="wpjobportal_mod_wrapper" class="wjportal-job-by-mod">';
                // if ($wpjobportal_showtitle == 1) {
                //     $wpjobportal_html .= '<div id="tp_heading" class="wjportal-mod-heading">'.esc_html($title).'</div>';
                // }
                $wpjobportal_html .= '<div id="wpjobportal-data-wrapper" class="'.esc_attr($wpjobportal_classname).' wjportal-job-by">';
                if (isset($wpjobportal_jobs)) {
                    foreach ($wpjobportal_jobs as $wpjobportal_job) {
                        if($wpjobportal_jobfor == 1) //Types
                            $anchor = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobs', 'jobtype'=>$wpjobportal_job->aliasid, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        if($wpjobportal_jobfor == 2) //Categories
                            $anchor = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobs', 'category'=>$wpjobportal_job->aliasid, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        $wpjobportal_html .='<div class="wjportal-job-by-item" style="width:'.esc_attr($wpjobportal_width).'%">
                                    <a href="'.esc_attr($anchor).'" class="wjportal-job-by-item-cnt">
                                        ' . esc_attr($wpjobportal_job->objtitle) . '<span class="wjportal-job-by-item-num"> (' . esc_html($wpjobportal_job->totaljobs) . ')</span>
                                    </a>
                                </div>';
                    }
                }
                $wpjobportal_html .= '</div>
            </div>
        ';

        return $wpjobportal_html;
    }

    function listModuleLocation($wpjobportal_jobs, $wpjobportal_classname, $wpjobportal_showtitle, $title, $columnperrow, $locationfor){

        if (! is_numeric($columnperrow)) {
            $columnperrow = 3;
        }
        if($columnperrow < 1){
            $columnperrow = 3;
        }

        $wpjobportal_width = 100 / $columnperrow;
        $wpjobportal_width = (int) $wpjobportal_width;

        $wpjobportal_html = '
            <div id="wpjobportal_mod_wrapper" class="wjportal-job-by-location-mod">';
                // if ($wpjobportal_showtitle == 1) {
                //     $wpjobportal_html .= '<div id="tp_heading" class="wjportal-mod-heading">'.esc_html($title).'</div>';
                // }
                $wpjobportal_html .= '<div id="wpjobportal-data-wrapper" class="'.esc_attr($wpjobportal_classname).' wjportal-job-by-loc">';
                if (is_array($wpjobportal_jobs)) {
                    foreach ($wpjobportal_jobs as $wpjobportal_job) {
                        if($locationfor == 1)
                            $anchor = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobs', 'city'=>$wpjobportal_job->locationid, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        if($locationfor == 2)
                            $anchor = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobs', 'state'=>$wpjobportal_job->locationid, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        if($locationfor == 3)
                            $anchor = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobs', 'country'=>$wpjobportal_job->locationid, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        $wpjobportal_html .='<div class="wjportal-job-by-loc-item" style="width:'.esc_attr($wpjobportal_width).'%">
                                    <a class="wjportal-job-by-loc-item-cnt" href="'.esc_url($anchor).'">
                                        ' . esc_html($wpjobportal_job->locationname) . ' <span class="wjportal-job-by-item-num">(' . esc_html($wpjobportal_job->totaljobs) . ')</span>
                                    </a>
                                </div>';
                    }
                }
                $wpjobportal_html .= '</div>
            </div>
        ';

        return $wpjobportal_html;
    }

    function prepareStyleForStats($wpjobportal_classname, $wpjobportal_color1, $wpjobportal_color2, $wpjobportal_color3){

        $wpjobportal_style = '<style type="text/css">';
            if (!empty($wpjobportal_color1)) {
                $wpjobportal_style .='  div.'.esc_attr($wpjobportal_classname).' div.wpjobportal-value{color: '.esc_attr($wpjobportal_color1).' !important;}';
            }
            if (!empty($wpjobportal_color2)) {
                $wpjobportal_style .='  div.'.esc_attr($wpjobportal_classname).' div.wpjobportal-value{background: '.esc_attr($wpjobportal_color2).' !important;}';
            }
            if (!empty($wpjobportal_color3)) {
                $wpjobportal_style .='  div.'.esc_attr($wpjobportal_classname).' div.wpjobportal-value{border: 1px solid '.esc_attr($wpjobportal_color3).' !important;}';
            }
        $wpjobportal_style .='</style>';

        return $wpjobportal_style;
    }

    function prepareStyleForBlocks($wpjobportal_classname, $wpjobportal_color1, $wpjobportal_color2, $wpjobportal_color3){
        $wpjobportal_style = '<style type="text/css">';
            if (!empty($wpjobportal_color1)) {
                $wpjobportal_style .='  div.'.esc_attr($wpjobportal_classname).' div.anchor a.anchor{color: '.esc_attr($wpjobportal_color1).' !important;}';
            }
            if (!empty($wpjobportal_color2)) {
                $wpjobportal_style .='  div.'.esc_attr($wpjobportal_classname).' div.anchor a.anchor{background: '.esc_attr($wpjobportal_color2).' !important;}';
            }
            if (!empty($wpjobportal_color3)) {
                $wpjobportal_style .='  div.'.esc_attr($wpjobportal_classname).' div.anchor a.anchor{border: 1px solid '.esc_attr($wpjobportal_color3).' !important;}';
            }
        $wpjobportal_style .='</style>';

        return $wpjobportal_style;
    }

    function perpareStyleSheet($wpjobportal_classname , $wpjobportal_color1 , $wpjobportal_color2 , $wpjobportal_color3 , $wpjobportal_color4 , $wpjobportal_color5 , $wpjobportal_color6 ){

        $wpjobportal_style = '<style type="text/css">';
            if (!empty($wpjobportal_color1)) {
                $wpjobportal_style .='  div#wpjobportal_module_wrapper.'.esc_attr($wpjobportal_classname).' a{color:'.esc_attr($wpjobportal_color1).';}';
            }
            if (!empty($wpjobportal_color3)) {
                $wpjobportal_style .='  div.'.esc_attr($wpjobportal_classname).' div#wpjobportal_module{background: '.esc_attr($wpjobportal_color3).';}
                            div.'.esc_attr($wpjobportal_classname).' div#wpjobportal_modulelist_databar{background: '.esc_attr($wpjobportal_color3).';}
                            div.'.esc_attr($wpjobportal_classname).' div#wpjobportal_modulelist_titlebar{background: '.esc_attr($wpjobportal_color3).';}
                        ';
            }
            if (!empty($wpjobportal_color4)) {
                $wpjobportal_style .='  div.'.esc_attr($wpjobportal_classname).' div#wpjobportal_module{border: 1px solid '.esc_attr($wpjobportal_color4).';}
                            div.'.esc_attr($wpjobportal_classname).' div#wpjobportal_modulelist_titlebar{border: 1px solid '.esc_attr($wpjobportal_color4).';}
                            div.'.esc_attr($wpjobportal_classname).' div#wpjobportal_modulelist_databar{border: 1px solid '.esc_attr($wpjobportal_color4).';}
                        ';
            }
            if (!empty($wpjobportal_color5)) {
                $wpjobportal_style .='  div#wpjobportal_module_wrapper.'.esc_attr($wpjobportal_classname).' div#wpjobportal_module_wrap div#wpjobportal_module_data_fieldwrapper span#wpjobportal_module_data_fieldtitle{color: '.esc_attr($wpjobportal_color5).';}
                            div.'.esc_attr($wpjobportal_classname).' div#wpjobportal_modulelist_databar{color: '.esc_attr($wpjobportal_color5).';}
                            div.'.esc_attr($wpjobportal_classname).' div#wpjobportal_modulelist_titlebar span#wpjobportal_modulelist_titlebar{color: '.esc_attr($wpjobportal_color5).';}
                        ';
            }
            if (!empty($wpjobportal_color6)) {
                $wpjobportal_style .='  div#wpjobportal_module_wrapper.'.esc_attr($wpjobportal_classname).' div#wpjobportal_module_wrap div#wpjobportal_module_data_fieldwrapper span#wpjobportal_module_data_fieldvalue{color: '.esc_attr($wpjobportal_color6).';}';
            }
            if (!empty($wpjobportal_color2)) {
                $wpjobportal_style .='  div.'.esc_attr($wpjobportal_classname).' div#wpjobportal_module span#wpjobportal_module_heading {border-bottom: 1px solid '.esc_attr($wpjobportal_color2).';}';
            }
        $wpjobportal_style .='</style>';
        return $wpjobportal_style;
    }

    function listModuleJobsForMap($wpjobportal_jobs, $title, $wpjobportal_showtitle, $wpjobportal_company, $wpjobportal_category, $wpjobportal_moduleheight, $wpjobportal_mapzoom){
        $wpjobportal_mappingservice = wpjobportal::$_config->getConfigValue('mappingservice');
        $wpjobportal_data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
        $wpjobportal_wpdir = wp_upload_dir();
        $wpjobportal_logopath = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/employer/comp_'/* . $wpjobportal_comp->id . '/logo/' . $wpjobportal_comp->logofilename*/;
        $wpjobportal_default_logoPath = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('employer');


        $wpjobportal_html = '';
        if($wpjobportal_mappingservice == "gmap"){
            $wpjobportal_filekey = WPJOBPORTALincluder::getJSModel('common')->getGoogleMapApiAddress();
            wp_enqueue_script( 'jp-google-map', $wpjobportal_filekey, array(), '1.1.1', false );
            //$wpjobportal_html = $wpjobportal_filekey;

        }elseif ($wpjobportal_mappingservice == "osm") {
            $wpjobportal_html = '';
            wp_enqueue_script('wpjobportal-ol-script', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/ol.min.js');
            wp_enqueue_style('wpjobportal-ol-style', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/ol.min.css');
        }
        $wpjobportal_default_longitude = wpjobportal::$_config->getConfigurationByConfigName('default_longitude');
        $wpjobportal_default_latitude = wpjobportal::$_config->getConfigurationByConfigName('default_latitude');
        if($wpjobportal_showtitle == 1){
            $wpjobportal_html .= '
            <div id="tp_heading" class="wjportal-mod-heading">
                '.esc_html($title).'
            </div>';
        }
            if ($wpjobportal_jobs) {
                $wpjobportal_html .= '<div id="map-canvas" class="map-canvas-module" style="height:'.$wpjobportal_moduleheight.'px;width:100%;"></div>';
                if($wpjobportal_mappingservice == "gmap"){
                    wp_register_script( 'wpjobportal-inline-handle', '' );
                    wp_enqueue_script( 'wpjobportal-inline-handle' );
                    $wpjobportal_inline_js_script = '
                    var jobsarray = '.wp_json_encode($wpjobportal_jobs).';
                    var showCategory = '.$wpjobportal_category.';
                    var showCompany = '.$wpjobportal_company.';

                    var map = new google.maps.Map(document.getElementById("map-canvas"), {
                      zoom: '.esc_attr($wpjobportal_mapzoom).',
                      center: new google.maps.LatLng('.$wpjobportal_default_latitude.','.$wpjobportal_default_longitude.'),
                    });
                    var markers = [];
                    for(i = 0; i < jobsarray.length; i++){
                      var geocoder =  new google.maps.Geocoder();
                      if(jobsarray[i].multicity !== undefined){
                        var job = jobsarray[i];
                        for(k = 0; k < jobsarray[i].multicity.length; k++){
                          geocoder.geocode( { "address": jobsarray[i].multicity[k].cityname + \',\' + jobsarray[i].multicity[k].countryname}, function(results, status) {
                            if (status == google.maps.GeocoderStatus.OK) {
                              latitude = results[0].geometry.location.lat();
                              longitude = results[0].geometry.location.lng();
                              setMarker(map,job,latitude,longitude);
                            } else {
                              latitude = 0;
                              longitude = 0;
                            }
                          });
                        }
                      }else{
                        if(jobsarray[i].latitude.indexOf(",") > -1){ // multi location
                            var latarray = jobsarray[i].latitude.split(",");
                            var longarray = jobsarray[i].longitude.split(",");
                            for(l = 0; l < latarray.length; l++){
                                var latitudemap = latarray[l];
                                var longitudemap = longarray[l];
                                var marker = setMarker(map,jobsarray[i],latitudemap,longitudemap);
                                markers.push(marker);
                            }
                        }else{
                            var marker = setMarker(map,jobsarray[i],jobsarray[i].latitude,jobsarray[i].longitude);
                            markers.push(marker);
                        }
                      }
                    }

                    function setMarker(map,jobObject,latitude,longitude){
                      marker = new google.maps.Marker({
                        position: new google.maps.LatLng(latitude, longitude),
                        map: map
                      });
                      var infowindow = new google.maps.InfoWindow();
                      google.maps.event.addListener(marker, "click", (function(marker) {
                        return function() {
                          var markerContent = "<div class=\'wjportal-jobs-list-map\'><div class=\'wjportal-jobs-list\'>";
                          if(jobObject.companylogo != ""){
                            markerContent += "<div class=\'wjportal-jobs-logo\'><img src=\''.$wpjobportal_logopath.'"+jobObject.companyid+"/logo/"+jobObject.companylogo+"\' ></div>";
                          }else{
                            markerContent += "<div class=\'wjportal-jobs-logo\'><img src=\''.$wpjobportal_default_logoPath.'\' ></div>";
                          }
                          markerContent += "<div class=\'wjportal-jobs-cnt\'>";
                          if(showCompany == 1){
                           markerContent += "<div class=\'wjportal-jobs-data\'><a href=\'#\' class=\'wjportal-companyname\'>" + jobObject.companyname + "</a></div>";
                          }
                          if(showCategory == 1){
                            markerContent += "<div class=\'wjportal-jobs-data\'><a href=\'#\' class=\'wjportal-job-title\'>"+jobObject.title+"</a></div><div class=\'wjportal-jobs-data\'><span class=\'wjportal-jobs-data-txt\'>"+jobObject.cat_title+"</span></div></div></div></div>";
                          }
                          infowindow.setContent(markerContent);
                          infowindow.open(map, marker);
                        }
                      })(marker));
                      return marker;
                    }
                    /*
                    function autoCenter() {
                      //  Create a new viewpoint bound
                      var bounds = new google.maps.LatLngBounds();
                      //  Go through each...
                      jQuery.each(markers, function (index, marker) {
                        bounds.extend(marker.position);
                      });
                      //  Fit these bounds to the map
                      map.fitBounds(bounds);
                    }
                    autoCenter();
                    */
                  ';
                  wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
                  return $wpjobportal_html;
            }elseif ($wpjobportal_mappingservice == "osm") {
                wp_register_script( 'wpjobportal-inline-handle', '' );
                wp_enqueue_script( 'wpjobportal-inline-handle' );
                $wpjobportal_inline_js_script = '
                            osmMap = null;
                            var showCategory = '.$wpjobportal_category.';
                            var showCompany = '.$wpjobportal_company.';
                            var default_latitude = parseFloat('.$wpjobportal_default_latitude.');
                            var default_longitude = parseFloat('.$wpjobportal_default_latitude.');;
                            var coordinate = [default_longitude,default_latitude];
                            if(!osmMap){
                                osmMap = new ol.Map({
                                    target: "map-canvas",
                                    layers: [
                                        new ol.layer.Tile({
                                            source: new ol.source.OSM()
                                        })
                                    ],
                                });
                            }
                            osmMap.setView(new ol.View({
                                center: ol.proj.fromLonLat(coordinate),
                                zoom: '.esc_attr($wpjobportal_mapzoom).'
                            }));
                            // For showing multiple marker on map
                            var jobsarray = '.wp_json_encode($wpjobportal_jobs).';
                            for(i = 0; i < jobsarray.length; i++){
                                var latarray = jobsarray[i].latitude.split(",");
                                var longarray = jobsarray[i].longitude.split(",");
                                for(l = 0; l < latarray.length; l++){
                                    var latitudemap = parseFloat(latarray[l]);
                                    var longitudemap = parseFloat(longarray[l]);
                                }
                                coordinate = [longitudemap,latitudemap];
                                osmAddMarker(osmMap, coordinate);
                                osmMap.addEventListener("click",function(event){
                                    osmMap.forEachFeatureAtPixel(event.pixel, function (feature, layer) {
                                        var index = ol.coordinate.toStringXY(feature.getGeometry().getCoordinates());
                                        var box = document.getElementById("osmmappopup");
                                        if(!box){
                                            box = document.createElement("div");
                                            box.id = "osmmappopup";
                                        }
                                        var html = "<div class=\'wjportal-jobs-list-map\'><div class=\'wjportal-jobs-list\'><div class=\'wjportal-jobs-logo\'><img src=\''. WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('employer') .'\' ></div><div class=\'wjportal-jobs-cnt\'><div class=\'wjportal-jobs-data\'><a href=\'#\' class=\'wjportal-companyname\'>Company Name</a></div><div class=\'wjportal-jobs-data\'><a href=\'#\' class=\'wjportal-job-title\'>Job Title</a></div><div class=\'wjportal-jobs-data\'><span class=\'wjportal-jobs-data-txt\'>Category</span></div></div></div></div>";
                                        box.innerHTML = html;
                                        var prev_infowindow = new ol.Overlay({
                                            element: box,
                                            offset: [-140,-35]
                                        });
                                        prev_infowindow.setPosition(event.coordinate);
                                        osmMap.addOverlay(prev_infowindow);
                                    });
                                });
                            }

                        function osmAddMarker(osmMap, coordinate, icon) {
                            if(osmMap && ol){
                                if(!icon){
                                    icon = "http://maps.gstatic.com/mapfiles/api-3/images/spotlight-poi2.png";
                                }
                                var vectorLayer = new ol.layer.Vector({
                                    source: new ol.source.Vector({
                                        features: [
                                            new ol.Feature({
                                                geometry: new ol.geom.Point(ol.proj.transform(coordinate, "EPSG:4326", "EPSG:3857")),
                                            })
                                        ]
                                    }),
                                    style: new ol.style.Style({
                                        image: new ol.style.Icon({
                                            src: icon
                                        })
                                    })
                                });
                                osmMap.addLayer(vectorLayer);
                                return vectorLayer;
                            }
                            return false;
                        }';
                        wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
             return $wpjobportal_html;
          }
        }
    }

    function getJOBSWidgetHTML($wpjobportal_jobs,$wpjobportal_pageid,$title,$wpjobportal_no_of_columns,$wpjobportal_layoutName,$listtype,$typetag){
        $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];

        $wpjobportal_moduleName = $wpjobportal_layoutName;
        $wpjobportal_moduleheight = '500';
        $wpjobportal_contentswrapperstart = '';
        $wpjobportal_contents = '';
        $wpjobportal_class = ' visible-all';

        if ($wpjobportal_jobs) {
            /*if ($listtype == 1) {*/ //list style
                $wpjobportal_contentswrapperstart .= '<div id="wpjobportal_module_wrapper" class="' . esc_attr($wpjobportal_moduleName) . '" >';
                    $wpjobportal_contentswrapperstart .= '
                                        <div id="tp_heading">
                                            <span id="tp_headingtext">
                                                <span id="tp_headingtext_center">' . esc_html($title) . '</span>
                                            </span>
                                        </div>
                                    ';
                $wpjobportal_wpdir = wp_upload_dir();
                $wpjobportal_data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
                if (isset($wpjobportal_jobs)) {
                    foreach ($wpjobportal_jobs as $wpjobportal_job) {
                        $wpjobportal_contents .= '<div id="wpjobportal-module-datalist" class="wjportal-jobs-list">';
                            $wpjobportal_contents .= '<div class="wjportal-jobs-list-top-wrp">';
                                $wpjobportal_contents .= '<div class="wjportal-jobs-logo">';
                                    $c_l = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_job->companyaliasid, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                                    $wpjobportal_logo = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('employer');
                                    if($wpjobportal_job->logofilename != ''){
                                        $wpjobportal_logo = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/employer/comp_' . $wpjobportal_job->companyid . '/logo/' . $wpjobportal_job->logofilename;
                                    }
                                    $wpjobportal_contents .= '<a href=' . esc_url($c_l) . '><img src="' . esc_url($wpjobportal_logo) . '"  /></a>';
                                $wpjobportal_contents .= '</div>';
                                $an_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'viewjob', 'wpjobportalid'=>$wpjobportal_job->jobaliasid, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                                $wpjobportal_contents .= '<div class="wjportal-jobs-cnt-wrp">
                                                <div class="wjportal-jobs-middle-wrp">
                                                    <div class="wjportal-jobs-data">
                                                        <a href="#" class="wjportal-companyname" title="'. esc_attr(__("Company Name",'wp-job-portal')) .'">
                                                            '. esc_html(__("Company Name",'wp-job-portal')) .'
                                                        </a>
                                                    </div>
                                                    <div class="wjportal-jobs-data">
                                                        <span class="wjportal-job-title">
                                                            <a href="' . esc_url($an_link) . '">
                                                                ' . esc_html($wpjobportal_job->title) . '
                                                            </a>
                                                        </span>
                                                    </div>
                                                    <div class="wjportal-jobs-data">
                                                        <span class="wjportal-jobs-data-text wjportal-jobs-data-icon-class-category">
                                                            '. esc_html($wpjobportal_job->cat_title) .'
                                                        </span>
                                                        <span class="wjportal-jobs-data-text wjportal-jobs-data-icon-class-category">
                                                            '. $wpjobportal_job->location .'
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="wjportal-jobs-right-wrp">
                                                    <div class="wjportal-jobs-info">';
                                                        $wpjobportal_tagname = 'New';
                                                        $wpjobportal_tagcolor = '#00A859';
                                                        $wpjobportal_textcolor = '#fff';
                                                        if ($typetag == 1) {
                                                            $wpjobportal_tagname = 'New';
                                                            $wpjobportal_tagcolor = '#00A859';
                                                            $wpjobportal_textcolor = '#fff';
                                                        } elseif ($typetag == 2) {
                                                            $wpjobportal_tagname = 'Top';
                                                            $wpjobportal_tagcolor = '#EFCEC5';
                                                            $wpjobportal_textcolor = '#0085BA';
                                                        } elseif ($typetag == 3) {
                                                            $wpjobportal_tagname = 'Hot';
                                                            $wpjobportal_tagcolor = '#DC143C';
                                                            $wpjobportal_textcolor = '#fff';
                                                        } elseif ($typetag == 4) {
                                                            $wpjobportal_tagname = 'Gold';
                                                            $wpjobportal_tagcolor = '#D6B043';
                                                            $wpjobportal_textcolor = '#fff';
                                                        } elseif ($typetag == 5) {
                                                            $wpjobportal_tagname = 'Featured';
                                                            $wpjobportal_tagcolor = '#378AD8';
                                                            $wpjobportal_textcolor = '#fff';
                                                        }
                                                        $wpjobportal_contents .= '<span class="wjportal-job-type" style="background:'.$wpjobportal_tagcolor.';color:'.$wpjobportal_textcolor.';">'. $wpjobportal_tagname .'</span>
                                                    </div>

                                                    <div class="wjportal-jobs-info">
                                                        <div class="wjportal-jobs-salary">
                                                            '. esc_html(__("0 $",'wp-job-portal')) .'
                                                            <span class="wjportal-salary-type">
                                                                '. esc_html(__(" / Per Month", 'wp-job-portal')) .'
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="wjportal-jobs-info">
                                                        '.human_time_diff(strtotime($wpjobportal_job->created),strtotime(date_i18n("Y-m-d H:i:s"))).' '.esc_html(__("Ago",'wp-job-portal'))  .'
                                                    </div>
                                                </div>
                                            </div>';
                            $wpjobportal_contents .= '</div>';
                        $wpjobportal_contents .= '</div>';
                    }
                }

                $wpjobportal_contentswrapperend = '</div>';
            /*}*/
            return $wpjobportal_contentswrapperstart . $wpjobportal_contents . $wpjobportal_contentswrapperend;
        }
    }

      function getCompanies_WidgetHtml($title,$wpjobportal_layoutName, $wpjobportal_companies, $wpjobportal_noofcompanies, $wpjobportal_listingstyle,$wpjobportal_companytype,$wpjobportal_no_of_columns){
        $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];

        $wpjobportal_moduleName = $wpjobportal_layoutName;
        $wpjobportal_moduleheight = '500';
        $wpjobportal_contentswrapperstart = '';
        $wpjobportal_contents = '';
        $wpjobportal_class = ' visible-all';
        if ($wpjobportal_companies) {
            /*if ($wpjobportal_listingstyle == 1) {*/ //list style
                $wpjobportal_contentswrapperstart .= '<div id="wpjobportal_module_wrapper" class="' . esc_attr($wpjobportal_moduleName) . '" >';
                    $wpjobportal_contentswrapperstart .= '
                                        <div id="tp_heading">
                                            <span id="tp_headingtext">
                                                <span id="tp_headingtext_center">' . esc_html($title) . '</span>
                                            </span>
                                        </div>
                                    ';
                if (isset($wpjobportal_companies)) {
                    $wpjobportal_wpdir = wp_upload_dir();
                    $wpjobportal_data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
                    foreach ($wpjobportal_companies as $wpjobportal_company) {
                        $wpjobportal_color = ($wpjobportal_company->status == 1) ? "green" : "red";
                        if ($wpjobportal_company->status == 1) {
                            $wpjobportal_statusCheck = esc_html(__('Approved', 'wp-job-portal'));
                        } elseif ($wpjobportal_company->status == 0) {
                            $wpjobportal_statusCheck = esc_html(__('Waiting for approval', 'wp-job-portal'));
                        }elseif($wpjobportal_company->status == 2){
                             $wpjobportal_statusCheck = esc_html(__('Pending For Approval of Payment', 'wp-job-portal'));
                        }elseif ($wpjobportal_company->status == 3) {
                            $wpjobportal_statusCheck = esc_html(__('Pending Due To Payment', 'wp-job-portal'));
                        }else {
                            $wpjobportal_statusCheck = esc_html(__('Rejected', 'wp-job-portal'));
                        }
                         if(in_array('multicompany', wpjobportal::$_active_addons)){
                            $wpjobportal_mod = "multicompany";
                        }else{
                            $wpjobportal_mod = "company";
                        }
                        $wpjobportal_contents .= '<div id="wpjobportal-module-datalist" class="wjportal-company-list">';
                            $wpjobportal_contents .= '<div class="wjportal-company-list-top-wrp">';
                                $wpjobportal_contents .= '<div class="wjportal-company-logo">';
                                    $c_l = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_company->alias, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                                    $wpjobportal_logo = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('employer');
                                    if($wpjobportal_company->logofilename != ''){
                                        $wpjobportal_logo = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/employer/comp_' . $wpjobportal_company->id . '/logo/' . $wpjobportal_company->logofilename;
                                    }
                                    $wpjobportal_contents .= '<a href=' . esc_url($c_l) . '><img src="' . esc_url($wpjobportal_logo) . '"  /></a>';
                                $wpjobportal_contents .= '</div>';
                                $wpjobportal_contents .= '<div class="wjportal-company-cnt-wrp">';
                                    $wpjobportal_contents .= '<div class="wjportal-company-middle-wrp">
                                                    <div class="wjportal-company-data">
                                                        <a class="wjportal-companyname" href="' . esc_url($wpjobportal_company->url) . '">
                                                            ' . esc_html($wpjobportal_company->url) . '
                                                        </a>
                                                    </div>
                                                    <div class="wjportal-company-data">
                                                        <span class="wjportal-company-title">
                                                            <a href="' . esc_url($c_l) . '">
                                                                ' . esc_html($wpjobportal_company->name) . '
                                                            </a>
                                                        </span>
                                                    </div>
                                                    <div class="wjportal-company-data">
                                                        <div class="wjportal-company-data-text">
                                                            <span class="wjportal-company-data-title">'. esc_html(__("Created",'wp-job-portal')) .':</span>
                                                            <span class="wjportal-company-data-value">'. human_time_diff(strtotime($wpjobportal_company->created),strtotime(date_i18n("Y-m-d H:i:s"))).' '.esc_html(__("Ago",'wp-job-portal')) .':</span>
                                                        </div>
                                                        <div class="wjportal-company-data-text">
                                                            <span class="wjportal-company-data-title">'. esc_html(__("Status",'wp-job-portal')) .':</span>
                                                            <span class="wjportal-company-data-value '.esc_attr($wpjobportal_color).' ">'. wpjobportal::wpjobportal_getVariableValue($wpjobportal_statusCheck) .'</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="wjportal-company-right-wrp">
                                                    <div class="wjportal-company-action">
                                                        <a href="'.esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>$wpjobportal_mod, 'wpjobportallt'=>'viewcompany','wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid() ,'wpjobportalid'=>$wpjobportal_company->companyaliasid))).'" class="wjportal-company-act-btn" title="'. esc_attr(__("View Company",'wp-job-portal')) .'">
                                                            '. esc_html(__("View Company",'wp-job-portal')) .'
                                                        </a>
                                                    </div>
                                                </div>
                                                ';
                                    $wpjobportal_contents .= '</div>';
                                $wpjobportal_contents .= '</div>';
                            $wpjobportal_contents .= '</div>';
                        $wpjobportal_contents .= '</div>';
                    }
                }

                $wpjobportal_contentswrapperend = '</div>';
            /*} */
            return $wpjobportal_contentswrapperstart . $wpjobportal_contents . $wpjobportal_contentswrapperend;
        }else{
            $wpjobportal_html = '<div id="tp_heading">
                        <span id="tp_headingtext">
                                <span id="tp_headingtext_left"></span>
                                <span id="tp_headingtext_center">' . esc_html(__("No Record Found",'wp-job-portal')) . '</span>
                                <span id="tp_headingtext_right"></span>
                        </span>
                    </div>';
            return $wpjobportal_html;
        }
    }

    function getResume_WidgetHtml($title,$wpjobportal_layoutName, $wpjobportal_resumes, $wpjobportal_noofresumes, $wpjobportal_listingstyle,$wpjobportal_resumetype,$wpjobportal_no_of_columns){
        $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];

        $wpjobportal_moduleName = $wpjobportal_layoutName;
        $wpjobportal_moduleheight = '500';
        $wpjobportal_contentswrapperstart = '';
        $wpjobportal_contents = '';
        $wpjobportal_class = ' visible-all';
        $wpjobportal_data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
        if ($wpjobportal_resumes) {
            /*if ($wpjobportal_listingstyle == 1) {*/ //list style
                $wpjobportal_contentswrapperstart .= '<div id="wpjobportal_module_wrapper" class="' . esc_attr($wpjobportal_moduleName) . '" >';
                    $wpjobportal_contentswrapperstart .= '
                                        <div id="tp_heading">
                                            <span id="tp_headingtext">
                                                    <span id="tp_headingtext_center">' . esc_html($title) . '</span>
                                            </span>
                                        </div>
                                    ';
                $wpjobportal_wpdir = wp_upload_dir();
                if (isset($wpjobportal_resumes)) {
                    foreach ($wpjobportal_resumes as $wpjobportal_resume) {
                        $wpjobportal_contents .= '<div id="wpjobportal-module-datalist" class="wjportal-resume-list">';
                            $wpjobportal_contents .= '<div class="wjportal-resume-list-top-wrp">';
                                $wpjobportal_contents .= '<div class="wjportal-resume-logo">';
                                    $c_l = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'viewresume', 'wpjobportalid'=>$wpjobportal_resume->resumealiasid, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                                    $wpjobportal_logo = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('jobseeker');
                                    if($wpjobportal_resume->photo != ''){
                                        $wpjobportal_logo = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/jobseeker/resume_' . $wpjobportal_resume->resumeid . '/photo/' . $wpjobportal_resume->photo;
                                    }
                                    $wpjobportal_contents .= '<a href=' . esc_url($c_l) . '><img class="wpjobportal-module-datalist-img" src="' . esc_url($wpjobportal_logo) . '"  /></a>';
                                $wpjobportal_contents .= '</div>';
                                $wpjobportal_contents .= '<div class="wjportal-resume-cnt-wrp">
                                                <div class="wjportal-resume-middle-wrp">
                                                    <div class="wjportal-resume-data">
                                                        <span class="wjportal-resume-job-type" style="background:'.$wpjobportal_resume->jobtypecolor.'">
                                                            ' .esc_html( $wpjobportal_resume->jobtypetitle) . '
                                                        </span>
                                                    </div>
                                                    <div class="wjportal-resume-data">
                                                        <a class="wpjobportal-module-datalist-anchor" href="' . esc_url($c_l) . '">
                                                            <span class="wjportal-resume-name">
                                                                ' . esc_html($wpjobportal_resume->name) . '
                                                            </span>
                                                        </a>
                                                    </div>
                                                    <div class="wjportal-resume-data">
                                                        <span class="wjportal-resume-title">
                                                            '. esc_html($wpjobportal_resume->applicationtitle) .'
                                                        </span>
                                                    </div>
                                                    <div class="wjportal-resume-data">';
                                                        if(isset($wpjobportal_resume->location) && !empty($wpjobportal_resume->location)){
                                                            $wpjobportal_contents .= '<div class="wjportal-resume-data-text">
                                                                        <span class="wjportal-resume-data-title">'. esc_html(__("Location",'wp-job-portal')) .':</span>
                                                                        <span class="wjportal-resume-data-value">'. esc_html($wpjobportal_resume->location) .'</span>
                                                                    </div>';
                                                       }
                                                    $wpjobportal_contents .='    <div class="wjportal-resume-data-text">
                                                            <span class="wjportal-resume-data-title">'. esc_html(__("Experience",'wp-job-portal')) .':</span>
                                                            <span class="wjportal-resume-data-value">'.wpjobportal::$_common->getTotalExp($wpjobportal_resume->resumeid).'</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="wjportal-resume-right-wrp">
                                                    <div class="wjportal-resume-action">
                                                        <a href="#" class="wjportal-resume-act-btn" title="' . esc_attr(__("View Profile",'wp-job-portal')) . '">
                                                            ' . esc_html(__("View Profile",'wp-job-portal')) . '
                                                        </a>
                                                    </div>
                                                </div>
                                        ';
                                $wpjobportal_contents .= '</div>';
                            $wpjobportal_contents .= '</div>';
                        $wpjobportal_contents .= '</div>';
                    }
                }
                 $wpjobportal_contentswrapperend = '</div>';
            /*}*/
            return $wpjobportal_contentswrapperstart . $wpjobportal_contents . $wpjobportal_contentswrapperend;
        }else{
            $wpjobportal_html = '<div id="tp_heading">
                        <span id="tp_headingtext">
                                <span id="tp_headingtext_left"></span>
                                <span id="tp_headingtext_center">' . esc_html(__("No Record Found",'wp-job-portal')) . '</span>
                                <span id="tp_headingtext_right"></span>
                        </span>
                    </div>';
            return $wpjobportal_html;
        }
    }

    function getSearchJobs_WidgetHTML($title, $wpjobportal_showtitle, $wpjobportal_fieldtitle, $wpjobportal_category, $wpjobportal_jobtype, $wpjobportal_jobstatus, $wpjobportal_salaryrange, $shift, $duration, $wpjobportal_startpublishing, $wpjobportal_stoppublishing, $wpjobportal_company, $wpjobportal_address, $columnperrow) {

        if ($columnperrow <= 0)
            $columnperrow = 1;
        $wpjobportal_width = round(100 / $columnperrow);
        $wpjobportal_style = "style='width:" . $wpjobportal_width . "%'";

        $wpjobportal_html = '
                <div id="wpjobportal_module_wrapper">';
        if ($wpjobportal_showtitle == 1) {
            $wpjobportal_html .= '<div id="tp_heading" class="">
                        <span id="tp_headingtext">
                            <span id="tp_headingtext_center">' . esc_html($title) . '</span>
                        </span>
                    </div>';
        }
        $wpjobportal_html .='<div class="wjportal-form-wrp wjportal-search-job-form">';
        $wpjobportal_html .='<form class="job_form wjportal-form" id="job_form" method="post" action="' . esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobs', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()))) . '">';

        if ($wpjobportal_fieldtitle == 1) {
            $title = esc_html(__('Title', 'wp-job-portal'));
            $wpjobportal_value = WPJOBPORTALformfield::text('jobtitle', '', array('class' => 'inputbox wjportal-form-input-field'));
            $wpjobportal_html .= '<div class="wjportal-form-row" ' . esc_attr($wpjobportal_style) . '>
                <div class="wjportal-form-title">' . esc_html($title) . '</div>
                <div class="wjportal-form-value">' . wp_kses($wpjobportal_value,WPJOBPORTAL_ALLOWED_TAGS) . '</div>
            </div>';
        }

        if ($wpjobportal_category == 1) {
            $title = esc_html(__('Category', 'wp-job-portal'));
            $wpjobportal_value = WPJOBPORTALformfield::select('category[]', WPJOBPORTALincluder::getJSModel('category')->getCategoriesForCombo(), isset(wpjobportal::$_data[0]['filter']->category) ? wpjobportal::$_data[0]['filter']->category : '', esc_html(__('Select','wp-job-portal')) .' '. esc_html(__('Category', 'wp-job-portal')), array('class' => 'inputbox wjportal-form-select-field'));
            $wpjobportal_html .= '<div class="wjportal-form-row" ' . esc_attr($wpjobportal_style) . '>
                <div class="wjportal-form-title">' . esc_html($title) . '</div>
                <div class="wjportal-form-value">' . wp_kses($wpjobportal_value,WPJOBPORTAL_ALLOWED_TAGS) . '</div>
            </div>';
        }

        if ($wpjobportal_jobtype == 1) {
            $title = esc_html(__('Job Type', 'wp-job-portal'));
            $wpjobportal_value = WPJOBPORTALformfield::select('jobtype[]', WPJOBPORTALincluder::getJSModel('jobtype')->getJobTypeForCombo(), isset(wpjobportal::$_data[0]['filter']->jobtype) ? wpjobportal::$_data[0]['filter']->jobtype : '', esc_html(__('Select','wp-job-portal')) .' '. esc_html(__('Job Type', 'wp-job-portal')), array('class' => 'inputbox wjportal-form-select-field'));
            $wpjobportal_html .= '<div class="wjportal-form-row" ' . esc_attr($wpjobportal_style) . '>
                <div class="wjportal-form-title">' . esc_html($title) . '</div>
                <div class="wjportal-form-value">' . wp_kses($wpjobportal_value,WPJOBPORTAL_ALLOWED_TAGS) . '</div>
            </div>';
        }
        if ($wpjobportal_jobstatus == 1) {
            $title = esc_html(__('Job Status', 'wp-job-portal'));
            $wpjobportal_value = WPJOBPORTALformfield::select('jobstatus[]', WPJOBPORTALincluder::getJSModel('jobstatus')->getJobStatusForCombo(), isset(wpjobportal::$_data[0]['filter']->jobstatus) ? wpjobportal::$_data[0]['filter']->jobstatus : '', esc_html(__('Select','wp-job-portal')) .' '. esc_html(__('Job Status', 'wp-job-portal')), array('class' => 'inputbox wjportal-form-select-field'));
            $wpjobportal_html .= '<div class="wjportal-form-row" ' . esc_attr($wpjobportal_style) . '>
                <div class="wjportal-form-title">' . esc_html($title) . '</div>
                <div class="wjportal-form-value">' . wp_kses($wpjobportal_value,WPJOBPORTAL_ALLOWED_TAGS) . '</div>
            </div>';
        }
        if ($wpjobportal_salaryrange == 1) {
            $wpjobportal_salarytypelist = array(
                (object) array('id'=>WPJOBPORTAL_SALARY_NEGOTIABLE,'text'=>esc_html(__("Negotiable",'wp-job-portal'))),
                (object) array('id'=>WPJOBPORTAL_SALARY_FIXED,'text'=>esc_html(__("Fixed",'wp-job-portal'))),
                (object) array('id'=>WPJOBPORTAL_SALARY_RANGE,'text'=>esc_html(__("Range",'wp-job-portal'))),
            );
            $title = esc_html(__('Salary Range', 'wp-job-portal'));
            $wpjobportal_value = WPJOBPORTALformfield::select('salarytype', $wpjobportal_salarytypelist,'', esc_html(__("Select",'wp-job-portal')).' '.esc_html(__("Salary Type",'wp-job-portal')), array('class' => 'inputbox sal wjportal-form-select-field'));
            $wpjobportal_value .= WPJOBPORTALformfield::text('salaryfixed','', array('class' => 'inputbox sal wjportal-form-input-field','placeholder'=> esc_html(__('e.g 45000','wp-job-portal'))));
            $wpjobportal_value .=  WPJOBPORTALformfield::text('salarymin', '', array('class' => 'inputbox sal wjportal-form-input-field','placeholder'=> esc_html(__('e.g 3000','wp-job-portal'))));
            $wpjobportal_value .=  WPJOBPORTALformfield::text('salarymax', '', array('class' => 'inputbox sal wjportal-form-input-field','placeholder'=> esc_html(__('e.g 6000','wp-job-portal'))));
            $wpjobportal_value .= WPJOBPORTALformfield::select('salaryduration', WPJOBPORTALincluder::getJSModel('salaryrangetype')->getSalaryRangeTypesForCombo(), WPJOBPORTALincluder::getJSModel('salaryrangetype')->getDefaultSalaryRangeTypeId(), esc_html(__('Select','wp-job-portal')), array('class' => 'inputbox sal wjportal-form-select-field'));
            $wpjobportal_html .= '<div class="wjportal-form-row" ' . esc_attr($wpjobportal_style) . '>
                        <div class="wjportal-form-title">' . esc_html($title) . '</div>
                        <div class="wjportal-form-value">
                                <div class="wjportal-form-5-fields">
                                    <div class="wjportal-form-inner-fields">
                                        '.WPJOBPORTALformfield::select('salarytype', $wpjobportal_salarytypelist,'', esc_html(__("Select",'wp-job-portal')).' '.esc_html(__("Salary Type",'wp-job-portal')), array('class' => 'inputbox sal wjportal-form-select-field')).'
                                    </div>
                                    <div class="wjportal-form-inner-fields">
                                        '.WPJOBPORTALformfield::text('salaryfixed','', array('class' => 'inputbox sal wjportal-form-input-field','placeholder'=> esc_html(__('e.g 45000','wp-job-portal')))).'
                                    </div>
                                    <div class="wjportal-form-inner-fields">
                                        '.WPJOBPORTALformfield::text('salarymin', '', array('class' => 'inputbox sal wjportal-form-input-field','placeholder'=> esc_html(__('e.g 3000','wp-job-portal')))).'
                                    </div>
                                    <div class="wjportal-form-inner-fields">
                                        '.WPJOBPORTALformfield::text('salarymax', '', array('class' => 'inputbox sal wjportal-form-input-field','placeholder'=> esc_html(__('e.g 6000','wp-job-portal')))).'
                                    </div>
                                    <div class="wjportal-form-inner-fields">
                                        '.WPJOBPORTALformfield::select('salaryduration', WPJOBPORTALincluder::getJSModel('salaryrangetype')->getSalaryRangeTypesForCombo(), WPJOBPORTALincluder::getJSModel('salaryrangetype')->getDefaultSalaryRangeTypeId(), esc_html(__('Select','wp-job-portal')), array('class' => 'inputbox sal wjportal-form-select-field')).'
                                    </div>
                                </div>
                        </div>
            </div>';
        }
        if ($duration == 1) {
            $title = esc_html(__('Duration', 'wp-job-portal'));
            $wpjobportal_value = WPJOBPORTALformfield::text('duration', isset(wpjobportal::$_data[0]['filter']->duration) ? wpjobportal::$_data[0]['filter']->duration : '', array('class' => 'inputbox wjportal-form-input-field'));
            $wpjobportal_html .= '<div class="wjportal-form-row" ' . esc_attr($wpjobportal_style) . '>
                <div class="wjportal-form-title">' . esc_html($title) . '</div>
                <div class="wjportal-form-value">' . wp_kses($wpjobportal_value,WPJOBPORTAL_ALLOWED_TAGS) . '</div>
            </div>';
        }
        if ($wpjobportal_startpublishing == 1) {

        }
        if ($wpjobportal_stoppublishing == 1) {

        }
        if ($wpjobportal_company == 1) {
            $title = esc_html(__('Company', 'wp-job-portal'));
            $wpjobportal_value = WPJOBPORTALformfield::select('company[]', WPJOBPORTALincluder::getJSModel('company')->getCompaniesForCombo(), isset(wpjobportal::$_data[0]['filter']->company) ? wpjobportal::$_data[0]['filter']->company : '', esc_html(__('Select','wp-job-portal')) .' '. esc_html(__('Company', 'wp-job-portal')), array('class' => 'inputbox wjportal-form-select-field'));
            $wpjobportal_html .= '<div class="wjportal-form-row" ' . esc_attr($wpjobportal_style) . '>
                <div class="wjportal-form-title">' . esc_html($title) . '</div>
                <div class="wjportal-form-value">' . wp_kses($wpjobportal_value,WPJOBPORTAL_ALLOWED_TAGS) . '</div>
            </div>';
        }
        if ($wpjobportal_address == 1) {
            $title = esc_html(__('City', 'wp-job-portal'));
            $wpjobportal_value = WPJOBPORTALformfield::text('city', isset(wpjobportal::$_data[0]['filter']->city) ? wpjobportal::$_data[0]['filter']->city : '', array('class' => 'inputbox wjportal-form-input-field'));
            $wpjobportal_html .= '<div class="wjportal-form-row" ' . esc_attr($wpjobportal_style) . '>
                <div class="wjportal-form-title">' . esc_html($title) . '</div>
                <div class="wjportal-form-value">' . wp_kses($wpjobportal_value,WPJOBPORTAL_ALLOWED_TAGS) . '</div>
            </div>';
        }

        $wpjobportal_html .= '<div class="wjportal-form-btn-wrp">
                        <div class="wjportal-form-2-btn">
                            ' . WPJOBPORTALformfield::submitbutton('save', esc_html(__('Search Job', 'wp-job-portal')), array('class' => 'button wjportal-form-btn wjportal-form-srch-btn')) . '
                        </div>
                        <div class="wjportal-form-2-btn">
                            <a class="anchor wjportal-form-btn wjportal-form-cancel-btn" href="' . esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobsearch', 'wpjobportallt'=>'jobsearch', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()))) . '">
                            ' . esc_html(__('Advance Search', 'wp-job-portal')) . '
                            </a>
                        </div>
                    </div>
                    <input type="hidden" id="issearchform" name="issearchform" value="1"/>
                    <input type="hidden" id="WPJOBPORTAL_form_search" name="WPJOBPORTAL_form_search" value="WPJOBPORTAL_SEARCH"/>
                    <input type="hidden" id="wpjobportallay" name="wpjobportallay" value="jobs"/>
                </form>
            </div>
';
            wp_register_script( 'wpjobportal-inline-handle', '' );
            wp_enqueue_script( 'wpjobportal-inline-handle' );
            $wpjobportal_inline_js_script = '
                function getTokenInput() {
                    var cityArray = "' . esc_url_raw(admin_url("admin.php?page=wpjobportal_city&action=wpjobportaltask&task=getaddressdatabycityname")) . '";
                    jQuery("#city").tokenInput(cityArray, {
                        theme: "wpjobportal",
                        preventDuplicates: true,
                        hintText: "' . esc_html(__('Type In A Search Term', 'wp-job-portal')) . '",
                        noResultsText: "' . esc_html(__('No Results', 'wp-job-portal')) . '",
                        searchingText: "' . esc_html(__('Searching', 'wp-job-portal')) . '"
                    });
                }
                jQuery(document).ready(function(){
                    getTokenInput();
                });
                jQuery(document).on("change", "#salarytype", function(){
                    var salarytype = jQuery(this).val();
                    if(salarytype == 1){ //negotiable
                        jQuery("#salaryfixed").hide();
                        jQuery("#salarymin").hide();
                        jQuery("#salarymax").hide();
                        jQuery("#salaryduration").hide();
                        jQuery(".wjportal-form-symbol").hide();
                    }else if(salarytype == 2){ //fixed
                        jQuery("#salaryfixed").show();
                        jQuery("#salarymin").hide();
                        jQuery("#salarymax").hide();
                        jQuery("#salaryduration").show();
                        jQuery(".wjportal-form-symbol").show();
                    }else if(salarytype == 3){ //range
                        jQuery("#salaryfixed").hide();
                        jQuery("#salarymin").show();
                        jQuery("#salarymax").show();
                        jQuery("#salaryduration").show();
                        jQuery(".wjportal-form-symbol").show();
                    }else{ //not selected
                        jQuery("#salaryfixed").hide();
                        jQuery("#salarymin").hide();
                        jQuery("#salarymax").hide();
                        jQuery("#salaryduration").hide();
                        jQuery(".wjportal-form-symbol").hide();
                    }
                });

                jQuery("#salarytype").change();
            ';
            wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
        return $wpjobportal_html;
    }

    function getMessagekey(){
        $wpjobportal_key = 'wpjobportalwidgets';if(wpjobportal::$_common->wpjp_isadmin()){$wpjobportal_key = 'admin_'.$wpjobportal_key;}return $wpjobportal_key;
    }

    function wpjobportalRenderJobsTemplate($wpjobportal_jobs, $wpjobportal_layout = 'list', $wpjobportal_num_of_columns = 1, $wpjobportal_show_title = true, $wpjobportal_show_company = true, $wpjobportal_show_location = true, $wpjobportal_show_jobtype = true, $wpjobportal_show_salary = true, $wpjobportal_show_stoppublishing = true, $wpjobportal_show_careerlevel = true, $wpjobportal_show_posted = true, $wpjobportal_show_category = true, $wpjobportal_show_logo = true, $wpjobportal_logo_width = 80, $wpjobportal_logo_height = 80, $wpjobportal_labels_for_values = 1, $wpjobportal_field_order = array(), $elemntor_call = 0, $wpjobportal_show_view_single_job_button = false, $wpjobportal_show_view_all_jobs_button = 'no') {

        $wpjobportal_html = '';

        if(empty($wpjobportal_num_of_columns) || $wpjobportal_num_of_columns == 0){
            $wpjobportal_num_of_columns = 1;
        }
        $wpjobportal_layout_class = 'wpjobportal-layout-' . esc_attr($wpjobportal_layout);
        $column_class = 'wpjobportal-cols-' . intval($wpjobportal_num_of_columns);

        $wpjobportal_html .= '<div class="wpjobportal-job-widget-multi-style-wrapper ' . esc_attr($wpjobportal_layout_class . ' ' . $column_class) . '">';

        $wpjobportal_wpdir = wp_upload_dir();
        $wpjobportal_data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
        $wpjobportal_i = 0;

        $wpjobportal_pageid = wpjobportal::wpjobportal_getPageidForWidgets();
        if(is_array($wpjobportal_jobs)){
            foreach ($wpjobportal_jobs as $wpjobportal_job) {
                $wpjobportal_job_id = isset($wpjobportal_job->jobaliasid) ? $wpjobportal_job->jobaliasid : 0;
                $wpjobportal_company_id = isset($wpjobportal_job->companyid) ? $wpjobportal_job->companyid : 0;
                $wpjobportal_company_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme' => 'company', 'wpjobportallt' => 'viewcompany', 'wpjobportalid' => isset($wpjobportal_job->companyaliasid) ? $wpjobportal_job->companyaliasid : 0, 'wpjobportalpageid' => $wpjobportal_pageid));
                $wpjobportal_job_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme' => 'job', 'wpjobportallt' => 'viewjob', 'wpjobportalid' => $wpjobportal_job_id, 'wpjobportalpageid' => $wpjobportal_pageid));

                $wpjobportal_logo = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('employer');
                if (!empty($wpjobportal_job->logofilename)) {
                    $wpjobportal_logo = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/employer/comp_' . $wpjobportal_company_id . '/logo/' . $wpjobportal_job->logofilename;
                }

                if ($wpjobportal_i % $wpjobportal_num_of_columns === 0) {
                    if ($wpjobportal_i !== 0) $wpjobportal_html .= '</div>';
                    $wpjobportal_html .= '<div class="wpjobportal-job-row">';
                }
                $wpjobportal_i++;

                $wpjobportal_html .= $this->wpjobportalRenderSingleJob($wpjobportal_job, $wpjobportal_layout, $wpjobportal_show_title, $wpjobportal_show_company, $wpjobportal_show_location, $wpjobportal_show_jobtype, $wpjobportal_show_salary, $wpjobportal_show_stoppublishing, $wpjobportal_show_careerlevel, $wpjobportal_show_posted, $wpjobportal_show_category, $wpjobportal_show_logo, $wpjobportal_logo_width, $wpjobportal_logo_height, $wpjobportal_labels_for_values, $wpjobportal_field_order, $wpjobportal_company_url, $wpjobportal_job_url, $wpjobportal_logo,$elemntor_call, $wpjobportal_show_view_single_job_button);
            }
        }

        if ($wpjobportal_i != 0) {
            $wpjobportal_html .= '</div>';
        }
        // add view job button
        if( $wpjobportal_show_view_all_jobs_button == 'yes' ){
            $wpjobportal_pageid = wpjobportal::wpjobportal_getPageidForWidgets();
            $wpjobportal_html .= '
                    <a class="wpjobportal-job-view-all-jb-button" href="'. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobs', 'wpjobportalpageid'=>$wpjobportal_pageid))).'">
                        '. esc_html(__("View All Jobs", 'wp-job-portal')).'
                    </a>
            ';
        }
        $wpjobportal_html .= '</div>';
        return $wpjobportal_html;
    }

    function wpjobportalRenderSingleJob($wpjobportal_job, $wpjobportal_layout, $wpjobportal_show_title, $wpjobportal_show_company, $wpjobportal_show_location, $wpjobportal_show_jobtype, $wpjobportal_show_salary, $wpjobportal_show_stoppublishing, $wpjobportal_show_careerlevel, $wpjobportal_show_posted, $wpjobportal_show_category, $wpjobportal_show_logo, $wpjobportal_logo_width, $wpjobportal_logo_height, $wpjobportal_labels_for_values, $wpjobportal_field_order, $wpjobportal_company_url, $wpjobportal_job_url, $wpjobportal_logo,$elemntor_call, $wpjobportal_show_view_single_job_button = false) {
        $wpjobportal_html = '<div class="wpjobportal-job-box wpjobportal-floatbox">';

        // Company Logo
        if ($wpjobportal_show_logo) {
            $wpjobportal_html .= '<div class="wpjobportal-job-logo">';
            $wpjobportal_html .= '<a href="' . esc_url($wpjobportal_company_url) . '">';
            $wpjobportal_html .= '<img src="' . esc_url($wpjobportal_logo) . '" alt="' . esc_attr(isset($wpjobportal_job->companyname) ? $wpjobportal_job->companyname : '') . '" width="' . esc_attr($wpjobportal_logo_width) . '" height="' . esc_attr($wpjobportal_logo_height) . '">';
            $wpjobportal_html .= '</a></div>';
        }

        // Job Details
        $wpjobportal_html .= '<div class="wpjobportal-job-details">';

            $wpjobportal_html .= '<div class="wpjobportal-job-company">';
            if ($wpjobportal_show_company && !empty($wpjobportal_job->companyname)) {
                $wpjobportal_html .= '<a href="' . esc_url($wpjobportal_company_url) . '">' . esc_html($wpjobportal_job->companyname) . '</a>';
            }
            if ($wpjobportal_show_posted && !empty($wpjobportal_job->created)) {
                $wpjobportal_html .= '&nbsp; - &nbsp; <span class="wpjobportal-job-company-posted-date">';
                $wpjobportal_html .= human_time_diff(strtotime($wpjobportal_job->created), strtotime(date_i18n("Y-m-d H:i:s"))) . ' ' . esc_html(__("Ago", 'wp-job-portal'));
                $wpjobportal_html .= '</span>';
            }
            $wpjobportal_html .= '</div>';

        if ($wpjobportal_show_title && !empty($wpjobportal_job->title)) {
            $wpjobportal_html .= '<div class="wpjobportal-job-title">';
            $wpjobportal_html .= '<a href="' . esc_url($wpjobportal_job_url) . '">' . esc_html($wpjobportal_job->title) . '</a>';

            // $wpjobportal_html .= '<span class="wpjobportal-job-widget-no-of-hits" title="views" >';
            // $wpjobportal_html .= '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
            //                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            //                       <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
            //                       <circle cx="12" cy="12" r="3"/>
            //                     </svg>';
            // $wpjobportal_html .= esc_html($wpjobportal_job->hits);
            // $wpjobportal_html .= '</span>';

            $wpjobportal_html .= '</div>';
        }


        // Meta Info Grouped
        $meta_class = ($wpjobportal_layout === 'list') ? 'wpjobportal-job-meta-row' : 'wpjobportal-job-meta-col';
        $wpjobportal_html .= '<div class="' . esc_attr($meta_class) . '">';

        // Render fields in specified order

        if($elemntor_call == 0){
            $wpjobportal_field_order = array();
            $wpjobportal_field_order[] = 'salary';
            $wpjobportal_field_order[] = 'location';
            $wpjobportal_field_order[] = 'job_type';
            $wpjobportal_field_order[] = 'job_category';
            $wpjobportal_field_order[] = 'careerlevel';
            $wpjobportal_field_order[] = 'posted';
            $wpjobportal_field_order[] = 'apply_before';
        }

        //$wpjobportal_labels_for_values = (int) $wpjobportal_job->jobid / 2;

        foreach ($wpjobportal_field_order as $wpjobportal_field_key) {
            $wpjobportal_html .= $this->wpjobportalRenderJobField($wpjobportal_job, $wpjobportal_field_key, $wpjobportal_labels_for_values, $wpjobportal_show_location, $wpjobportal_show_jobtype, $wpjobportal_show_salary, $wpjobportal_show_stoppublishing, $wpjobportal_show_careerlevel, $wpjobportal_show_posted, $wpjobportal_show_category,$wpjobportal_layout);
        }

        $wpjobportal_html .= '</div>'; // .wpjobportal-job-meta-*
        $wpjobportal_html .= '</div>'; // .wpjobportal-job-details
        $wpjobportal_html .= '<div class="wpjobportal-job-details-right" >'; // .wpjobportal-job-details
        $wpjobportal_html .= '<div class="wpjobportal-job-details-right-location-salary-wrap">';
            if ($wpjobportal_show_salary && !empty($wpjobportal_job->salarytype)) {
                $wpjobportal_salary = wpjobportal::$_common->getSalaryRangeView($wpjobportal_job->salarytype, $wpjobportal_job->salarymin, $wpjobportal_job->salarymax, isset($wpjobportal_job->currency) ? $wpjobportal_job->currency : '');
                if (isset($wpjobportal_job->salarytype) && ($wpjobportal_job->salarytype == 3 || $wpjobportal_job->salarytype == 2)) {
                    $wpjobportal_salary .= ' / ' . esc_html(wpjobportal::wpjobportal_getVariableValue ($wpjobportal_job->srangetypetitle));
                }

                $wpjobportal_html .= '<div class="wpjobportal-job-details-right-salary">';
                    $wpjobportal_html .= $wpjobportal_salary;
                $wpjobportal_html .= '</div>';
            }
            if ($wpjobportal_show_location && !empty($wpjobportal_job->location)) {
                $wpjobportal_html .= '<div class="wpjobportal-job-details-right-location">';
                    $wpjobportal_html .= $wpjobportal_job->location;;
                $wpjobportal_html .= '</div>';
            }
        $wpjobportal_html .= '</div>';
        if($wpjobportal_show_view_single_job_button){
            $wpjobportal_pageid = wpjobportal::wpjobportal_getPageidForWidgets();
            $wpjobportal_html .= '
                <div class="wpjobportal-job-details-right-view-button-wrap" >
                    <a class="wpjobportal-job-details-right-view-button" href="'. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'viewjob', 'wpjobportalid'=>$wpjobportal_job->jobaliasid, 'wpjobportalpageid'=>$wpjobportal_pageid ))).'">
                        '. esc_html(__("Apply Now", 'wp-job-portal')).'
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <line x1="2" y1="12" x2="26" y2="12"/>
                          <polyline points="18 4 26 12 18 20"/>
                        </svg>

                    </a>
                </div>';

        }


        $wpjobportal_html .= '</div>'; // .wpjobportal-job-details-right

        // add view job button
        // if($wpjobportal_layout != 'list' && $wpjobportal_show_view_single_job_button == 'yes'){ // button only in grid and card styles
        //     $wpjobportal_pageid = wpjobportal::wpjobportal_getPageidForWidgets();
        //     $wpjobportal_html .= '
        //         <div class="wpjobportal-widget-entity-view-button-wrap" >
        //             <a class="wpjobportal-job-view-jb-button" href="'. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'viewjob', 'wpjobportalid'=>$wpjobportal_job->jobaliasid, 'wpjobportalpageid'=>$wpjobportal_pageid ))).'">
        //                 '. esc_html(__("View Job", 'wp-job-portal')).'
        //             </a>
        //         </div>
        //     ';
        // }

        $wpjobportal_html .= '</div>'; // .wpjobportal-job-box

        return $wpjobportal_html;
    }

    function wpjobportalRenderJobField($wpjobportal_job, $wpjobportal_field_key, $wpjobportal_labels_for_values, $wpjobportal_show_location, $wpjobportal_show_jobtype, $wpjobportal_show_salary, $wpjobportal_show_stoppublishing, $wpjobportal_show_careerlevel, $wpjobportal_show_posted, $wpjobportal_show_category, $wpjobportal_layout='list') {
        $wpjobportal_field_value = '';

        switch ($wpjobportal_field_key) {
            case 'salary':
                // if ($wpjobportal_show_salary && !empty($wpjobportal_job->salarytype)) {
                //     $wpjobportal_salary = wpjobportal::$_common->getSalaryRangeView($wpjobportal_job->salarytype, $wpjobportal_job->salarymin, $wpjobportal_job->salarymax, isset($wpjobportal_job->currency) ? $wpjobportal_job->currency : '');
                //     if (isset($wpjobportal_job->salarytype) && ($wpjobportal_job->salarytype == 3 || $wpjobportal_job->salarytype == 2)) {
                //         $wpjobportal_salary .= ' / ' . esc_html(wpjobportal::wpjobportal_getVariableValue ($wpjobportal_job->srangetypetitle));
                //     }
                //     $wpjobportal_field_value = $wpjobportal_salary;
                // }
                break;

            case 'location':
                // if ($wpjobportal_show_location && !empty($wpjobportal_job->location)) {
                //     $wpjobportal_field_value = $wpjobportal_job->location;
                // }
                break;

            case 'job_type':
                if ($wpjobportal_show_jobtype && !empty($wpjobportal_job->jobtypetitle)) {
                    //if($wpjobportal_layout != 'grid'){
                        $wpjobportal_field_value = $wpjobportal_job->jobtypetitle;
                        $wpjobportal_job_type_color = $wpjobportal_job->jobtypecolor;
                    // }else{
                    //     $wpjobportal_field_value = '
                    //     <span class="wjportal-job-type" style="background:'. esc_attr($wpjobportal_job->jobtypecolor).'">
                    //         '. esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->jobtypetitle)).'
                    //     </span>';
                    // }
                }
                break;

            case 'job_category':
                if ($wpjobportal_show_category && !empty($wpjobportal_job->cat_title)) {
                    $wpjobportal_field_value = $wpjobportal_job->cat_title;
                }
                break;

            case 'careerlevel':
                if ($wpjobportal_show_careerlevel && !empty($wpjobportal_job->careerleveltitle)) {
                    $wpjobportal_field_value = $wpjobportal_job->careerleveltitle;
                }
                break;

            case 'posted':
                // if ($wpjobportal_show_posted && !empty($wpjobportal_job->created)) {
                //     $wpjobportal_field_value = human_time_diff(strtotime($wpjobportal_job->created), strtotime(date_i18n("Y-m-d H:i:s"))) . ' ' . esc_html(__("Ago", 'wp-job-portal'));
                // }
                break;

            case 'apply_before':
                if ($wpjobportal_show_stoppublishing && !empty($wpjobportal_job->stoppublishing)) {
                    $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
                    $wpjobportal_field_value = date_i18n($wpjobportal_dateformat, strtotime($wpjobportal_job->stoppublishing));
                }
                break;
        }

        if (empty($wpjobportal_field_value)) {
            return '';
        }
        $color_css = '';
        if(!empty($wpjobportal_job_type_color)){
            $color_css = ' style="background:'. esc_attr($wpjobportal_job_type_color).';color:#fff;" ';
        }

        $wpjobportal_html = '<div class="wpjobportal-job-widget-detail-field-data wpjobportal-job-' . esc_attr($wpjobportal_field_key) . '"  '.$color_css.'  >' .
                $this->wpjobportalRenderFieldLabel($wpjobportal_field_key, $wpjobportal_labels_for_values) .
                '<span class="wpjobportal-job-widget-detail-field-data-value" >'.
                    wp_kses($wpjobportal_field_value,WPJOBPORTAL_ALLOWED_TAGS) . '
                </span>
            </div>';

        return $wpjobportal_html;
    }

    function wpjobportalRenderFieldLabel($wpjobportal_field_key, $wpjobportal_labels_for_values) {
        $wpjobportal_icons = array(
            'salary' => 'fa-money',
            'location' => 'fa-globe',
            'jobtype' => 'fa-briefcase',
            'job_type' => 'fa-briefcase',
            'job_category' => 'fa-folder',
            'posted' => 'fa-clock-o',
            'careerlevel' => 'fa-level-up',
            'stoppublishing' => 'fa-calendar',
            'apply_before' => 'fa-calendar',
        );

        if($wpjobportal_labels_for_values == 1){ // use text
            $wpjobportal_label = ucwords(str_replace('_', ' ', $wpjobportal_field_key));
            return esc_html($wpjobportal_label) . ': ';
        }
        if($wpjobportal_labels_for_values == 2){ // use icons
            if (isset($wpjobportal_icons[$wpjobportal_field_key])) {
                return '<i class="fa ' . esc_attr($wpjobportal_icons[$wpjobportal_field_key]) . '"></i> ';
            }
        }

        return '';
    }

    function wpjobportalRenderResumesWidgets($wpjobportal_resumes, $wpjobportal_layout = 'list', $wpjobportal_num_of_columns = 1, $wpjobportal_show_title = true, $wpjobportal_show_photo = true, $wpjobportal_show_name = true, $wpjobportal_show_category = true, $wpjobportal_show_jobtype = true, $wpjobportal_show_experience = true, $wpjobportal_show_available = true, $wpjobportal_show_gender = true, $wpjobportal_show_nationality = true, $wpjobportal_show_location = true, $wpjobportal_show_posted = true, $wpjobportal_photo_width = 80, $wpjobportal_photo_height = 80, $wpjobportal_labels_for_values = 1, $wpjobportal_field_order = array(), $elemntor_call = 0, $wpjobportal_show_all_resumes_button = 'yes', $wpjobportal_show_view_resume_button = 'no') {

        $wpjobportal_html = '';

        if(empty($wpjobportal_num_of_columns) || $wpjobportal_num_of_columns == 0){
            $wpjobportal_num_of_columns = 1;
        }

        $wpjobportal_layout_class = 'wpjobportal-layout-' . esc_attr($wpjobportal_layout);
        $column_class = 'wpjobportal-cols-' . intval($wpjobportal_num_of_columns);

        $wpjobportal_html .= '<div class="wpjobportal-resume-widget-multi-style-wrapper ' . esc_attr($wpjobportal_layout_class . ' ' . $column_class) . '">';

        $wpjobportal_wpdir = wp_upload_dir();
        $wpjobportal_data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
        $wpjobportal_i = 0;

        $wpjobportal_pageid = wpjobportal::wpjobportal_getPageidForWidgets();
        if(!empty($wpjobportal_resumes)){
            foreach ($wpjobportal_resumes as $wpjobportal_resume) {
                $wpjobportal_resume_id = isset($wpjobportal_resume->resumealiasid) ? $wpjobportal_resume->resumealiasid : 0;
                $wpjobportal_resume_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme' => 'resume', 'wpjobportallt' => 'viewresume', 'wpjobportalid' => $wpjobportal_resume_id, 'wpjobportalpageid' => $wpjobportal_pageid));

                $wpjobportal_photo = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('jobseeker');
                if (!empty($wpjobportal_resume->photo)) {
                    $wpjobportal_photo = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/jobseeker/resume_' . $wpjobportal_resume->resumeid . '/photo/' . $wpjobportal_resume->photo;
                }

                if ($wpjobportal_i % $wpjobportal_num_of_columns === 0) {
                    if ($wpjobportal_i !== 0) $wpjobportal_html .= '</div>';
                    $wpjobportal_html .= '<div class="wpjobportal-resume-row">';
                }
                $wpjobportal_i++;

                $wpjobportal_html .= $this->wpjobportalRenderSingleResume($wpjobportal_resume, $wpjobportal_layout, $wpjobportal_show_title, $wpjobportal_show_photo, $wpjobportal_show_name, $wpjobportal_show_category, $wpjobportal_show_jobtype, $wpjobportal_show_experience, $wpjobportal_show_available, $wpjobportal_show_gender, $wpjobportal_show_nationality, $wpjobportal_show_location, $wpjobportal_show_posted, $wpjobportal_photo_width, $wpjobportal_photo_height, $wpjobportal_labels_for_values, $wpjobportal_field_order, $wpjobportal_resume_url, $wpjobportal_photo, $elemntor_call, $wpjobportal_show_view_resume_button);
            }
        }

        if ($wpjobportal_i != 0) {
            $wpjobportal_html .= '</div>';
        }

        if( $wpjobportal_show_all_resumes_button == 'yes'){ // button only in grid and card styles
            $wpjobportal_pageid = wpjobportal::wpjobportal_getPageidForWidgets();
            $wpjobportal_html .= '
                    <a class="wpjobportal-resume-view-all-button" href="'. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'resumes','wpjobportalpageid'=>$wpjobportal_pageid ))).'">
                        '. esc_html(__("View All Resumes", 'wp-job-portal')).'
                    </a>
            ';
        }

        $wpjobportal_html .= '</div>';

        return $wpjobportal_html;
    }

    function wpjobportalRenderSingleResume($wpjobportal_resume, $wpjobportal_layout, $wpjobportal_show_title, $wpjobportal_show_photo, $wpjobportal_show_name, $wpjobportal_show_category, $wpjobportal_show_jobtype, $wpjobportal_show_experience, $wpjobportal_show_available, $wpjobportal_show_gender, $wpjobportal_show_nationality, $wpjobportal_show_location, $wpjobportal_show_posted, $wpjobportal_photo_width, $wpjobportal_photo_height, $wpjobportal_labels_for_values, $wpjobportal_field_order, $wpjobportal_resume_url, $wpjobportal_photo, $elemntor_call, $wpjobportal_show_view_resume_button = 'no') {
        $wpjobportal_html = '<div class="wpjobportal-resume-box wpjobportal-floatbox">';

        // Resume Photo
        if ($wpjobportal_show_photo) {
            $wpjobportal_html .= '<div class="wpjobportal-resume-photo">';
            $wpjobportal_html .= '<a href="' . esc_url($wpjobportal_resume_url) . '">';
            $wpjobportal_html .= '<img src="' . esc_url($wpjobportal_photo) . '" alt="' . esc_attr(isset($wpjobportal_resume->name) ? $wpjobportal_resume->name : '') . '" width="' . esc_attr($wpjobportal_photo_width) . '" height="' . esc_attr($wpjobportal_photo_height) . '">';
            $wpjobportal_html .= '</a></div>';
        }

        // Resume Details
        $wpjobportal_html .= '<div class="wpjobportal-resume-details">';

        $wpjobportal_html .= '<div class="wpjobportal-resume-details-title">';
            if ($wpjobportal_show_name && !empty($wpjobportal_resume->name)) {
                $wpjobportal_html .= '<div class="wpjobportal-resume-name">';
                $wpjobportal_html .= '<a href="' . esc_url($wpjobportal_resume_url) . '">' . esc_html($wpjobportal_resume->name) . '</a>';
                $wpjobportal_html .= '</div>';
            }

            if ($wpjobportal_show_title && !empty($wpjobportal_resume->applicationtitle)) {
                $wpjobportal_html .= '<div class="wpjobportal-resume-title">';
                $wpjobportal_html .= '<a href="' . esc_url($wpjobportal_resume_url) . '">' . esc_html($wpjobportal_resume->applicationtitle) . '</a>';
                $wpjobportal_html .= '</div>';
            }
        $wpjobportal_html .= '</div>'; // close

        // Meta Info Grouped
        $meta_class = ($wpjobportal_layout === 'list') ? 'wpjobportal-resume-meta-row' : 'wpjobportal-resume-meta-col';
        $wpjobportal_html .= '<div class="' . esc_attr($meta_class) . '">';

        // Render fields in specified order
        if($elemntor_call == 0){
            $wpjobportal_field_order = array();
            $wpjobportal_field_order[] = 'job_type';
            $wpjobportal_field_order[] = 'category';
            //$wpjobportal_field_order[] = 'jobtype';
            $wpjobportal_field_order[] = 'experience';
            $wpjobportal_field_order[] = 'nationality';
            $wpjobportal_field_order[] = 'gender';
            $wpjobportal_field_order[] = 'available';
            $wpjobportal_field_order[] = 'posted';
            $wpjobportal_field_order[] = 'location';
        }

        foreach ($wpjobportal_field_order as $wpjobportal_field_key) {
            $wpjobportal_html .= $this->wpjobportalRenderResumeFieldsData($wpjobportal_resume, $wpjobportal_field_key, $wpjobportal_labels_for_values, $wpjobportal_show_category, $wpjobportal_show_jobtype, $wpjobportal_show_experience, $wpjobportal_show_available, $wpjobportal_show_gender, $wpjobportal_show_nationality, $wpjobportal_show_location, $wpjobportal_show_posted,$wpjobportal_layout);
        }

        $wpjobportal_html .= '</div>'; // .wpjobportal-resume-meta-*
        $wpjobportal_html .= '</div>'; // .wpjobportal-resume-details
        // code interlopabilty for elementor and wordpress wiudget
        if($wpjobportal_show_view_resume_button == 'no' && $wpjobportal_show_view_resume_button !== true ){
            $wpjobportal_show_view_resume_button = false;
        }
        if(!empty($wpjobportal_show_view_resume_button)){
            $wpjobportal_pageid = wpjobportal::wpjobportal_getPageidForWidgets();
            $wpjobportal_html .= '
                <div class="wpjobportal-widget-entity-view-button-wrap" >
                    <a class="wpjobportal-resume-view-button" href="'. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'resumes', 'wpjobportalid'=>$wpjobportal_resume->resumealiasid, 'wpjobportalpageid'=>$wpjobportal_pageid ))).'">
                        '. esc_html(__("View Resume", 'wp-job-portal')).'
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <line x1="2" y1="12" x2="26" y2="12"/>
                          <polyline points="18 4 26 12 18 20"/>
                        </svg>
                    </a>
                </div>
            ';
        }
        $wpjobportal_html .= '</div>'; // .wpjobportal-resume-box

        return $wpjobportal_html;
    }

    function wpjobportalRenderResumeFieldsData($wpjobportal_resume, $wpjobportal_field_key, $wpjobportal_labels_for_values, $wpjobportal_show_category, $wpjobportal_show_jobtype, $wpjobportal_show_experience, $wpjobportal_show_available, $wpjobportal_show_gender, $wpjobportal_show_nationality, $wpjobportal_show_location, $wpjobportal_show_posted,$wpjobportal_layout) {
        $wpjobportal_field_value = '';

        switch ($wpjobportal_field_key) {
            case 'category':
                if ($wpjobportal_show_category && !empty($wpjobportal_resume->cat_title)) {
                    $wpjobportal_field_value = $wpjobportal_resume->cat_title;
                }
                break;

            case 'jobtype':
            case 'job_type':
                if ($wpjobportal_show_jobtype && !empty($wpjobportal_resume->jobtypetitle)) {
                    $wpjobportal_field_value = $wpjobportal_resume->jobtypetitle;
                    $wpjobportal_job_type_color = $wpjobportal_resume->jobtypecolor;
                    // if($wpjobportal_layout != 'grid'){
                    //     $wpjobportal_field_value = $wpjobportal_resume->jobtypetitle;
                    // }else{
                    //     $wpjobportal_field_value = '
                    //     <span class="wjportal-job-type" style="background:'. esc_attr($wpjobportal_resume->jobtypecolor).'">
                    //         '. esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_resume->jobtypetitle)).'
                    //     </span>';
                    // }
                }
                break;

            // case 'experience':
            //     if ($wpjobportal_show_experience && !empty($wpjobportal_resume->experiencetitle)) {
            //         $wpjobportal_field_value = $wpjobportal_resume->experiencetitle;
            //     }
            //     break;

            case 'location':
                if ($wpjobportal_show_location && !empty($wpjobportal_resume->location)) {
                    $wpjobportal_field_value = $wpjobportal_resume->location;
                }
                break;

            case 'nationality':
                if ($wpjobportal_show_nationality && !empty($wpjobportal_resume->nationalityname)) {
                    $wpjobportal_field_value = $wpjobportal_resume->nationalityname;
                }
                break;

            // case 'gender':
            //     if ($wpjobportal_show_gender && isset($wpjobportal_resume->gender)) {
            //         $wpjobportal_field_value = ($wpjobportal_resume->gender == 1) ? esc_html(__('Male', 'wp-job-portal')) : esc_html(__('Female', 'wp-job-portal'));
            //     }
            //     break;

            // case 'available':
            //     if ($wpjobportal_show_available && isset($wpjobportal_resume->available)) {
            //         $wpjobportal_field_value = ($wpjobportal_resume->available == 1) ? esc_html(__('Yes', 'wp-job-portal')) : esc_html(__('No', 'wp-job-portal'));
            //     }
            //     break;

            case 'posted':
                if ($wpjobportal_show_posted && !empty($wpjobportal_resume->created)) {
                    $wpjobportal_field_value = human_time_diff(strtotime($wpjobportal_resume->created), strtotime(date_i18n("Y-m-d H:i:s"))) . ' ' . esc_html(__("Ago", 'wp-job-portal'));
                }
                break;
        }

        if (empty($wpjobportal_field_value)) {
            return '';
        }

        $color_css = '';
        if(!empty($wpjobportal_job_type_color)){
            $color_css = ' style="background:'. esc_attr($wpjobportal_job_type_color).';color:#fff;" ';
        }

        $wpjobportal_html = '<div class="wpjobportal-resume-widget-detail-field-data wpjobportal-resume-' . esc_attr($wpjobportal_field_key) . '"  '.$color_css.' >' .
            $this->wpjobportalRenderResumeFieldLabel($wpjobportal_field_key, $wpjobportal_labels_for_values) .
            $wpjobportal_field_value . '</div>';

        return $wpjobportal_html;
    }

    function wpjobportalRenderResumeFieldLabel($wpjobportal_field_key, $wpjobportal_labels_for_values) {
        $wpjobportal_icons = array(
            'category' => 'fa-folder',
            'jobtype' => 'fa-briefcase',
            'job_type' => 'fa-briefcase',
            'experience' => 'fa-line-chart',
            'location' => 'fa-globe',
            'nationality' => 'fa-flag',
            'gender' => 'fa-venus-mars',
            'available' => 'fa-check-circle',
            'posted' => 'fa-calendar'
        );

        if($wpjobportal_labels_for_values == 1){ // use text
            $wpjobportal_label = ucwords(str_replace('_', ' ', $wpjobportal_field_key));
            return esc_html($wpjobportal_label) . ': ';
        }
        if($wpjobportal_labels_for_values == 2){ // use icons
            if (isset($wpjobportal_icons[$wpjobportal_field_key])) {
                return '<i class="fa ' . esc_attr($wpjobportal_icons[$wpjobportal_field_key]) . '"></i> ';
            }
        }

        return '';
    }

    // companies widget
    function wpjobportalRenderCompaniesTemplate($wpjobportal_companies, $wpjobportal_layout = 'list', $wpjobportal_num_of_columns = 1, $wpjobportal_show_comapny_name = true, $wpjobportal_show_category = true, $wpjobportal_show_location = true, $wpjobportal_show_posted = true, $wpjobportal_show_logo = true, $wpjobportal_logo_width = 80, $wpjobportal_logo_height = 80, $wpjobportal_labels_for_values = 1, $wpjobportal_field_order = array(), $wpjobportal_show_view_company_button = 'yes', $wpjobportal_show_no_of_jobs = true) {
        $wpjobportal_html = '';

        // Set default field order if not provided
        if(empty($wpjobportal_field_order)) {
            $wpjobportal_field_order = array('category', 'location', 'posted');
        }

        // Module wrapper and title
        $wpjobportal_html .= '<div class="wpjobportal-companies-widget-wrapper">';

        // if($wpjobportal_show_module_title && !empty($wpjobportal_module_title)) {
        //     $wpjobportal_html .= '<div class="wjportal-mod-heading">'.esc_html($wpjobportal_module_title).'</div>';
        // }


        // code interlopabilty for elementor and wordpress wiudget
        if($wpjobportal_show_view_company_button == 'no' && $wpjobportal_show_view_company_button !== true ){
            $wpjobportal_show_view_company_button = false;
        }

        // List layout
        if($wpjobportal_layout == 'list') {
            $wpjobportal_html .= $this->widgetRenderCompanyList($wpjobportal_companies, $wpjobportal_show_comapny_name, $wpjobportal_show_category, $wpjobportal_show_location, $wpjobportal_show_posted, $wpjobportal_show_logo, $wpjobportal_logo_width, $wpjobportal_logo_height, $wpjobportal_labels_for_values, $wpjobportal_field_order, $wpjobportal_show_view_company_button, $wpjobportal_show_no_of_jobs);
        }
        // Grid/Box layout
        else {
            $wpjobportal_html .= $this->widgetRenderCompanyGrid($wpjobportal_companies, $wpjobportal_num_of_columns, $wpjobportal_show_comapny_name, $wpjobportal_show_category, $wpjobportal_show_location, $wpjobportal_show_posted, $wpjobportal_show_logo, $wpjobportal_logo_width, $wpjobportal_logo_height, $wpjobportal_labels_for_values, $wpjobportal_field_order,$wpjobportal_show_view_company_button, $wpjobportal_show_no_of_jobs);
        }

        $wpjobportal_html .= '</div>'; // End wrapper

        return $wpjobportal_html;
    }

    function widgetRenderCompanyList($wpjobportal_companies, $wpjobportal_show_comapny_name, $wpjobportal_show_category, $wpjobportal_show_location, $wpjobportal_show_posted, $wpjobportal_show_logo, $wpjobportal_logo_width, $wpjobportal_logo_height, $wpjobportal_labels_for_values, $wpjobportal_field_order, $wpjobportal_show_view_company_button, $wpjobportal_show_no_of_jobs) {
        $wpjobportal_html = '';
        $wpjobportal_wpdir = wp_upload_dir();
        $wpjobportal_data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
        $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];

        $wpjobportal_pageid = wpjobportal::wpjobportal_getPageidForWidgets();

        // Company rows
        if(!empty($wpjobportal_companies)){
            foreach($wpjobportal_companies as $wpjobportal_company) {
                $wpjobportal_company_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme' => 'company','wpjobportallt' => 'viewcompany','wpjobportalid' => isset($wpjobportal_company->companyaliasid) ? $wpjobportal_company->companyaliasid : 0,'wpjobportalpageid' => $wpjobportal_pageid ));

                $wpjobportal_logo = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('employer');
                if (!empty($wpjobportal_company->logofilename)) {
                    $wpjobportal_logo = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/employer/comp_' . $wpjobportal_company->id . '/logo/' . $wpjobportal_company->logofilename;
                }

                $wpjobportal_html .= '<div class="wpjobportal-companies-list-row">';

                // Logo
                if($wpjobportal_show_logo) {
                    $wpjobportal_html .= '<div class="wpjobportal-companies-list-col-logo">';
                    $wpjobportal_html .= '<a href="'.esc_url($wpjobportal_company_url).'">';
                    $wpjobportal_html .= '<img src="'.esc_url($wpjobportal_logo).'" alt="'.esc_attr($wpjobportal_company->name).'" width="'.esc_attr($wpjobportal_logo_width).'" height="'.esc_attr($wpjobportal_logo_height).'">';
                    $wpjobportal_html .= '</a></div>';
                }

                // company name
                if($wpjobportal_show_comapny_name) {
                    $wpjobportal_html .= '<div class="wpjobportal-companies-list-col-title">';
                    $wpjobportal_html .= '<a href="'.esc_url($wpjobportal_company_url).'">'.esc_html($wpjobportal_company->name).'</a>';
                    $wpjobportal_html .= '</div>';
                }

                $wpjobportal_html .= '<div class="wpjobportal-companies-list-copmany-detail-wrap">';
                // Fields
                if(empty($wpjobportal_field_order)){
                    foreach($wpjobportal_field_order as $wpjobportal_field) {
                        $wpjobportal_field_value = '';
                        $wpjobportal_field_class = '';
                        $wpjobportal_field_label = '';

                        switch($wpjobportal_field) {
                            case 'category':
                                // if($wpjobportal_show_category && !empty($wpjobportal_company->cat_title)) {
                                //     $wpjobportal_field_value = $wpjobportal_company->cat_title;
                                //     $wpjobportal_field_class = 'category';
                                //     $wpjobportal_field_label = __('Category', 'wp-job-portal');
                                // }
                                break;
                            case 'location':
                                if($wpjobportal_show_location && !empty($wpjobportal_company->location)) {
                                    $wpjobportal_field_value = $wpjobportal_company->location;
                                    $wpjobportal_field_class = 'location';
                                    $wpjobportal_field_label = __('Location', 'wp-job-portal');
                                }
                                break;
                            case 'posted':
                                if($wpjobportal_show_posted && !empty($wpjobportal_company->created)) {
                                    $wpjobportal_field_value = date_i18n($wpjobportal_dateformat, strtotime($wpjobportal_company->created));
                                    $wpjobportal_field_class = 'posted';
                                    $wpjobportal_field_label = __('Posted', 'wp-job-portal');
                                }
                                break;
                        }

                        if(!empty($wpjobportal_field_value)) {
                           // $wpjobportal_html .= '<div class="wpjobportal-companies-list-col-'.esc_attr($wpjobportal_field_class).'">'.esc_html($wpjobportal_field_value).'</div>';
                            $wpjobportal_html .= $this->widgetRenderCompanyField($wpjobportal_field_value, $wpjobportal_field_label, $wpjobportal_labels_for_values, $wpjobportal_field);
                        }
                    }
                }
                // code interlopabilty for elementor and wordpress wiudget
                if($wpjobportal_show_no_of_jobs == 'no' && $wpjobportal_show_no_of_jobs !== true ){
                    $wpjobportal_show_no_of_jobs = false;
                }
                if(!empty($wpjobportal_show_no_of_jobs)){ //
                    $wpjobportal_html .= '<div class="wpjobportal-companies-list-copmany-noofjobs-wrap" >';// no of jobs
                        $wpjobportal_html .=  '<span class="noofjobsdot"></span>'.$wpjobportal_company->noofjobs;
                        if($wpjobportal_company->noofjobs > 0){
                            $wpjobportal_html .=  __('Jobs', 'wp-job-portal');
                        }else{
                            $wpjobportal_html .=  __('Job', 'wp-job-portal');
                        }

                    $wpjobportal_html .= '</div>';// no of jobs close
                }
                if(!empty($wpjobportal_show_view_company_button)){
                    $wpjobportal_html .= '<div class="wpjobportal-companies-list-copmany-detailview-link-wrap" >';// detail link wrap
                    $wpjobportal_html .= '<a class="wpjobportal-companies-list-copmany-detailview-link"  href="'.esc_url($wpjobportal_company_url).'">';
                    $wpjobportal_html .=    __('View Company', 'wp-job-portal').'
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 24" fill="none"
                                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                              <line x1="2" y1="12" x2="26" y2="12"/>
                                              <polyline points="18 4 26 12 18 20"/>
                                            </svg>
                                        ';

                    $wpjobportal_html .= '</a>';
                    $wpjobportal_html .= '</div>';// detail link wrap close
                }


                $wpjobportal_html .= '</div>';// details wrap close

                $wpjobportal_html .= '</div>'; // End row
            }
        }

        return $wpjobportal_html;
    }

    function widgetRenderCompanyGrid($wpjobportal_companies, $wpjobportal_num_of_columns, $wpjobportal_show_company_name, $wpjobportal_show_category, $wpjobportal_show_location, $wpjobportal_show_posted, $wpjobportal_show_logo, $wpjobportal_logo_width, $wpjobportal_logo_height, $wpjobportal_labels_for_values, $wpjobportal_field_order, $wpjobportal_show_view_company_button, $wpjobportal_show_no_of_jobs) {
        $wpjobportal_html = '';
        $wpjobportal_wpdir = wp_upload_dir();
        $wpjobportal_data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
        $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];

        $column_class = 'wpjobportal-cols-'.intval($wpjobportal_num_of_columns);
        $wpjobportal_html .= '<div class="wpjobportal-companies-grid-wrapper '.esc_attr($column_class).'">';
        $wpjobportal_count_company_wrp = 0;

        $wpjobportal_pageid = wpjobportal::wpjobportal_getPageidForWidgets();
        if(!empty($wpjobportal_companies)){
            foreach($wpjobportal_companies as $wpjobportal_company) {

                $wpjobportal_company_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme' => 'company','wpjobportallt' => 'viewcompany','wpjobportalid' => isset($wpjobportal_company->companyaliasid) ? $wpjobportal_company->companyaliasid : 0,'wpjobportalpageid' => $wpjobportal_pageid));
                $wpjobportal_logo = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('employer');
                if (!empty($wpjobportal_company->logofilename)) {
                    $wpjobportal_logo = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/employer/comp_' . $wpjobportal_company->id . '/logo/' . $wpjobportal_company->logofilename;
                }

                if ($wpjobportal_count_company_wrp % $wpjobportal_num_of_columns === 0) {
                    if ($wpjobportal_count_company_wrp !== 0) $wpjobportal_html .= '</div>';
                    $wpjobportal_html .= '<div class="wpjobportal-companies-widget-company-row">';
                }
                $wpjobportal_count_company_wrp++;

                $wpjobportal_html .= '<div class="wpjobportal-company-box">';



                // Logo
                if($wpjobportal_show_logo) {
                    // only show this if logo is visble
                    $wpjobportal_html .= '<div class="wpjobportal-company-box-top-decoration">';
                    $wpjobportal_html .= '</div>';

                    $wpjobportal_html .= '<div class="wpjobportal-companies-list-col-logo">';
                    $wpjobportal_html .= '<a href="'.esc_url($wpjobportal_company_url).'">';
                    $wpjobportal_html .= '<img src="'.esc_url($wpjobportal_logo).'" alt="'.esc_attr($wpjobportal_company->name).'" width="'.esc_attr($wpjobportal_logo_width).'" height="'.esc_attr($wpjobportal_logo_height).'">';
                    $wpjobportal_html .= '</a></div>';
                }

                // company
                if($wpjobportal_show_company_name) {
                    $wpjobportal_html .= '<div class="wpjobportal-companies-list-col-title">';
                    $wpjobportal_html .= '<a href="'.esc_url($wpjobportal_company_url).'">'.esc_html($wpjobportal_company->name).'</a>';
                    $wpjobportal_html .= '</div>';
                }

                $wpjobportal_html .= '<div class="wpjobportal-companies-list-copmany-detail-wrap">';
                // Fields
                foreach($wpjobportal_field_order as $wpjobportal_field) {
                    $wpjobportal_field_value = '';
                    $wpjobportal_field_class = '';
                    $wpjobportal_field_label = '';

                    switch($wpjobportal_field) {
                        case 'category':
                            // if($wpjobportal_show_category && !empty($wpjobportal_company->cat_title)) {
                            //     $wpjobportal_field_value = $wpjobportal_company->cat_title;
                            //     $wpjobportal_field_class = 'category';
                            //     $wpjobportal_field_label = __('Category', 'wp-job-portal');
                            // }
                            break;
                        case 'location':
                            if($wpjobportal_show_location && !empty($wpjobportal_company->location)) {
                                $wpjobportal_field_value = $wpjobportal_company->location;
                                $wpjobportal_field_class = 'location';
                                $wpjobportal_field_label = __('Location', 'wp-job-portal');
                            }
                            break;
                        case 'posted':
                            if($wpjobportal_show_posted && !empty($wpjobportal_company->created)) {
                                $wpjobportal_field_value = date_i18n($wpjobportal_dateformat, strtotime($wpjobportal_company->created));
                                $wpjobportal_field_class = 'posted';
                                $wpjobportal_field_label = __('Posted', 'wp-job-portal');
                            }
                            break;
                    }

                    if(!empty($wpjobportal_field_value)) {
                       // $wpjobportal_html .= '<div class="wpjobportal-companies-list-col-'.esc_attr($wpjobportal_field_class).'">'.esc_html($wpjobportal_field_value).'</div>';
                        $wpjobportal_html .= $this->widgetRenderCompanyField($wpjobportal_field_value, $wpjobportal_field_label, $wpjobportal_labels_for_values, $wpjobportal_field);
                    }
                }

                if(!empty($wpjobportal_show_no_of_jobs)){
                    $wpjobportal_html .= '<div class="wpjobportal-companies-list-copmany-noofjobs-wrap" >';// no of jobs
                        $wpjobportal_html .=  '<span class="noofjobsdot"></span>'.$wpjobportal_company->noofjobs;
                        if($wpjobportal_company->noofjobs > 0){
                            $wpjobportal_html .=  __('Jobs', 'wp-job-portal');
                        }else{
                            $wpjobportal_html .=  __('Job', 'wp-job-portal');
                        }

                    $wpjobportal_html .= '</div>';// no of jobs close
                }
                if(!empty($wpjobportal_show_view_company_button)){
                    $wpjobportal_html .= '<div class="wpjobportal-companies-list-copmany-detailview-link-wrap" >';// detail link wrap
                    $wpjobportal_html .= '<a class="wpjobportal-companies-list-copmany-detailview-link"  href="'.esc_url($wpjobportal_company_url).'">';
                    $wpjobportal_html .=    __('View Company', 'wp-job-portal').'
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 24" fill="none"
                                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                              <line x1="2" y1="12" x2="26" y2="12"/>
                                              <polyline points="18 4 26 12 18 20"/>
                                            </svg>
                                        ';

                    $wpjobportal_html .= '</a>';
                    $wpjobportal_html .= '</div>';// detail link wrap close
                }

                $wpjobportal_html .= '</div>';// details wrap close

                // old code
                /*

                // Logo
                if($wpjobportal_show_logo) {
                    $wpjobportal_html .= '<div class="wpjobportal-company-logo">';
                    $wpjobportal_html .= '<a href="'.esc_url($wpjobportal_company_url).'">';
                    $wpjobportal_html .= '<img src="'.esc_url($wpjobportal_logo).'" alt="'.esc_attr($wpjobportal_company->name).'" width="'.esc_attr($wpjobportal_logo_width).'" height="'.esc_attr($wpjobportal_logo_height).'">';
                    $wpjobportal_html .= '</a></div>';
                }

                // Title
                if($wpjobportal_show_company_name) {
                    $wpjobportal_html .= '<div class="wpjobportal-company-title">';
                    $wpjobportal_html .= '<a href="'.esc_url($wpjobportal_company_url).'">'.esc_html($wpjobportal_company->name).'</a>';
                    $wpjobportal_html .= '</div>';
                }

                // Fields
                $wpjobportal_html .= '<div class="wpjobportal-company-details">';
                foreach($wpjobportal_field_order as $wpjobportal_field) {
                    $wpjobportal_field_value = '';
                    $wpjobportal_field_label = '';

                    switch($wpjobportal_field) {
                        case 'category':
                            if($wpjobportal_show_category && !empty($wpjobportal_company->cat_title)) {
                                $wpjobportal_field_value = $wpjobportal_company->cat_title;
                                $wpjobportal_field_label = __('Category', 'wp-job-portal');
                            }
                            break;
                        case 'location':
                            if($wpjobportal_show_location && !empty($wpjobportal_company->location)) {
                                $wpjobportal_field_value = $wpjobportal_company->location;
                                $wpjobportal_field_label = __('Location', 'wp-job-portal');
                            }
                            break;
                        case 'posted':
                            if($wpjobportal_show_posted && !empty($wpjobportal_company->created)) {
                                $wpjobportal_field_value = date_i18n($wpjobportal_dateformat, strtotime($wpjobportal_company->created));
                                $wpjobportal_field_label = __('Posted', 'wp-job-portal');
                            }
                            break;
                    }

                    if(!empty($wpjobportal_field_value)) {
                        $wpjobportal_html .= $this->widgetRenderCompanyField($wpjobportal_field_value, $wpjobportal_field_label, $wpjobportal_labels_for_values, $wpjobportal_field);
                    }
                }
                $wpjobportal_html .= '</div>';

                // view company button
                if($wpjobportal_show_view_company_button == 'yes'){
                    $wpjobportal_pageid = wpjobportal::wpjobportal_getPageidForWidgets();
                    $wpjobportal_html .= '
                            <a class="wpjobportal-company-grid-view-company-button" href="'. esc_url($wpjobportal_company_url).'">
                                '. esc_html(__("View Company", 'wp-job-portal')).'
                            </a>
                    ';
                }

                */ /// old code close
                $wpjobportal_html .= '</div>'; // End company box
            }
        }
        // close row wrapper
        if ($wpjobportal_count_company_wrp !== 0) $wpjobportal_html .= '</div>';

        $wpjobportal_html .= '</div>'; // End grid wrapper

        return $wpjobportal_html;
    }

    function widgetRenderCompanyField($wpjobportal_value, $wpjobportal_label, $wpjobportal_labels_for_values, $wpjobportal_field_key) {
        $wpjobportal_icons = array(
            'category' => 'fa-folder',
            'location' => 'fa-globe',
            'posted' => 'fa-calendar'
        );

        $wpjobportal_html = '<div class="wpjobportal-company-field wpjobportal-company-'.esc_attr($wpjobportal_field_key).'">';

        if($wpjobportal_labels_for_values == 1) { // Text labels
            $wpjobportal_html .= '<span class="wpjobportal-company-field-label">'.esc_html($wpjobportal_label).':</span> ';
        } elseif($wpjobportal_labels_for_values == 2 && isset($wpjobportal_icons[$wpjobportal_field_key])) { // Icons
            $wpjobportal_html .= '<i class="fa '.esc_attr($wpjobportal_icons[$wpjobportal_field_key]).'"></i> ';
        }elseif($wpjobportal_labels_for_values == 3) { // no labels
            $wpjobportal_html .= '';
        }

        $wpjobportal_html .= '<span class="wpjobportal-company-field-value">'.esc_html($wpjobportal_value).'</span>';
        $wpjobportal_html .= '</div>';

        return $wpjobportal_html;
    }

}
?>
