<?php
if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Shared parts of the admin add/edit forms (design 4). The fields keep the
 * order set in Forms & fields; one row per field (label column + control);
 * optional fields that take room fold in their own place; a side panel holds
 * what is not a Forms & fields field (approval status, Save, Cancel).
 * Styles: the "Forms (design 4)" block of wpjobportal-admin-list.css.
 */
class WPJOBPORTAL_Admin_Form_UI {

    /**
     * Top bar and title row for a short form (a value list's add/edit form): its fields
     * stay in the form's own template, the form itself carries wjpl-form wjpl-form-simple.
     *
     * @param string $noun_title e.g. Category (for "Add New Category").
     * @param string $noun       e.g. category (for "Edit category").
     * @param string $page       The list's page slug, for the breadcrumb.
     * @param string $form_id    The form element's id, for the shared behaviour.
     */
    public static function simple_header($noun_title, $noun, $page, $form_id) {
        $id = WPJOBPORTALrequest::getVar('wpjobportalid');
        $isedit = $id !== null && $id !== '' && (int) $id > 0;
        $crumbs = WPJOBPORTAL_Admin_List_UI::menu_crumbs();
        if ($crumbs) {
            $crumbs[count($crumbs) - 1]['url'] = admin_url('admin.php?page=' . $page);
        }
        /* translators: %s: what is added, e.g. Category */
        $title = $isedit ? sprintf(__('Edit %s', 'wp-job-portal'), $noun) : sprintf(__('Add New %s', 'wp-job-portal'), $noun_title);
        $crumbs[] = array('label' => $title);
        echo '<div class="wjpl wjpl-formhead">';
        WPJOBPORTAL_Admin_List_UI::top_bar($crumbs);
        WPJOBPORTAL_Admin_List_UI::title_row($title, __('Fields marked * are required.', 'wp-job-portal'));
        echo '</div>';
        self::$simple_title = sprintf(/* translators: %s: e.g. Category */ __('%s details', 'wp-job-portal'), $noun_title);
        self::scripts($form_id);
    }

    /**
     * Top bar and title row for a short form that is not an add/edit form (e.g. Assign
     * role); use with simple_open()/simple_close() like simple_header().
     *
     * @param string $title      Page title.
     * @param string $subtitle   Line beside the title.
     * @param string $card_title Heading of the main card.
     * @param string $form_id    The form element's id, for the shared behaviour.
     * @param string $side_title Heading of the side card (default Publishing).
     */
    public static function header($title, $subtitle, $card_title, $form_id, $side_title = '') {
        $crumbs = WPJOBPORTAL_Admin_List_UI::menu_crumbs();
        $last = end($crumbs);
        // A tab page's breadcrumb already ends with its own name.
        if (!$last || strcasecmp($last['label'], $title) !== 0) {
            $crumbs[] = array('label' => $title);
        }
        echo '<div class="wjpl wjpl-formhead">';
        WPJOBPORTAL_Admin_List_UI::top_bar($crumbs);
        WPJOBPORTAL_Admin_List_UI::title_row($title, $subtitle);
        echo '</div>';
        self::$simple_title = $card_title;
        self::$side_title = $side_title;
        self::scripts($form_id);
    }

    /** Headings of a short form's main and side cards, set by simple_header() or header(). */
    private static $simple_title = '';
    private static $side_title = '';

    /**
     * Called right after a short form's <form> tag: its fields (in the template or a
     * partial) are collected so simple_close() can lay them out like the long forms.
     */
    public static function simple_open() {
        ob_start();
    }

    /**
     * Called right before </form>: the fields go in a main card, the Save/Cancel block
     * (div.wpjobportal-form-button, wherever the partial printed it) in a side card,
     * as on the job and company forms. Without that block everything stays in the card.
     */
    public static function simple_close() {
        $html = ob_get_clean();
        $acts = '';
        $start = strpos($html, '<div class="wpjobportal-form-button');
        if ($start !== false) {
            // The block ends at its matching </div>.
            $depth = 0;
            $end = false;
            if (preg_match_all('#<div\b|</div>#i', $html, $m, PREG_OFFSET_CAPTURE, $start)) {
                foreach ($m[0] as $tag) {
                    $depth += ($tag[0][1] === '/') ? -1 : 1;
                    if ($depth === 0) {
                        $end = $tag[1] + 6;
                        break;
                    }
                }
            }
            if ($end !== false) {
                $acts = substr($html, $start, $end - $start);
                $acts = preg_replace('#^<div class="wpjobportal-form-button#', '<div class="wjpl-form-acts wpjobportal-form-button', $acts);
                $html = substr($html, 0, $start) . substr($html, $end);
            }
        }
        // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- the form's own markup, escaped where it was printed; fixed SVG icons
        echo '<div class="wjpl-form-main"><section class="wjpl-card wjpl-fcard"><h2>'
            . self::icon('<path d="M4 6h16M4 12h16M4 18h10"/>') . esc_html(self::$simple_title) . '</h2><div class="wjpl-fgrid">'
            . $html . '</div></section></div>';
        if ($acts !== '') {
            echo '<aside class="wjpl-form-side"><section class="wjpl-card wjpl-fcard wjpl-publish"><h2>'
                . self::icon('<circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/>') . esc_html(self::$side_title !== '' ? self::$side_title : __('Publishing', 'wp-job-portal')) . '</h2>'
                . $acts . '</section></aside>';
        }
        // phpcs:enable
    }

    /**
     * The AI Content Generation window behind the button on description/skills fields.
     * 2.7 loaded it on the WordPress post editor only, so on these forms the button did
     * nothing. Without an API key the window says so and links to the AI writer settings.
     */
    private static function ai_writer() {
        static $done = false;
        if ($done || !function_exists('wpjobportal_load_classic_editor_assets')) {
            return;
        }
        $done = true;
        wpjobportal_load_classic_editor_assets('', true);
        // The window's markup is modules/zywrap/admin_classic_modal.php (not under tmpl/,
        // where include_file() looks), so it is included directly.
        add_action('admin_footer', function () {
            $file = WPJOBPORTAL_PLUGIN_PATH . 'modules/zywrap/admin_classic_modal.php';
            if (is_readable($file)) {
                include $file;
            }
        });
    }

    /** A card heading icon tile (one colour, like the menu). */
    public static function icon($paths) {
        return '<span class="wjpl-fcard-i" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $paths . '</svg></span>';
    }

    /**
     * One field row, or a folded row for an optional field listed in $foldable.
     *
     * @param array  $formfield From a module's form-fields template.
     * @param object $record    The record being edited (for fold summaries), or null.
     * @param array  $foldable  Field keys that start folded when not required.
     * @param array  $titles    Field key => label shown instead of the Forms & fields title.
     */
    public static function field($formfield, $record = null, $foldable = array(), $titles = array()) {
        $f = isset($formfield['wpjobportal_field']) ? $formfield['wpjobportal_field'] : null;
        $key = $f && isset($f->field) ? $f->field : '';
        if (isset($titles[$key])) {
            $formfield['title'] = $titles[$key];
        }
        if ($key !== '' && in_array($key, $foldable, true) && empty($f->required)) {
            if ($key === 'map') {
                $pinned = $record && isset($record->latitude) && trim((string) $record->latitude) !== '' && trim((string) $record->longitude) !== '';
                $summary = $pinned ? sprintf(/* translators: 1: latitude, 2: longitude */ __('Pinned at %1$s, %2$s', 'wp-job-portal'), $record->latitude, $record->longitude) : __('Not pinned yet', 'wp-job-portal');
            } else {
                $value = $record && isset($record->{$key}) ? trim(wp_strip_all_tags((string) $record->{$key})) : '';
                $summary = $value !== '' ? wp_trim_words($value, 10) : __('Not set', 'wp-job-portal');
            }
            $label = isset($formfield['title']) ? $formfield['title'] : wpjobportal::wpjobportal_getVariableValue($f->fieldtitle);
            echo '<details class="wjpl-f wjpl-fold wjpl-fold-field"><summary><span class="wjpl-fold-t">' . esc_html($label) . '</span><span class="wjpl-fold-s">' . esc_html($summary) . '</span></summary>';
            WPJOBPORTALincluder::getTemplate('templates/admin/form-field', $formfield);
            echo '</details>';
            return;
        }
        echo '<div class="wjpl-f">';
        WPJOBPORTALincluder::getTemplate('templates/admin/form-field', $formfield);
        echo '</div>';
    }

    /**
     * Behaviour shared by the forms: a map inside a fold is redrawn when it opens,
     * and leaving after the user changed something asks first. Only the user's own
     * edits count (pickers and the map set values after load): trusted input/change,
     * removing a chip, clicking the map, typing in the visual editor. Saving does not ask.
     *
     * @param string $form_id
     */
    public static function scripts($form_id) {
        self::ai_writer();
        wp_register_script('wpjobportal-inline-handle', '', array(), WPJOBPORTAL_VERSION, false);
        wp_enqueue_script('wpjobportal-inline-handle');
        wp_add_inline_script('wpjobportal-inline-handle', 'document.addEventListener("toggle", function (e) { if (e.target.open && e.target.classList && e.target.classList.contains("wjpl-fold")) { window.dispatchEvent(new Event("resize")); } }, true);'
            // Popups (user picker) live inside the form, whose container query makes it the box
            // fixed elements are placed in: moved to the body they cover the window again.
            . 'jQuery(function ($) { $("#full_background, #popup_main").appendTo(document.body); });'
            . 'jQuery(function ($) { var $f = $(' . wp_json_encode('#' . $form_id) . '), dirty = false, saving = false;'
            . ' $f.on("input change", function (e) { if (e.originalEvent && e.originalEvent.isTrusted) { dirty = true; } });'
            . ' $f.on("click", ".wpjobportal-input-delete-token-wpjobportal, #map_container", function () { dirty = true; });'
            // Saving does not ask - including forms that submit from script (the resume builder's submitresume()).
            . ' $f.on("submit", function () { saving = true; }).on("click", ".wpjobportal-form-save-btn, input[type=submit], button[type=submit], [onclick*=\\"submit\\"]", function () { saving = true; });'
            . ' var watchEditor = function (ed) { if (ed && ed.on) { ed.on("input change undo redo keyup", function (e) { if (!e || e.type !== "keyup" || (e.key && e.key.length === 1) || e.key === "Backspace" || e.key === "Delete" || e.key === "Enter") { dirty = true; } }); } };'
            . ' if (window.tinymce) { (tinymce.editors || []).forEach(watchEditor); tinymce.on("AddEditor", function (e) { watchEditor(e.editor); }); }'
            . ' $(window).on("beforeunload", function (e) { if (dirty && !saving) { e.preventDefault(); e.returnValue = ""; return ""; } }); });');
    }
}
