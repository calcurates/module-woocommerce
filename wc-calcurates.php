<?php
/**
 * Plugin Name:  WooCommerce Calcurates
 */

// Stop direct HTTP access.
if (!defined('ABSPATH')) {
    die;
}

require_once plugin_dir_path(__FILE__) . 'lib/autoload.php';

class WC_Calcurates
{
    const prefix = 'wc_calcurates';

    public static function run()
    {
        register_activation_hook(__FILE__, [__CLASS__, 'activate']);
        register_deactivation_hook(__FILE__, [__CLASS__, 'deactivate']);
    }

    public static function activate()
    {
        self::deps_check();
    }

    public static function deps_check()
    {
        /**
         * Check if WooCommerce is activated
         */
        if (!defined('WC_VERSION')) {
            wp_die(sprintf(
                __('WooCommerce Calcurates requires WooCommerce 3.9 or later.', 'WC_Calcurates')
            ));
        }
    }

    public static function deactivate()
    {
    }
}

WC_Calcurates::run();
