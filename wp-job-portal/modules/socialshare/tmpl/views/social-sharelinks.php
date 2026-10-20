<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
/**
* @param WP SOCIAL SHARE LINKS
*/

$wpjobportal_job_title = '';
if (isset($data->title) && $data->title !== '') {
    $wpjobportal_job_title = wp_strip_all_tags($data->title);
} else {
    $wpjobportal_job_title = wp_strip_all_tags(get_the_title());
}

$wpjobportal_job_slug_id = '';
if (isset($data->jobaliasid) && $data->jobaliasid !== '') {
    $wpjobportal_job_slug_id = $data->jobaliasid;
} elseif (isset($data->alias) && $data->alias !== '' && isset($data->id) && is_numeric($data->id)) {
    $wpjobportal_job_slug_id = sanitize_title($data->alias) . '-' . absint($data->id);
} elseif (isset($data->id) && is_numeric($data->id)) {
    $wpjobportal_job_slug_id = absint($data->id);
}

$wpjobportal_share_url = '';
if ($wpjobportal_job_slug_id !== '' && class_exists('wpjobportal') && method_exists('wpjobportal', 'wpjobportal_makeUrl')) {
    $wpjobportal_share_url = wpjobportal::wpjobportal_makeUrl(array(
        'wpjobportalme' => 'job',
        'wpjobportallt' => 'viewjob',
        'wpjobportalid' => $wpjobportal_job_slug_id,
        'wpjobportalpageid' => wpjobportal::wpjobportal_getPageid(),
    ));
}

if ($wpjobportal_share_url === '') {
    $wpjobportal_request_uri = WPJOBPORTALrequest::requestUri();
    if ($wpjobportal_request_uri === '') {
        $wpjobportal_request_uri = '/';
    }
    $wpjobportal_share_url = home_url($wpjobportal_request_uri);
}

$wpjobportal_share_url = esc_url_raw($wpjobportal_share_url);
$wpjobportal_encoded_url = rawurlencode($wpjobportal_share_url);
$wpjobportal_encoded_title = rawurlencode($wpjobportal_job_title);
$wpjobportal_email_subject = rawurlencode(sprintf(/* translators: %s: job title */ __('Job opening: %s', 'wp-job-portal'), $wpjobportal_job_title));
$wpjobportal_email_body = rawurlencode($wpjobportal_job_title . "\n\n" . $wpjobportal_share_url);
$wpjobportal_share_buttons = array();

if (wpjobportal_checkLinks('employer_share_fb_share')) {
    $wpjobportal_share_buttons[] = array(
        'class' => 'wpjobportal-social-share-facebook',
        'label' => esc_html__('Facebook', 'wp-job-portal'),
        'url' => 'https://www.facebook.com/sharer/sharer.php?u=' . $wpjobportal_encoded_url,
        'popup' => true,
    );
}

if (wpjobportal_checkLinks('employer_share_twitter_share')) {
    $wpjobportal_share_buttons[] = array(
        'class' => 'wpjobportal-social-share-x',
        'label' => esc_html__('X', 'wp-job-portal'),
        'url' => 'https://twitter.com/intent/tweet?url=' . $wpjobportal_encoded_url . '&text=' . $wpjobportal_encoded_title,
        'popup' => true,
    );
}

if (wpjobportal_checkLinks('employer_share_linkedin_share')) {
    $wpjobportal_share_buttons[] = array(
        'class' => 'wpjobportal-social-share-linkedin',
        'label' => esc_html__('LinkedIn', 'wp-job-portal'),
        'url' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $wpjobportal_encoded_url,
        'popup' => true,
    );
}

if (wpjobportal_checkLinks('employer_share_blog_share')) {
    $wpjobportal_share_buttons[] = array(
        'class' => 'wpjobportal-social-share-blogger',
        'label' => esc_html__('Blogger', 'wp-job-portal'),
        'url' => 'https://www.blogger.com/blog-this.g?u=' . $wpjobportal_encoded_url . '&n=' . $wpjobportal_encoded_title . '&t=' . $wpjobportal_encoded_title,
        'popup' => true,
    );
}

if (wpjobportal_checkLinks('employer_share_digg_share')) {
    $wpjobportal_share_buttons[] = array(
        'class' => 'wpjobportal-social-share-digg',
        'label' => esc_html__('Digg', 'wp-job-portal'),
        'url' => 'https://digg.com/submit?url=' . $wpjobportal_encoded_url,
        'popup' => true,
    );
}

if (wpjobportal_checkLinks('employer_share_yahoo_share')) {
    $wpjobportal_share_buttons[] = array(
        'class' => 'wpjobportal-social-share-yahoo',
        'label' => esc_html__('Yahoo Mail', 'wp-job-portal'),
        'url' => 'https://compose.mail.yahoo.com/?subject=' . $wpjobportal_email_subject . '&body=' . $wpjobportal_email_body,
        'popup' => true,
    );
}

if (!empty($wpjobportal_share_buttons)) {
    $wpjobportal_share_buttons[] = array(
        'class' => 'wpjobportal-social-share-email',
        'label' => esc_html__('Email', 'wp-job-portal'),
        'url' => 'mailto:?subject=' . $wpjobportal_email_subject . '&body=' . $wpjobportal_email_body,
        'popup' => false,
    );
}

$wpjobportal_show_fb_like = wpjobportal_checkLinks('employer_share_fb_like');
$wpjobportal_has_share_content = !empty($wpjobportal_share_buttons) || $wpjobportal_show_fb_like;

if (!$wpjobportal_has_share_content) {
    return;
}

$wpjobportal_theme_mode = (isset(wpjobportal::$wpjobportal_theme_chk) && wpjobportal::$wpjobportal_theme_chk == 1);
$wpjobportal_wrapper_class = $wpjobportal_theme_mode ? 'wpj-jp-social-share-wrp' : 'wjportal-social-share-wrp';
$wpjobportal_share_class = $wpjobportal_theme_mode ? 'wpj-jp-social-share' : 'wjportal-social-share';
$wpjobportal_footer_class = $wpjobportal_theme_mode ? 'wpj-jp-social-share-footer' : 'wjportal-social-share-footer';
$wpjobportal_panel_class = $wpjobportal_theme_mode ? 'wpj-jp-social-share-panel' : 'wjportal-social-share-panel';
?>
<div class="<?php echo esc_attr($wpjobportal_wrapper_class); ?> wpjobportal-social-share-modern">
    <style>
        .wpjobportal-social-share-modern{margin:18px 0;padding:16px;border:1px solid #e5e7eb;border-radius:14px;background:#fff;box-sizing:border-box}.wpjobportal-social-share-modern .wpjobportal-social-share-title{display:block;margin:0 0 12px;font-size:15px;font-weight:700;color:#111827}.wpjobportal-social-share-modern .wpjobportal-social-share-row{display:flex;flex-wrap:wrap;gap:8px;align-items:center}.wpjobportal-social-share-modern .wpjobportal-social-share-btn,.wpjobportal-social-share-modern .wpjobportal-social-share-copy{display:inline-flex;align-items:center;justify-content:center;gap:6px;min-height:34px;padding:7px 12px;border:1px solid #d1d5db;border-radius:999px;background:#f9fafb;color:#111827;font-size:13px;font-weight:600;line-height:1;text-decoration:none;cursor:pointer;box-shadow:none}.wpjobportal-social-share-modern .wpjobportal-social-share-btn:hover,.wpjobportal-social-share-modern .wpjobportal-social-share-copy:hover{background:#f3f4f6;color:#111827;text-decoration:none}.wpjobportal-social-share-modern .wpjobportal-social-share-btn:focus,.wpjobportal-social-share-modern .wpjobportal-social-share-copy:focus{outline:2px solid #2563eb;outline-offset:2px}.wpjobportal-social-share-modern .wpjobportal-social-share-copy-copied{border-color:#16a34a;background:#f0fdf4;color:#166534}.wpjobportal-social-share-modern .jportal-social-share-content{margin-top:12px}.wpjobportal-social-share-modern .wpjobportal-social-share-screen-reader{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}@media(max-width:480px){.wpjobportal-social-share-modern .wpjobportal-social-share-btn,.wpjobportal-social-share-modern .wpjobportal-social-share-copy{width:100%}}
    </style>
    <div class="wpjobportal-social-share-title"><?php echo esc_html__('Share This Job', 'wp-job-portal'); ?></div>
    <?php if (!empty($wpjobportal_share_buttons)) { ?>
        <div class="<?php echo esc_attr($wpjobportal_share_class); ?> wpjobportal-social-share-row">
            <?php foreach ($wpjobportal_share_buttons as $wpjobportal_share_button) { ?>
                <a class="wjportal-social-share-link wpjobportal-social-share-btn <?php echo esc_attr($wpjobportal_share_button['class']); ?> <?php echo $wpjobportal_share_button['popup'] ? 'wpjobportal-social-share-popup' : ''; ?>" href="<?php echo esc_url($wpjobportal_share_button['url']); ?>" target="<?php echo $wpjobportal_share_button['popup'] ? '_blank' : '_self'; ?>" rel="noopener noreferrer nofollow">
                    <?php echo esc_html($wpjobportal_share_button['label']); ?>
                    <span class="wpjobportal-social-share-screen-reader"><?php echo esc_html($wpjobportal_share_url); ?></span>
                </a>
            <?php } ?>
            <button type="button" class="wpjobportal-social-share-copy" data-wpjobportal-copy-link="<?php echo esc_attr($wpjobportal_share_url); ?>" data-default-text="<?php echo esc_attr__('Copy Link', 'wp-job-portal'); ?>" data-copied-text="<?php echo esc_attr__('Copied', 'wp-job-portal'); ?>">
                <?php echo esc_html__('Copy Link', 'wp-job-portal'); ?>
            </button>
        </div>
    <?php } ?>
    <?php if ($wpjobportal_show_fb_like) { ?>
        <div id="wpjobportalfooter" class="<?php echo esc_attr($wpjobportal_footer_class); ?>">
            <div class="js_job_share_pannel <?php echo esc_attr($wpjobportal_panel_class); ?>">
                <div class="jportal-social-share-content">
                    <div id="fb-root" class="jportal-social-share-fb-root"></div>
                    <script>
                        (function(d, s, id) {
                            var js, fjs = d.getElementsByTagName(s)[0];
                            if (d.getElementById(id)) { return; }
                            js = d.createElement(s); js.id = id; js.async = true;
                            js.src = 'https://connect.facebook.net/en_US/sdk.js#xfbml=1';
                            fjs.parentNode.insertBefore(js, fjs);
                        }(document, 'script', 'facebook-jssdk'));
                    </script>
                    <div class="fb-like jportal-social-share-fb-like" data-href="<?php echo esc_url($wpjobportal_share_url); ?>" data-layout="button_count" data-action="like" data-size="small" data-share="false"></div>
                </div>
            </div>
        </div>
    <?php } ?>
    <script>
        (function(){
            if (!window.wpjobportalSocialSharePopupBound) {
                window.wpjobportalSocialSharePopupBound = true;
                document.addEventListener('click', function(event) {
                    var link = event.target.closest ? event.target.closest('.wpjobportal-social-share-popup') : null;
                    if (!link) { return; }
                    event.preventDefault();
                    window.open(link.href, 'wpjobportalShareWindow', 'scrollbars=1,resizable=1,menubar=0,left=200,top=200,width=620,height=520,toolbar=0,status=0');
                });
            }
            if (!window.wpjobportalSocialShareCopyBound) {
                window.wpjobportalSocialShareCopyBound = true;
                document.addEventListener('click', function(event) {
                    var button = event.target.closest ? event.target.closest('.wpjobportal-social-share-copy') : null;
                    if (!button) { return; }
                    var text = button.getAttribute('data-wpjobportal-copy-link') || '';
                    var defaultText = button.getAttribute('data-default-text') || 'Copy Link';
                    var copiedText = button.getAttribute('data-copied-text') || 'Copied';
                    var done = function() {
                        button.textContent = copiedText;
                        button.classList.add('wpjobportal-social-share-copy-copied');
                        window.setTimeout(function() {
                            button.textContent = defaultText;
                            button.classList.remove('wpjobportal-social-share-copy-copied');
                        }, 1800);
                    };
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(text).then(done).catch(function(){});
                    } else {
                        var input = document.createElement('textarea');
                        input.value = text;
                        input.setAttribute('readonly', 'readonly');
                        input.style.position = 'absolute';
                        input.style.left = '-9999px';
                        document.body.appendChild(input);
                        input.select();
                        try { document.execCommand('copy'); done(); } catch (e) {}
                        document.body.removeChild(input);
                    }
                });
            }
        })();
    </script>
</div>
