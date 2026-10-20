<?php
/**
 * The design picker at the top of WP Job Portal > Design.
 *
 * Core has one design. Another plugin can offer a second -- Pro offers Elegant
 * Design -- through the wpjobportal_design_switch filter, which answers with
 * its name, its points, an optional tag, whether it is on, and whether it can
 * be changed here. Activating a card goes to the theme controller's
 * savedesign task, which hands the choice back to that plugin through
 * wpjobportal_design_switch_save. Core keeps no setting of its own, so with
 * no such plugin installed this renders nothing and the page is the standard
 * design's settings alone.
 *
 * Each design keeps its own settings; the section below the cards edits the
 * one that is active.
 *
 * @package WP Job Portal
 */

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

$wpjobportal_switch = apply_filters('wpjobportal_design_switch', array());

if (!is_array($wpjobportal_switch) || empty($wpjobportal_switch['label'])) {
    return;
}

$wpjobportal_switch_on = !empty($wpjobportal_switch['on']);
$wpjobportal_switch_locked = !empty($wpjobportal_switch['locked']);
$wpjobportal_theme_values = isset(wpjobportal::$_data[0]) && is_array(wpjobportal::$_data[0]) ? wpjobportal::$_data[0] : array();

// Where "View site" goes: the job board's own page, else the home page.
$wpjobportal_site_page = absint(WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue('default_pageid'));
$wpjobportal_site_url = $wpjobportal_site_page > 0 ? get_permalink($wpjobportal_site_page) : '';
if (!$wpjobportal_site_url) {
    $wpjobportal_site_url = home_url('/');
}

// Just switched? Only when the switch took effect: the design named is live.
$wpjobportal_switched = sanitize_key((string) WPJOBPORTALrequest::getVar('wpjobportal_design_switched', 'get', ''));
if (!in_array($wpjobportal_switched, array('standard', 'alternative'), true)
    || ($wpjobportal_switched === 'alternative') !== $wpjobportal_switch_on) {
    $wpjobportal_switched = '';
}

/*
 * Each preview is drawn in the colour that design will use, so the card
 * shows the site's own primary rather than a stock picture.
 */
$wpjobportal_designs = array(
    'standard' => array(
        'name' => __('Standard design', 'wp-job-portal'),
        'tag' => '',
        'points' => array(
            __('Light, flat layout', 'wp-job-portal'),
            __('Blends with your WordPress theme', 'wp-job-portal'),
            __('Fewest styles to load', 'wp-job-portal'),
        ),
        'live' => !$wpjobportal_switch_on,
        'color' => isset($wpjobportal_theme_values['color1']) ? $wpjobportal_theme_values['color1'] : '#3baeda',
    ),
    'alternative' => array(
        'name' => $wpjobportal_switch['label'],
        'tag' => !empty($wpjobportal_switch['tag']) ? $wpjobportal_switch['tag'] : '',
        'points' => !empty($wpjobportal_switch['points']) ? array_slice((array) $wpjobportal_switch['points'], 0, 4) : array(),
        'live' => $wpjobportal_switch_on,
        'color' => isset($wpjobportal_theme_values['ed_color1']) ? $wpjobportal_theme_values['ed_color1'] : '#3baeda',
    ),
);
?>
<style>
    .wpjp-design-picker { margin: 0 0 24px; padding: 20px; background: #fff; border: 1px solid #dcdcde; border-radius: 8px; }
    /* Its own row from the first paint: sized from content, it sat beside Presets while the page loaded, then jumped. */
    .wpjobportal-admin-themepge-wrapper > .wpjp-design-picker { flex: 0 0 100%; box-sizing: border-box; }
    .wpjp-design-picker > h2 { margin: 0 0 4px; font-size: 16px; }
    .wpjp-design-picker > p { margin: 0 0 16px; color: #50575e; }
    .wpjp-design-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 280px), 1fr)); gap: 16px; }
    .wpjp-design-card { position: relative; display: flex; flex-direction: column; border: 1px solid #dcdcde; border-radius: 8px; overflow: hidden; background: #fff; }
    .wpjp-design-card.is-live { border-color: var(--wjp-color-primary, #4f46e5); box-shadow: 0 0 0 1px var(--wjp-color-primary, #4f46e5); }
    .wpjp-design-card-body { display: flex; flex-direction: column; gap: 10px; flex: 1; padding: 14px 16px 16px; }
    .wpjp-design-card-name { display: flex; align-items: center; gap: 8px; margin: 0; font-size: 15px; font-weight: 600; color: #1d2327; }
    .wpjp-design-tag { padding: 1px 8px; border-radius: 999px; background: #eef0ff; color: #3730a3; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .03em; }
    .wpjp-design-points { margin: 0; padding: 0; list-style: none; color: #50575e; }
    .wpjp-design-points li { position: relative; margin: 0 0 4px; padding-inline-start: 20px; }
    .wpjp-design-points li::before { content: ""; position: absolute; inset-inline-start: 2px; top: 5px; width: 9px; height: 5px; border-inline-start: 2px solid #2271b1; border-bottom: 2px solid #2271b1; transform: rotate(-45deg); }
    .wpjp-design-card-foot { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: auto; padding-top: 12px; border-top: 1px solid #f0f0f1; min-height: 46px; }
    /* Which design is live, at a glance: a tag on the card itself. */
    .wpjp-design-active { position: absolute; top: 10px; inset-inline-start: 10px; z-index: 1; padding: 3px 10px; border-radius: 999px; background: var(--wjp-color-primary, #4f46e5); color: #fff; font-size: 11px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
    .wpjp-design-live { display: inline-flex; align-items: center; gap: 6px; color: #116329; font-weight: 600; }
    .wpjp-design-live::before { content: ""; width: 8px; height: 8px; border-radius: 50%; background: #1a7f37; }
    /* The switch is the card's one action: full width, the size of the page's other main buttons. */
    #wpjobportaladmin-wrapper .wpjp-design-card .wpjp-design-switch { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; min-height: 46px; height: auto; margin: 0; padding: 10px 18px; border-radius: 6px; background: var(--wjp-color-primary, #4f46e5); border: 1px solid var(--wjp-color-primary, #4f46e5); color: #fff; font-size: 15px; font-weight: 600; line-height: 1.3; box-shadow: none; }
    #wpjobportaladmin-wrapper .wpjp-design-card .wpjp-design-switch:hover,
    #wpjobportaladmin-wrapper .wpjp-design-card .wpjp-design-switch:focus-visible { background: var(--wjp-color-primary-dark, #3730a3); border-color: var(--wjp-color-primary-dark, #3730a3); color: #fff; }
    #wpjobportaladmin-wrapper .wpjp-design-card .wpjp-design-switch:focus-visible { outline: 2px solid var(--wjp-color-primary, #4f46e5); outline-offset: 2px; }
    #wpjobportaladmin-wrapper .wpjp-design-card .wpjp-design-switch[disabled] { opacity: .55; cursor: not-allowed; }
    .wpjp-design-switch-arrow { font-size: 18px; line-height: 1; }
    [dir="rtl"] .wpjp-design-switch-arrow { transform: scaleX(-1); }
    #wpjobportaladmin-wrapper .wpjp-design-card-foot a { color: var(--wjp-color-primary, #4f46e5); font-weight: 600; text-decoration: none; }
    #wpjobportaladmin-wrapper .wpjp-design-card-foot a:hover, #wpjobportaladmin-wrapper .wpjp-design-card-foot a:focus { text-decoration: underline; }
    .wpjp-design-locked { margin: 12px 0 0; color: #8a4b00; }
    .wpjp-design-hint { margin: 0; font-size: 12px; color: #646970; }
    /* Hint above, button last: the button lines up with "Live on your site" on the other card. */
    .wpjp-design-card-foot.has-hint { flex-direction: column-reverse; align-items: stretch; gap: 8px; }
    .wpjp-design-done { display: flex; align-items: center; flex-wrap: wrap; gap: 8px 14px; margin: 0 0 16px; padding: 10px 14px; background: #edfaef; border: 1px solid #b8e6bf; border-inline-start: 4px solid #1a7f37; border-radius: 6px; color: #1d2327; }
    .wpjp-design-done strong { margin-inline-end: auto; }
    #wpjobportaladmin-wrapper .wpjp-design-done a { color: var(--wjp-color-primary, #4f46e5); font-weight: 600; text-decoration: none; }
    #wpjobportaladmin-wrapper .wpjp-design-done .button { min-height: 30px; height: auto; padding: 2px 12px; line-height: 1.6; font-size: 13px; }

    /* The preview: one sample job card laid out as each design lays out its
       job lists (Standard: framed logo on the left; Elegant: status and
       company on top, large title, logo on the right). Fixed sample text, so
       it looks the same whatever the site holds. */
    .wpjp-design-mock { --mock: #3baeda; display: flex; align-items: center; justify-content: center; height: 150px; padding: 14px; background: #f6f6f6; border-bottom: 1px solid #dcdcde; }
    .wpjp-design-mock .mock-card { display: grid; gap: 4px 10px; align-items: center; width: 100%; max-width: 340px; padding: 10px 12px; background: #fff; border: 1px solid #e2e4e7; border-inline-start: 3px solid #1e8e3e; font-size: 11px; line-height: 1.3; color: #575757; }
    .wpjp-design-mock .mock-title { font-weight: 700; color: #222; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .wpjp-design-mock .mock-line { display: flex; align-items: center; gap: 6px; min-width: 0; white-space: nowrap; overflow: hidden; }
    .wpjp-design-mock .mock-company { color: var(--mock); font-weight: 600; overflow: hidden; text-overflow: ellipsis; }
    .wpjp-design-mock .mock-status { padding: 1px 6px; background: #28a745; color: #fff; font-size: 10px; font-weight: 600; border-radius: 3px; }
    .wpjp-design-mock .mock-logo { display: flex; align-items: center; justify-content: center; background: #e5e5e5; color: #8a8a8a; font-weight: 700; }

    .wpjp-design-mock.is-standard .mock-card { grid-template-columns: 52px minmax(0, 1fr) auto; }
    .wpjp-design-mock.is-standard .mock-logo { grid-column: 1; }
    .wpjp-design-mock.is-standard :is(.mock-title, .mock-line) { grid-column: 2; }
    .wpjp-design-mock.is-standard .mock-side { grid-column: 3; grid-row: 1 / span 3; display: flex; flex-direction: column; align-items: flex-end; gap: 6px; align-self: start; font-weight: 700; color: #222; white-space: nowrap; }
    .wpjp-design-mock.is-standard .mock-logo { grid-row: 1 / span 3; width: 52px; height: 52px; border: 1px solid #f0c27b; padding: 3px; background-clip: content-box; font-size: 12px; }
    .wpjp-design-mock.is-standard .mock-title { font-size: 13px; }
    .wpjp-design-mock.is-standard .mock-type { padding: 2px 8px; background: #00abfa; color: #fff; font-size: 10px; font-weight: 600; border-radius: 4px; }

    .wpjp-design-mock.is-alternative { background: #f6f7fb; }
    .wpjp-design-mock.is-alternative .mock-card { grid-template-columns: minmax(0, 1fr) 40px; border-color: #e3e6ee; border-inline-start-color: #1e8e3e; border-radius: 10px; box-shadow: 0 6px 16px -10px rgba(18, 23, 43, .35); color: #4d5566; }
    .wpjp-design-mock.is-alternative .mock-logo { grid-column: 2; grid-row: 1 / span 2; width: 40px; height: 40px; border-radius: 8px; font-size: 11px; }
    .wpjp-design-mock.is-alternative .mock-status { border-radius: 999px; background: #16803c; }
    .wpjp-design-mock.is-alternative .mock-title { grid-column: 1; font-size: 16px; color: #12172b; letter-spacing: -0.01em; }
    .wpjp-design-mock.is-alternative .mock-line.is-meta { grid-column: 1 / -1; font-weight: 600; color: #4d5566; }
</style>
<form class="wpjp-design-picker" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_theme&task=savedesign&action=task'), 'wpjobportal_theme_nonce')); ?>">
    <h2><?php echo esc_html__('Choose a design', 'wp-job-portal'); ?></h2>
    <p><?php echo esc_html__('How your job board looks to visitors. Each design keeps its own settings.', 'wp-job-portal'); ?></p>
    <input type="hidden" name="form_request" value="wpjobportal" />
    <?php if ($wpjobportal_switched !== '') { ?>
        <div class="wpjp-design-done" role="status">
            <strong><?php
                /* translators: %s: design name, e.g. Elegant Design */
                echo esc_html(sprintf(__('%s is now live on your site.', 'wp-job-portal'), $wpjobportal_designs[$wpjobportal_switched]['name']));
            ?></strong>
            <a href="<?php echo esc_url($wpjobportal_site_url); ?>" target="_blank" rel="noopener"><?php echo esc_html__('View site', 'wp-job-portal'); ?></a>
            <button type="submit" name="design" value="<?php echo esc_attr($wpjobportal_switched === 'alternative' ? 'standard' : 'alternative'); ?>" class="button"<?php disabled($wpjobportal_switch_locked); ?>>
                <?php echo esc_html__('Undo', 'wp-job-portal'); ?>
            </button>
        </div>
    <?php } ?>
    <div class="wpjp-design-cards">
        <?php foreach ($wpjobportal_designs as $wpjobportal_design_key => $wpjobportal_design) { ?>
            <div class="wpjp-design-card<?php echo $wpjobportal_design['live'] ? ' is-live' : ''; ?>">
                <?php if ($wpjobportal_design['live']) { ?>
                    <span class="wpjp-design-active"><?php echo esc_html__('Active', 'wp-job-portal'); ?></span>
                <?php } ?>
                <div class="wpjp-design-mock is-<?php echo esc_attr($wpjobportal_design_key); ?>" style="--mock: <?php echo esc_attr($wpjobportal_design['color']); ?>;" aria-hidden="true">
                    <?php if ($wpjobportal_design_key === 'standard') { ?>
                        <div class="mock-card">
                            <span class="mock-logo">BP</span>
                            <span class="mock-title"><?php echo esc_html__('Senior PHP Developer', 'wp-job-portal'); ?></span>
                            <span class="mock-line"><span class="mock-status"><?php echo esc_html__('Publish', 'wp-job-portal'); ?></span><span class="mock-company">Brightpath</span></span>
                            <span class="mock-line">IT &bull; Karachi</span>
                            <span class="mock-side"><span class="mock-type"><?php echo esc_html__('Full-Time', 'wp-job-portal'); ?></span>4,500 $</span>
                        </div>
                    <?php } else { ?>
                        <div class="mock-card">
                            <span class="mock-line"><span class="mock-status"><?php echo esc_html__('Publish', 'wp-job-portal'); ?></span><span class="mock-company">Brightpath</span><span>Karachi</span></span>
                            <span class="mock-logo">BP</span>
                            <span class="mock-title"><?php echo esc_html__('Senior PHP Developer', 'wp-job-portal'); ?></span>
                            <span class="mock-line is-meta"><?php echo esc_html__('Full-Time', 'wp-job-portal'); ?> &middot; IT &middot; 4,500 $</span>
                        </div>
                    <?php } ?>
                </div>
                <div class="wpjp-design-card-body">
                    <h3 class="wpjp-design-card-name">
                        <?php echo esc_html($wpjobportal_design['name']); ?>
                        <?php if ($wpjobportal_design['tag'] !== '') { ?>
                            <span class="wpjp-design-tag"><?php echo esc_html($wpjobportal_design['tag']); ?></span>
                        <?php } ?>
                    </h3>
                    <?php if (!empty($wpjobportal_design['points'])) { ?>
                        <ul class="wpjp-design-points">
                            <?php foreach ($wpjobportal_design['points'] as $wpjobportal_design_point) { ?>
                                <li><?php echo esc_html($wpjobportal_design_point); ?></li>
                            <?php } ?>
                        </ul>
                    <?php } ?>
                    <div class="wpjp-design-card-foot<?php echo $wpjobportal_design['live'] ? '' : ' has-hint'; ?>">
                        <?php if ($wpjobportal_design['live']) { ?>
                            <span class="wpjp-design-live"><?php echo esc_html__('Live on your site', 'wp-job-portal'); ?></span>
                            <a href="<?php echo esc_url($wpjobportal_site_url); ?>" target="_blank" rel="noopener"><?php echo esc_html__('View site', 'wp-job-portal'); ?></a>
                        <?php } else { ?>
                            <button type="submit" name="design" value="<?php echo esc_attr($wpjobportal_design_key); ?>" class="button wpjp-design-switch"<?php disabled($wpjobportal_switch_locked); ?>>
                                <?php
                                /* translators: %s: design name, e.g. Elegant Design */
                                echo esc_html(sprintf(__('Switch to %s', 'wp-job-portal'), $wpjobportal_design['name']));
                                ?>
                                <span class="wpjp-design-switch-arrow" aria-hidden="true">&rarr;</span>
                            </button>
                            <p class="wpjp-design-hint"><?php echo esc_html__('Visitors see it straight away. The other design keeps its settings.', 'wp-job-portal'); ?></p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
    <?php if ($wpjobportal_switch_locked && !empty($wpjobportal_switch['locked_note'])) { ?>
        <p class="wpjp-design-locked"><?php echo esc_html($wpjobportal_switch['locked_note']); ?></p>
    <?php } ?>
</form>
