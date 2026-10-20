<?php
if (!defined('ABSPATH')) die('Restricted Access');
    // The owner pickers are plain selects. They used the Chosen widget, which
    // drew them taller and squarer than every other field in the wizard with
    // a clipped arrow; a native select takes the wizard's own field style and
    // still jumps to a user as their name is typed.
    wp_register_script( 'wpjobportal-inline-handle', '', array(), WPJOBPORTAL_VERSION, false );
    wp_enqueue_script( 'wpjobportal-inline-handle' );

    $wpjobportal_inline_js_script = "
        // The owner lists follow the Users screen: an administrator who adds a
        // user in the other tab finds them here on coming back, without
        // reloading the form they were filling in.
        function wpjpRefreshOwnerLists() {
            fetch(window.location.href, {credentials: 'same-origin'}).then(function (r) { return r.text(); }).then(function (html) {
                var fresh = new DOMParser().parseFromString(html, 'text/html');
                ['employer_id', 'jobseeker_id'].forEach(function (id) {
                    var now = document.getElementById(id), next = fresh.getElementById(id);
                    if (!now || !next) { return; }
                    var keep = now.value;
                    now.innerHTML = next.innerHTML;
                    now.value = keep;
                });
                wpjpSyncOwners();
            }).catch(function () {});
        }
        // Whoever is chosen for one role is greyed out in the other list, so
        // the same account cannot be picked twice; and once both are chosen,
        // one line says what each of them will own.
        function wpjpOwnerName(select) {
            var option = select.options[select.selectedIndex];
            return option && option.value ? (option.getAttribute('data-name') || option.text).replace(/ \(.*$/, '') : '';
        }
        function wpjpSyncOwners() {
            var employer = document.getElementById('employer_id'), seeker = document.getElementById('jobseeker_id');
            if (!employer || !seeker) { return; }
            [[employer, seeker, '" . esc_js(__('chosen as employer', 'wp-job-portal')) . "'], [seeker, employer, '" . esc_js(__('chosen as job seeker', 'wp-job-portal')) . "']].forEach(function (pair) {
                var taken = pair[0].value;
                Array.prototype.forEach.call(pair[1].options, function (option) {
                    if (!option.hasAttribute('data-name')) { option.setAttribute('data-name', option.text); }
                    var off = option.value !== '' && option.value === taken;
                    option.disabled = off;
                    option.text = off ? option.getAttribute('data-name').replace(/ - .*$/, '') + ' - ' + pair[2] : option.getAttribute('data-name');
                });
            });
            var summary = document.getElementById('wpjp-sample-owner-summary');
            var who = wpjpOwnerName(employer), whom = wpjpOwnerName(seeker);
            if (who && whom) {
                summary.querySelector('span').textContent = '" . esc_js(/* translators: 1: value, 2: value */ __('%1$s will own the sample companies and jobs, and %2$s the sample resumes and applications.', 'wp-job-portal')) . "'.replace('%1\$s', who).replace('%2\$s', whom);
                summary.hidden = false;
            } else {
                summary.hidden = true;
            }
        }
        jQuery(document).on('change', '#employer_id, #jobseeker_id', wpjpSyncOwners);
        jQuery(wpjpSyncOwners);
        // Coming back to this tab fires one or the other depending on the
        // browser, so both are heard.
        window.addEventListener('focus', wpjpRefreshOwnerLists);
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') { wpjpRefreshOwnerLists(); }
        });
        jQuery(document).on('change', '#employer_id, #jobseeker_id', function () {
            document.getElementById('wpjp-sample-owner-error').hidden = true;
        });
        function wpjpSampleNext() {
            var message = '';
            if (jQuery('#sampledata').val() == 1) {
                var employer = jQuery('#employer_id').val(), seeker = jQuery('#jobseeker_id').val();
                if (!employer || !seeker) {
                    message = '" . esc_js(__('Choose who owns the sample data: an employer and a job seeker. Add a user first if you need one, or choose No to skip the sample data.', 'wp-job-portal')) . "';
                } else if (employer === seeker) {
                    message = '" . esc_js(__('Choose different users for the employer and the job seeker. One account cannot be both.', 'wp-job-portal')) . "';
                }
            }
            var box = document.getElementById('wpjp-sample-owner-error');
            if (message) {
                box.textContent = message;
                box.hidden = false;
                box.scrollIntoView({block: 'center'});
                return;
            }
            box.hidden = true;
            document.getElementById('wpjobportal-form-ins').submit();
        }
        function showHideUserForm() {
            var sampledata = jQuery('#sampledata').val();
            if (sampledata == 0) {
                jQuery('.wpjobportal-post-show-default-user-form').addClass('wpjobportal-post-hide-default-user-form');
            } else {
                jQuery('.wpjobportal-post-show-default-user-form').removeClass('wpjobportal-post-hide-default-user-form');
            }
        }
        jQuery(document).ready(function () {
            jQuery('.inputbox').on('focus', function () {
              jQuery(this)
                .closest('.wpjobportal-post-val')
                .prev('.wpjobportal-post-tit')
                .css('color', '#1572e8');
            });
            jQuery('.inputbox').on('blur', function () {
              jQuery(this)
                .closest('.wpjobportal-post-val')
                .prev('.wpjobportal-post-tit')
                .css('color', '');
            });
          });
               
    ";
    wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );

$wpjobportal_yesno = array((object) array('id' => 1, 'text' => esc_html(__('Yes', 'wp-job-portal')))
                    , (object) array('id' => 0, 'text' => esc_html(__('No', 'wp-job-portal'))));
?>
<div id="wpjobportaladmin-wrapper" class="wpjobportal-post-installation-wrp">
    <!-- content -->
    <div class="wpjobportal-post-installation">
        <div class="wpjobportal-post-menu">
            <div class="wpjobportal-post-installation-logowrp">
                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/postinstallation/quickstrt_logo.png" />
            </div>
            <?php $wpjobportal_setup_active = 'sample'; include __DIR__ . '/setup-steps.php'; ?>
        </div>
        <div class="wpjobportal-post-data">
            <div class="wpjobportal-post-heading-wrp">
                <div class="wpjobportal-post-heading">
                    <?php echo esc_html(__('Sample Data','wp-job-portal'));?>
                </div>
                <div class="wpjobportal-post-head-rightbtns-wrp">
                    <span class="wpjobportal-post-head-pagestep">
                        <?php echo esc_html(__('Step 3 of 4','wp-job-portal')); ?>
                    </span>
                    <a class="wpjobportal-post-head-closebtn" href="admin.php?page=wpjobportal"title="<?php echo esc_attr(__('Close','wp-job-portal'));?>">
                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/postinstallation/close.png" />
                    </a>
                </div>
            </div>
            <form id="wpjobportal-form-ins" class="wpjobportal-form" method="post" action="<?php echo esc_url_raw(admin_url("admin.php?page=wpjobportal_postinstallation&task=savesampledata")); ?>">
                <div class="wpjobportal-post-data-row">
                    <div class="wpjobportal-post-tit">
                        <?php echo esc_html(__('Insert Sample Data','wp-job-portal')); ?>
                    </div>
                    <div class="wpjobportal-post-val">
                        <?php echo wp_kses(WPJOBPORTALformfield::select('sampledata', $wpjobportal_yesno,1,'',array('class' => 'inputbox','onchange' => 'showHideUserForm()')),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <div class="wpjobportal-post-smpledata-infowrp">
                            <img alt="<?php echo esc_attr(__('Info Icon','wp-job-portal')); ?>" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/postinstallation/info-icon.png" />
                            <?php echo esc_html(__('Import demo data to populate your site with example companies, jobs, resumes, and applications.','wp-job-portal')); ?>
                        </div>
                    </div>
                </div>

                <?php
                // Sample data needs an owner for each role; see
                // WPJOBPORTAL_Sample_Data_Service. The first entry is a prompt,
                // not a choice, so it posts nothing.
                $wpjobportal_owner_lists = array();
                foreach (array('employer' => WPJOBPORTAL_Sample_Data_Service::ROLE_EMPLOYER, 'jobseeker' => WPJOBPORTAL_Sample_Data_Service::ROLE_JOBSEEKER) as $wpjobportal_owner_key => $wpjobportal_owner_role) {
                    $wpjobportal_owner_list = WPJOBPORTAL_Sample_Data_Service::owner_candidates($wpjobportal_owner_role);
                    $wpjobportal_owner_list[0] = (object) array('id' => '', 'text' => __('Choose a user', 'wp-job-portal'));
                    $wpjobportal_owner_lists[$wpjobportal_owner_key] = $wpjobportal_owner_list;
                }
                $wpjobportal_owner_notice = isset(wpjobportal::$_data['sample_owner_notice']) ? wpjobportal::$_data['sample_owner_notice'] : null;
                ?>
                <div id="wpjp-sample-owner-error" class="wpjobportal-post-owner-error" role="alert"<?php echo empty($wpjobportal_owner_notice['text']) ? ' hidden' : ''; ?>><?php echo empty($wpjobportal_owner_notice['text']) ? '' : esc_html($wpjobportal_owner_notice['text']); ?></div>

                <div class="wpjobportal-post-data-row wpjobportal-post-show-default-user-form">
                    <div class="wpjobportal-post-tit">
                        <?php echo esc_html(__('Sample Employer','wp-job-portal')); ?>
                    </div>
                    <div class="wpjobportal-post-val wpjobportal-post-val-user-list">
                        <?php echo wp_kses(WPJOBPORTALformfield::select('employer_id', $wpjobportal_owner_lists['employer'], WPJOBPORTAL_Sample_Data_Service::default_owner(WPJOBPORTAL_Sample_Data_Service::ROLE_EMPLOYER) ?: '', '', array('class' => 'inputbox wpjobportal-userselect')),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <a target="_blank" rel="noopener" class="wpjobportal-post-create-user-btn" href="<?php echo esc_url( admin_url( 'user-new.php' ) ); ?>"><?php echo esc_html(__('Add new user','wp-job-portal')); ?><span aria-hidden="true">&#8599;</span><span class="screen-reader-text"><?php echo esc_html(__('(opens in a new tab)','wp-job-portal')); ?></span></a>
                        <div class="wpjobportal-post-help-text">
                            <?php echo esc_html(__('Owns the sample companies and jobs, and sees them in the employer dashboard. A new user added on the WordPress Users screen appears here when you come back to this tab.','wp-job-portal'));?>
                        </div>
                    </div>
                </div>

                <div class="wpjobportal-post-data-row wpjobportal-post-show-default-user-form">
                    <div class="wpjobportal-post-tit">
                        <?php echo esc_html(__('Sample Job Seeker','wp-job-portal')); ?>
                    </div>
                    <div class="wpjobportal-post-val wpjobportal-post-val-user-list">
                        <?php echo wp_kses(WPJOBPORTALformfield::select('jobseeker_id', $wpjobportal_owner_lists['jobseeker'], WPJOBPORTAL_Sample_Data_Service::default_owner(WPJOBPORTAL_Sample_Data_Service::ROLE_JOBSEEKER) ?: '', '', array('class' => 'inputbox wpjobportal-userselect')),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <a target="_blank" rel="noopener" class="wpjobportal-post-create-user-btn" href="<?php echo esc_url( admin_url( 'user-new.php' ) ); ?>"><?php echo esc_html(__('Add new user','wp-job-portal')); ?><span aria-hidden="true">&#8599;</span><span class="screen-reader-text"><?php echo esc_html(__('(opens in a new tab)','wp-job-portal')); ?></span></a>
                        <div class="wpjobportal-post-help-text">
                            <?php echo esc_html(__('Owns the sample resumes and applications, and sees them in the job seeker dashboard. Choose a different user from the employer; one account cannot be both.','wp-job-portal'));?>
                        </div>
                    </div>
                </div>

                <div id="wpjp-sample-owner-summary" class="wpjobportal-post-owner-summary wpjobportal-post-show-default-user-form" role="status" hidden><span></span></div>

                <?php if(wpjobportal::$wpjobportal_theme_chk == 0){ ?>
                    <div class="wpjobportal-post-heading">
                        <?php echo esc_html(__('Menu','wp-job-portal'));?>
                    </div>
                    <div class="wpjobportal-post-data-row">
                        <div class="wpjobportal-post-tit">
                            <?php echo esc_html(__('Job Seeker Menu','wp-job-portal')); ?>
                        </div>
                        <div class="wpjobportal-post-val">
                            <?php echo wp_kses(WPJOBPORTALformfield::select('jsmenu', $wpjobportal_yesno,1,'',array('class' => 'inputbox')),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        </div>
                    </div>
                    <?php $wpjobportal_multiple_employers = get_option('wpjobportal_multiple_employers', 1); ?>
                    <?php if($wpjobportal_multiple_employers == 1){?>
                        <div class="wpjobportal-post-data-row">
                            <div class="wpjobportal-post-tit">
                                <?php echo esc_html(__('Employer Menu','wp-job-portal')); ?>
                            </div>
                            <div class="wpjobportal-post-val">
                                <?php echo wp_kses(WPJOBPORTALformfield::select('empmenu', $wpjobportal_yesno,1,'',array('class' => 'inputbox')),WPJOBPORTAL_ALLOWED_TAGS); ?>
                            </div>
                        </div>
                    <?php }else{ ?>
                        <div class="wpjobportal-post-data-row">
                            <div class="wpjobportal-post-tit">
                                <?php echo esc_html(__('Jobs Listing Menu','wp-job-portal')); ?>
                            </div>
                            <div class="wpjobportal-post-val">
                                <?php echo wp_kses(WPJOBPORTALformfield::select('job_listing_menu', $wpjobportal_yesno,1,'',array('class' => 'inputbox')),WPJOBPORTAL_ALLOWED_TAGS); ?>
                            </div>
                        </div>
                    <?php } ?>
                <?php  } elseif(wpjobportal::$wpjobportal_theme_chk != 1){ ?>
                        <div class="pic-config temp-demo-data">
                            <div class="wpjobportal-post-tit">
                                <?php     echo esc_html(__('Job Hub Sample Data','wp-job-portal')); ?>
                            </div>
                            <div class="wpjobportal-post-val">
                                <?php echo wp_kses(WPJOBPORTALformfield::select('temp_data', $wpjobportal_yesno,1,'',array('class' => 'inputbox')),WPJOBPORTAL_ALLOWED_TAGS); ?>
                            </div>
                            <div class="desc"><?php echo esc_html(__('if yes is selected then pages and menus of job manager template will be created and published.','wp-job-portal'));?>. </div>
                        </div>
                <?php } ?>
                <div class="wpjobportal-post-action-btn" style="text-align: center;">
                    <a class="next-step wpjobportal-post-act-btn" href="javascript:void();" onclick="wpjpSampleNext();"  title="<?php echo esc_attr(__('Next','wp-job-portal')); ?>" style="float: none;">
                        <?php echo esc_html(__('Next Step','wp-job-portal')); ?>
                    </a>
                </div>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('form_request', 'wpjobportal'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('step', 3),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('_wpnonce', esc_html(wp_create_nonce('wpjobportal_postinstallation_nonce'))),WPJOBPORTAL_ALLOWED_TAGS); ?>
            </form>
        </div>
    </div>
</div>
