<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

if (class_exists('WPJOBPORTAL_AI_Policy')) {
    return;
}

/**
 * The one switch every AI button asks first, what is scrubbed from text before
 * it leaves the site, and the journal of who changed either. (2.9.0)
 *
 * This class can only refuse. "Enabled" does not mean an engine is set up or a
 * user may run a task; those are asked by their owners. What must never happen
 * is the reverse: AI switched off and something still going out.
 *
 * Pattern: JS Help Desk's JSSTaipolicy, without the lanes (every engine here is
 * a hosted one).
 */
class WPJOBPORTAL_AI_Policy {

    const OPT_MASTER  = 'wpjobportal_ai_master';
    const OPT_REDACT  = 'wpjobportal_ai_redact';
    const OPT_JOURNAL = 'wpjobportal_ai_journal';
    const OPT_NOTICE  = 'wpjobportal_ai_disclosure';
    const JOURNAL_LIMIT = 60;

    /** On by default: 2.8.0 sites already had AI buttons, and an upgrade must not remove them silently. */
    public static function enabled() {
        return ((int) get_option(self::OPT_MASTER, 1) === 1);
    }

    public static function setMaster($on) {
        $on = $on ? 1 : 0;
        if ((self::enabled() ? 1 : 0) !== $on) {
            self::record($on ? 'master-on' : 'master-off', '');
        }
        update_option(self::OPT_MASTER, $on, false);
        return $on;
    }

    /* ------------------------------------------------------------------ *
     * What gets sent
     * ------------------------------------------------------------------ */

    /**
     * How hard to scrub. A job board's text is resumes and applications, so the
     * default also removes contact details: an AI writing a skills section or a
     * cover letter never needs the candidate's e-mail address or phone number.
     */
    public static function redactionModes() {
        return array(
            'credentials' => array(
                'label' => __('Credentials only', 'wp-job-portal'),
                'blurb' => __('Anything shaped like a password or an API key is removed.', 'wp-job-portal'),
            ),
            'contacts' => array(
                'label' => __('Credentials and contact details (recommended)', 'wp-job-portal'),
                'blurb' => __('Also removes e-mail addresses, phone numbers and IP addresses.', 'wp-job-portal'),
            ),
            'strict' => array(
                'label' => __('Strict', 'wp-job-portal'),
                'blurb' => __('Also removes card-shaped numbers and long runs of digits.', 'wp-job-portal'),
            ),
        );
    }

    public static function redactionMode() {
        $mode = (string) get_option(self::OPT_REDACT, 'contacts');
        $modes = self::redactionModes();
        return isset($modes[$mode]) ? $mode : 'contacts';
    }

    public static function setRedactionMode($mode) {
        $modes = self::redactionModes();
        if (!isset($modes[$mode])) {
            return false;
        }
        if (self::redactionMode() !== $mode) {
            self::record('redaction', $mode);
        }
        update_option(self::OPT_REDACT, $mode, false);
        return true;
    }

    /**
     * Scrub text about to be sent to a model. Labelled secrets keep their label
     * so the model can still see that one was mentioned.
     */
    public static function redact($text) {
        $text = (string) $text;
        if ($text === '') {
            return $text;
        }
        $text = preg_replace(
            '/\b(sk-[A-Za-z0-9_\-]{16,}|zw_[A-Za-z0-9_]{16,}|ghp_[A-Za-z0-9]{20,}|xox[baprs]-[A-Za-z0-9\-]{10,}|AKIA[0-9A-Z]{16}|AIza[0-9A-Za-z_\-]{30,})\b/',
            '[redacted key]',
            $text
        );
        $text = preg_replace(
            '/\b(pass(?:word|phrase)?|secret|api[ _-]?key|token|licen[cs]e[ _-]?key)\b(\s*[:=]\s*)[^\s,;.]+/i',
            '$1$2[redacted]',
            $text
        );
        $mode = self::redactionMode();
        if ($mode === 'contacts' || $mode === 'strict') {
            $text = preg_replace('/[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}/', '[redacted e-mail]', $text);
            $text = preg_replace('/\b(?:\d{1,3}\.){3}\d{1,3}\b/', '[redacted ip]', $text);
            $text = preg_replace('/\+?\d[\d\s\-().]{8,}\d/', '[redacted number]', $text);
        }
        if ($mode === 'strict') {
            $text = preg_replace('/\b(?:\d[ \-]?){13,19}\b/', '[redacted card]', $text);
            $text = preg_replace('/\b\d{9,}\b/', '[redacted digits]', $text);
        }
        return (string) apply_filters('wpjobportal_ai_redact', $text, $mode);
    }

    /* ------------------------------------------------------------------ *
     * What people are told
     * ------------------------------------------------------------------ */

    /** off | noengine | ready, with one sentence every screen shows. */
    public static function status() {
        if (!self::enabled()) {
            return array('state' => 'off', 'label' => __('AI is switched off. Nothing is sent anywhere.', 'wp-job-portal'));
        }
        if (!class_exists('WPJOBPORTAL_AI_Engine') || !WPJOBPORTAL_AI_Engine::configured()) {
            return array('state' => 'noengine', 'label' => __('AI is on, but no engine is set up yet.', 'wp-job-portal'));
        }
        return array('state' => 'ready', 'label' => sprintf(
            /* translators: %s: the AI engine, for example "Zywrap" */
            __('AI is on. %s writes the text, after contact details are removed.', 'wp-job-portal'),
            WPJOBPORTAL_AI_Engine::currentLabel()
        ));
    }

    /**
     * The line shown under an AI button, telling the person that text they ask
     * for is written by AI and sent to the engine. Editable; never empty.
     */
    public static function disclosure() {
        $text = trim((string) get_option(self::OPT_NOTICE, ''));
        if ($text === '') {
            $text = __('Written with AI. Check it before you save; what you enter here is sent to our AI provider.', 'wp-job-portal');
        }
        return (string) apply_filters('wpjobportal_ai_disclosure', $text);
    }

    public static function setDisclosure($text) {
        update_option(self::OPT_NOTICE, sanitize_text_field((string) $text), false);
    }

    /* ------------------------------------------------------------------ *
     * The journal: settings changes only, never text
     * ------------------------------------------------------------------ */

    public static function record($what, $subject) {
        $journal = get_option(self::OPT_JOURNAL, array());
        if (!is_array($journal)) {
            $journal = array();
        }
        array_unshift($journal, array(
            'what'    => sanitize_key((string) $what),
            'subject' => sanitize_text_field((string) $subject),
            'user'    => (int) get_current_user_id(),
            'when'    => time(),
        ));
        update_option(self::OPT_JOURNAL, array_slice($journal, 0, self::JOURNAL_LIMIT), false);
    }

    public static function journal() {
        $journal = get_option(self::OPT_JOURNAL, array());
        return is_array($journal) ? $journal : array();
    }

    public static function describe($entry) {
        $subject = isset($entry['subject']) ? (string) $entry['subject'] : '';
        switch (isset($entry['what']) ? $entry['what'] : '') {
            case 'master-on':
                return __('AI switched on', 'wp-job-portal');
            case 'master-off':
                return __('AI switched off', 'wp-job-portal');
            case 'redaction':
                $modes = self::redactionModes();
                /* translators: %s: a redaction level */
                return sprintf(__('Redaction set to %s', 'wp-job-portal'), isset($modes[$subject]) ? $modes[$subject]['label'] : $subject);
            case 'engine':
                /* translators: %s: an AI engine */
                return sprintf(__('Engine set to %s', 'wp-job-portal'), $subject);
            case 'key':
                /* translators: %s: an AI engine */
                return sprintf(__('Key saved for %s', 'wp-job-portal'), $subject);
            case 'key-forget':
                /* translators: %s: an AI engine */
                return sprintf(__('Key removed for %s', 'wp-job-portal'), $subject);
            case 'features':
                return __('AI features changed', 'wp-job-portal');
            case 'budget':
                return __('AI limits changed', 'wp-job-portal');
            case 'zywrap-connect':
                return ($subject !== '')
                    /* translators: %s: the e-mail address of the Zywrap account */
                    ? sprintf(__('Connected to Zywrap as %s', 'wp-job-portal'), $subject)
                    : __('Connected to Zywrap', 'wp-job-portal');
            case 'zywrap-disconnect':
                return __('Disconnected from Zywrap', 'wp-job-portal');
            case 'credits-claim':
                return __('Licence credits claimed for the connected Zywrap account', 'wp-job-portal');
        }
        return __('Setting changed', 'wp-job-portal');
    }
}
