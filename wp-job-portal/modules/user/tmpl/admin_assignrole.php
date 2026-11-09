<?php
    if (!defined('ABSPATH'))
        die('Restricted Access');
?>
<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper" class="wjpl-page">
    <!-- left menu -->
    <div id="wpjobportaladmin-leftmenu">
        <?php  WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <!-- popup -->
        <div id="full_background" style="display:none;"></div>
        <div id="popup_main" style="display:none;">
            <span class="popup-top">
                <span id="popup_title" ></span>
                <img id="popup_cross" alt="<?php echo esc_attr(__('popup close','wp-job-portal')); ?>" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/popup-close.png">
            </span>
            <div class="popup-search">
                <form id="userpopupsearch">
                    <div class="popup-form-fields-wrp">
                        <div class="popup-form-field search-value">
                            <input type="text" name="uname" id="uname" placeholder="<?php echo esc_attr(__('Username', 'wp-job-portal'));?>" />
                        </div>
                        <div class="popup-form-field search-value">
                            <input type="text" name="name" id="name" placeholder="<?php echo esc_attr(__('Name', 'wp-job-portal'));?>" />
                        </div>
                        <div class="popup-form-field search-value">
                            <input type="text" name="email" id="email" placeholder="<?php echo esc_attr(__('Email Address', 'wp-job-portal'));?>"/>
                        </div>
                        <div class="popup-form-btn-wrp">
                            <input type="submit" class="popup-search-btn" value="<?php echo esc_attr(__('Search', 'wp-job-portal'));?>" />
                            <input type="submit" class="popup-reset-btn" onclick="document.getElementById('name').value = '';document.getElementById('uname').value = ''; document.getElementById('email').value = '';" value="<?php echo esc_attr(__('Reset', 'wp-job-portal'));?>" />
                        </div>
                    </div>
                </form>
            </div>
            <div id="popup-record-data"></div>
        </div>
        <?php WPJOBPORTAL_Admin_Form_UI::header(__('Assign role', 'wp-job-portal'), __('Give a user who has no role yet the employer or job seeker role.', 'wp-job-portal'), __('Role details', 'wp-job-portal'), 'wpjobportal-form'); ?>
        <!-- page content -->
        <div id="wpjobportal-admin-wrapper">
            <form id="wpjobportal-form" class="wpjobportal-form wjpl-form wjpl-form-simple" method="post" action="<?php echo esc_url_raw(admin_url("admin.php?page=wpjobportal_user&task=assignuserrole")); ?>"><?php WPJOBPORTAL_Admin_Form_UI::simple_open(); ?>
                <div class="wpjobportal-form-wrapper">
                    <div class="wpjobportal-form-title">
                        <?php echo esc_html(__('Select User', 'wp-job-portal')); ?>
                        <span class="wpjp-ds-required" aria-hidden="true">*</span>
                        <span class="wpjp-ds-visually-hidden"><?php echo esc_html__('Required', 'wp-job-portal'); ?></span>
                    </div>
                    <div class="wpjobportal-form-value">
                        <?php if (!isset(wpjobportal::$_data[0]->uid)) { ?>
                        <span id="username-div"></span>
                        <a href="#" id="userpopup">
                            <?php echo esc_html(__('Select User', 'wp-job-portal')); ?>
                        </a>
                        <?php } ?>               
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('uid', '', array('data-validation' => 'required')),WPJOBPORTAL_ALLOWED_TAGS); ?>
                    </div>
                </div>
                <div class="wpjobportal-form-wrapper">
                    <div class="wpjobportal-form-title">
                        <?php echo esc_html(__('Role', 'wp-job-portal')); ?>
                    </div>
                    <div class="wpjobportal-form-value">
                        <?php echo wp_kses(WPJOBPORTALformfield::select('roleid', WPJOBPORTALincluder::getJSModel('common')->getRolesForCombo(), '', '', array('class' => 'inputbox wpjobportal-form-select-field')),WPJOBPORTAL_ALLOWED_TAGS); ?>
                    </div>
                </div>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('form_request', 'wpjobportal'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('payer_firstname', ''),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('payer_emailadress', ''),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('user-popup-title-text', esc_html(__('Select User','wp-job-portal'))),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('_wpnonce', esc_html(wp_create_nonce('wpjobportal_user_nonce'))),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <div class="wpjobportal-form-button">
                    <?php echo wp_kses(WPJOBPORTALformfield::submitbutton('save', esc_html(__('Assign Role', 'wp-job-portal')), array('class' => 'button wpjobportal-form-save-btn')),WPJOBPORTAL_ALLOWED_TAGS); ?>
                </div>
            <?php WPJOBPORTAL_Admin_Form_UI::simple_close(); ?></form>
        </div>
    </div>
</div>
<?php
    wp_register_script( 'wpjobportal-inline-handle', '', array(), WPJOBPORTAL_VERSION, false );
    wp_enqueue_script( 'wpjobportal-inline-handle' );

    $wpjobportal_inline_js_script = "
        jQuery(document).ready(function () {
            jQuery.validate();
            jQuery('a#userpopup').click(function (e) {
                e.preventDefault();
                jQuery('div#popup-new-company').css('display', 'none');
                jQuery('img.icon').css('display', 'none');
                jQuery('div#popup-record-data').css('display', 'block');
                jQuery('div#full_background').show();
                var ajaxurl = \"". esc_url_raw(admin_url('admin-ajax.php'))."\";
                jQuery.post(ajaxurl, {action: 'wpjobportal_ajax', wpjobportalme: 'user', task: 'getAllRoleLessUsersAjax', listfor: 1, '_wpnonce':'". esc_attr(wp_create_nonce("get-all-role-less-users-ajax"))."'}, function (data) {
                    if (data) {
                        jQuery('div#popup-record-data').html('');
                        jQuery('span#popup_title').html(jQuery('input#user-popup-title-text').val());
                        jQuery('div#popup-record-data').html(data);
                        setUserLink();
                    }
                });
                jQuery('div#popup_main').slideDown('slow');
            });
        });
        
        function setUserLink() {
            jQuery('a.js-userpopup-link').each(function () {
                var anchor = jQuery(this);
                jQuery(anchor).click(function (e) {
                    var name = jQuery(this).attr('data-name');
                    jQuery('#username-div').html(name);
                    var id = jQuery(this).attr('data-id');
                    var email = jQuery(this).attr('data-email');
                    var name = jQuery(this).attr('data-name');
                    jQuery('input#uid').val(id);
                    jQuery('input#payer_firstname').val(name);
                    jQuery('input#payer_emailadress').val(email);
                    jQuery('div#popup_main').slideUp('slow', function () {
                        jQuery('div#full_background').hide();
                    });
                });
            });
        }
        
        jQuery(document).on('submit', 'form#userpopupsearch', function (e) {
            e.preventDefault();
            e.preventDefault();
            var username = jQuery('input#uname').val();
            var name = jQuery('input#name').val();
            var emailaddress = jQuery('input#email').val();
            var ajaxurl = \"". esc_url_raw(admin_url('admin-ajax.php')) ."\";
            jQuery.post(ajaxurl, {action: 'wpjobportal_ajax', wpjobportalme: 'user', task: 'getAllRoleLessUsersAjax', name: name, uname: username, email: emailaddress, listfor: 1, '_wpnonce':'". esc_attr(wp_create_nonce("get-all-role-less-users-ajax"))."'}, function (data) {
                if (data) {
                    console.log(data);
                    jQuery('span#popup_title').html(jQuery('input#user-popup-title-text').val());
                    jQuery('div#popup-record-data').html(data);
                    setUserLink();
                }
            });//jquery closed
        });
        jQuery('span.close, div#full_background,img#popup_cross').click(function (e) {
            jQuery('div#popup_main').slideUp('slow', function () {
                jQuery('div#full_background').hide();
            });
        
        });
    ";
    wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
?>
