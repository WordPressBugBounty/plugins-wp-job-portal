<?php
if (!defined('ABSPATH')) die('Restricted Access');
/*
 * Site health › Scheduled tasks (design 4): how to call the job alert task from an
 * outside cron service when WordPress Cron is not enough. Five ways, one tab each.
 */
WPJOBPORTAL_Admin_List_UI::enqueue();
$wpjobportal_url = '';
if (!empty(wpjobportal::$_data[0]['ck'])) {
    $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array(
        'wpjobportalme' => 'jobalert',
        'task' => 'sendjobalert',
        'action' => 'wpjobportaltask',
        'ck' => wpjobportal::$_data[0]['ck'],
        'wpjobportalpageid' => wpjobportal::wpjobportal_getPageid()
    ));
}
$wpjobportal_url = esc_url_raw($wpjobportal_url);
$wpjobportal_php = '$curl_handle = curl_init();' . "\n"
    . 'curl_setopt($curl_handle, CURLOPT_URL, \'' . $wpjobportal_url . '\');' . "\n"
    . 'curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, true);' . "\n"
    . 'curl_setopt($curl_handle, CURLOPT_MAXREDIRS, 10);' . "\n"
    . 'curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);' . "\n"
    . '$buffer = curl_exec($curl_handle);' . "\n"
    . 'curl_close($curl_handle);' . "\n"
    . 'echo $buffer;';
// A code box with a Copy button.
$wpjobportal_code = function ($text) {
    echo '<div class="wjpl-code"><pre>' . esc_html($text) . '</pre><button type="button" class="wjpl-btn wjpl-btn-sec wjpl-copy">' . esc_html(__('Copy', 'wp-job-portal')) . '</button></div>';
};
$tabs = array(
    'webcron' => array('Webcron.org', 'Configuration Of A Backup Job With'),
    'wget' => array(__('Wget', 'wp-job-portal'), 'Cron Scheduling Using Wget Most Hosts'),
    'curl' => array(__('CURL', 'wp-job-portal'), 'Cron Scheduling Using Curl siteground And A Few Other Hosts'),
    'php' => array(__('PHP Script', 'wp-job-portal'), 'Custom Php Script To Run'),
    'url' => array(__('Website', 'wp-job-portal'), 'URL For Use With Your Own Scripts And Third Party Services'),
);
?>
<div id="wpjobportaladmin-wrapper" class="wjpl-page">
    <div id="wpjobportaladmin-leftmenu">
        <?php WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <?php WPJOBPORTALMessages::getLayoutMessage(WPJOBPORTALincluder::getJSModel('configuration')->getMessagekey()); ?>
        <div class="wjpl">
            <?php
            WPJOBPORTAL_Admin_List_UI::top_bar(WPJOBPORTAL_Admin_List_UI::menu_crumbs());
            WPJOBPORTAL_Admin_List_UI::title_row(__('Scheduled tasks', 'wp-job-portal'), __('Job alerts run daily through WordPress Cron. Configure an external cron only when the site has low traffic or WordPress Cron is disabled.', 'wp-job-portal'));
            WPJOBPORTAL_Admin_List_UI::section_tabs();
            ?>
            <section class="wjpl-card wjpl-cron" aria-label="<?php echo esc_attr(__('Cron Job', 'wp-job-portal')); ?>">
                <div class="wjpl-cron-tabs" role="tablist">
                    <?php $wpjobportal_first = true; foreach ($tabs as $wpjobportal_key => $tab) : ?>
                        <button type="button" role="tab" id="wjpl-cron-t-<?php echo esc_attr($wpjobportal_key); ?>" aria-controls="wjpl-cron-p-<?php echo esc_attr($wpjobportal_key); ?>" aria-selected="<?php echo $wpjobportal_first ? 'true' : 'false'; ?>"<?php echo $wpjobportal_first ? '' : ' tabindex="-1"'; ?>><?php echo esc_html($tab[0]); ?></button>
                    <?php $wpjobportal_first = false; endforeach; ?>
                </div>
                <?php if ($wpjobportal_url === '') : ?>
                    <div class="wjpl-flash wjpl-flash-bad" role="alert"><?php echo esc_html(__('No cron key is set yet, so there is no address to call.', 'wp-job-portal')); ?></div>
                <?php endif; ?>

                <div class="wjpl-cron-p" role="tabpanel" id="wjpl-cron-p-webcron" aria-labelledby="wjpl-cron-t-webcron">
                    <h2><?php echo esc_html__('Configuration Of A Backup Job With', 'wp-job-portal') . ' Webcron.org'; ?></h2>
                    <dl class="wjpl-cron-dl">
                        <dt><?php echo esc_html__('Cron Job Name', 'wp-job-portal'); ?></dt>
                        <dd><?php echo esc_html__('Log In To','wp-job-portal') . ' Webcron.org. ' . esc_html__('In The Cron Area','wp-job-portal') . ', ' . esc_html__('Click On The New Cron Button','wp-job-portal') . '. ' . esc_html__('Below You will Find What You Have To Enter At','wp-job-portal') . ' Webcron.orgs' . esc_html__('Interface', 'wp-job-portal'); ?></dd>
                        <dt><?php echo esc_html__('Timeout', 'wp-job-portal'); ?></dt>
                        <dd><?php echo esc_html__('180 Seconds If The Backup Does Not Complete','wp-job-portal') . ', ' . esc_html__('Increase It','wp-job-portal') . '. ' . esc_html__('Most Sites Will Work With A Setting Of 180 Or 600 Here','wp-job-portal') . '. ' . esc_html__('If You Have A Very Big Site Which Takes More Than 5 Minutes To Back Itself Up','wp-job-portal') . ', ' . esc_html__('You Might Consider Using Akeeba Backup Professional And The Native Cli Cron Job Instead','wp-job-portal') . ', ' . esc_html__('As Its Much More Cost-effective.', 'wp-job-portal'); ?></dd>
                        <dt><?php echo esc_html__('Url You Want To Execute', 'wp-job-portal'); ?></dt>
                        <dd><?php $wpjobportal_code($wpjobportal_url); ?></dd>
                        <dt><?php echo esc_html__('Login', 'wp-job-portal'); ?></dt>
                        <dd><?php echo esc_html__('Leave This Blank', 'wp-job-portal'); ?></dd>
                        <dt><?php echo esc_html__('Password', 'wp-job-portal'); ?></dt>
                        <dd><?php echo esc_html__('Leave This Blank', 'wp-job-portal'); ?></dd>
                        <dt><?php echo esc_html__('Execution Time', 'wp-job-portal'); ?></dt>
                        <dd><?php echo esc_html__('Thats The Grid Below The Other Options','wp-job-portal') . '. ' . esc_html__('Select When And How Often You Want Your Cron Job To Run.', 'wp-job-portal'); ?></dd>
                        <dt><?php echo esc_html__('Alerts', 'wp-job-portal'); ?></dt>
                        <dd><?php echo esc_html__('If You Have Already Set Up Alert Methods In','wp-job-portal') . ' Webcron.orgs' . esc_html__('Interface','wp-job-portal') . ', ' . esc_html__('We Recommend Choosing An Alert Method Here And Not Checking The only On Error So That You Always Get A Notification When The Backup Cron Job Runs','wp-job-portal') . '. ' . esc_html__('Finally Click On The Submit Button To Finish Setting Up Your Cron Job.', 'wp-job-portal'); ?></dd>
                    </dl>
                </div>
                <div class="wjpl-cron-p" role="tabpanel" id="wjpl-cron-p-wget" aria-labelledby="wjpl-cron-t-wget" hidden>
                    <h2><?php echo esc_html__('Cron Scheduling Using Wget Most Hosts', 'wp-job-portal'); ?></h2>
                    <?php $wpjobportal_code('wget --max-redirect=10000 "' . $wpjobportal_url . '" -O - 1>/dev/null 2>/dev/null'); ?>
                </div>
                <div class="wjpl-cron-p" role="tabpanel" id="wjpl-cron-p-curl" aria-labelledby="wjpl-cron-t-curl" hidden>
                    <h2><?php echo esc_html__('Cron Scheduling Using Curl siteground And A Few Other Hosts', 'wp-job-portal'); ?></h2>
                    <?php $wpjobportal_code('curl "' . $wpjobportal_url . '"'); ?>
                    <p class="wjpl-small"><?php echo esc_html__('Or', 'wp-job-portal'); ?></p>
                    <?php $wpjobportal_code('curl -L --max-redirs 1000 -v "' . $wpjobportal_url . '" 1>/dev/null 2>/dev/null'); ?>
                </div>
                <div class="wjpl-cron-p" role="tabpanel" id="wjpl-cron-p-php" aria-labelledby="wjpl-cron-t-php" hidden>
                    <h2><?php echo esc_html__('Custom Php Script To Run', 'wp-job-portal'); ?></h2>
                    <?php $wpjobportal_code($wpjobportal_php); ?>
                </div>
                <div class="wjpl-cron-p" role="tabpanel" id="wjpl-cron-p-url" aria-labelledby="wjpl-cron-t-url" hidden>
                    <h2><?php echo esc_html__('URL For Use With Your Own Scripts And Third Party Services', 'wp-job-portal'); ?></h2>
                    <?php $wpjobportal_code($wpjobportal_url); ?>
                </div>
            </section>
        </div>
    </div>
</div>
<?php
wp_register_script('wpjobportal-inline-handle', '', array(), WPJOBPORTAL_VERSION, false);
wp_enqueue_script('wpjobportal-inline-handle');
wp_add_inline_script('wpjobportal-inline-handle', '(function () {
    var tabs = [].slice.call(document.querySelectorAll(".wjpl-cron-tabs [role=tab]"));
    function pick(tab, focus) {
        tabs.forEach(function (t) {
            var on = t === tab;
            t.setAttribute("aria-selected", on ? "true" : "false");
            t.tabIndex = on ? 0 : -1;
            document.getElementById(t.getAttribute("aria-controls")).hidden = !on;
        });
        if (focus) { tab.focus(); }
    }
    tabs.forEach(function (t, i) {
        t.addEventListener("click", function () { pick(t); });
        t.addEventListener("keydown", function (e) {
            var n = e.key === "ArrowRight" ? 1 : e.key === "ArrowLeft" ? -1 : 0;
            if (n) { e.preventDefault(); pick(tabs[(i + n + tabs.length) % tabs.length], true); }
        });
    });
    document.addEventListener("click", function (e) {
        var b = e.target.closest(".wjpl-copy");
        if (!b || !navigator.clipboard) { return; }
        navigator.clipboard.writeText(b.previousElementSibling.textContent).then(function () {
            var old = b.textContent; b.textContent = ' . wp_json_encode(__('Copied', 'wp-job-portal')) . '; setTimeout(function () { b.textContent = old; }, 1500);
        });
    });
})();');
