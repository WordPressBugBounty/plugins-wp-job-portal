<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Licences and add-ons.
 *
 * The old Add Ons section had Install Addons, Addons Status, Update Key and
 * Addons List: a three-step wizard, a separate status table, a key form and a
 * catalogue, each with its own idea of what was installed. This is one screen:
 * the licence this site holds, and a card for each product it can have, with
 * the one action that product needs next - install, activate, update, or
 * nothing.
 *
 * Updates also still appear on WordPress's own Updates screen. Both routes go
 * through the same upgrader and the same checksum test, so they cannot
 * disagree about what is installed.
 */


$wpjobportal_licence = new WPJOBPORTAL_Licence_Client();
$wpjobportal_state = $wpjobportal_licence->refresh();
$wpjobportal_key = WPJOBPORTAL_Licence_Client::key();
$wpjobportal_masked = WPJOBPORTAL_Licence_Client::mask($wpjobportal_key);
$wpjobportal_locked = WPJOBPORTAL_Licence_Client::key_is_locked();
$wpjobportal_reason = isset($wpjobportal_state['reason']) ? (string) $wpjobportal_state['reason'] : '';
$wpjobportal_active = !empty($wpjobportal_state['ok']);
$wpjobportal_entitled = isset($wpjobportal_state['plugins']) ? (array) $wpjobportal_state['plugins'] : array();
$wpjobportal_manifest = WPJOBPORTAL_Update_Service::manifest();

if (!function_exists('get_plugins')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}
$wpjobportal_all_plugins = (array) get_plugins();

$wpjobportal_can_install = current_user_can('install_plugins') && current_user_can('activate_plugins');
$wpjobportal_can_update = current_user_can('update_plugins');
$wpjobportal_can_activate = current_user_can('activate_plugins');
$wpjobportal_can_write = WPJOBPORTAL_Addon_Installer::can_write_plugins();
$wpjobportal_plans_url = WPJOBPORTAL_Addon_Installer::plans_url();

// The licence, in one word and a colour.
if ($wpjobportal_key === '') {
    $wpjobportal_chip = array('neutral', __('No key', 'wp-job-portal'));
} elseif ($wpjobportal_active && $wpjobportal_state['status'] === 'active') {
    $wpjobportal_chip = array('success', __('Active', 'wp-job-portal'));
} elseif ($wpjobportal_reason === 'deactivated') {
    $wpjobportal_chip = array('neutral', __('Released', 'wp-job-portal'));
} elseif (in_array($wpjobportal_reason, array('unreachable', 'protocol_mismatch'), true)) {
    $wpjobportal_chip = array('warning', __('Not checked', 'wp-job-portal'));
} elseif ($wpjobportal_state['status'] === 'expired' || $wpjobportal_reason === 'license_expired') {
    $wpjobportal_chip = array('warning', __('Expired', 'wp-job-portal'));
} else {
    $wpjobportal_chip = array('danger', __('Not accepted', 'wp-job-portal'));
}

// What needs saying above the licence. A healthy licence needs nothing - the
// chip already says so.
$wpjobportal_notice = null;
if ($wpjobportal_key === '') {
    $wpjobportal_notice = array('info', __('Add the licence key from your account to install your add-ons and receive updates.', 'wp-job-portal'));
} elseif ($wpjobportal_reason === 'deactivated') {
    $wpjobportal_notice = array('info', __('This site has been released from the licence. Your products keep working; activate the site again to install and update.', 'wp-job-portal'));
} elseif ($wpjobportal_reason === 'unreachable') {
    $wpjobportal_notice = array('warning', __('The licence server could not be reached. Your products keep working; installs and update checks are paused until it answers.', 'wp-job-portal'));
} elseif ($wpjobportal_reason === 'protocol_mismatch') {
    // Something answered, but it was not the licence server. Saying the key
    // was rejected would send the customer to look in the wrong place.
    $wpjobportal_notice = array('warning', __('The licence server gave an answer this version does not understand. Nothing has changed here; try again shortly, or update WP Job Portal.', 'wp-job-portal'));
} elseif (!$wpjobportal_active) {
    $wpjobportal_notice = array('error', $wpjobportal_state['message'] !== ''
        ? $wpjobportal_state['message']
        : __('This licence key was not accepted for this site.', 'wp-job-portal'));
}

$wpjobportal_flash = get_transient('wpjobportal_licence_notice');
if (is_array($wpjobportal_flash) && isset($wpjobportal_flash['text'])) {
    delete_transient('wpjobportal_licence_notice');
    $wpjobportal_notice = array(
        isset($wpjobportal_flash['type']) ? $wpjobportal_flash['type'] : 'error',
        $wpjobportal_flash['text'],
    );
}

$wpjobportal_expires = '';
if (!empty($wpjobportal_state['expires_at'])) {
    $wpjobportal_time = strtotime($wpjobportal_state['expires_at']);
    $wpjobportal_expires = $wpjobportal_time ? date_i18n(get_option('date_format'), $wpjobportal_time) : $wpjobportal_state['expires_at'];
} elseif ($wpjobportal_active) {
    $wpjobportal_expires = __('Never', 'wp-job-portal');
}

$wpjobportal_kind = isset($wpjobportal_state['activation']) ? (string) $wpjobportal_state['activation'] : '';
$wpjobportal_seats = '';
if ($wpjobportal_kind !== '' && isset($wpjobportal_state['slots'][$wpjobportal_kind])) {
    $wpjobportal_seats = sprintf(
        /* translators: 1: sites used, 2: sites allowed. */
        __('%1$d of %2$d used', 'wp-job-portal'),
        $wpjobportal_state['slots'][$wpjobportal_kind]['used'],
        $wpjobportal_state['slots'][$wpjobportal_kind]['allowed']
    );
}

// One entry per product card, with the single next step it needs.
$wpjobportal_cards = array();
$wpjobportal_installable = 0;
foreach (WPJOBPORTAL_Addon_Installer::catalogue() as $wpjobportal_slug => $wpjobportal_product) {
    $wpjobportal_file = WPJOBPORTAL_Addon_Installer::installed_file($wpjobportal_slug);
    $wpjobportal_version = $wpjobportal_file !== '' && isset($wpjobportal_all_plugins[$wpjobportal_file]['Version'])
        ? (string) $wpjobportal_all_plugins[$wpjobportal_file]['Version'] : '';

    if (!empty($wpjobportal_product['legacy']) && $wpjobportal_file === '') {
        continue;
    }

    $wpjobportal_covered = in_array($wpjobportal_slug, $wpjobportal_entitled, true);
    $wpjobportal_entry = isset($wpjobportal_manifest[$wpjobportal_slug]) ? $wpjobportal_manifest[$wpjobportal_slug] : null;
    $wpjobportal_card = array(
        'slug' => $wpjobportal_slug,
        'label' => $wpjobportal_product['label'],
        'summary' => $wpjobportal_product['summary'],
        'version' => $wpjobportal_version,
        'active' => $wpjobportal_file !== '' && is_plugin_active($wpjobportal_file),
        'tone' => 'neutral',
        'status' => '',
        'action' => '',
        'note' => '',
    );

    if ($wpjobportal_file === '') {
        $wpjobportal_card['status'] = __('Not installed', 'wp-job-portal');

        if ($wpjobportal_covered && $wpjobportal_can_install && $wpjobportal_can_write) {
            $wpjobportal_card['tone'] = 'primary';
            $wpjobportal_card['action'] = 'install';
            $wpjobportal_installable++;
        } elseif ($wpjobportal_covered && !$wpjobportal_can_write) {
            $wpjobportal_card['note'] = __('In your licence. This server needs FTP details to add plugins, so download it from your account and upload it under Plugins → Add New.', 'wp-job-portal');
        } elseif ($wpjobportal_covered) {
            $wpjobportal_card['note'] = __('In your licence. Ask an administrator who can install plugins.', 'wp-job-portal');
        } elseif ($wpjobportal_active) {
            $wpjobportal_card['tone'] = 'muted';
            $wpjobportal_card['action'] = 'plans';
        } else {
            $wpjobportal_card['note'] = __('Activate your licence to install.', 'wp-job-portal');
        }
    } else {
        $wpjobportal_card['status'] = sprintf(
            /* translators: %s: version number. */
            __('Version %s', 'wp-job-portal'), $wpjobportal_version);
        $wpjobportal_card['tone'] = 'success';

        $wpjobportal_newer = $wpjobportal_entry !== null && !empty($wpjobportal_entry['update_available'])
            && $wpjobportal_entry['version'] !== '' && version_compare($wpjobportal_entry['version'], $wpjobportal_version, '>');

        if (!$wpjobportal_card['active'] && !empty($wpjobportal_product['legacy'])) {
            // Not offered back. Address Data's and Elegant Design's features
            // now come from Pro, and switching the standalone add-on on again
            // would run both.
            $wpjobportal_card['tone'] = 'muted';
            $wpjobportal_card['note'] = __('Its features are part of Pro, so it does not need to be active. You can delete it from Plugins.', 'wp-job-portal');
        } elseif ($wpjobportal_card['active'] && !empty($wpjobportal_product['legacy']) && class_exists('WPJOBPORTAL_Pro')) {
            // Still active beside Pro. Pro stands its own copy down while the
            // add-on runs, so nothing is doubled; this says what to do next.
            $wpjobportal_card['tone'] = 'muted';
            $wpjobportal_card['note'] = __('Pro now includes this. Deactivate it from Plugins and Pro takes over, with the same look and settings.', 'wp-job-portal');
        } elseif (!$wpjobportal_card['active'] && $wpjobportal_can_activate) {
            $wpjobportal_card['action'] = 'activate';
        } elseif ($wpjobportal_newer && $wpjobportal_entry['package'] !== '' && $wpjobportal_can_update && $wpjobportal_can_write) {
            $wpjobportal_card['tone'] = 'warning';
            $wpjobportal_card['action'] = 'update';
            $wpjobportal_card['offered'] = $wpjobportal_entry['version'];
        } elseif ($wpjobportal_newer && $wpjobportal_entry['package'] === '') {
            $wpjobportal_card['tone'] = 'warning';
            $wpjobportal_card['note'] = sprintf(
                /* translators: %s: version number. */
                __('Version %s is available. Renew your licence to download it.', 'wp-job-portal'), $wpjobportal_entry['version']);
        } elseif ($wpjobportal_newer) {
            $wpjobportal_card['tone'] = 'warning';
            $wpjobportal_card['note'] = sprintf(
                /* translators: %s: version number. */
                __('Version %s is available on Dashboard → Updates.', 'wp-job-portal'), $wpjobportal_entry['version']);
        } elseif ($wpjobportal_active && !$wpjobportal_covered) {
            // Installed, but not on this licence: it keeps working, it just
            // does not update. Only said when the server actually answered -
            // a failed request is not evidence against a paying customer.
            $wpjobportal_card['tone'] = 'muted';
            $wpjobportal_card['note'] = __('Not in your licence. It keeps working, without updates.', 'wp-job-portal');
        } elseif ($wpjobportal_entry !== null) {
            $wpjobportal_card['note'] = __('Up to date.', 'wp-job-portal');
        }
    }

    $wpjobportal_cards[] = $wpjobportal_card;
}

// Sent here from an admin screen an old add-on still serves: say which, what
// replaces it, and - only when this licence cannot get it now - let the
// administrator continue to the old screen.
$wpjobportal_legacy = null;
$wpjobportal_legacy_page = sanitize_key((string) WPJOBPORTALrequest::getVar('wpjobportal_legacy', 'get', ''));
$wpjobportal_legacy_slug = $wpjobportal_legacy_page !== '' ? WPJOBPORTAL_Superseded_Addons::legacy_screen($wpjobportal_legacy_page) : '';
if ($wpjobportal_legacy_slug !== '') {
    $wpjobportal_legacy_product = WPJOBPORTAL_Superseded_Addons::product_for($wpjobportal_legacy_slug);
    $wpjobportal_legacy_title = '';
    global $submenu;
    foreach (array('wpjobportal', 'wpjobportal_hide') as $wpjobportal_parent) {
        foreach (isset($submenu[$wpjobportal_parent]) ? (array) $submenu[$wpjobportal_parent] : array() as $wpjobportal_item) {
            if (isset($wpjobportal_item[0], $wpjobportal_item[2]) && $wpjobportal_item[2] === $wpjobportal_legacy_page) {
                $wpjobportal_legacy_title = wp_strip_all_tags(html_entity_decode((string) $wpjobportal_item[0], ENT_QUOTES, 'UTF-8'));
                break 2;
            }
        }
    }
    $wpjobportal_legacy_folder = WPJOBPORTAL_Superseded_Addons::product_folder($wpjobportal_legacy_product);
    // What this administrator can do: it is done (the product was switched
    // on by this very request, from the button below), they can do it now,
    // they lack the rights, or the licence cannot get it.
    if (WPJOBPORTAL_Superseded_Addons::product_switched_on($wpjobportal_legacy_product)) {
        $wpjobportal_legacy_state = 'done';
    } elseif (WPJOBPORTAL_Superseded_Addons::can_install_now($wpjobportal_legacy_product)) {
        $wpjobportal_legacy_state = 'install';
    } elseif (!current_user_can('activate_plugins')) {
        $wpjobportal_legacy_state = 'staff';
    } else {
        $wpjobportal_legacy_state = 'licence';
    }
    $wpjobportal_legacy = array(
        'state' => $wpjobportal_legacy_state,
        'title' => $wpjobportal_legacy_title !== '' ? $wpjobportal_legacy_title : __('This screen', 'wp-job-portal'),
        'product' => WPJOBPORTAL_Superseded_Addons::product_name($wpjobportal_legacy_product),
        'folder' => $wpjobportal_legacy_folder,
        'action' => WPJOBPORTAL_Addon_Installer::installed_file($wpjobportal_legacy_folder) !== '' ? 'activate_addon' : 'install',
        'open' => admin_url('admin.php?page=' . rawurlencode($wpjobportal_legacy_page)),
        'continue' => WPJOBPORTAL_Superseded_Addons::continue_url($wpjobportal_legacy_page),
    );
}

wp_register_script( 'wpjobportal-licences', '', array(), WPJOBPORTAL_VERSION, true );
?>
<div id="wpjobportaladmin-wrapper">
    <div id="wpjobportaladmin-leftmenu">
        <?php WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <?php WPJOBPORTALincluder::getTemplate('templates/admin/pagetitle', array('wpjobportal_module' => 'wpjobportal', 'wpjobportal_layouts' => 'licences')); ?>
        <div id="wpjobportal-admin-wrapper" class="p0 bg-n bs-n">
        <div class="wjp-licences">

            <?php if ($wpjobportal_legacy !== null && $wpjobportal_legacy['state'] === 'done') { ?>
                <div class="wjp-licences-notice is-success wjp-licences-legacy" role="status">
                    <p><strong>
                        <?php
                        /* translators: 1: admin screen name, e.g. Cover Letters, 2: product name. */
                        echo esc_html(sprintf(__('%1$s now runs on %2$s', 'wp-job-portal'), $wpjobportal_legacy['title'], $wpjobportal_legacy['product']));
                        ?>
                    </strong></p>
                    <p><?php echo esc_html__('The old add-on has stopped. Your data and settings are kept; you can deactivate and delete the old add-on from Plugins when you are ready.', 'wp-job-portal'); ?></p>
                    <p>
                        <a class="wjp-licences-link" href="<?php echo esc_url($wpjobportal_legacy['open']); ?>">
                            <?php
                            /* translators: %s: admin screen name, e.g. Cover Letters. */
                            echo esc_html(sprintf(__('Open %s', 'wp-job-portal'), $wpjobportal_legacy['title']));
                            ?>
                        </a>
                    </p>
                </div>
            <?php } elseif ($wpjobportal_legacy !== null) { ?>
                <div class="wjp-licences-notice is-warning wjp-licences-legacy" role="status">
                    <p><strong>
                        <?php
                        /* translators: 1: admin screen name, e.g. Cover Letters, 2: product name. */
                        echo esc_html(sprintf(__('%1$s is now part of %2$s', 'wp-job-portal'), $wpjobportal_legacy['title'], $wpjobportal_legacy['product']));
                        ?>
                    </strong></p>
                    <?php if ($wpjobportal_legacy['state'] === 'install') { ?>
                        <p>
                            <?php
                            echo esc_html($wpjobportal_legacy['action'] === 'activate_addon'
                                /* translators: %s: product name. */
                                ? sprintf(__('This screen still runs on an old add-on from before version 2.7.0. %s is already installed; activate it below, it takes a moment. Your data and settings are kept, and your users can keep working meanwhile.', 'wp-job-portal'), $wpjobportal_legacy['product'])
                                /* translators: %s: product name. */
                                : sprintf(__('This screen still runs on an old add-on from before version 2.7.0. Add %s with your licence key; it takes a minute. Your data and settings are kept, and your users can keep working meanwhile.', 'wp-job-portal'), $wpjobportal_legacy['product']));
                            ?>
                        </p>
                        <form method="post" action="" data-wpjp-no-pending>
                            <?php wp_nonce_field('wpjobportal_save_licence', 'wpjobportal_licence_nonce'); ?>
                            <input type="hidden" name="wpjobportal_addon" value="<?php echo esc_attr($wpjobportal_legacy['folder']); ?>">
                            <?php if ($wpjobportal_legacy['action'] === 'install') { ?>
                                <button type="submit" class="wjp-licences-btn is-primary" name="wpjobportal_licence_action" value="install"
                                        data-busy="<?php echo esc_attr__('Installing…', 'wp-job-portal'); ?>">
                                    <?php
                                    /* translators: %s: product name. */
                                    echo esc_html(sprintf(__('Install %s', 'wp-job-portal'), $wpjobportal_legacy['product']));
                                    ?>
                                </button>
                            <?php } else { ?>
                                <button type="submit" class="wjp-licences-btn is-primary" name="wpjobportal_licence_action" value="activate_addon"
                                        data-busy="<?php echo esc_attr__('Activating…', 'wp-job-portal'); ?>">
                                    <?php
                                    /* translators: %s: product name. */
                                    echo esc_html(sprintf(__('Activate %s', 'wp-job-portal'), $wpjobportal_legacy['product']));
                                    ?>
                                </button>
                            <?php } ?>
                        </form>
                    <?php } else { ?>
                        <p>
                            <?php
                            echo esc_html($wpjobportal_legacy['state'] === 'staff'
                                /* translators: %s: product name. */
                                ? sprintf(__('This screen still runs on an old add-on from before version 2.7.0. Ask a site administrator to add %s. Until then you can keep using the old screen.', 'wp-job-portal'), $wpjobportal_legacy['product'])
                                /* translators: %s: product name. */
                                : sprintf(__('This screen still runs on an old add-on from before version 2.7.0. %s cannot be added with this licence right now; check the licence below. Until then you can keep using the old screen.', 'wp-job-portal'), $wpjobportal_legacy['product']));
                            ?>
                        </p>
                        <p>
                            <a class="wjp-licences-link" href="<?php echo esc_url($wpjobportal_legacy['continue']); ?>">
                                <?php
                                /* translators: %s: admin screen name, e.g. Cover Letters. */
                                echo esc_html(sprintf(__('Continue to %s', 'wp-job-portal'), $wpjobportal_legacy['title']));
                                ?>
                            </a>
                        </p>
                    <?php } ?>
                </div>
            <?php } ?>

            <?php if ($wpjobportal_notice !== null) { ?>
                <div class="wjp-licences-notice is-<?php echo esc_attr($wpjobportal_notice[0]); ?>" role="status">
                    <?php if (is_array($wpjobportal_notice[1]) && count($wpjobportal_notice[1]) > 1) { ?>
                        <ul>
                            <?php foreach ($wpjobportal_notice[1] as $wpjobportal_line) { ?>
                                <li><?php echo esc_html($wpjobportal_line); ?></li>
                            <?php } ?>
                        </ul>
                    <?php } else { ?>
                        <p><?php echo esc_html(is_array($wpjobportal_notice[1]) ? reset($wpjobportal_notice[1]) : $wpjobportal_notice[1]); ?></p>
                    <?php } ?>
                </div>
            <?php } ?>

            <section class="wjp-licences-key" aria-labelledby="wjp-licences-key-title">
                <div class="wjp-licences-key-head">
                    <div>
                        <h2 id="wjp-licences-key-title"><?php echo esc_html__('Your licence', 'wp-job-portal'); ?></h2>
                        <p><?php echo esc_html__('One key covers every product in the plan you bought.', 'wp-job-portal'); ?></p>
                    </div>
                    <span class="wjp-licences-chip is-<?php echo esc_attr($wpjobportal_chip[0]); ?>"><?php echo esc_html($wpjobportal_chip[1]); ?></span>
                </div>

                <?php if ($wpjobportal_key !== '') { ?>
                    <dl class="wjp-licences-facts">
                        <div>
                            <dt><?php echo esc_html__('Key', 'wp-job-portal'); ?></dt>
                            <dd><code><?php echo esc_html($wpjobportal_masked); ?></code></dd>
                        </div>
                        <?php if (!empty($wpjobportal_state['plan'])) { ?>
                            <div>
                                <dt><?php echo esc_html__('Plan', 'wp-job-portal'); ?></dt>
                                <dd><?php echo esc_html($wpjobportal_state['plan']); ?></dd>
                            </div>
                        <?php } ?>
                        <div>
                            <dt><?php echo esc_html__('Valid until', 'wp-job-portal'); ?></dt>
                            <dd><?php echo $wpjobportal_expires !== '' ? esc_html($wpjobportal_expires) : '&mdash;'; ?></dd>
                        </div>
                        <?php if ($wpjobportal_kind !== '') { ?>
                            <div>
                                <dt><?php echo esc_html__('This site counts as', 'wp-job-portal'); ?></dt>
                                <dd>
                                    <?php echo esc_html(ucfirst($wpjobportal_kind)); ?>
                                    <?php if ($wpjobportal_seats !== '') { ?>
                                        <span class="wjp-licences-sub"><?php echo esc_html($wpjobportal_seats); ?></span>
                                    <?php } ?>
                                </dd>
                            </div>
                        <?php } ?>
                    </dl>
                <?php } ?>

                <form method="post" action="" data-wpjp-no-pending class="wjp-licences-form">
                    <?php wp_nonce_field('wpjobportal_save_licence', 'wpjobportal_licence_nonce'); ?>

                    <?php if ($wpjobportal_locked) { ?>
                        <p class="wjp-licences-hint">
                            <?php echo esc_html__('This key is set in wp-config.php, so it cannot be changed from here. Edit the WPJOBPORTAL_LICENCE_KEY constant to change it.', 'wp-job-portal'); ?>
                        </p>
                    <?php } elseif ($wpjobportal_key === '') { ?>
                        <label class="wjp-licences-label" for="wpjobportal_licence_key"><?php echo esc_html__('Licence key', 'wp-job-portal'); ?></label>
                        <div class="wjp-licences-keyrow">
                            <input type="text" class="wjp-licences-input" id="wpjobportal_licence_key" name="wpjobportal_licence_key"
                                   value="" placeholder="XXXXX-XXXXX-XXXXX" autocomplete="off" spellcheck="false" required>
                            <button type="submit" class="wjp-licences-btn is-primary" name="wpjobportal_licence_action" value="save"
                                    data-busy="<?php echo esc_attr__('Activating…', 'wp-job-portal'); ?>">
                                <?php echo esc_html__('Save and activate', 'wp-job-portal'); ?>
                            </button>
                        </div>
                        <p class="wjp-licences-hint">
                            <?php
                            printf(
                                /* translators: %s: the wp-config.php constant name. */
                                esc_html__('It is sent only to the licence server. You can also set it in wp-config.php as %s, which keeps it out of the database and out of backups.', 'wp-job-portal'),
                                '<code>WPJOBPORTAL_LICENCE_KEY</code>'
                            );
                            ?>
                        </p>
                    <?php } ?>

                    <?php if ($wpjobportal_key !== '') { ?>
                        <div class="wjp-licences-actions">
                            <?php if ($wpjobportal_reason === 'deactivated') { ?>
                                <button type="submit" class="wjp-licences-btn is-primary" name="wpjobportal_licence_action" value="activate"
                                        data-busy="<?php echo esc_attr__('Activating…', 'wp-job-portal'); ?>">
                                    <?php echo esc_html__('Activate this site', 'wp-job-portal'); ?>
                                </button>
                            <?php } else { ?>
                                <button type="submit" class="wjp-licences-btn" name="wpjobportal_licence_action" value="refresh"
                                        data-busy="<?php echo esc_attr__('Checking…', 'wp-job-portal'); ?>">
                                    <?php echo esc_html__('Check again', 'wp-job-portal'); ?>
                                </button>
                            <?php } ?>
                            <?php if ($wpjobportal_active) { ?>
                                <button type="submit" class="wjp-licences-btn is-link-danger" name="wpjobportal_licence_action" value="deactivate"
                                        data-busy="<?php echo esc_attr__('Releasing…', 'wp-job-portal'); ?>"
                                        data-confirm="<?php echo esc_attr__('Release this site from the licence? Installed products keep working, but installs and updates stop until it is activated again.', 'wp-job-portal'); ?>">
                                    <?php echo esc_html__('Release this site', 'wp-job-portal'); ?>
                                </button>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </form>

                <?php if ($wpjobportal_key !== '' && !$wpjobportal_locked) { ?>
                    <?php // A form of its own, so Enter in the key field saves the key rather than pressing the first button above. ?>
                    <form method="post" action="" data-wpjp-no-pending class="wjp-licences-form">
                        <?php wp_nonce_field('wpjobportal_save_licence', 'wpjobportal_licence_nonce'); ?>
                            <details class="wjp-licences-replace">
                                <summary><?php echo esc_html__('Replace key', 'wp-job-portal'); ?></summary>
                                <label class="wjp-licences-label" for="wpjobportal_licence_key"><?php echo esc_html__('New licence key', 'wp-job-portal'); ?></label>
                                <div class="wjp-licences-keyrow">
                                    <input type="text" class="wjp-licences-input" id="wpjobportal_licence_key" name="wpjobportal_licence_key"
                                           value="" placeholder="<?php echo esc_attr($wpjobportal_masked); ?>" autocomplete="off" spellcheck="false">
                                    <button type="submit" class="wjp-licences-btn is-primary" name="wpjobportal_licence_action" value="save"
                                            data-busy="<?php echo esc_attr__('Activating…', 'wp-job-portal'); ?>">
                                        <?php echo esc_html__('Save and activate', 'wp-job-portal'); ?>
                                    </button>
                                </div>
                                <p class="wjp-licences-hint"><?php echo esc_html__('The current key releases this site first, so its slot is given back.', 'wp-job-portal'); ?></p>
                            </details>
                    </form>
                <?php } ?>
            </section>

            <section class="wjp-licences-products" aria-labelledby="wjp-licences-products-title">
                <div class="wjp-licences-products-head">
                    <div>
                        <h2 id="wjp-licences-products-title"><?php echo esc_html__('Add-ons', 'wp-job-portal'); ?></h2>
                        <p><?php echo esc_html__('Install and update what your licence covers, straight from here.', 'wp-job-portal'); ?></p>
                    </div>
                    <?php if ($wpjobportal_installable > 1) { ?>
                        <form method="post" action="" data-wpjp-no-pending>
                            <?php wp_nonce_field('wpjobportal_save_licence', 'wpjobportal_licence_nonce'); ?>
                            <button type="submit" class="wjp-licences-btn is-primary" name="wpjobportal_licence_action" value="install_all"
                                    data-busy="<?php echo esc_attr__('Installing…', 'wp-job-portal'); ?>">
                                <?php
                                /* translators: %d: number of products. */
                                echo esc_html(sprintf(__('Install all (%d)', 'wp-job-portal'), $wpjobportal_installable));
                                ?>
                            </button>
                        </form>
                    <?php } ?>
                </div>

                <div class="wjp-licences-grid">
                    <?php foreach ($wpjobportal_cards as $wpjobportal_card) { ?>
                        <article class="wjp-licences-card is-<?php echo esc_attr($wpjobportal_card['tone']); ?>" data-product="<?php echo esc_attr($wpjobportal_card['slug']); ?>">
                            <div class="wjp-licences-card-head">
                                <h3><?php echo esc_html($wpjobportal_card['label']); ?></h3>
                                <?php if ($wpjobportal_card['version'] !== '') { ?>
                                    <span class="wjp-licences-chip is-<?php echo $wpjobportal_card['active'] ? 'success' : 'neutral'; ?>">
                                        <?php echo $wpjobportal_card['active'] ? esc_html__('Active', 'wp-job-portal') : esc_html__('Inactive', 'wp-job-portal'); ?>
                                    </span>
                                <?php } elseif ($wpjobportal_card['action'] === 'plans') { ?>
                                    <span class="wjp-licences-chip is-neutral"><?php echo esc_html__('Not in your licence', 'wp-job-portal'); ?></span>
                                <?php } elseif (in_array($wpjobportal_card['slug'], $wpjobportal_entitled, true)) { ?>
                                    <span class="wjp-licences-chip is-primary"><?php echo esc_html__('In your licence', 'wp-job-portal'); ?></span>
                                <?php } ?>
                            </div>
                            <p class="wjp-licences-card-summary"><?php echo esc_html($wpjobportal_card['summary']); ?></p>
                            <div class="wjp-licences-card-foot">
                                <span class="wjp-licences-card-status"><?php echo esc_html($wpjobportal_card['status']); ?></span>

                                <?php if (in_array($wpjobportal_card['action'], array('install', 'update', 'activate'), true)) {
                                    $wpjobportal_labels = array(
                                        'install' => array(__('Install', 'wp-job-portal'), __('Installing…', 'wp-job-portal'), 'install'),
                                        'update' => array(
                                            /* translators: %s: version number. */
                                            sprintf(__('Update to %s', 'wp-job-portal'), isset($wpjobportal_card['offered']) ? $wpjobportal_card['offered'] : ''),
                                            __('Updating…', 'wp-job-portal'), 'update'),
                                        'activate' => array(__('Activate', 'wp-job-portal'), __('Activating…', 'wp-job-portal'), 'activate_addon'),
                                    );
                                    $wpjobportal_button = $wpjobportal_labels[$wpjobportal_card['action']];
                                    ?>
                                    <form method="post" action="" data-wpjp-no-pending>
                                        <?php wp_nonce_field('wpjobportal_save_licence', 'wpjobportal_licence_nonce'); ?>
                                        <input type="hidden" name="wpjobportal_addon" value="<?php echo esc_attr($wpjobportal_card['slug']); ?>">
                                        <button type="submit" class="wjp-licences-btn<?php echo $wpjobportal_card['action'] === 'activate' ? '' : ' is-primary'; ?>"
                                                name="wpjobportal_licence_action" value="<?php echo esc_attr($wpjobportal_button[2]); ?>"
                                                data-busy="<?php echo esc_attr($wpjobportal_button[1]); ?>">
                                            <?php echo esc_html($wpjobportal_button[0]); ?>
                                        </button>
                                    </form>
                                <?php } elseif ($wpjobportal_card['action'] === 'plans') { ?>
                                    <a class="wjp-licences-link" href="<?php echo esc_url($wpjobportal_plans_url); ?>" target="_blank" rel="noopener noreferrer">
                                        <?php echo esc_html__('See plans', 'wp-job-portal'); ?>
                                    </a>
                                <?php } ?>
                            </div>
                            <?php if ($wpjobportal_card['note'] !== '') { ?>
                                <p class="wjp-licences-card-note"><?php echo esc_html($wpjobportal_card['note']); ?></p>
                            <?php } ?>
                        </article>
                    <?php } ?>
                </div>
            </section>

            <div class="wjp-licences-footnotes">
                <p>
                    <?php echo esc_html__('Every download is checked against the checksum published with its release before it is unpacked. A file that does not match is deleted and nothing is installed.', 'wp-job-portal'); ?>
                </p>
                <p>
                    <?php
                    printf(
                        /* translators: %s: link to the WordPress Updates screen. */
                        esc_html__('Updates also appear on %s, with automatic updates if you turn them on there.', 'wp-job-portal'),
                        '<a href="' . esc_url(admin_url('update-core.php')) . '">' . esc_html__('Dashboard → Updates', 'wp-job-portal') . '</a>'
                    );
                    ?>
                </p>
                <p>
                    <?php echo esc_html__('When a licence lapses the products you have installed keep working. Installs, updates and support stop until it is renewed, and none of your data is removed.', 'wp-job-portal'); ?>
                </p>
            </div>

        </div>
        </div>
    </div>
</div>
<?php
// One submission at a time, and the button says what is happening: an install
// can take a while, and a second click would start a second one. Buttons are
// marked busy rather than disabled, because a disabled button is left out of
// the form and the server would not know which action was asked for.
wp_enqueue_script('wpjobportal-licences');
wp_add_inline_script('wpjobportal-licences', <<<'JS'
document.querySelectorAll('.wjp-licences form').forEach(function (form) {
    form.addEventListener('submit', function (event) {
        var button = event.submitter;
        if (button && button.dataset.confirm && !window.confirm(button.dataset.confirm)) {
            event.preventDefault();
            return;
        }
        if (document.documentElement.dataset.wjpLicencesBusy) {
            event.preventDefault();
            return;
        }
        document.documentElement.dataset.wjpLicencesBusy = '1';
        document.querySelectorAll('.wjp-licences-btn').forEach(function (other) {
            other.setAttribute('aria-disabled', 'true');
        });
        if (button && button.dataset.busy) {
            button.classList.add('is-busy');
            button.textContent = button.dataset.busy;
        }
    });
});
JS
);
