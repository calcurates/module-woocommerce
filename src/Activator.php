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
         *
         * @return void
         */
        public static function activate(): void
        {
            self::deps_check();
            self::key_setup();
        }

        /**
         * Check plugin dependensies
         *
         * @return void
         */
        public static function deps_check(): void
        {
            /**
             * Check if WooCommerce is activated
             */
            if (!\defined('WC_VERSION')) {
                \wp_die(sprintf(
                    __('WooCommerce Calcurates requires WooCommerce 3.9 or later.', 'WC_Calcurates')
                ));
            }
        }

        /**
         * Generate plugins key
         *
         * @return void
         */
        public static function key_setup(): void
        {
            if (!\get_option('wc_calcurates_key')) {
                \update_option(Basic::get_prefix() . '_key', \wc_rand_hash());
            }
        }
    }
}
