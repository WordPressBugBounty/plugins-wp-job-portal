<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

?>
<div class="wjportal-main-up-wrapper">
<?php
   if(!WPJOBPORTALincluder::getTemplate('templates/header',array('wpjobportal_module'=>'common'))){
        return;
   }

// to disbale this page for admin
$wpjobportal_error_flag = null;
$wpjobportal_error_flag_message = '';
if(current_user_can( 'manage_options' )){ // if current user is admin
    $wpjobportal_error_flag = true; // set error flag if there's no already
    $wpjobportal_error_flag_message_for = 10;
    $wpjobportal_error_flag_message = WPJOBPORTALLayout::setMessageFor(10 , '' , '',1);
}
if (wpjobportal::$_error_flag == null && $wpjobportal_error_flag == null) {
    $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme');
    $wpjobportal_layout = WPJOBPORTALrequest::getVar('wpjobportallt');
    $wpjobportal_email = "";
    $wpjobportal_uid = get_current_user_id();
    if(WPJOBPORTALrequest::hasCookie('wpjobportal-socialemail')){
        $wpjobportal_email = sanitize_key((string) WPJOBPORTALrequest::cookie('wpjobportal-socialemail', ''));
    }
    $title = wpjobportal::$_config->getConfigurationByConfigName('title');?>
    <div class="wjportal-main-wrapper wjportal-clearfix">
        <div class="wjportal-page-header">
            <div class="wjportal-page-heading">
                <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue( $title)); ?>
            </div>
        </div>
        <div class="wjportal-form-wrp wjportal-new-login-form">
            <form class="wjportal-form" id="coverletter_form" method="post" action="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'common', 'task'=>'savenewinwpjobportal'))); ?>">
                <div class="wjportal-form-sec-heading"><?php echo esc_html(__('Are you new in', 'wp-job-portal')).' '.esc_html(wpjobportal::wpjobportal_getVariableValue( $title)); ?></div>
                <div class="wjportal-form-row">
                    <div class="wjportal-form-title"><?php echo esc_html(__('Please select your role', 'wp-job-portal')); ?> <font >*</font></div>
                    <div class="wjportal-form-value">
                        <?php
                        /*
                         * This is the first thing a new account is asked, and the
                         * answer decides which half of the site they get. A dropdown
                         * of two words does not say what either one means; the
                         * Elegant Design add-on drew explained choices instead, and
                         * that is the better screen, so it is Core's now.
                         *
                         * Radios rather than the add-on's links and script: the same
                         * choice without JavaScript, and with nothing chosen until
                         * the person chooses. The add-on's version arrived with
                         * Employer already selected, so anyone who submitted without
                         * looking became an employer.
                         *
                         * getRolesForCombo still decides which roles may be offered -
                         * a site that has closed employer sign-up offers one - so the
                         * descriptions attach to whatever it returns, and a role
                         * without one simply shows its name.
                         */
                        $wpjobportal_roles = WPJOBPORTALincluder::getJSModel('common')->getRolesForCombo('');
                        $wpjobportal_role_notes = apply_filters('wpjobportal_role_descriptions', array(
                            '1' => __('Employers add companies and jobs, and search for candidates to hire.', 'wp-job-portal'),
                            '2' => __('Job seekers add a resume and cover letters, search jobs, and apply to them.', 'wp-job-portal'),
                        ));
                        ?>
                        <div class="wjportal-role-choices" role="radiogroup" aria-label="<?php echo esc_attr(__('Please select your role', 'wp-job-portal')); ?>">
                            <?php foreach ($wpjobportal_roles as $wpjobportal_role) {
                                $wpjobportal_role_field = 'wjportal-role-' . $wpjobportal_role->id; ?>
                                <label class="wjportal-role-choice" for="<?php echo esc_attr($wpjobportal_role_field); ?>">
                                    <input type="radio" name="roleid" id="<?php echo esc_attr($wpjobportal_role_field); ?>" class="wjportal-role-choice-input" value="<?php echo esc_attr($wpjobportal_role->id); ?>" data-validation="required" required />
                                    <span class="wjportal-role-choice-body">
                                        <span class="wjportal-role-choice-title"><?php echo esc_html($wpjobportal_role->text); ?></span>
                                        <?php if (!empty($wpjobportal_role_notes[$wpjobportal_role->id])) { ?>
                                            <span class="wjportal-role-choice-desc"><?php echo esc_html($wpjobportal_role_notes[$wpjobportal_role->id]); ?></span>
                                        <?php } ?>
                                    </span>
                                </label>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('desired_module', esc_html($wpjobportal_module)),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('desired_layout', esc_html($wpjobportal_layout)),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('id', ''),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('uid', esc_html($wpjobportal_uid)),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('action', 'common_savenewinwpjobportal'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('wpjobportalpageid', esc_html(get_the_ID())),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('form_request', 'wpjobportal'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('_wpnonce', esc_html(wp_create_nonce('wpjobportal_new_in_jobportal_nonce'))),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php
                    if(WPJOBPORTALrequest::hasCookie('wpjobportal-socialmedia') && !empty(WPJOBPORTALrequest::cookie('wpjobportal-socialid'))){
                        echo wp_kses(WPJOBPORTALformfield::hidden('emailaddress', $wpjobportal_email),WPJOBPORTAL_ALLOWED_TAGS);
                        echo wp_kses(WPJOBPORTALformfield::hidden('socialmedia', sanitize_key((string) WPJOBPORTALrequest::cookie('wpjobportal-socialmedia', ''))),WPJOBPORTAL_ALLOWED_TAGS);
                        echo wp_kses(WPJOBPORTALformfield::hidden('first_name', sanitize_key((string) WPJOBPORTALrequest::cookie('wpjobportal-socialfirstname', ''))),WPJOBPORTAL_ALLOWED_TAGS);
                        echo wp_kses(WPJOBPORTALformfield::hidden('last_name', sanitize_key((string) WPJOBPORTALrequest::cookie('wpjobportal-sociallastname', ''))),WPJOBPORTAL_ALLOWED_TAGS);
                        echo wp_kses(WPJOBPORTALformfield::hidden('socialid', sanitize_key((string) WPJOBPORTALrequest::cookie('wpjobportal-socialid', ''))),WPJOBPORTAL_ALLOWED_TAGS);
                    }
                ?>
                <div class="wjportal-form-btn-wrp">
                    <?php echo wp_kses(WPJOBPORTALformfield::submitbutton('save', esc_html(__('Submit', 'wp-job-portal')), array('class' => 'button wjportal-form-btn wjportal-save-btn')),WPJOBPORTAL_ALLOWED_TAGS); ?>
                </div>
            </form>
        </div>
    </div><?php 
}else{
    if($wpjobportal_error_flag_message == ''){
        echo wp_kses_post(wpjobportal::$_error_flag_message);
    }else{
        echo wp_kses_post($wpjobportal_error_flag_message);
    }
}
?>
</div>
