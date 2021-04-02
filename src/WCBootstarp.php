<?php
namespace Calcurates;

use Calcurates\Basic;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

if (!\class_exists(WCBootstarp::class)) {
    /**
     * Changes WC by adding some new features through hooks
     */
    class WCBootstarp
    {
        /**
         * run
         *
         * @return void
         */
        public function run()
        {
            // Create Calcurates shipping method
            \add_action('init', [$this, 'init_shipping']);

            // Add feature to add to session ship_to_different_address checkbox status on checkout
            \add_action('woocommerce_checkout_update_order_review', [$this, 'ship_to_different_address_set_session']);

            // add text after rate on checkout
            \add_action('woocommerce_after_shipping_rate', [$this, 'add_data_after_shipping_rate'], 10, 2);

            // add text to order email
            \add_action('woocommerce_email_after_order_table', [$this, 'add_shipping_data_after_order_table_in_email'], 10, 4);
        }

        /**
         * init_shipping
         *
         * @return void
         */
        public function init_shipping()
        {
            if (\class_exists('WC_Shipping_Method')) {
                require_once Basic::get_plugin_dir_path().'includes/wc-calcurates-shipping-method.php';
                \add_filter('woocommerce_shipping_methods', [$this, 'add_calcurates_shipping']);
            }
        }

        /**
         * add_calcurates_shipping
         *
         * @param  mixed $methods
         * @return array
         */
        public function add_calcurates_shipping(array $methods): array
        {
            $methods['calcurates'] = 'WC_Calcurates_Shipping_Method';
            return $methods;
        }

        /**
         * ship_to_different_address_set_session
         *
         * @param  string $data
         * @return string
         */
        public function ship_to_different_address_set_session(string $data): string
        {
            $data_array = [];

            if ($data) {
                \parse_str($data, $data_array);

                if (\array_key_exists('ship_to_different_address', $data_array) && $data_array['ship_to_different_address']) {
                    \WC()->session->set('ship_to_different_address', 1);
                } else {
                    \WC()->session->set('ship_to_different_address', 0);
                }
            }

            return $data;
        }

        /**
         * action_after_shipping_rate
         *
         * @param  \WC_Shipping_Rate $rate
         * @param  int $index
         * @return void
         */
        public function add_data_after_shipping_rate(\WC_Shipping_Rate $rate, int $index)
        {
            if (false === \strpos($rate->get_id(), 'calcurates:')) {
                return;
            }

            $meta = $rate->get_meta_data();

            $text = null;

            // shipping rate description
            if (\array_key_exists('message', $meta)) {
                if ($meta['message']) {
                    $text .= "<div class='calcurates-checkout__shipping-rate-message'>" . $meta['message'] . "</div>";
                }
            }

            // shipping rate dates
            if (\array_key_exists('delivery_date_from', $meta) && \array_key_exists('delivery_date_to', $meta)) {
                $estimated_delivery_date = '';

                if ($meta['delivery_date_from'] === $meta['delivery_date_to']) {
                    $delivery_date_from = \strtotime($meta['delivery_date_from']);

                    if ($delivery_date_from) {
                        $delivery_date_from = \date(\get_option('date_format') . " " . \get_option('time_format'), $delivery_date_from);

                        $estimated_delivery_date = $delivery_date_from;
                    }
                } else {
                    $delivery_date_from = \strtotime($meta['delivery_date_from']);
                    $delivery_date_to = \strtotime($meta['delivery_date_to']);

                    if ($delivery_date_from) {
                        $delivery_date_from = \date(\get_option('date_format') . " " . \get_option('time_format'), $delivery_date_from);
                    }
                    if ($delivery_date_to) {
                        $delivery_date_to = \date(\get_option('date_format') . " " . \get_option('time_format'), $delivery_date_to);
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

        /**
         * add_shipping_data_after_order_table_in_email
         *
         * @param  \WC_Order $order
         * @param  bool $sent_to_admin
         * @param  string $plain_text
         * @param  \WC_Email $email
         * @return void
         */
        public function add_shipping_data_after_order_table_in_email(\WC_Order $order, bool $sent_to_admin, string $plain_text, \WC_Email $email)
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
                $delivery_date_from = \strtotime($delivery_date_from);

                if ($delivery_date_from) {
                    $delivery_date_from = \date(\get_option('date_format') . " " . \get_option('time_format'), $delivery_date_from);
                    $estimated_delivery_date = $delivery_date_from;
                }
            } elseif ($delivery_date_from && $delivery_date_to) {
                $delivery_date_from = \strtotime($delivery_date_from);
                $delivery_date_to = \strtotime($delivery_date_to);

                if ($delivery_date_from) {
                    $delivery_date_from = \date(\get_option('date_format') . " " . \get_option('time_format'), $delivery_date_from);
                }
                if ($delivery_date_to) {
                    $delivery_date_to = \date(\get_option('date_format') . " " . \get_option('time_format'), $delivery_date_to);
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
}
