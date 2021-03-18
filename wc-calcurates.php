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

    public static function run()
    {
        register_activation_hook(__FILE__, [__CLASS__, 'activate']);
        register_deactivation_hook(__FILE__, [__CLASS__, 'deactivate']);
        self::register_rest_routes();
        add_action('init', [__CLASS__, 'init_shipping']);

        // add feature to add to session ship_to_different_address checkbox status on checkout
        add_action('woocommerce_checkout_update_order_review', [__CLASS__, 'ship_to_different_address_set_session']);

        // add text after rate on checkout
        add_action('woocommerce_after_shipping_rate', [__CLASS__, 'action_after_shipping_rate'], 10, 2);

        add_action('wp_enqueue_scripts', [__CLASS__, 'calcurates_scripts']);

        // add text to order email
        add_action('woocommerce_email_after_order_table', [__CLASS__, 'add_shipping_data_after_order_table_in_email'], 10, 4);

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
        if (!get_option('wc_calcurates_key')) {
            update_option('wc_calcurates_key', wc_rand_hash());
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

    public static function action_after_shipping_rate($rate, $index)
    {

        if (str_contains($rate->id, 'calcurates:')) {

            $meta = $rate->get_meta_data();

            $text = null;

            // shipping rate description
            if (array_key_exists('message', $meta)) {

                if ($meta['message']) {
                    $text .= "<div class='calcurates-checkout__shipping-rate-message'>" . $meta['message'] . "</div>";
                }

            }

            // shipping rate dates
            if (array_key_exists('delivery_date_from', $meta) && array_key_exists('delivery_date_to', $meta)) {

                $estimated_delivery_date = '';

                if ($meta['delivery_date_from'] === $meta['delivery_date_to']) {

                    $delivery_date_from = strtotime($meta['delivery_date_from']);

                    if ($delivery_date_from) {
                        $delivery_date_from = date(get_option('date_format') . " " . get_option('time_format'), $delivery_date_from);

                        $estimated_delivery_date = $delivery_date_from;
                    }

                } else {

                    $delivery_date_from = strtotime($meta['delivery_date_from']);
                    $delivery_date_to = strtotime($meta['delivery_date_to']);

                    if ($delivery_date_from) {
                        $delivery_date_from = date(get_option('date_format') . " " . get_option('time_format'), $delivery_date_from);
                    }
                    if ($delivery_date_to) {
                        $delivery_date_to = date(get_option('date_format') . " " . get_option('time_format'), $delivery_date_to);
                    }

                    if ($delivery_date_from && $delivery_date_to) {
                        $estimated_delivery_date = $delivery_date_from . ' - ' . $delivery_date_to;
                    }

                }

                if ($estimated_delivery_date) {
                    $text .= "<div class='calcurates-checkout__shipping-rate-dates'>Estimated delivery date: " . $estimated_delivery_date . "</div>";
                }

            }

            if ($text) {

                echo "<div class='calcurates-checkout__shipping-rate-description'>" . $text . "</div>";

            }
        }

    }

    public static function calcurates_scripts()
    {

        wp_register_style('wc-calcurates', plugins_url('/assets/css/calcurates-checkout.css', __FILE__));

        if (is_cart() || is_checkout()) {
            wp_enqueue_style('wc-calcurates');
        }
    }

    public static function add_shipping_data_after_order_table_in_email($order, $sent_to_admin, $plain_text, $email)
    {
        $message = null;
        $delivery_date_from = null;
        $delivery_date_to = null;
        $estimated_delivery_date = '';
        $text = '';

        foreach ($order->get_items('shipping') as $item_id => $item) {

            if ($item->get_method_id() === 'calcurates') {
                $message = $item->get_meta('message');
                $delivery_date_from = $item->get_meta('delivery_date_from');
                $delivery_date_to = $item->get_meta('delivery_date_to');
                break;
            }

        }

        if ($delivery_date_from && $delivery_date_from === $delivery_date_to) {

            $delivery_date_from = strtotime($delivery_date_from);

            if ($delivery_date_from) {
                $delivery_date_from = date(get_option('date_format') . " " . get_option('time_format'), $delivery_date_from);
                $estimated_delivery_date = $delivery_date_from;
            }

        } elseif ($delivery_date_from && $delivery_date_to) {

            $delivery_date_from = strtotime($delivery_date_from);
            $delivery_date_to = strtotime($delivery_date_to);

            if ($delivery_date_from) {
                $delivery_date_from = date(get_option('date_format') . " " . get_option('time_format'), $delivery_date_from);
            }
            if ($delivery_date_to) {
                $delivery_date_to = date(get_option('date_format') . " " . get_option('time_format'), $delivery_date_to);
            }

            if ($delivery_date_from && $delivery_date_to) {
                $estimated_delivery_date = $delivery_date_from . ' - ' . $delivery_date_to;
            }

        }

        if ($message) {
            $text .= "Shipping info: " . $message . "<br/>";
        }
        if ($estimated_delivery_date) {
            $text .= "Estimated delivery date: " . $estimated_delivery_date;
        }

        if ($text) {
            echo "<p>" . $text . "</p>";
        }

    }
}

WC_Calcurates::run();
