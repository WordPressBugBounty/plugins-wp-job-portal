<?php
/**
 * Colours an administrator picks (job types) drawn so they stay readable.
 *
 * A job type colour is used two ways: as a badge background behind white
 * text, and as text on a white card. Both need the colour itself to reach
 * 4.5:1 against white (WCAG AA for normal text). The seeded Full-Time
 * colour #00abfa reached 2.6:1. Rather than change what the administrator
 * saved, the colour is darkened on output only as far as it has to be, so
 * the hue stays recognisable and a colour that already passes is untouched.
 *
 * @package WP_Job_Portal
 */

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

class WPJOBPORTAL_Color {

    const MIN_CONTRAST = 4.5;

    /**
     * @param string $wpjobportal_color A #rgb or #rrggbb colour; anything else is returned unchanged.
     * @return string The same colour, or a darker shade of it that reads on white.
     */
    public static function on_white($wpjobportal_color) {
        static $wpjobportal_cache = array();
        $wpjobportal_color = trim((string) $wpjobportal_color);
        if (isset($wpjobportal_cache[$wpjobportal_color])) {
            return $wpjobportal_cache[$wpjobportal_color];
        }
        $wpjobportal_rgb = self::to_rgb($wpjobportal_color);
        if (null === $wpjobportal_rgb) {
            return $wpjobportal_cache[$wpjobportal_color] = $wpjobportal_color;
        }
        if (self::contrast_with_white($wpjobportal_rgb) >= self::MIN_CONTRAST) {
            return $wpjobportal_cache[$wpjobportal_color] = $wpjobportal_color;
        }
        // Mix towards black in small steps; the first shade that passes wins.
        for ($wpjobportal_keep = 0.98; $wpjobportal_keep > 0; $wpjobportal_keep -= 0.02) {
            $wpjobportal_shade = array();
            foreach ($wpjobportal_rgb as $wpjobportal_channel) {
                $wpjobportal_shade[] = (int) round($wpjobportal_channel * $wpjobportal_keep);
            }
            if (self::contrast_with_white($wpjobportal_shade) >= self::MIN_CONTRAST) {
                break;
            }
        }
        $wpjobportal_out = sprintf('#%02x%02x%02x', $wpjobportal_shade[0], $wpjobportal_shade[1], $wpjobportal_shade[2]);
        $wpjobportal_out = (string) apply_filters('wpjobportal_readable_color', $wpjobportal_out, $wpjobportal_color);
        return $wpjobportal_cache[$wpjobportal_color] = $wpjobportal_out;
    }

    /**
     * @param array $wpjobportal_rgb Three 0-255 channels.
     * @return float
     */
    public static function contrast_with_white($wpjobportal_rgb) {
        $wpjobportal_lum = 0.0;
        $wpjobportal_weights = array(0.2126, 0.7152, 0.0722);
        foreach ($wpjobportal_rgb as $wpjobportal_i => $wpjobportal_channel) {
            $wpjobportal_c = $wpjobportal_channel / 255;
            $wpjobportal_c = $wpjobportal_c <= 0.03928 ? $wpjobportal_c / 12.92 : pow(($wpjobportal_c + 0.055) / 1.055, 2.4);
            $wpjobportal_lum += $wpjobportal_weights[$wpjobportal_i] * $wpjobportal_c;
        }
        return 1.05 / ($wpjobportal_lum + 0.05);
    }

    /**
     * @param string $wpjobportal_color
     * @return array|null
     */
    private static function to_rgb($wpjobportal_color) {
        if (!preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $wpjobportal_color, $wpjobportal_m)) {
            return null;
        }
        $wpjobportal_hex = $wpjobportal_m[1];
        if (3 === strlen($wpjobportal_hex)) {
            $wpjobportal_hex = $wpjobportal_hex[0] . $wpjobportal_hex[0] . $wpjobportal_hex[1] . $wpjobportal_hex[1] . $wpjobportal_hex[2] . $wpjobportal_hex[2];
        }
        return array(hexdec(substr($wpjobportal_hex, 0, 2)), hexdec(substr($wpjobportal_hex, 2, 2)), hexdec(substr($wpjobportal_hex, 4, 2)));
    }
}
