<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Translations: which languages WP Job Portal is installed in, and the files
 * to download. The installing itself happens without anyone opening this
 * screen (WPJOBPORTAL_Translations).
 */


$wpjobportal_tr = WPJOBPORTAL_Translations::rows();
$wpjobportal_site = array_filter($wpjobportal_tr['rows'], function ($wpjobportal_row) {
    return $wpjobportal_row['site'];
});
$wpjobportal_offered = array_filter($wpjobportal_tr['rows'], function ($wpjobportal_row) {
    return !empty($wpjobportal_row['entry']) && $wpjobportal_row['entry']['locale'] === $wpjobportal_row['locale'];
});
$wpjobportal_can_install = current_user_can('install_languages');
$wpjobportal_can_write = WPJOBPORTAL_Translations::can_write();
$wpjobportal_manifest = get_site_transient(WPJOBPORTAL_Translations::T_MANIFEST);
$wpjobportal_result = sanitize_key((string) WPJOBPORTALrequest::getVar('wpjobportal_tr', 'get', '')); // the outcome of a nonce-checked action, only shown
$wpjobportal_trl = sanitize_text_field((string) WPJOBPORTALrequest::getVar('wpjobportal_trl', 'get', '')); // which language it installed, only shown
$wpjobportal_date = function ($wpjobportal_version) {
    $wpjobportal_time = strtotime((string) $wpjobportal_version);
    return $wpjobportal_time ? wp_date(get_option('date_format'), $wpjobportal_time) : (string) $wpjobportal_version;
};
// Each status as a chip: tone and words.
$wpjobportal_chips = array(
    'current' => array('success', __('Installed', 'wp-job-portal')),
    'update' => array('warning', __('Update available', 'wp-job-portal')),
    'missing' => array('warning', __('Not installed yet', 'wp-job-portal')),
    'failed' => array('danger', __('Could not be installed', 'wp-job-portal')),
    'own' => array('success', __('Your own file', 'wp-job-portal')),
    'bundled' => array('primary', __('Included with the plugin', 'wp-job-portal')),
    'none' => array('neutral', __('No translation yet', 'wp-job-portal')),
);
?>
<div id="wpjobportaladmin-wrapper">
    <div id="wpjobportaladmin-leftmenu">
        <?php WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <?php WPJOBPORTALincluder::getTemplate('templates/admin/pagetitle', array('wpjobportal_module' => 'wpjobportal', 'wpjobportal_layouts' => 'translations')); ?>
        <div id="wpjobportal-admin-wrapper" class="p0 bg-n bs-n">
        <div class="wjp-licences wjp-translations">

            <?php if (($wpjobportal_result === 'added' || $wpjobportal_result === 'addednowp') && $wpjobportal_trl !== '') { ?>
                <div class="wjp-licences-notice is-success" role="status">
                    <p>
                        <?php
                        /* translators: %s: a language name, e.g. Français */
                        echo esc_html(sprintf(__('%s is installed.', 'wp-job-portal'), WPJOBPORTAL_Translations::language_name($wpjobportal_trl, WPJOBPORTAL_Translations::entry_for($wpjobportal_trl, $wpjobportal_manifest), true)));
                        ?>
                        <?php if ($wpjobportal_result === 'addednowp') { ?>
                            <?php echo esc_html__('WordPress itself has no language pack for it, so it cannot be chosen as the site language yet.', 'wp-job-portal'); ?>
                        <?php } ?>
                    </p>
                    <?php if ($wpjobportal_result === 'added') { ?>
                        <p class="wjp-translations-actions">
                            <a class="wjp-licences-btn" href="<?php echo esc_url(WPJOBPORTAL_Translations::action_url('useme', $wpjobportal_trl)); ?>"><?php echo esc_html__('Use it for me', 'wp-job-portal'); ?></a>
                            <a class="wjp-licences-btn" href="<?php echo esc_url(admin_url('options-general.php#WPLANG')); ?>"><?php echo esc_html__('Use it for the whole site', 'wp-job-portal'); ?></a>
                        </p>
                    <?php } ?>
                </div>
            <?php } elseif ($wpjobportal_result === 'useme') { ?>
                <div class="wjp-licences-notice is-success" role="status"><p><?php echo esc_html__('Your own language is changed. The rest of the site keeps its language.', 'wp-job-portal'); ?></p></div>
            <?php } elseif ($wpjobportal_result === 'done') { ?>
                <div class="wjp-licences-notice is-success" role="status"><p><?php echo esc_html__('Translations are up to date.', 'wp-job-portal'); ?></p></div>
            <?php } elseif ($wpjobportal_result === 'failed') { ?>
                <div class="wjp-licences-notice is-error" role="status"><p><?php echo esc_html__('Not every translation could be installed. The reason is shown beside it below.', 'wp-job-portal'); ?></p></div>
            <?php } ?>

            <?php if ($wpjobportal_tr['listerror'] !== '') { ?>
                <div class="wjp-licences-notice is-warning"><p><?php echo esc_html($wpjobportal_tr['listerror']); ?></p></div>
            <?php } ?>
            <?php if (!$wpjobportal_can_install) { ?>
                <div class="wjp-licences-notice is-info"><p><?php echo esc_html__('Only a site administrator can install languages. You can see what is installed here.', 'wp-job-portal'); ?></p></div>
            <?php } elseif (!$wpjobportal_can_write) { ?>
                <div class="wjp-licences-notice is-warning"><p><?php echo esc_html__('This site does not allow plugins to install language files (DISALLOW_FILE_MODS). Download the files below and upload them to wp-content/languages/plugins/.', 'wp-job-portal'); ?></p></div>
            <?php } elseif (!WPJOBPORTAL_Translations::automatic()) { ?>
                <div class="wjp-licences-notice is-info"><p><?php echo esc_html__('Automatic installation is switched off on this site (WPJOBPORTAL_TRANSLATIONS_AUTO). Use Install now or Update now to install a language.', 'wp-job-portal'); ?></p></div>
            <?php } ?>

            <section class="wjp-licences-products wjp-translations-box<?php echo in_array('failed', wp_list_pluck($wpjobportal_site, 'status'), true) ? ' is-warning' : ''; ?>">
                <div class="wjp-licences-products-head">
                    <div>
                        <h2><?php echo esc_html__('This site\'s languages', 'wp-job-portal'); ?></h2>
                        <p><?php echo esc_html__('WP Job Portal installs the translation for your site\'s language, and for every language WordPress has installed for your users, by itself - when it is installed or updated, when the site language changes, and once a day for improvements.', 'wp-job-portal'); ?></p>
                    </div>
                </div>

                <?php if (empty($wpjobportal_site)) { ?>
                    <p class="wjp-translations-note"><?php echo esc_html__('This site is in English, so no translation is needed. To use another language, change the Site Language under Settings > General.', 'wp-job-portal'); ?></p>
                <?php } else { ?>
                    <table class="wjp-translations-table">
                        <thead><tr>
                            <th scope="col"><?php echo esc_html__('Language', 'wp-job-portal'); ?></th>
                            <th scope="col"><?php echo esc_html__('Status', 'wp-job-portal'); ?></th>
                            <th scope="col"><?php echo esc_html__('Translation', 'wp-job-portal'); ?></th>
                            <th scope="col"><span class="screen-reader-text"><?php echo esc_html__('Action', 'wp-job-portal'); ?></span></th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($wpjobportal_site as $wpjobportal_row) {
                            $wpjobportal_entry = $wpjobportal_row['entry'];
                            $wpjobportal_chip = isset($wpjobportal_chips[$wpjobportal_row['status']]) ? $wpjobportal_chips[$wpjobportal_row['status']] : $wpjobportal_chips['none'];
                            ?>
                            <tr>
                                <td data-label="<?php echo esc_attr__('Language', 'wp-job-portal'); ?>">
                                    <?php echo esc_html($wpjobportal_row['name']); ?>
                                    <span class="wjp-translations-code"><?php echo esc_html($wpjobportal_row['locale']); ?></span>
                                </td>
                                <td data-label="<?php echo esc_attr__('Status', 'wp-job-portal'); ?>">
                                    <span class="wjp-licences-chip is-<?php echo esc_attr($wpjobportal_chip[0]); ?>"><?php echo esc_html($wpjobportal_chip[1]); ?></span>
                                    <?php if ($wpjobportal_row['error'] !== '' && $wpjobportal_row['status'] !== 'current') { ?>
                                        <span class="wjp-translations-error"><?php echo esc_html($wpjobportal_row['error']); ?></span>
                                    <?php } ?>
                                </td>
                                <td data-label="<?php echo esc_attr__('Translation', 'wp-job-portal'); ?>">
                                    <?php
                                    if ($wpjobportal_entry) {
                                        echo esc_html(sprintf(
                                            /* translators: 1: a percentage, 2: a date */
                                            __('%1$d%% translated, updated %2$s', 'wp-job-portal'),
                                            $wpjobportal_entry['percent'],
                                            $wpjobportal_date($wpjobportal_entry['version'])
                                        ));
                                        if ($wpjobportal_entry['locale'] !== $wpjobportal_row['locale']) {
                                            echo '<span class="wjp-translations-sub">' . esc_html(sprintf(
                                                /* translators: %s: a language name, e.g. Français */
                                                __('Using the %s translation, the nearest there is.', 'wp-job-portal'),
                                                WPJOBPORTAL_Translations::language_name($wpjobportal_entry['locale'], $wpjobportal_entry, true)
                                            )) . '</span>';
                                        }
                                        if ($wpjobportal_row['status'] === 'bundled') {
                                            echo '<span class="wjp-translations-sub">' . esc_html__('Until it is installed, the copy included with the plugin is used.', 'wp-job-portal') . '</span>';
                                        }
                                    } elseif ($wpjobportal_row['status'] === 'bundled') {
                                        echo esc_html__('The copy included with the plugin is used.', 'wp-job-portal');
                                    } elseif ($wpjobportal_row['status'] === 'own') {
                                        echo esc_html__('A file someone added to wp-content/languages/plugins/. It is used as it is.', 'wp-job-portal');
                                    } else {
                                        echo esc_html__('Nobody has translated WP Job Portal into this language yet.', 'wp-job-portal');
                                    }
                                    ?>
                                </td>
                                <td class="wjp-translations-action">
                                    <?php if ($wpjobportal_entry && $wpjobportal_can_install && $wpjobportal_can_write && $wpjobportal_row['status'] !== 'current') { ?>
                                        <a class="wjp-licences-btn is-primary" href="<?php echo esc_url(WPJOBPORTAL_Translations::action_url('install', $wpjobportal_row['locale'])); ?>">
                                            <?php echo esc_html($wpjobportal_row['status'] === 'update' ? __('Update now', 'wp-job-portal') : __('Install now', 'wp-job-portal')); ?>
                                        </a>
                                    <?php } elseif (get_user_locale() === $wpjobportal_row['locale']) { ?>
                                        <span class="wjp-translations-sub"><?php echo esc_html__('Your language', 'wp-job-portal'); ?></span>
                                    <?php } elseif (in_array($wpjobportal_row['locale'], get_available_languages(), true) && $wpjobportal_can_install) { ?>
                                        <a class="wjp-licences-btn" href="<?php echo esc_url(WPJOBPORTAL_Translations::action_url('useme', $wpjobportal_row['locale'])); ?>"><?php echo esc_html__('Use it for me', 'wp-job-portal'); ?></a>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>

                <div class="wjp-translations-actions">
                    <?php if ($wpjobportal_can_install) { ?>
                        <a class="wjp-licences-btn" href="<?php echo esc_url(WPJOBPORTAL_Translations::action_url('sync')); ?>"><?php echo esc_html__('Check for updates', 'wp-job-portal'); ?></a>
                        <?php if (get_user_locale() !== 'en_US') { ?>
                            <a class="wjp-licences-btn" href="<?php echo esc_url(WPJOBPORTAL_Translations::action_url('useme', 'en_US')); ?>"><?php echo esc_html__('Use English for me', 'wp-job-portal'); ?></a>
                        <?php } ?>
                    <?php } ?>
                    <?php if ($wpjobportal_tr['checked']) { ?>
                        <span class="wjp-translations-sub"><?php
                            /* translators: %s: a date and time */
                            echo esc_html(sprintf(__('Last checked %s', 'wp-job-portal'), wp_date(get_option('date_format') . ' ' . get_option('time_format'), $wpjobportal_tr['checked'])));
                        ?></span>
                    <?php } ?>
                </div>
            </section>

            <?php if (!empty($wpjobportal_offered)) { ?>
                <section class="wjp-licences-products wjp-translations-box">
                    <div class="wjp-licences-products-head">
                        <div>
                            <h2><?php echo esc_html__('All translations', 'wp-job-portal'); ?></h2>
                            <p><?php echo esc_html__('Install adds a language to this site - WordPress\'s own and WP Job Portal\'s - so it can be chosen for the whole site or for one person. The files are for a site that cannot download by itself, or to change the wording: the .po file is the one you edit (with Poedit or Loco Translate), the .mo file is what WordPress reads. Both go in wp-content/languages/plugins/.', 'wp-job-portal'); ?></p>
                        </div>
                    </div>
                    <table class="wjp-translations-table">
                        <thead><tr>
                            <th scope="col"><?php echo esc_html__('Language', 'wp-job-portal'); ?></th>
                            <th scope="col"><?php echo esc_html__('Translated', 'wp-job-portal'); ?></th>
                            <th scope="col"><?php echo esc_html__('Updated', 'wp-job-portal'); ?></th>
                            <th scope="col"><?php echo esc_html__('Files', 'wp-job-portal'); ?></th>
                            <th scope="col"><span class="screen-reader-text"><?php echo esc_html__('Action', 'wp-job-portal'); ?></span></th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($wpjobportal_offered as $wpjobportal_row) {
                            $wpjobportal_entry = $wpjobportal_row['entry']; ?>
                            <tr>
                                <td data-label="<?php echo esc_attr__('Language', 'wp-job-portal'); ?>">
                                    <?php echo esc_html($wpjobportal_row['name']); ?>
                                    <span class="wjp-translations-code"><?php echo esc_html($wpjobportal_row['locale']); ?></span>
                                </td>
                                <td data-label="<?php echo esc_attr__('Translated', 'wp-job-portal'); ?>"><?php echo esc_html($wpjobportal_entry['percent'] . '%'); ?></td>
                                <td data-label="<?php echo esc_attr__('Updated', 'wp-job-portal'); ?>"><?php echo esc_html($wpjobportal_date($wpjobportal_entry['version'])); ?></td>
                                <td data-label="<?php echo esc_attr__('Files', 'wp-job-portal'); ?>" class="wjp-translations-files">
                                    <?php if ($wpjobportal_entry['po'] !== '') { ?><a href="<?php echo esc_url($wpjobportal_entry['po']); ?>" download>.po</a><?php } ?>
                                    <?php if ($wpjobportal_entry['mo'] !== '') { ?><a href="<?php echo esc_url($wpjobportal_entry['mo']); ?>" download>.mo</a><?php } ?>
                                    <a href="<?php echo esc_url($wpjobportal_entry['package']); ?>">.zip</a>
                                </td>
                                <td class="wjp-translations-action">
                                    <?php if ($wpjobportal_row['site'] && $wpjobportal_row['status'] === 'current') { ?>
                                        <span class="wjp-licences-chip is-success"><?php echo esc_html__('Installed', 'wp-job-portal'); ?></span>
                                    <?php } elseif ($wpjobportal_can_install && $wpjobportal_can_write) { ?>
                                        <a class="wjp-licences-btn is-primary" href="<?php echo esc_url(WPJOBPORTAL_Translations::action_url($wpjobportal_row['site'] ? 'install' : 'add', $wpjobportal_row['locale'])); ?>">
                                            <?php echo esc_html($wpjobportal_row['site'] && $wpjobportal_row['status'] === 'update' ? __('Update now', 'wp-job-portal') : __('Install', 'wp-job-portal')); ?>
                                        </a>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </section>
            <?php } ?>

            <section class="wjp-licences-products wjp-translations-box">
                <h2><?php echo esc_html__('Changing the wording', 'wp-job-portal'); ?></h2>
                <p class="wjp-translations-note"><?php echo esc_html__('A newer translation replaces the files in wp-content/languages/plugins/, so wording you change there is lost at the next update. To keep your own wording, save it with Loco Translate in its Custom location, which updates never touch, or switch automatic updates off by adding define(\'WPJOBPORTAL_TRANSLATIONS_AUTO\', false); to wp-config.php.', 'wp-job-portal'); ?></p>
                <p class="wjp-translations-note"><?php echo esc_html__('Your language is missing, or a word is wrong? Contact us at wpjobportal.com - corrections go into the next update for everyone.', 'wp-job-portal'); ?></p>
            </section>

        </div>
        </div>
    </div>
</div>
