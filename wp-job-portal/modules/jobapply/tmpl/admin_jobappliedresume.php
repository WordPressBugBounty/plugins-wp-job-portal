<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
wp_enqueue_style('wpjobportal-wpjobportalrating', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/wpjobportalrating.css', array(), wpjobportal_asset_version('includes/css/wpjobportalrating.css'));
if (!WPJOBPORTALincluder::getTemplate('templates/admin/header',array('wpjobportal_module' => 'jobapply'))){
    return;
}
$wpjobportal_nationality = WPJOBPORTALincluder::getJSModel('country')->getCountriesForCombo();
$wpjobportal_job_type = WPJOBPORTALincluder::getJSModel('jobtype')->getJobTypeForCombo();
$wpjobportal_heighesteducation = WPJOBPORTALincluder::getJSModel('highesteducation')->getHighestEducationForCombo();
$wpjobportal_job_categories = WPJOBPORTALincluder::getJSModel('category')->getCategoriesForCombo();
$wpjobportal_gender = array(
    (object) array('id' => '', 'text' => esc_html(__('Search All', 'wp-job-portal'))),
    (object) array('id' => 1, 'text' => esc_html(__('Male', 'wp-job-portal'))),
    (object) array('id' => 2, 'text' => esc_html(__('Female', 'wp-job-portal'))));
?>
<?php
    // to handle the email candidate case
    $wpjobportal_jobid = WPJOBPORTALrequest::getVar('jobid','','');
    wp_register_script( 'wpjobportal-inline-handle', '', array(), WPJOBPORTAL_VERSION, false );
    wp_enqueue_script( 'wpjobportal-inline-handle' );

    $wpjobportal_inline_js_script = "
        // Tester report (Send Message / cover letter popups): the list block (.wjpl) has
        // container-type: inline-size, which turns the popups' position:fixed overlay into a
        // box the size of the list, so the popup was squeezed and its Send button cut off.
        // Moving the popups up to the admin wrapper gives fixed its full-screen meaning again.
        jQuery(function () {
            var wpjobportalPopupHost = jQuery('#wpjobportaladmin-wrapper');
            if (!wpjobportalPopupHost.length) { wpjobportalPopupHost = jQuery('body'); }
            jQuery('.wjpl div#popup-main-outer, .wjpl div#full_background').appendTo(wpjobportalPopupHost);
        });
        function sendMessageToCandidate(resumeid, jobseekerid, jobid, name, employerid) {
            jQuery('span#popup_title.message').html(name);
            jQuery('input#resumeid').val(resumeid);
            jQuery('input#jobseekerid').val(jobseekerid);
            jQuery('input#employerid').val(employerid);
            jQuery('input#jobid').val(jobid);
            jQuery('div#full_background').show();
            jQuery('div#popup-main-outer.sendmessage').show();
            jQuery('div#popup-main.sendmessage').slideDown('slow');
            jQuery('div#full_background').click(function () {
                closePopup();
            });
            jQuery('img#popup_cross').click(function () {
                closePopup();
            });
        }
        function sendMessage() {
            var jobseekerid = jQuery('input#jobseekerid').val();
            var employerid = jQuery('input#employerid').val();
            var resumeid = jQuery('input#resumeid').val();
            var jobid = jQuery('input#jobid').val();
            var subject = jQuery('input#subject').val();
            if (subject == '') {
                alert(\"". esc_html(__("Please fill the subject", 'wp-job-portal'))."\");
                return false;
            }
            is_tinyMCE_active = true;
            if (typeof (tinyMCE) != 'undefined') {
                if (tinyMCE.activeEditor == null || tinyMCE.activeEditor.isHidden() != false) {
                    is_tinyMCE_active = false;
                }
            }
            if (is_tinyMCE_active == true) {
                var message = tinyMCE.get('jobseekermessage').getContent();
            } else {
                var message = jQuery('textarea#jobseekermessage').val();
            }
            if (message == '') {
                alert(\"". esc_html(__("Please fill the message", 'wp-job-portal'))."\");
                return false;
            }
            jQuery.post(ajaxurl, {action: 'wpjobportal_ajax', wpjobportalme: 'message', task: 'sendmessageresume', subject: subject, message: message, resumeid: resumeid, uid: jobseekerid, jobid: jobid, isadmin:1, employerid:employerid, '_wpnonce':'". esc_attr(wp_create_nonce("send-message-resume"))."'}, function (data) {
                if (data) {
                    alert(\"". esc_html(__("Message sent", 'wp-job-portal'))."\");
                    jQuery('div#popup-main-outer').slideUp('slow',function(){
                        jQuery('div#full_background').hide();
                    })
                }else{
                    alert(\"". esc_html(__("Message not sent", 'wp-job-portal'))."\");
                }
            });
        }
        function tabaction(jobid, action) {
            jQuery('#jobid').val(jobid);
            jQuery('#tab_action').val(action);
            jQuery('#task').val('aappliedresumetabactions');
            jQuery('#wpjobportal-form').submit();
        }
        function tabsearch(jobid, searchtype, selected_tab) {
            var element = jQuery('#wpjobportal-applied-tabs-container .wpjobportal_appliedapplication_tab_selected');
            element.removeClass('wpjobportal_appliedapplication_tab_selected');
            jQuery('#'+selected_tab).parents('span').addClass('wpjobportal_appliedapplication_tab_selected');
            var searchhtml = '#wpjobportal_appliedresume_tab_search';
            jQuery('form.wpjobportal-adv-srch-filter-form').show();
            jQuery(searchhtml).slideToggle('slow');
        }
        function closetabsearch(src) {
            jQuery(src).slideUp('slow');
        }
        function actioncall(jobapplyid, jobid, resumeid, action) {
            if (action == 3) { // folder
                getfolders('resumeaction_' + jobapplyid, jobid, resumeid, jobapplyid);
            } else if (action == 4) { // comments
                getresumecomments('resumeaction_' + jobapplyid, jobapplyid);
            } else if (action == 5) { // email candidate
                mailtocandidate('resumeaction_' + jobapplyid, resumeid, jobapplyid);
            } else {
                var src = '#resumeactionmessage_' + jobapplyid;
                var htmlsrc = '#wpjobportal_appliedresume_data_action_message_' + jobapplyid;
                jQuery(src).html('Loading ...');
            }
        }
        function closeresumeactiondiv(src) {
            jQuery(src).slideUp('slow');
        }
        function setresumeid(resumeid, action) {
            jQuery('#resumeid').val(resumeid);
            jQuery('#action').val(jQuery('#' + action).val());
            jQuery('wpjobportal-form').submit();
        }
        function saveresumecomments(jobapplyid, resumeid) {
            var src = '#resumeactionmessage_' + jobapplyid;
            var htmlsrc = '#wpjobportal_appliedresume_data_action_message_' + jobapplyid;
            var clearhtml = '#resumeaction_' + jobapplyid;
            var comments = jQuery('#comments').val();
            jQuery(src).html('Loading ...');
            jQuery.post('index.php?option=com_wpjobportal&task=jobapply.saveresumecomments', {jobapplyid: jobapplyid, resumeid: resumeid, comments: comments}, function (data) {
                if (data) {
                    jQuery(src).html(data);
                    jQuery(clearhtml).html('');
                    jQuery(htmlsrc).slideDown('slow');
                    setTimeout(function () {
                        closeresumeactiondiv(htmlsrc)
                    }, 3000);
                }
            });
        }
        function mailtocandidate(src, resumeid, jobapplyid) {
            jQuery('#' + src).html('Loading ...');
            jQuery.post('index.php?option=com_wpjobportal&task=jobapply.mailtocandidate', {resumeid: resumeid, jobapplyid: jobapplyid}, function (data) {
                if (data) {
                    jQuery('#' + src).html(data).show();
                }
            });
        }

        function sendtocandidate(arr, jobapplyid) {
            var src = '#resumeactionmessage_' + jobapplyid;
            var htmlsrc = '#wpjobportal_appliedresume_data_action_message_' + jobapplyid;
            var clearhtml = '#resumeaction_' + jobapplyid;
            jQuery(src).html('Loading ...');
            jQuery.post('index.php?option=com_wpjobportal&task=jobapply.sendtocandidate', {val: JSON.stringify(arr)}, function (data) {
                if (data) {
                    jQuery(src).html(data);
                    jQuery(clearhtml).html('');
                    jQuery(htmlsrc).slideDown('slow');
                    setTimeout(function () {
                        closeresumeactiondiv(htmlsrc)
                    }, 3000);
                }
            });
        }
        function clsjobdetail(src) {
            jQuery('#' + src).html('');
        }
        function clsaddtofolder(src) {
            jQuery('#' + src).html('');
        }
        function echeck(str) {
            var at = '@';
            var dot = '.';
            var lat = str.indexOf(at);
            var lstr = str.length;
            var ldot = str.indexOf(dot);

            if (str.indexOf(at) == -1)
                return false;
            if (str.indexOf(at) == -1 || str.indexOf(at) == 0 || str.indexOf(at) == lstr)
                return false;
            if (str.indexOf(dot) == -1 || str.indexOf(dot) == 0 || str.indexOf(dot) == lstr)
                return false;
            if (str.indexOf(at, (lat + 1)) != -1)
                return false;
            if (str.substring(lat - 1, lat) == dot || str.substring(lat + 1, lat + 2) == dot)
                return false;
            if (str.indexOf(dot, (lat + 2)) == -1)
                return false;
            if (str.indexOf(' ') != -1)
                return false;
            return true;
        }";

        wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
        $wpjobportal_inline_js_script = "
        " . WPJOBPORTAL_Application_Status::status_change_js() . "
        function changeStatusOfResume(id, resumeid, aid, themecall, extra) {
            // W-1: Recruit Suite asks for the rejection reason in the card first.
            if (aid == 4 && !extra && window.wpjobportalAskRejection && wpjobportalAskRejection(id, jQuery('div#' + resumeid + ', div#wjportal-' + resumeid).first(), function (x) { changeStatusOfResume(id, resumeid, aid, themecall, x); })) {
                return;
            }
            var ajaxurl = \"". esc_url_raw(admin_url('admin-ajax.php')) ."\";
            jQuery.post(ajaxurl, jQuery.extend({action: 'wpjobportal_ajax', wpjobportalme: 'resumeaction', task: 'updateJobApplyResumeStatus', jobapplyid: id, actionid: aid, '_wpnonce':'". esc_attr(wp_create_nonce("update-job-apply-resume-status"))."'}, extra || {}), function (data) {
                if (data) {
                    var obj = jQuery.parseJSON(data);
                    if (obj.saved == 'save') {
                        jQuery('div#' + resumeid).html('<div id=\"notification-ok\"><label id=\"popup_message\"><img src=\"". esc_url(WPJOBPORTAL_PLUGIN_URL)."includes/images/approve.png\"/>' + obj.message + '</label></div>');
                    } else {
                        jQuery('div#' + resumeid).html('<div id=\"notification-not-ok\"><label id=\"popup_message\"><img src=\"". esc_url(WPJOBPORTAL_PLUGIN_URL)."includes/images/unpublish.png\"/>' + obj.message + '</label></div>');
                    }
                }
                // W-1: a confirmation that stays, naming the tab it moved to.
                var ok = false;
                try { ok = data && jQuery.parseJSON(data).saved == 'save'; } catch (e) {}
                if (ok) {
                    wpjobportalStatusChanged(jQuery.parseJSON(data), aid);
                }
            });
        }
        function addComments(jid, resumeid) {
            //Addons
            var ajaxurl = \"". esc_url_raw(admin_url('admin-ajax.php')) ."\";
            jQuery.post(ajaxurl, {action: 'wpjobportal_ajax', wpjobportalme: 'resumeaction', task: 'getResumeCommentSection', jobapplyid: jid, resumeid: resumeid, '_wpnonce':'". esc_attr(wp_create_nonce("get-resume-comment-section"))."'}, function (data) {
                if (data) {
                    jQuery('div.' + resumeid).html(data).show();
                }
            });
        }
        function closeSection() {
            jQuery('div#comments').html('').hide();
        }
        function getFolders(uid, resumeid, jobid) {
            var ajaxurl = \"". esc_url_raw(admin_url('admin-ajax.php')) ."\";
            /*Advance*/
            jQuery.post(ajaxurl, {action: 'wpjobportal_ajax', wpjobportalme: 'folder', task: 'getFolderSection', userid: uid, rid: resumeid, jid: jobid, '_wpnonce':'". esc_attr(wp_create_nonce("get-folder-section"))."'}, function (data) {
                if (data) {
                    jQuery('div.' + resumeid).html('<img id=\"close-section\" onclick=\"closeSection()\" src=\"". esc_url(WPJOBPORTAL_PLUGIN_URL)."includes/images/no.png\"/>' + data).show();
                }
            });
        }
        function saveToFolder(uid, resumeid, jobid) {
            var val = jQuery('#combobox').find('option:selected').val();
            var ajaxurl = \"". esc_url_raw(admin_url('admin-ajax.php')) ."\";
            jQuery.post(ajaxurl, {action: 'wpjobportal_ajax', wpjobportalme: 'folderresume', task: 'saveToFolderResume', userid: uid, rid: resumeid, jid: jobid, folid: val, '_wpnonce':'". esc_attr(wp_create_nonce("save-to-folder-resume"))."'}, function (data) {
                if (data) {
                    var obj = jQuery.parseJSON(data);
                    if (obj.saved == 'save') {
                        jQuery('div#' + resumeid).html('<div id=\"notification-ok\"><label id=\"popup_message\"><img src=\"". esc_url(WPJOBPORTAL_PLUGIN_URL)."includes/images/approve.png\"/>' + obj.message + '</label></div>');
                    } else {
                        jQuery('div#' + resumeid).html('<div id=\"notification-not-ok\"><label id=\"popup_message\"><img src=\"". esc_url(WPJOBPORTAL_PLUGIN_URL)."includes/images/unpublish.png\"/>' + obj.message + '</label></div>');
                    }
                }
            });
        }
        function saveComments(jobid, resumeid) {
            var ajaxurl = \"". esc_url_raw(admin_url('admin-ajax.php')) ."\";
            var comment = jQuery('textarea#comments').val();
            jQuery.post(ajaxurl, {action: 'wpjobportal_ajax', wpjobportalme: 'resumeaction', task: 'storeResumeComments', jobapplyid: jobid, commenttext: comment, '_wpnonce':'". esc_attr(wp_create_nonce("store-resume-comments"))."'}, function (data) {
                if (data) {
                    var obj = jQuery.parseJSON(data);
                    if (obj.saved == 'save') {
                        jQuery('div#' + resumeid).html('<div id=\"notification-ok\"><label id=\"popup_message\"><img src=\"". esc_url(WPJOBPORTAL_PLUGIN_URL)."includes/images/approve.png\"/>". esc_html(__('Note have been saved successfully','wp-job-portal'))."</label></div>');
                    } else {
                        jQuery('div#' + resumeid).html('<div id=\"notification-not-ok\"><label id=\"popup_message\"><img src=\"". esc_url(WPJOBPORTAL_PLUGIN_URL)."includes/images/unpublish.png\"/>". esc_html(__('Failed while performing action','wp-job-portal'))."</label></div>');
                    }
                }
            });
        }
        function sendEmail(resumeid) {
            var ajaxurl = \"". esc_url_raw(admin_url('admin-ajax.php')) ."\";
            var jid = jQuery('input#jobseeker').val();
            var subject = jQuery('input#e-subject').val();
            var sid = jQuery('input#sender').val();
            var test = true;
            var jobid = jQuery('div.'+resumeid).find('input#jobid').val();
            if(subject =='' && sid ==''){ // not to proceed for empty fields
                jQuery('div#' + resumeid).html('<div id=\"notification-not-ok\"><label id=\"popup_message\"><img src=\"". esc_url(WPJOBPORTAL_PLUGIN_URL)."includes/images/unpublish.png\"/>". esc_html(__('Please Fill out fields','wp-job-portal'))."</label></div>');
                event.preventDefault();
                return false;
            }
            if(sid.length != 0){
                var pattern = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                test = pattern.test(sid);
            }
            if (test == false) {
                jQuery('div#' + resumeid).html('<div id=\"notification-not-ok\"><label id=\"popup_message\"><img src=\"". esc_url(WPJOBPORTAL_PLUGIN_URL)."includes/images/unpublish.png\"/>". esc_html(__('sender email is not of correct format','wp-job-portal'))."</label></div>');
                event.preventDefault();
                return false;
            } else {
                var body = jQuery('textarea#email-body').val();
                jQuery.post(ajaxurl, {action: 'wpjobportal_ajax', wpjobportalme: 'jobapply', task: 'sendEmailToJobSeeker', jobseekerid: jid, emailsubject: subject, senderid: sid, mailbody: body, jobid: jobid, resumeid: resumeid,  '_wpnonce':'". esc_attr(wp_create_nonce("send-email-to-jobseeker"))."'}, function (data) {
                    if (data) {
                        jQuery('div#' + resumeid).html('<div id=\"notification-ok\"><label id=\"popup_message\"><img src=\"". esc_url(WPJOBPORTAL_PLUGIN_URL)."includes/images/approve.png\"/>". esc_html(__('Email has been sent','wp-job-portal'))."</label></div>');
                    }else{
                        jQuery('div#' + resumeid).html('<div id=\"notification-not-ok\"><label id=\"popup_message\"><img src=\"". esc_url(WPJOBPORTAL_PLUGIN_URL).">includes/images/unpublish.png\"/>". esc_html(__('Error sending email','wp-job-portal'))."</label></div>');
                    }
                });
            }
        }
        function resetFrom() {
            if(jQuery('#application_title').length > 0 && jQuery('#application_title').val() != ''){
                document.getElementById('application_title').value = '';
            } if(jQuery('#applicantname').length > 0 && jQuery('#applicantname').val() != ''){
                document.getElementById('applicantname').value = '';
            } if(jQuery('#experince').length > 0 && jQuery('#experince').val() != ''){
                document.getElementById('experince').value = '';
            } if(jQuery('#nationality').length > 0 && jQuery('#nationality').val() != ''){
                document.getElementById('nationality').value = '';
            } if(jQuery('#jobcategory').length > 0 && jQuery('#jobcategory').val() != ''){
                document.getElementById('jobcategory').value = '';
            } if(jQuery('#gender').length > 0 && jQuery('#gender').val() != ''){
                document.getElementById('gender').value = '';
            } if(jQuery('#jobtype').length > 0 && jQuery('#jobtype').val() != ''){
                document.getElementById('jobtype').value = '';
            } if(jQuery('#currency').length > 0 && jQuery('#currency').val() != ''){
                document.getElementById('currency').value = '';
            } if(jQuery('#jobsalaryrange').length > 0 && jQuery('#jobsalaryrange').val() != ''){
                document.getElementById('jobsalaryrange').value = '';
            } if(jQuery('#heighestfinisheducation').length > 0 && jQuery('#heighestfinisheducation').val() != ''){
                document.getElementById('heighestfinisheducation').value = '';
            }
            document.getElementById('wpjobportalform').submit();
        }
        function setRating(src,newrating){
            src = 'rating_'+src;
            setrating_jsadmin(src,newrating);
        }
        function setrating_jsadmin(src, newrating) { 
            var jobapplyid = jQuery('#jobapplyid').val();
            var ajaxurl = \"". esc_url_raw(admin_url('admin-ajax.php')) ."\";
                jQuery.post(ajaxurl, {action: 'wpjobportal_ajax', wpjobportalme: 'resumeaction', task: 'setResumeRatting', jobapplyid:jobapplyid, rate: newrating, '_wpnonce':'". esc_attr(wp_create_nonce("set-resume-ratting"))."'}, function (data) {
                    if (data) {
                        jQuery('#' + src).width(parseInt(newrating * 20) + '%');
                    }
                });        
        }
        function showPopupAndSetValues(name, title, id) {
            var desc = jQuery('input#cover-letter-text_' + id).val();
            jQuery('div#full_background').css('display', 'block');
            jQuery('div#popup-main.coverletter').css('display', 'block');
            jQuery('div#popup-main-outer.coverletter').css('display', 'block');
            jQuery('div#full_background').click(function () {
                closePopup();
            });
            jQuery('img#popup_cross').click(function () {
                closePopup();
            });
            jQuery('div#popup_main.coverletter').slideDown('slow');
            jQuery('span#popup_title.coverletter').html(name);
            jQuery('span#popup_coverletter_title.coverletter').html(title);
            jQuery('span#popup_coverletter_desc.coverletter').html(desc);
        }
        function closePopup() {
            jQuery('div#popup-main-outer').slideUp('slow');
            setTimeout(function () {
                jQuery('div#full_background').hide();
                jQuery('span#popup_title.coverletter').html('');
                jQuery('div#popup-main').css('display', 'none');
                jQuery('span#popup_coverletter_title.coverletter').html('');
                jQuery('span#popup_coverletter_desc.coverletter').html('');
            }, 700);
        }
        function getResumeDetails(resumeid, salary, exp, inisi, study, available) {
            var ajaxurl = \"". esc_url_raw(admin_url('admin-ajax.php')) ."\";
            jQuery.post(ajaxurl, {action: 'wpjobportal_ajax', wpjobportalme: 'jobapply', task: 'getResumeDetail', rid: resumeid, sal: salary, expe: exp, institue: inisi, stud: study, ava: available, '_wpnonce':'". esc_attr(wp_create_nonce("get-resume-detail"))."'}, function (data) {
                if (data) {
                    jQuery('div.' + resumeid).html(data).show();
                }
            });
        }
        function getEmailFields(emailid, resumeid) {
            var jobid = ".$wpjobportal_jobid.";// needed to get employer email address
            var ajaxurl = \"". esc_url_raw(admin_url('admin-ajax.php')) ."\";
            jQuery.post(ajaxurl, {action: 'wpjobportal_ajax', wpjobportalme: 'jobapply', task: 'getEmailFields', em: emailid,resumeid: resumeid, jobid:jobid, '_wpnonce':'". esc_attr(wp_create_nonce("get-email-fields"))."'}, function (data) {
                if (data) {
                    jQuery('div.' + resumeid).html(data).show();
                }
            });
        }
        function showCoverLetterData(cover_letter_id) {
            var cover_letter_title = jQuery('div#cover_letter_data_title_'+cover_letter_id).html();
            var cover_letter_desc = jQuery('div#cover_letter_data_desc_'+cover_letter_id).html();

            jQuery('span#popup_title.coverletter').html(cover_letter_title);
            jQuery('#popup_coverletter_desc.coverletter').html(cover_letter_desc);
            jQuery('div#full_background.coverletter').show();
            jQuery('div#popup-main-outer.coverletter').show();
            jQuery('div#popup-main.coverletter').slideDown('slow');
            jQuery('div#full_background').click(function () {
                closePopup();
            });
            jQuery('img#popup_cross').click(function () {
                closePopup();
            });
        }
    ";
    wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );

?>
<?php
/*
 * Candidates › Applications › one job's applicants (design 4, the Jobs list's pattern):
 * a strip with the job and its numbers, stage chips (All / Inbox / Shortlisted / Hired /
 * Rejected / Spam), filters and sort, then one row per applicant with View profile, a
 * "Move to" menu (the stage actions) and a "More" menu (every other action the add-ons
 * print: note, details, email, print, PDF, export, folder, message, recruitment details).
 */
// Opened without a job, or for one that was deleted
if (!isset(wpjobportal::$_data[0]['jobid'])) {
    echo '<div class="wrap"><div class="notice notice-error"><p>' . esc_html__('That record could not be found. It may have been deleted.', 'wp-job-portal') . '</p></div></div>';
    return;
}
WPJOBPORTAL_Admin_List_UI::enqueue();
wpjobportal::$wpjobportal_data['is_admin_layout_check'] = 1;
$wpjobportal_d0 = wpjobportal::$_data[0];
$wpjobportal_job = isset(wpjobportal::$_data[4]['jobinfo'][0]) ? wpjobportal::$_data[4]['jobinfo'][0] : null;
$wpjobportal_jid = (int) $wpjobportal_d0['jobid'];
$wpjobportal_ta = (int) wpjobportal::$_data['tab_action_value'];
$wpjobportal_sorton = (int) wpjobportal::$_data['sorton'];
$wpjobportal_sortby = (int) wpjobportal::$_data['sortby'];
$wpjobportal_rows = isset($wpjobportal_d0['data']) ? (array) $wpjobportal_d0['data'] : array();
$wpjobportal_total = (int) wpjobportal::$_data['total'];
$wpjobportal_fields = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldOrderingDataForListing(3);
$wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
// Stage => label, badge style. The numbers are jobapply.action_status.
$wpjobportal_stages = array(
    1 => array(__('Inbox', 'wp-job-portal'), 'info'),
    5 => array(__('Shortlisted', 'wp-job-portal'), 'pri'),
    3 => array(__('Hired', 'wp-job-portal'), 'ok'),
    4 => array(__('Rejected', 'wp-job-portal'), 'bad'),
    2 => array(__('Spam', 'wp-job-portal'), 'closed'),
);
$wpjobportal_counts = array(0 => (int) $wpjobportal_d0['applied'], 1 => (int) $wpjobportal_d0['inbox'], 5 => (int) $wpjobportal_d0['shortlisted'], 3 => (int) $wpjobportal_d0['hired'], 4 => (int) $wpjobportal_d0['reject'], 2 => (int) $wpjobportal_d0['spam']);
$wpjobportal_link = function ($args = array()) use ($wpjobportal_jid, $wpjobportal_ta, $wpjobportal_sorton, $wpjobportal_sortby) {
    $q = array_merge(array('page' => 'wpjobportal_jobapply', 'wpjobportallt' => 'jobappliedresume', 'jobid' => $wpjobportal_jid, 'ta' => $wpjobportal_ta, 'sorton' => $wpjobportal_sorton, 'sortby' => $wpjobportal_sortby), $args);
    return add_query_arg(array_map('rawurlencode', array_filter($q, function ($v) { return $v !== null; })), admin_url('admin.php'));
};
// Filters that are set (the search form keeps them in the saved search).
$wpjobportal_active_filters = 0;
foreach (array('title', 'name', 'nationality', 'jobcategory', 'gender', 'jobtype', 'salarytype', 'heighestfinisheducation') as $wpjobportal_k) {
    if (isset(wpjobportal::$_data['filter'][$wpjobportal_k]) && (string) wpjobportal::$_data['filter'][$wpjobportal_k] !== '') {
        $wpjobportal_active_filters++;
    }
}
// What the add-ons print, captured so it can be placed in the new layout.
$wpjobportal_capture = function ($hook) {
    $args = array_slice(func_get_args(), 1);
    ob_start();
    do_action_ref_array($hook, $args);
    return trim(ob_get_clean());
};
$wpjobportal_popups = $wpjobportal_capture('wpjobportal_addons_popup_main_outer_admin');
$wpjobportal_has_search = strpos($wpjobportal_popups, 'wpjobportal_appliedresume_tab_search') !== false;
$wpjobportal_export_all = $wpjobportal_capture('wpjobportal_addons_resume_top_buttons_actions_export_admin', $wpjobportal_jid);
// Splits an add-on's buttons into stage moves (changeStatusOfResume(id, resume, N…)) and the rest.
$wpjobportal_split = function ($html) {
    $moves = array();
    $other = array();
    if (preg_match_all('#<a\b[^>]*>.*?</a>#is', $html, $m)) {
        foreach ($m[0] as $a) {
            if (preg_match('#changeStatusOfResume\(\s*\d+\s*,\s*\d+\s*,\s*(\d+)#', $a, $s)) {
                $moves[(int) $s[1]] = $a;
            } else {
                // Two add-on hooks print the same Export link; list a real link once.
                if (preg_match('~href="([^"#][^"]*)"~', $a, $h)) {
                    if (isset($other['href:' . $h[1]])) {
                        continue;
                    }
                    $other['href:' . $h[1]] = $a;
                } else {
                    $other[] = $a;
                }
            }
        }
    }
    return array($moves, $other);
};
$wpjobportal_photo_base = '';
if (isset($wpjobportal_fields['photo'])) {
    $wpjobportal_up = wp_upload_dir();
    $wpjobportal_photo_base = $wpjobportal_up['baseurl'] . '/' . WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory') . '/data/jobseeker/resume_';
}
$wpjobportal_icon = function ($paths) {
    return '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths . '</svg>';
};
?>
<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper" class="wjpl-page">
    <div id="full_background" class="coverletter" style="display:none;"></div>
    <div id="popup-main-outer" class="coverletter" style="display:none;">
        <div id="popup-main" class="coverletter" style="display:none;">
            <span class="popup-top">
                <span id="popup_title" class="coverletter"></span>
                <img id="popup_cross" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/popup-close.png">
            </span>
            <div class="popup-content-area" id="popup-bottom-part">
                <span id="popup_coverletter_title" class="coverletter popup-content-title"></span>
                <span id="popup_coverletter_desc" class="coverletter popup-content-desc"> </span>
            </div>
        </div>
    </div>
    <div id="wpjobportaladmin-leftmenu">
        <?php WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <?php
        $wpjobportal_more = array(__('All applications', 'wp-job-portal') => admin_url('admin.php?page=wpjobportal_jobapply'));
        if ($wpjobportal_job) {
            $wpjobportal_more = array(
                __('Edit job', 'wp-job-portal') => admin_url('admin.php?page=wpjobportal_job&wpjobportallt=formjob&wpjobportalid=' . $wpjobportal_jid),
                __('View job on site', 'wp-job-portal') => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme' => 'job', 'wpjobportallt' => 'viewjob', 'wpjobportalid' => $wpjobportal_jid)),
            ) + $wpjobportal_more;
        }
        WPJOBPORTAL_Admin_List_UI::page_head(
            __('Applicants', 'wp-job-portal'),
            /* translators: %s: number of applicants */
            sprintf(_n('%s applicant', '%s applicants', $wpjobportal_counts[0], 'wp-job-portal'), number_format_i18n($wpjobportal_counts[0])),
            $wpjobportal_more
        );
        do_action('wpjobportal_addons_admin_search_jobapplied');
        ?>
        <div class="wjpl">
            <?php if ($wpjobportal_job) :
                $wpjobportal_posted = strtotime((string) $wpjobportal_job->created);
                ?>
                <section class="wjpl-card wjpl-apl-job" aria-label="<?php echo esc_attr(__('Job', 'wp-job-portal')); ?>">
                    <div class="wjpl-apl-job-main">
                        <a class="wjpl-apl-job-title" href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_job&wpjobportallt=formjob&wpjobportalid=' . $wpjobportal_jid)); ?>"><?php echo esc_html($wpjobportal_job->title); ?></a>
                        <?php if ((string) $wpjobportal_job->jobtypetitle !== '') : ?><span class="wjpl-badge wjpl-b-closed"><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->jobtypetitle)); ?></span><?php endif; ?>
                        <div class="wjpl-meta wjpl-meta-plain">
                            <?php
                            $wpjobportal_bits = array();
                            if ((string) $wpjobportal_job->companyname !== '') { $wpjobportal_bits[] = esc_html($wpjobportal_job->companyname); }
                            if ((string) $wpjobportal_job->cat_title !== '') { $wpjobportal_bits[] = esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->cat_title)); }
                            if (!empty($wpjobportal_job->location)) { $wpjobportal_bits[] = esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->location)); }
                            /* translators: %s: how long ago */
                            if ($wpjobportal_posted) { $wpjobportal_bits[] = esc_html(sprintf(__('posted %s ago', 'wp-job-portal'), human_time_diff($wpjobportal_posted))); }
                            echo implode(' · ', $wpjobportal_bits); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above
                            ?>
                        </div>
                    </div>
                    <dl class="wjpl-apl-kpis">
                        <div><dt><?php echo esc_html(__('Applied', 'wp-job-portal')); ?></dt><dd><?php echo esc_html(number_format_i18n($wpjobportal_counts[0])); ?></dd></div>
                        <div><dt><?php echo esc_html(__('Views', 'wp-job-portal')); ?></dt><dd><?php echo esc_html(number_format_i18n((int) $wpjobportal_d0['hits'])); ?></dd></div>
                        <div><dt><?php echo esc_html(__('Shortlisted', 'wp-job-portal')); ?></dt><dd><?php echo esc_html(number_format_i18n($wpjobportal_counts[5])); ?></dd></div>
                        <div><dt><?php echo esc_html(__('Hired', 'wp-job-portal')); ?></dt><dd><?php echo esc_html(number_format_i18n($wpjobportal_counts[3])); ?></dd></div>
                    </dl>
                </section>
            <?php endif; ?>

            <?php if (!$wpjobportal_job) : ?>
                <section class="wjpl-card"><?php WPJOBPORTAL_Admin_List_UI::empty_state(__('Job not found', 'wp-job-portal'), __('It may have been deleted, or the link is wrong.', 'wp-job-portal'), array(__('All applications', 'wp-job-portal') => admin_url('admin.php?page=wpjobportal_jobapply'))); ?></section>
            <?php else : ?>
            <section class="wjpl-card" aria-label="<?php echo esc_attr(__('Applicants', 'wp-job-portal')); ?>">
                <div class="wjpl-filters">
                    <div class="wjpl-tb1">
                        <?php
                        $wpjobportal_views = array(0 => array(__('All', 'wp-job-portal'), ''));
                        foreach ($wpjobportal_stages as $wpjobportal_n => $wpjobportal_st) {
                            $wpjobportal_views[$wpjobportal_n] = array($wpjobportal_st[0], '');
                        }
                        WPJOBPORTAL_Admin_List_UI::chips($wpjobportal_views, $wpjobportal_counts, $wpjobportal_ta, function ($key) use ($wpjobportal_link) {
                            return $wpjobportal_link(array('ta' => $key, 'pagenum' => null));
                        });
                        ?>
                        <label class="wjpl-sort"><?php echo esc_html(__('Sort', 'wp-job-portal')); ?>
                            <select class="wjpl-sel" onchange="window.location = this.value;" aria-label="<?php echo esc_attr(__('Sort by', 'wp-job-portal')); ?>">
                                <?php
                                $wpjobportal_sorts = array(
                                    '8|2' => __('Newest applications', 'wp-job-portal'),
                                    '8|1' => __('Oldest applications', 'wp-job-portal'),
                                    '9|2' => __('Highest rating', 'wp-job-portal'),
                                    '2|1' => __('Name A–Z', 'wp-job-portal'),
                                    '1|1' => __('Resume title A–Z', 'wp-job-portal'),
                                    '6|2' => __('Newest resumes', 'wp-job-portal'),
                                );
                                $wpjobportal_cur = $wpjobportal_sorton . '|' . ($wpjobportal_sortby === 1 ? 1 : 2);
                                foreach ($wpjobportal_sorts as $wpjobportal_v => $wpjobportal_l) {
                                    list($wpjobportal_on, $wpjobportal_by) = explode('|', $wpjobportal_v);
                                    echo '<option value="' . esc_url($wpjobportal_link(array('sorton' => $wpjobportal_on, 'sortby' => $wpjobportal_by, 'pagenum' => null))) . '"' . selected($wpjobportal_cur, $wpjobportal_v, false) . '>' . esc_html($wpjobportal_l) . '</option>';
                                }
                                ?>
                            </select>
                        </label>
                    </div>
                    <?php if ($wpjobportal_has_search || $wpjobportal_export_all !== '') : ?>
                        <div class="wjpl-apl-tools">
                            <?php if ($wpjobportal_has_search) : ?>
                                <button type="button" class="wjpl-btn wjpl-btn-sec" onclick="tabsearch(<?php echo esc_js($wpjobportal_jid); ?>, 'search', 'appliedresume_tabnarmalsearch');" aria-controls="wpjobportal_appliedresume_tab_search">
                                    <?php echo $wpjobportal_icon('<path d="M3 5h18M6 12h12M10 19h4"/>'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed SVG ?>
                                    <?php echo esc_html(__('Filters', 'wp-job-portal')); ?>
                                    <?php if ($wpjobportal_active_filters) : ?><span class="wjpl-c"><?php echo esc_html(number_format_i18n($wpjobportal_active_filters)); ?></span><?php endif; ?>
                                </button>
                            <?php endif; ?>
                            <?php if ($wpjobportal_export_all !== '') : ?><span class="wjpl-apl-export"><?php echo wp_kses($wpjobportal_export_all, WPJOBPORTAL_ALLOWED_TAGS); ?></span><?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="wjpl-apl-search"><?php echo $wpjobportal_popups; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the add-ons' own escaped markup ?></div>

                <?php if (!empty($wpjobportal_rows)) : ?>
                    <ul class="wjpl-apl-list">
                    <?php foreach ($wpjobportal_rows as $wpjobportal_r) :
                        if (isset($wpjobportal_r->socialprofile)) {
                            $wpjobportal_sp = json_decode($wpjobportal_r->socialprofile);
                            if ($wpjobportal_sp) {
                                $wpjobportal_r->first_name = isset($wpjobportal_r->first_name) ? $wpjobportal_r->first_name : $wpjobportal_sp->first_name;
                                $wpjobportal_r->last_name = isset($wpjobportal_r->last_name) ? $wpjobportal_r->last_name : $wpjobportal_sp->last_name;
                            }
                        }
                        $wpjobportal_name = trim((string) $wpjobportal_r->first_name . ' ' . (string) $wpjobportal_r->last_name);
                        $wpjobportal_stage = isset($wpjobportal_stages[(int) $wpjobportal_r->action_status]) ? (int) $wpjobportal_r->action_status : 1;
                        $wpjobportal_profile = admin_url('admin.php?page=wpjobportal_resume&wpjobportallt=viewresume&wpjobportalid=' . (int) $wpjobportal_r->appid);
                        $wpjobportal_initials = '';
                        foreach (preg_split('/\s+/', $wpjobportal_name, -1, PREG_SPLIT_NO_EMPTY) as $wpjobportal_w) {
                            $wpjobportal_initials .= function_exists('mb_substr') ? mb_substr($wpjobportal_w, 0, 1) : substr($wpjobportal_w, 0, 1);
                        }
                        $wpjobportal_initials = strtoupper(substr($wpjobportal_initials, 0, 2));
                        list($wpjobportal_moves, $wpjobportal_other) = $wpjobportal_split(
                            $wpjobportal_capture('wpjobportal_addons_resume_bottom_action_appliedresume', $wpjobportal_r, 'wjpl-apl-mi')
                            . $wpjobportal_capture('wpjobportal_addons_resume_bottom_action_appliedresume_exc', wpjobportal::$_data['jobid'], $wpjobportal_r)
                        );
                        $wpjobportal_applied = strtotime((string) $wpjobportal_r->apply_date);
                        ?>
                        <li class="wjpl-apl-row" data-jobapplyid="<?php echo esc_attr($wpjobportal_r->jobapplyid); ?>">
                            <div class="wjpl-apl-who">
                                <span class="wjpl-apl-av" aria-hidden="true">
                                    <?php if ($wpjobportal_photo_base !== '' && (string) $wpjobportal_r->photo !== '') : ?>
                                        <img src="<?php echo esc_url($wpjobportal_photo_base . (int) $wpjobportal_r->resumeid . '/photo/' . $wpjobportal_r->photo); ?>" alt="">
                                    <?php else : echo esc_html($wpjobportal_initials !== '' ? $wpjobportal_initials : '?'); endif; ?>
                                </span>
                                <div class="wjpl-apl-id">
                                    <div class="wjpl-apl-line1">
                                        <a class="wjpl-title" href="<?php echo esc_url($wpjobportal_profile); ?>"><?php echo esc_html($wpjobportal_name !== '' ? $wpjobportal_name : __('Unnamed applicant', 'wp-job-portal')); ?></a>
                                        <span class="wjpl-badge wjpl-b-<?php echo esc_attr($wpjobportal_stages[$wpjobportal_stage][1]); ?>"><?php echo esc_html($wpjobportal_stages[$wpjobportal_stage][0]); ?></span>
                                        <?php if ((int) $wpjobportal_r->quick_apply === 1) : ?><span class="wjpl-badge wjpl-b-closed"><?php echo esc_html(__('Quick apply', 'wp-job-portal')); ?></span><?php endif; ?>
                                    </div>
                                    <?php if ((int) $wpjobportal_r->quick_apply !== 1 && (string) $wpjobportal_r->applicationtitle !== '') : ?>
                                        <div class="wjpl-apl-role"><?php echo esc_html($wpjobportal_r->applicationtitle); ?></div>
                                    <?php endif; ?>
                                    <div class="wjpl-meta wjpl-meta-plain">
                                        <?php
                                        $wpjobportal_bits = array();
                                        if ($wpjobportal_applied) { /* translators: %s: how long ago */ $wpjobportal_bits[] = '<span title="' . esc_attr(date_i18n($wpjobportal_dateformat, $wpjobportal_applied)) . '">' . esc_html(sprintf(__('applied %s ago', 'wp-job-portal'), human_time_diff($wpjobportal_applied))) . '</span>'; }
                                        if ((int) $wpjobportal_r->quick_apply !== 1) {
                                            if (isset($wpjobportal_fields['address_city']) && !empty($wpjobportal_r->location)) { $wpjobportal_bits[] = esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_r->location)); }
                                            if (isset($wpjobportal_fields['job_category']) && (string) $wpjobportal_r->resume_category !== '') { $wpjobportal_bits[] = esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_r->resume_category)); }
                                            if (isset($wpjobportal_fields['jobtype']) && (string) $wpjobportal_r->jobtypetitle !== '') { $wpjobportal_bits[] = esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_r->jobtypetitle)); }
                                            if (isset($wpjobportal_fields['salaryfixed']) && (string) $wpjobportal_r->salary !== '') { /* translators: %s: expected salary */ $wpjobportal_bits[] = esc_html(sprintf(__('expects %s', 'wp-job-portal'), $wpjobportal_r->salary)); }
                                            if (in_array('advanceresumebuilder', wpjobportal::$_active_addons)) {
                                                $wpjobportal_exp = wpjobportal::wpjobportal_getVariableValue(wpjobportal::$_common->getTotalExp($wpjobportal_r->resumeid));
                                                if ((string) $wpjobportal_exp !== '') { /* translators: %s: total experience */ $wpjobportal_bits[] = esc_html(sprintf(__('%s experience', 'wp-job-portal'), $wpjobportal_exp)); }
                                            }
                                        }
                                        echo implode(' · ', $wpjobportal_bits); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above
                                        ?>
                                    </div>
                                    <?php if ((string) $wpjobportal_r->apply_message !== '') : ?>
                                        <p class="wjpl-apl-msg">“<?php echo esc_html(wp_html_excerpt((string) $wpjobportal_r->apply_message, 220, '…')); ?>”</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="wjpl-apl-side">
                                <?php
                                if (in_array('smartmatching', wpjobportal::$_active_addons)) {
                                    $wpjobportal_show_score = wpjobportal::$_config->getConfigurationByConfigName('show_match_score_resume_list');
                                    if (!empty($wpjobportal_show_score) && !empty($wpjobportal_r->match_score)) {
                                        do_action('wpjobportal_addon_resumelisting_match_score', (int) $wpjobportal_r->match_score, isset($wpjobportal_r->match_reasons) ? $wpjobportal_r->match_reasons : array());
                                    }
                                }
                                do_action('wpjobportal_addons_rating_resume_applied', $wpjobportal_r);
                                do_action('wpjobportal_addons_credit_applied_resume_ratting_admin', $wpjobportal_r);
                                if (in_array('coverletter', wpjobportal::$_active_addons)) {
                                    if (isset($wpjobportal_r->coverletterid) && is_numeric($wpjobportal_r->coverletterid) && $wpjobportal_r->coverletterid > 0) {
                                        $wpjobportal_cl = isset($wpjobportal_r->coverletterdata) && !empty($wpjobportal_r->coverletterdata) ? $wpjobportal_r->coverletterdata : null;
                                        echo '<div id="cover_letter_data_title_' . esc_attr($wpjobportal_r->coverletterid) . '" style="display:none;">' . esc_html($wpjobportal_cl ? $wpjobportal_cl->title : '') . '</div>';
                                        echo '<div id="cover_letter_data_desc_' . esc_attr($wpjobportal_r->coverletterid) . '" style="display:none;">' . wp_kses_post($wpjobportal_cl ? $wpjobportal_cl->description : '') . '</div>';
                                        echo '<a class="wjpl-apl-cl" href="#" onclick="showCoverLetterData(' . esc_attr($wpjobportal_r->coverletterid) . '); return false;">' . esc_html(__('View Cover Letter', 'wp-job-portal')) . '</a>';
                                    } else {
                                        echo '<span class="wjpl-small">' . esc_html(__('No Cover Letter', 'wp-job-portal')) . '</span>';
                                    }
                                }
                                ?>
                            </div>
                            <div class="wjpl-apl-acts">
                                <a class="wjpl-btn wjpl-btn-sec" href="<?php echo esc_url($wpjobportal_profile); ?>"><?php echo esc_html(__('View Profile', 'wp-job-portal')); ?></a>
                                <?php if ($wpjobportal_moves) : ?>
                                    <details class="wjpl-dd">
                                        <summary class="wjpl-btn wjpl-btn-sec"><?php echo esc_html(__('Move to', 'wp-job-portal')); ?> <?php echo $wpjobportal_icon('<path d="M6 9l6 6 6-6"/>'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed SVG ?></summary>
                                        <div class="wjpl-dd-menu" role="menu">
                                            <?php foreach ($wpjobportal_stages as $wpjobportal_n => $wpjobportal_st) :
                                                if (!isset($wpjobportal_moves[$wpjobportal_n])) { continue; }
                                                // The add-on's link, relabelled with the stage it moves to; the current stage is shown, not offered.
                                                $wpjobportal_a = preg_replace('#>(.*?)</a>$#s', '><span class="wjpl-dot wjpl-dot-' . esc_attr($wpjobportal_st[1]) . '"></span>' . esc_html($wpjobportal_st[0]) . '</a>', $wpjobportal_moves[$wpjobportal_n]);
                                                if ($wpjobportal_n === $wpjobportal_stage) {
                                                    echo '<span class="wjpl-dd-cur" role="menuitem" aria-disabled="true"><span class="wjpl-dot wjpl-dot-' . esc_attr($wpjobportal_st[1]) . '"></span>' . esc_html($wpjobportal_st[0]) . ' · ' . esc_html(__('current', 'wp-job-portal')) . '</span>';
                                                } else {
                                                    echo wp_kses($wpjobportal_a, WPJOBPORTAL_ALLOWED_TAGS);
                                                }
                                            endforeach; ?>
                                        </div>
                                    </details>
                                <?php endif; ?>
                                <?php if ($wpjobportal_other) : ?>
                                    <details class="wjpl-dd">
                                        <summary class="wjpl-btn wjpl-btn-sec" aria-label="<?php echo esc_attr(__('More actions', 'wp-job-portal')); ?>"><?php echo esc_html(__('More', 'wp-job-portal')); ?> <?php echo $wpjobportal_icon('<path d="M6 9l6 6 6-6"/>'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed SVG ?></summary>
                                        <div class="wjpl-dd-menu" role="menu">
                                            <?php foreach ($wpjobportal_other as $wpjobportal_a) { echo wp_kses($wpjobportal_a, WPJOBPORTAL_ALLOWED_TAGS); } ?>
                                        </div>
                                    </details>
                                <?php endif; ?>
                            </div>
                            <div class="wjpl-apl-panel">
                                <div id="<?php echo esc_attr($wpjobportal_r->appid); ?>"></div>
                                <div id="comments" class="wpjobportal-applied-job-actions-popup <?php echo esc_attr($wpjobportal_r->appid); ?>"></div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                    <?php
                    echo wp_kses(WPJOBPORTALformfield::hidden('jobapplyid', $wpjobportal_rows[0]->jobapplyid), WPJOBPORTAL_ALLOWED_TAGS);
                    echo wp_kses(WPJOBPORTALformfield::hidden('ta', $wpjobportal_jid), WPJOBPORTAL_ALLOWED_TAGS);
                    ?>
                <?php elseif ($wpjobportal_counts[0] === 0) : ?>
                    <?php WPJOBPORTAL_Admin_List_UI::empty_state(__('No applicants yet', 'wp-job-portal'), __('When someone applies to this job, they appear here.', 'wp-job-portal')); ?>
                <?php elseif ($wpjobportal_total === 0 && $wpjobportal_active_filters) : ?>
                    <?php WPJOBPORTAL_Admin_List_UI::empty_state(__('Nobody matches these filters', 'wp-job-portal'), __('Change or reset the filters.', 'wp-job-portal')); ?>
                <?php elseif ($wpjobportal_total > 0) : ?>
                    <?php WPJOBPORTAL_Admin_List_UI::empty_state(__('Nothing on this page', 'wp-job-portal'), __('This list has fewer pages now.', 'wp-job-portal'), array(__('Go to the first page', 'wp-job-portal') => $wpjobportal_link(array('pagenum' => null)))); ?>
                <?php else : ?>
                    <?php
                    /* translators: %s: stage name */
                    WPJOBPORTAL_Admin_List_UI::empty_state(sprintf(__('No applicants in %s', 'wp-job-portal'), isset($wpjobportal_stages[$wpjobportal_ta]) ? $wpjobportal_stages[$wpjobportal_ta][0] : __('this view', 'wp-job-portal')), __('Applicants move here from the Move to menu on each row.', 'wp-job-portal'), array(__('Show all applicants', 'wp-job-portal') => $wpjobportal_link(array('ta' => 0, 'pagenum' => null))));
                    ?>
                <?php endif; ?>
                <?php WPJOBPORTAL_Admin_List_UI::footer($wpjobportal_total, __('applicants', 'wp-job-portal'), function ($page) use ($wpjobportal_link) {
                    return $wpjobportal_link(array('pagenum' => $page > 1 ? $page : null));
                }); ?>
            </section>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
    $wpjobportal_inline_js_script = "
        jQuery(document).ready(function () {
            jQuery('a#print-link').click(function (e) {
                e.preventDefault();
                var printurl = jQuery(this).attr('data-print-url');
                print = window.open(printurl, 'print_win', 'width=1024, height=800, scrollbars=yes');
            });
            // One menu open at a time; choosing an item closes it.
            jQuery('.wjpl-dd').on('toggle', function () { var m = this.querySelector('.wjpl-dd-menu'); this.classList.remove('wjpl-dd-up', 'wjpl-dd-left'); if (this.open) { var r = m.getBoundingClientRect(); if (r.bottom > window.innerHeight && r.top - this.offsetHeight - r.height > 40) { this.classList.add('wjpl-dd-up'); } if (r.right > document.documentElement.clientWidth - 8) { this.classList.add('wjpl-dd-left'); } } }).on('click', '.wjpl-dd-menu a', function () { jQuery(this).closest('details').prop('open', false); });
            jQuery(document).on('click', function (e) { jQuery('.wjpl-dd[open]').each(function () { if (!this.contains(e.target)) { this.open = false; } }); });
        });
        // 2.7 read one hidden jobapplyid (the first row's) for every row's stars, so rating
        // anyone rated the first applicant. Each row carries its own id now.
        function setrating_jsadmin(src, newrating) {
            var row = jQuery('#' + src).closest('.wjpl-apl-row');
            var jobapplyid = row.length ? row.data('jobapplyid') : jQuery('#jobapplyid').val();
            jQuery.post(ajaxurl, {action: 'wpjobportal_ajax', wpjobportalme: 'resumeaction', task: 'setResumeRatting', jobapplyid: jobapplyid, rate: newrating, '_wpnonce': '" . esc_attr(wp_create_nonce('set-resume-ratting')) . "'}, function (data) {
                if (data) { jQuery('#' + src).width(parseInt(newrating * 20) + '%'); }
            });
        }
    ";
    wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
?>
