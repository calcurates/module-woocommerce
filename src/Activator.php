<?php

declare(strict_types=1);

namespace Calcurates;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

if (!\class_exists(Activator::class)) {
    /**
     * Defines methods run on plugin activation.
     */
    class Activator
    {
        /**
         * Run on plugin activation.
         */
        public static function activate(): void
        {
            self::deps_check();
            self::key_setup();
        }

        /**
         * Check plugin dependencies.
         */
        public static function deps_check(): void
        {
            // Check if WooCommerce is has been activated
            if (!\defined('WC_VERSION') || \version_compare(WC_VERSION, '4.3.0', '<')) {
                \wp_die(
                    \__('WooCommerce Calcurates requires WooCommerce 4.3 or later.', 'WC_Calcurates')
                );
            }
        }

        /**
         * Generate plugins key.
         */
        public static function key_setup(): void
        {
            if (!\get_option(WCCalcurates::get_prefix().'key')) {
                \update_option(WCCalcurates::get_prefix().'key', \wc_rand_hash());
            }
        }
    }
}
