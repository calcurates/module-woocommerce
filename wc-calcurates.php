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
    const prefix = 'wc_calcurates_';

    public static function run()
    {
        register_activation_hook(__FILE__, [__CLASS__, 'activate']);
        register_deactivation_hook(__FILE__, [__CLASS__, 'deactivate']);
        self::register_rest_routes();
        add_action('init', [__CLASS__, 'init_shipping']);

        // add feature to add to session ship_to_different_address checkbox status on checkout
        add_action('woocommerce_checkout_update_order_review', [__CLASS__, 'ship_to_different_address_set_session']);

    }

    public static function ship_to_different_address_set_session($data)
    {
        $data_array = [];

        if ($data) {

            parse_str($data, $data_array);

            if (array_key_exists('ship_to_different_address', $data_array) && $data_array['ship_to_different_address']) {
                WC()->session->set('ship_to_different_address', 1);
            } else {
                WC()->session->set('ship_to_different_address', 0);
            }
        }

        return $data;
    }

    public static function activate()
    {
        self::deps_check();
        self::key_setup();
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

    public static function key_setup()
    {
        if (!get_option(self::prefix . 'key')) {
            update_option(self::prefix . 'key', wc_rand_hash());
        }

    }

    private static function register_rest_routes()
    {
        add_action('rest_api_init', ['Calcurates\RESTAPI\Routes\WooCommmerceSettingsRoutes', 'register_route']);
        add_action('rest_api_init', ['Calcurates\RESTAPI\Routes\MultisiteRoutes', 'register_route']);
    }

    public static function init_shipping()
    {
        if (class_exists('WC_Shipping_Method')) {
            require_once 'shipping-methods/wc-calcurates-shipping-method.php';
            add_filter('woocommerce_shipping_methods', [__CLASS__, 'add_calcurates_shipping']);
        }
    }

    public static function add_calcurates_shipping($methods)
    {
        $methods['calcurates'] = 'WC_Calcurates_Shipping_Method';
        return $methods;
    }

    public static function deactivate()
    {
    }
}

WC_Calcurates::run();
