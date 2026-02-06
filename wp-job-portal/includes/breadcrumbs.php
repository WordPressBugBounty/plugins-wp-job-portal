<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALbreadcrumbs {

    static function getBreadcrumbs() {
        $cur_location = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('cur_location');
        if ($cur_location != 1)
            return false;
        if (!is_admin()) {
            $cur_user =  wpjobportalincluder::getObjectClass('user');
            if($cur_user->isjobseeker()){
                $wpjobportal_url = "jobseeker";
            }elseif ($cur_user->isemployer()) {
                $wpjobportal_url = "employer";
            }elseif($cur_user->isguest()){
                $wpjobportal_url = 'jobseeker';
            }else{
                $wpjobportal_url = "employer";
            }
            $editid = WPJOBPORTALrequest::getVar('wpjobportalid');
            $wpjobportal_isnew = ($editid == null) ? true : false;
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme');
            $wpjobportal_layout = WPJOBPORTALrequest::getVar('wpjobportallt');
            $wpjobportal_array[] = array('link' => get_the_permalink(), 'text' => esc_html(__('Control Panel', 'wp-job-portal')));
            $wpjobportal_staticUrl = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>$wpjobportal_url, 'wpjobportallt'=>'controlpanel')), 'text' => esc_html(__('Dashboard', 'wp-job-portal')));
            if ($wpjobportal_layout == 'printresume' || $wpjobportal_layout == 'pdf')
                return false; // b/c we have print and pdf layouts
            if ($wpjobportal_module != null) {
                switch ($wpjobportal_module) {
                    case 'company':
                    case 'multicompany':
                       // Add default module link
                        if(in_array('multicompany', wpjobportal::$_active_addons)){
                                $wpjobportal_mod = "multicompany";

                            }else{
                                $wpjobportal_mod = "company";
                            }
                        switch ($wpjobportal_layout) {
                            case 'addcompany':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=> $wpjobportal_mod, 'wpjobportallt'=>'mycompanies')), 'text' => esc_html(__('My Companies', 'wp-job-portal')));

                                $wpjobportal_text = ($wpjobportal_isnew) ? esc_html(__('Add','wp-job-portal')) .' '. esc_html(__('Company', 'wp-job-portal')) : esc_html(__('Edit','wp-job-portal')) .' '. esc_html(__('Company', 'wp-job-portal'));
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>$wpjobportal_mod, 'wpjobportallt'=>'addcompany')), 'text' => $wpjobportal_text);
                                break;
                            case 'mycompanies':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>$wpjobportal_mod, 'wpjobportallt'=>'mycompanies')), 'text' => esc_html(__('My Companies', 'wp-job-portal')));
                                break;
                            case 'companies':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>$wpjobportal_mod, 'wpjobportallt'=>'companies')), 'text' => esc_html(__('Companies', 'wp-job-portal')));
                                break;
                            case 'featuredcompanies':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>$wpjobportal_mod, 'wpjobportallt'=>'featuredcompanies')), 'text' => esc_html(__('Featured Companies', 'wp-job-portal')));
                                break;
                            case 'viewcompany':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                if (WPJOBPORTALincluder::getObjectClass('user')->isemployer()) {
                                    $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=> $wpjobportal_mod, 'wpjobportallt'=>'mycompanies')), 'text' => esc_html(__('My Companies', 'wp-job-portal')));
                                }
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=> $wpjobportal_mod, 'wpjobportallt'=>'viewcompany')), 'text' => esc_html(__('Company Information', 'wp-job-portal')));
                                break;
                        }
                        break;
                    case 'departments':
                    case 'departments':
                        // Add default module link
                        switch ($wpjobportal_layout) {
                            case 'adddepartment':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'departments', 'wpjobportallt'=>'mydepartments')), 'text' => esc_html(__('My Departments', 'wp-job-portal')));
                                $wpjobportal_text = ($wpjobportal_isnew) ? esc_html(__('Add','wp-job-portal')) .' '. esc_html(__('Department', 'wp-job-portal')) : esc_html(__('Edit','wp-job-portal')) .' '. esc_html(__('Department', 'wp-job-portal'));
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'departments', 'wpjobportallt'=>'adddepartment')), 'text' => $wpjobportal_text);
                                break;
                            case 'mydepartments':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'departments', 'wpjobportallt'=>'mydepartments')), 'text' => esc_html(__('My Departments', 'wp-job-portal')));
                                break;
                            case 'viewdepartment':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'departments', 'wpjobportallt'=>'mydepartments')), 'text' => esc_html(__('My Departments', 'wp-job-portal')));
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'departments', 'wpjobportallt'=>'viewdepartment')), 'text' => esc_html(__('View Department', 'wp-job-portal')));
					 break;
                        }
                        break;
                    case 'coverletter':
                        // Add default module link
                        switch ($wpjobportal_layout) {
                            case 'addcoverletter':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'coverletter', 'wpjobportallt'=>'mycoverletters')), 'text' => esc_html(__('My Cover Letters', 'wp-job-portal')));
                                $wpjobportal_text = ($wpjobportal_isnew) ? esc_html(__('Add','wp-job-portal')) .' '. esc_html(__('Cover Letter', 'wp-job-portal')) : esc_html(__('Edit','wp-job-portal')) .' '. esc_html(__('Cover Letter', 'wp-job-portal'));
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'coverletter', 'wpjobportallt'=>'addcoverletter')), 'text' => $wpjobportal_text);
                                break;
                            case 'mycoverletters':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'coverletter', 'wpjobportallt'=>'mycoverletters')), 'text' => esc_html(__('My Cover Letters', 'wp-job-portal')));
                                break;
                            case 'viewcoverletter':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                if(WPJOBPORTALincluder::getObjectClass('user')->isjobseeker())
                                    $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'coverletter', 'wpjobportallt'=>'mycoverletters')), 'text' => esc_html(__('My Cover Letters', 'wp-job-portal')));

                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'coverletter', 'wpjobportallt'=>'viewcoverletter')), 'text' => esc_html(__('View Cover Letter', 'wp-job-portal')));
                           break;
                        }
                        break;
                    case 'job':
                        // Add default module link
                        switch ($wpjobportal_layout) {
                            case 'addjob':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'myjobs')), 'text' => esc_html(__('My Jobs', 'wp-job-portal')));
                                $wpjobportal_text = ($wpjobportal_isnew) ? esc_html(__('Add','wp-job-portal')) .' '. esc_html(__('Job', 'wp-job-portal')) : esc_html(__('Edit','wp-job-portal')) .' '. esc_html(__('Job', 'wp-job-portal'));
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'addjob')), 'text' => $wpjobportal_text);
                                break;
                            case 'myjobs':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                /*if(!WPJOBPORTALincluder::getObjectClass('user')->isguest()){*/
                                    $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'myjobs')), 'text' => esc_html(__('My Jobs', 'wp-job-portal')));
                                /*}*/
                                break;
                            case 'viewjob':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                if (WPJOBPORTALincluder::getObjectClass('user')->isemployer()) {
                                    $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'myjobs')), 'text' => esc_html(__('My Jobs', 'wp-job-portal')));
                                }
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'viewjob')), 'text' => esc_html(__('View Job', 'wp-job-portal')));
                                break;
                            case 'jobsbycategories':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobsbycategories')), 'text' => esc_html(__('Jobs By Categories', 'wp-job-portal')));
                                break;
                            case 'jobsbytypes':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobsbycategories')), 'text' => esc_html(__('Jobs By Types', 'wp-job-portal')));
                                break;
                            case 'jobsbycities':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobsbycities')), 'text' => esc_html(__('Jobs By Cities', 'wp-job-portal')));
                                break;
                            case 'jobs':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobsbycategories')), 'text' => esc_html(__('Jobs', 'wp-job-portal')));
                                break;
                            case 'newestjobs':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobsbycategories')), 'text' => esc_html(__('Newest Jobs', 'wp-job-portal')));
                                break;
                            case 'featuredjobs':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'featuredjobs')), 'text' => esc_html(__('Featured Jobs', 'wp-job-portal')));
                                break;
                        }
                            break;
                        case 'shortlist':
                            switch ($wpjobportal_layout) {
                                case 'shortlistedjobs':
                                    $wpjobportal_array[] = $wpjobportal_staticUrl;
                                    $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'shortlistedjobs')), 'text' => esc_html(__('Short Listed Jobs', 'wp-job-portal')));
                                    break;
                            }
                        break;
                        case 'visitorcanaddjob':
                            switch ($wpjobportal_layout) {
                                case 'visitoraddjob':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_text = ($wpjobportal_isnew) ? esc_html(__('Add','wp-job-portal')) .' '. esc_html(__('Job', 'wp-job-portal')) : esc_html(__('Edit','wp-job-portal')) .' '. esc_html(__('Job', 'wp-job-portal'));
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'addjob')), 'text' => $wpjobportal_text);
                                break;

                            }
                            break;
                        case 'message':
                        // Add default module link
                        switch ($wpjobportal_layout) {
                            case 'employermessages':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'message', 'wpjobportallt'=>'employermessages')), 'text' => esc_html(__('Messages', 'wp-job-portal')));
                                break;
                            case 'jobseekermessages':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'message', 'wpjobportallt'=>'jobseekermessages')), 'text' => esc_html(__('Job Seeker Messages', 'wp-job-portal')));
                                break;
                            case 'jobmessages':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'message', 'wpjobportallt'=>'employermessages')), 'text' => esc_html(__('Messages', 'wp-job-portal')));
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'message', 'wpjobportallt'=>'jobmessages')), 'text' => esc_html(__('Job Messages', 'wp-job-portal')));
                                break;
                            case 'sendmessage':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                if (wpjobportalincluder::getObjectClass('user')->isemployer()) {
                                    $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'message', 'wpjobportallt'=>'employermessages')), 'text' => esc_html(__('Messages', 'wp-job-portal')));
                                } else {
                                    $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'message', 'wpjobportallt'=>'jobseekermessages')), 'text' => esc_html(__('Messages', 'wp-job-portal')));
                                }
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'message', 'wpjobportallt'=>'sendmessage')), 'text' => esc_html(__('Send Message', 'wp-job-portal')));
                                break;
                        }
                        break;
                    case 'resumesearch':
                        // Add default module link
                        switch ($wpjobportal_layout) {
                            case 'resumesearch':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resumesearch', 'wpjobportallt'=>'resumesearch')), 'text' => esc_html(__('Resume Search', 'wp-job-portal')));
                                break;
                            case 'resumesavesearch':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => get_the_permalink(), 'text' => esc_html(__('Saved Searches', 'wp-job-portal')));
                                break;
                            case 'resumes':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => get_the_permalink(), 'text' => esc_html(__('Resume List', 'wp-job-portal')));
                                break;
                        }
                        break;
                case 'purchasehistory':
                        // Add default module link
                switch ($wpjobportal_layout) {
                        case 'employerpurchasehistory':
                            $wpjobportal_array[] = $wpjobportal_staticUrl;
                            $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'purchasehistory', 'wpjobportallt'=>'employerpurchasehistory')), 'text' => esc_html(__('Purchase History', 'wp-job-portal')));
                            break;
                        case 'jobseekerpurchasehistory':
                            $wpjobportal_array[] = $wpjobportal_staticUrl;
                            $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'purchasehistory', 'wpjobportallt'=>'jobseekerpurchasehistory')), 'text' => esc_html(__('Purchase History', 'wp-job-portal')));
                            break;
                        case 'mysubscriptions':
                            $wpjobportal_array[] = $wpjobportal_staticUrl;
                            $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'purchasehistory', 'wpjobportallt'=>'mysubscriptions')), 'text' => esc_html(__('My Subscription', 'wp-job-portal')));
                            break;
                        case 'purchasehistory':
                            $wpjobportal_array[] = $wpjobportal_staticUrl;
                            $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'purchasehistory', 'wpjobportallt'=>'purchasehistory')), 'text' => esc_html(__('My  Packages', 'wp-job-portal')));
                            break;
                        case 'paydepartment':
                            $wpjobportal_array[] = $wpjobportal_staticUrl;
                           $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'departments', 'wpjobportallt'=>'mydepartments')), 'text' => "My Department");
                                   $wpjobportal_array[] = array('text'=>'Select Payment');


                            break;
                        case 'payjobapply':
                            $wpjobportal_array[] = $wpjobportal_staticUrl;
                            $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobapply', 'wpjobportallt'=>'myappliedjobs')), 'text' => "My Applied Jobs");
                                   $wpjobportal_array[] = array('text'=>'Select Payment');
                            break;
                        case 'paycompany':
                        case 'payfeaturedcompany':
                            $wpjobportal_array[] = $wpjobportal_staticUrl;
                            $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'multicompany', 'wpjobportallt'=>'mycompanies')), 'text' => "My Company");
                                   $wpjobportal_array[] = array('text'=>'Select Payment');
                             break;

                        case 'payjob':
                        case 'payfeaturedjob':
                            $wpjobportal_array[] = $wpjobportal_staticUrl;
                            $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'myjobs')), 'text' => "My Job");
                                   $wpjobportal_array[] = array('text'=>'Select Payment');
                             break;
                         case 'payresumesearch':
                            $wpjobportal_array[] = $wpjobportal_staticUrl;
                            $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resumesearch', 'wpjobportallt'=>'resumesavesearch')), 'text' => "My Resume Search");
                                   $wpjobportal_array[] = array('text'=>'Select Payment');
                            break;
                        case 'payresume':
                        case 'payfeaturedresume':
                            $wpjobportal_array[] = $wpjobportal_staticUrl;
                            $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'multiresume', 'wpjobportallt'=>'myresumes')), 'text' => "My Resume ");
                                   $wpjobportal_array[] = array('text'=>'Select Payment');
                            break;
                    }
                break;
                case 'package':
                    switch ($wpjobportal_layout) {
                        case 'packages':
                            $wpjobportal_array[] = $wpjobportal_staticUrl;
                            $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'package', 'wpjobportallt'=>'packages')), 'text' => esc_html(__('Package', 'wp-job-portal')));
                            break;
                    }

                    break;
                case 'invoice':
                    switch ($wpjobportal_layout) {
                        case 'myinvoices':
                            $wpjobportal_array[] = $wpjobportal_staticUrl;
                            $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'invoice', 'wpjobportallt'=>'myinvoices')), 'text' => esc_html(__('My Invoices', 'wp-job-portal')));
                            break;
                    }

                    break;
                case 'folder':
                    // Add default module link
                    switch ($wpjobportal_layout) {
                        case 'addfolder':
                            $wpjobportal_array[] = $wpjobportal_staticUrl;
                            $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'folder', 'wpjobportallt'=>'myfolders')), 'text' => esc_html(__('My Folders', 'wp-job-portal')));
                            $wpjobportal_text = ($wpjobportal_isnew) ? esc_html(__('Add','wp-job-portal')) .' '. esc_html(__('Folder', 'wp-job-portal')) : esc_html(__('Edit','wp-job-portal')) .' '. esc_html(__('Folder', 'wp-job-portal'));
                            $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'folder', 'wpjobportallt'=>'addfolder')), 'text' => $wpjobportal_text);
                            break;
                        case 'myfolders':
                            $wpjobportal_array[] = $wpjobportal_staticUrl;
                            $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'folder', 'wpjobportallt'=>'myfolders')), 'text' => esc_html(__('My Folders', 'wp-job-portal')));
                            break;
                        case 'viewfolder':
                            $wpjobportal_array[] = $wpjobportal_staticUrl;
                            $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'folder', 'wpjobportallt'=>'myfolders')), 'text' => esc_html(__('My Folders', 'wp-job-portal')));
                            $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'folder', 'wpjobportallt'=>'viewfolder')), 'text' => esc_html(__('View Folder', 'wp-job-portal')));
                            break;
                    }
                    break;
                case 'folderresume':
                    // Add default module link
                    switch ($wpjobportal_layout) {
                        case 'folderresume':
                            $wpjobportal_array[] = $wpjobportal_staticUrl;
                            $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'folder', 'wpjobportallt'=>'myfolders')), 'text' => esc_html(__('My Folders', 'wp-job-portal')));
                            $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'folderresume', 'wpjobportallt'=>'folderresume')), 'text' => esc_html(__('Folder Resumes', 'wp-job-portal')));
                            break;
                    }
                    break;
                    case 'resume':
                    case 'multiresume':
                        if(in_array('multiresume', wpjobportal::$_active_addons)){
                            $wpjobportal_modresume = "multiresume";
                        }else{
                            $wpjobportal_modresume = "resume";
                        }
                        // Add default module link
                        switch ($wpjobportal_layout) {
                            case 'addresume':
                                $wpjobportal_text = ($wpjobportal_isnew) ? esc_html(__('Add','wp-job-portal')) .' '. esc_html(__('Resume', 'wp-job-portal')) : esc_html(__('Edit','wp-job-portal')) .' '. esc_html(__('Resume', 'wp-job-portal'));
                                    $wpjobportal_array[] = $wpjobportal_staticUrl;
                                if (!WPJOBPORTALincluder::getObjectClass('user')->isguest() && in_array('multiresume', wpjobportal::$_active_addons)) {
                                    $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'myresumes')), 'text' => esc_html(__('My Resumes', 'wp-job-portal')));
                                    $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'addresume')), 'text' => $wpjobportal_text );
                                } else {
                                    $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'addresume')), 'text' => $wpjobportal_text );
                                }
                                break;
                            case 'myresumes':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=> $wpjobportal_modresume, 'wpjobportallt'=>'myresumes')), 'text' => esc_html(__('My Resumes', 'wp-job-portal')));
                                break;
                            case 'resumes':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'resumebycategory')), 'text' => esc_html(__('Resumes', 'wp-job-portal')));
                                break;
                            case 'featuredresumes':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'featuredresumes')), 'text' => esc_html(__('Featured Resumes', 'wp-job-portal')));
                                break;
                            case 'resumebycategory':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'resumebycategory')), 'text' => esc_html(__('Resume By Categories', 'wp-job-portal')));
                                break;
                            case 'viewresume':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                if (WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()) {
                                    $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=> $wpjobportal_modresume, 'wpjobportallt'=>'myresumes')), 'text' => esc_html(__('My Resumes', 'wp-job-portal')));
                                }
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=> $wpjobportal_modresume, 'wpjobportallt'=>'viewresume')), 'text' => esc_html(__('View Resume', 'wp-job-portal')));
                                break;
                        }
                        break;
                    case 'jobapply':
                        // Add default module link
                        switch ($wpjobportal_layout) {
                            case 'myappliedjobs':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobapply', 'wpjobportallt'=>'myappliedjobs')), 'text' => esc_html(__('My Applied Jobs', 'wp-job-portal')));
                                break;
                            case 'jobappliedresume':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'myjobs')), 'text' => esc_html(__('My Jobs', 'wp-job-portal')));
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobapply', 'wpjobportallt'=>'jobappliedresume')), 'text' => esc_html(__('Job Applied Resume', 'wp-job-portal')));
                                break;
                        }
                        break;
                        case 'jobalert':
                        // Add default module link
                        switch ($wpjobportal_layout) {
                            case 'jobalert':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobalert', 'wpjobportallt'=>'jobalert')), 'text' => esc_html(__('Job Alert', 'wp-job-portal')));
                                break;
                        }
                        break;
                    case 'jobsearch':
                        // Add default module link
                        switch ($wpjobportal_layout) {
                            case 'jobsearch':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobsearch', 'wpjobportallt'=>'jobsearch')), 'text' => esc_html(__('Job Search', 'wp-job-portal')));
                                break;
                            case 'jobsavesearch':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobsearch', 'wpjobportallt'=>'jobsavesearch')), 'text' => esc_html(__('Saved Searches', 'wp-job-portal')));
                                break;
                        }
                        break;
                    case 'jobseeker':
                        // Add default module link
                        switch ($wpjobportal_layout) {
                            case 'controlpanel':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobseeker', 'wpjobportallt'=>'controlpanel')), 'text' => esc_html(__('Control Panel', 'wp-job-portal')));
                                break;
                        }
                        break;
                    case 'employer':
                        // Add default module link
                        switch ($wpjobportal_layout) {
                            case 'controlpanel':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'employer', 'wpjobportallt'=>'controlpanel')), 'text' => esc_html(__('Control Panel', 'wp-job-portal')));
                                break;
                        }
                        break;
                    case 'wpjobportal':
                        // Add default module link
                        switch ($wpjobportal_layout) {
                            case 'login':
                                $wpjobportal_defaultUrl = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'wpjobportal', 'wpjobportallt'=>'login'));
                                $wpjobportal_lrlink = WPJOBPORTALincluder::getJSModel('configuration')->getLoginRegisterRedirectLink($wpjobportal_defaultUrl,'login');
                                $wpjobportal_array[] = array('link' => $wpjobportal_lrlink, 'text' => esc_html(__('Log In', 'wp-job-portal')));
                                break;
                        }
                        break;
                    case 'user':
                        // Add default module link
                        switch ($wpjobportal_layout) {
                            case 'regemployer':
                                $wpjobportal_defaultUrl = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'user', 'wpjobportallt'=>'userregister'));
                                $wpjobportal_lrlink = WPJOBPORTALincluder::getJSModel('configuration')->getLoginRegisterRedirectLink($wpjobportal_defaultUrl,'register');
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => $wpjobportal_lrlink, 'text' => esc_html(__('Register', 'wp-job-portal')));
                                break;
                            case 'regjobseeker':
                                $wpjobportal_defaultUrl = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'user', 'wpjobportallt'=>'userregister'));
                                $wpjobportal_lrlink = WPJOBPORTALincluder::getJSModel('configuration')->getLoginRegisterRedirectLink($wpjobportal_defaultUrl,'register');
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => $wpjobportal_lrlink, 'text' => esc_html(__('Register', 'wp-job-portal')));
                                break;
                            case 'formprofile':
                                $wpjobportal_array[] = $wpjobportal_staticUrl;
                                $wpjobportal_array[] = array('link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'user', 'wpjobportallt'=>'userregister')), 'text' => esc_html(__('Edit Profile', 'wp-job-portal')));
                                break;
                        }
                        break;
                }
            }
        }

        if (isset($wpjobportal_array)) {
            $wpjobportal_count = count($wpjobportal_array);
            $wpjobportal_i = 0;
            echo '<div class="wjportal-breadcrumbs-wrp">';
            foreach ($wpjobportal_array AS $obj) {
                if ($wpjobportal_i == 0) {
                   // echo '<div class="wjportal-breadcrumbs-home"><a href="' . esc_url($obj['link']) . '"></a></div>';
                } else {
                    if ($wpjobportal_i == ($wpjobportal_count - 1)) {
                        echo '<div class="wjportal-breadcrumbs-links wjportal-breadcrumbs-lastlink">' . esc_html(wpjobportal::wpjobportal_getVariableValue($obj['text'])) . '</div>';
                    } else {
                        echo '<div class="wjportal-breadcrumbs-links wjportal-breadcrumbs-firstlinks"><a class="wjportal-breadcrumbs-link" href="' . esc_url($obj['link']) . '">' . esc_html(wpjobportal::wpjobportal_getVariableValue($obj['text'])) . '</a></div>';
                    }
                }
                $wpjobportal_i++;
            }
            echo '</div>';
        }
    }

}

$WPJOBPORTALbreadcrumbs = new WPJOBPORTALbreadcrumbs;
?>
