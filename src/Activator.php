<?php

namespace Calcurates;

use Calcurates\Basic;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

if (!\class_exists(Activator::class)) {
    /**
     * Defines methods run on plugin activation
     */
    class Activator
    {
        /**
         * Run on plugin activation
         */
        public static function activate(): void
        {
            self::deps_check();
            self::key_setup();
        }

        /**
         * Check plugin dependencies
         */
        public static function deps_check(): void
        {
            // Check if WooCommerce is has been activated
            if (!\defined('WC_VERSION')) {
                \wp_die(\sprintf(
                    __('WooCommerce Calcurates requires WooCommerce 3.9 or later.', 'WC_Calcurates')
                ));
            }
        }

        /**
         * Generate plugins key
         */
        public static function key_setup(): void
        {
            if (!\get_option(Basic::get_prefix() . 'key')) {
                \update_option(Basic::get_prefix() . 'key', \wc_rand_hash());
            }
        }
    }
}
