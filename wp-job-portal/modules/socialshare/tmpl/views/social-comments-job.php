<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
/**
* @param wp job portal
* @param wpjobportal Addons Social
* @param Social Comments
*/

if (!wpjobportal_checkLinks('employer_share_fb_comments')) {
    return;
}

$wpjobportal_request_uri = WPJOBPORTALrequest::requestUri();
    if ($wpjobportal_request_uri === '') {
        $wpjobportal_request_uri = '/';
    }
$wpjobportal_comments_url = esc_url_raw(home_url($wpjobportal_request_uri));
$wpjobportal_comments_iframe = add_query_arg(array(
    'href' => $wpjobportal_comments_url,
    'numposts' => 5,
    'width' => 100,
), 'https://www.facebook.com/plugins/comments.php');

if (wpjobportal::$wpjobportal_theme_chk == 1) { ?>
    <div class="wpj-jp-fb-comments-wrp">
        <div id="js_job_fb_commentparent" class="wpj-jp-fb-comments-parent">
            <div id="js_job_fb_commentheading" class="wpj-jp-fb-commentsheading">
                <?php echo esc_html__('Facebook Comments', 'job-portal-theme'); ?>
            </div>
            <div id="wpjobportal_fbcomment" class="wpj-jp-fb-comment">
                <iframe id="employer_fb_comments" title="<?php echo esc_attr__('Facebook Comments', 'job-portal-theme'); ?>" src="<?php echo esc_url($wpjobportal_comments_iframe); ?>" scrolling="yes" frameborder="0" style="border:none; overflow:hidden; width:100%; height:240px;" allow="encrypted-media"></iframe>
            </div>
        </div>
    </div>
<?php } else { ?>
    <div class="wjportal-fb-comments-wrp">
        <div class="wjportal-fb-comments-heading">
            <?php echo esc_html__('Facebook Comments', 'wp-job-portal'); ?>
        </div>
        <div id="js_job_fb_commentparent" class="wjportal-fb-comments-parent">
            <div id="js_job_fb_commentheading" class="wjportal-fb-commentsheading">
                <?php echo esc_html__('Facebook Comments', 'wp-job-portal'); ?>
            </div>
            <div id="wpjobportal_fbcomment" class="wjportal-fb-comment">
                <iframe id="employer_fb_comments" title="<?php echo esc_attr__('Facebook Comments', 'wp-job-portal'); ?>" src="<?php echo esc_url($wpjobportal_comments_iframe); ?>" scrolling="yes" frameborder="0" style="border:none; overflow:hidden; width:100%; height:240px;" allow="encrypted-media"></iframe>
            </div>
        </div>
    </div>
<?php } ?>
