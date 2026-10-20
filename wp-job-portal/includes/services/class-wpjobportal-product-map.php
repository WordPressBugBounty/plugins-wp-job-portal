<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Which shipping product a customer has to own to be entitled to an add-on.
 *
 * This is deliberately a different question from the one the guards answer, and
 * conflating the two would break both:
 *
 *   - WPJOBPORTAL_Absorbed_Config_Guard asks "has this add-on been superseded?"
 *     — has something else taken its feature over, so its settings must survive
 *     being switched off. Credits has not been superseded: its code still runs
 *     and still owns every purchase the site makes.
 *
 *   - This asks "whose licence covers it?" Credits is sold as part of the
 *     Marketplace Suite, because the Suite's contents — featured jobs, featured
 *     companies, featured resumes, multi-company — are all things a site sells,
 *     and Credits is what takes the money for them. A menu and a till are one
 *     product.
 *
 * Coverage is entitlement only. Nothing here disables a feature, and nothing
 * here moves code: an add-on named as covered keeps working exactly as it did,
 * and the map decides which key it is checked against on the day licence
 * checking is switched on. That is what makes this safe to land before the
 * larger absorption work.
 *
 * @see WPJOBPORTAL_Absorbed_Config_Guard for the supersession map.
 */
class WPJOBPORTAL_Product_Map {

    /**
     * The products a licence can be held for.
     *
     * @return array Product key => label, plugin, licence slug.
     */
    public static function products() {
        return array(
            'core' => array(
                'label' => 'WP Job Portal',
                'plugin' => 'wp-job-portal',
                'licence' => '',
            ),
            'pro' => array(
                'label' => 'Pro',
                'plugin' => 'wp-job-portal-pro',
                'licence' => 'pro',
            ),
            'matching' => array(
                'label' => 'Matching Suite',
                'plugin' => 'wp-job-portal-matchingsuite',
                'licence' => 'matchingsuite',
            ),
            'recruit' => array(
                'label' => 'Recruit Suite',
                'plugin' => 'wp-job-portal-recruitsuite',
                'licence' => 'recruitsuite',
            ),
            'marketplace' => array(
                'label' => 'Marketplace Suite',
                'plugin' => 'wp-job-portal-marketplacesuite',
                'licence' => 'marketplacesuite',
            ),
        );
    }

    /**
     * Add-ons that ship as part of a suite rather than on their own.
     *
     * Only products still shipped as separate plugins belong here. An add-on
     * that has been absorbed outright is not covered by a licence — it no
     * longer exists as a thing to license.
     *
     * @return array Add-on slug => product key.
     */
    public static function coverage() {
        $coverage = array(
            // Sold with the Marketplace Suite: the Suite sells featured jobs,
            // featured companies, featured resumes and multi-company, and
            // Credits is what charges for them.
            'credits' => 'marketplace',
        );

        /**
         * Filter which product covers each separately-shipped add-on.
         *
         * @param array $coverage Add-on slug => product key.
         */
        return (array) apply_filters('wpjobportal_product_coverage', $coverage);
    }

    /**
     * The product whose licence covers an add-on.
     *
     * @param string $slug Add-on slug, e.g. 'credits'.
     * @return string Product key, or empty when the add-on stands alone.
     */
    public static function covering_product($slug) {
        $coverage = self::coverage();
        $slug = strtolower((string) $slug);

        return isset($coverage[$slug]) ? $coverage[$slug] : '';
    }

    /**
     * Is this add-on sold as part of a suite?
     *
     * @param string $slug Add-on slug.
     * @return bool
     */
    public static function is_covered($slug) {
        return self::covering_product($slug) !== '';
    }

    /**
     * Everything a product's licence covers.
     *
     * @param string $product Product key.
     * @return array Add-on slugs.
     */
    public static function covered_by($product) {
        $covered = array();
        foreach (self::coverage() as $slug => $owner) {
            if ($owner === $product) {
                $covered[] = $slug;
            }
        }

        return $covered;
    }

    /**
     * The option a product's licence key is stored under.
     *
     * @param string $product Product key.
     * @return string Empty for products that are not licensed.
     */
    public static function key_option($product) {
        $products = self::products();

        if (!isset($products[$product]) || $products[$product]['licence'] === '') {
            return '';
        }

        return 'transaction_key_for_' . $products[$product]['plugin'];
    }

    /**
     * The licence key an add-on should be checked against.
     *
     * Its own key still wins where one is present, so a customer who bought
     * the add-on separately before it joined a suite is not asked to do
     * anything. Only a site with no key of its own falls through to the
     * suite's — which is the whole of what "the Suite unlocks it" means.
     *
     * @param string $slug Add-on slug.
     * @return array {key, source} — source is 'own', a product key, or ''.
     */
    public static function licence_key_for($slug) {
        $slug = strtolower((string) $slug);

        $own = trim((string) get_option('transaction_key_for_wp-job-portal-' . $slug, ''));
        if ($own !== '') {
            return array('key' => $own, 'source' => 'own');
        }

        $product = self::covering_product($slug);
        if ($product === '') {
            return array('key' => '', 'source' => '');
        }

        $option = self::key_option($product);
        $key = $option === '' ? '' : trim((string) get_option($option, ''));

        return array('key' => $key, 'source' => $key === '' ? '' : $product);
    }

    /**
     * A one-line description of how an add-on is licensed, for admin screens.
     *
     * @param string $slug Add-on slug.
     * @return string
     */
    public static function describe($slug) {
        $product = self::covering_product($slug);
        if ($product === '') {
            return '';
        }

        $products = self::products();
        $label = isset($products[$product]) ? $products[$product]['label'] : $product;

        $found = self::licence_key_for($slug);

        if ($found['source'] === 'own') {
            return sprintf(
                /* translators: %s: suite name. */
                __('Included with the %s. Your existing key for this add-on is still being used.', 'wp-job-portal'),
                $label
            );
        }

        if ($found['source'] === $product) {
            return sprintf(
                /* translators: %s: suite name. */
                __('Included with the %s, and covered by your Suite licence.', 'wp-job-portal'),
                $label
            );
        }

        return sprintf(
            /* translators: %s: suite name. */
            __('Included with the %s.', 'wp-job-portal'),
            $label
        );
    }
}
