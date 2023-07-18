<?php

declare(strict_types=1);

namespace Calcurates;

use Calcurates\Origins\OriginsTaxonomy;
use Calcurates\Origins\OriginUtils;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

if (!\class_exists(WCBootstrap::class)) {
    /**
     * Changes WC by adding some new features through hooks.
     */
    class WCBootstrap
    {
        private static $delivery_date_meta_name = 'selected_delivery_date';
        private static $delivery_time_meta_name = 'selected_delivery_time';

        public function run(): void
        {
            // Create Calcurates shipping method
            \add_action('init', [$this, 'init_shipping']);

            // Add feature to add to session ship_to_different_address checkbox status on checkout
            \add_action('woocommerce_checkout_update_order_review', [$this, 'ship_to_different_address_set_session']);

            // add text to order email
            \add_action('woocommerce_email_after_order_table', [$this, 'add_shipping_data_after_order_table_in_email'], 10, 1);

            // add origins select
            \add_action('woocommerce_product_options_shipping', [$this, 'add_origin_select']);
            \add_action('woocommerce_process_product_meta', [$this, 'save_origins_select'], 10, 2);

            // validate selected rate if has no error
            \add_action('woocommerce_after_checkout_validation', [$this, 'validate_selected_rate'], 10, 2);

            \add_filter('woocommerce_cart_shipping_method_full_label', [$this, 'filter_woocommerce_cart_shipping_method_full_label'], 10, 2);

            \add_action('woocommerce_checkout_update_order_review', [$this, 'checkout_update_refresh_shipping_methods'], 10, 1);

            \add_action('woocommerce_checkout_create_order_shipping_item', [$this, 'action_checkout_create_order_shipping_item'], 20, 4);

            // add some data to thankyou page
            \add_filter( 'woocommerce_get_order_item_totals', [$this, 'add_custom_order_totals_row'], 30, 3 );

        }

        public function add_custom_order_totals_row( $total_rows, $order, $tax_display ) {
            $delivery_date_from = null;
            $delivery_date_to = null;
            $delivery_date = null;
            $delivery_time_from = null;
            $delivery_time_to = null;

            // Set last total row in a variable and remove it.
            $gran_total = $total_rows['order_total'];
            unset( $total_rows['order_total'] );

            /** @var \WC_Order_Item_Shipping $item */
            foreach ($order->get_items('shipping') as $item) {
                if (\WC_Calcurates_Shipping_Method::CODE === $item->get_method_id()) {
                    $delivery_date_from = $item->get_meta('delivery_date_from');
                    $delivery_date_to = $item->get_meta('delivery_date_to');
                    $delivery_date = $item->get_meta(self::$delivery_date_meta_name);
                    $delivery_time_from = $item->get_meta(self::$delivery_time_meta_name.'_from');
                    $delivery_time_to = $item->get_meta(self::$delivery_time_meta_name.'_to');

                    break;
                }
            }

            if ($delivery_date) {
                $time_slots = $this->get_time_slots_text($delivery_date, $delivery_time_from, $delivery_time_to);
                if ($time_slots) {
                    $total_rows['time_slots'] = array(
                        'label' => __( 'UTC delivery date:', 'woocommerce' ),
                        'value' =>\htmlspecialchars($time_slots, \ENT_NOQUOTES),
                    );
                }
            }
            if (($delivery_date_from || $delivery_date_to) && !$time_slots) {
                $estimated_delivery_date = $this->get_estimated_delivery_dates_text(
                    $delivery_date_from,
                    $delivery_date_to
                );

                if ($estimated_delivery_date) {
                    $total_rows['estimated_delivery_date'] = array(
                        'label' => __( 'Estimated delivery date:', 'woocommerce' ),
                        'value' =>\htmlspecialchars($estimated_delivery_date, \ENT_NOQUOTES),
                    );                
                }
            }

            // Set back last total row
            $total_rows['order_total'] = $gran_total;

            return $total_rows;
        }

        // For new orders via checkout
        public function action_checkout_create_order_shipping_item($item, $package_key, $package, $order): void
        {
            if (isset($_POST[self::$delivery_date_meta_name]) && $_POST[self::$delivery_date_meta_name]) {
                $item->update_meta_data(self::$delivery_date_meta_name, $_POST[self::$delivery_date_meta_name]);
            }

            if (isset($_POST[self::$delivery_time_meta_name]) && $_POST[self::$delivery_time_meta_name]) {
                $data = \json_decode(\stripslashes($_POST[self::$delivery_time_meta_name]), true);

                $item->update_meta_data(self::$delivery_time_meta_name.'_from', $data['from']);
                $item->update_meta_data(self::$delivery_time_meta_name.'_to', $data['to']);
            }
        }

        /**
         * Always trigger shipping recalculation on update_checkout js trigger.
         */
        public function checkout_update_refresh_shipping_methods($post_data): void
        {
            $packages = \WC()->cart->get_shipping_packages();

            foreach ($packages as $package_key => $package) {
                \WC()->session->set('shipping_for_package_'.$package_key, true);
            }
        }

        public function init_shipping(): void
        {
            if (\class_exists(\WC_Shipping_Method::class)) {
                require_once WCCalcurates::get_plugin_dir_path().'includes/wc-calcurates-shipping-method.php';
                \add_filter('woocommerce_shipping_methods', [$this, 'add_calcurates_shipping']);
            }
        }

        /**
         * Shipping methods register themselves by returning their main class name through the woocommerce_shipping_methods filter.
         *
         * @param array<string, string> $methods
         *
         * @return array<string, string>
         */
        public function add_calcurates_shipping(array $methods): array
        {
            $methods[\WC_Calcurates_Shipping_Method::CODE] = 'WC_Calcurates_Shipping_Method';

            return $methods;
        }

        public function ship_to_different_address_set_session(string $data): string
        {
            if ($data) {
                $data_array = [];
                \parse_str($data, $data_array);

                if (isset($data_array['ship_to_different_address']) && $data_array['ship_to_different_address']) {
                    \WC()->session->set('ship_to_different_address', '1');
                } else {
                    \WC()->session->set('ship_to_different_address', '0');
                }
            }

            return $data;
        }

        public function add_shipping_data_after_order_table_in_email(\WC_Order $order): void
        {
            $message = null;
            $text = '';

            /** @var \WC_Order_Item_Shipping $item */
            foreach ($order->get_items('shipping') as $item) {
                if (\WC_Calcurates_Shipping_Method::CODE === $item->get_method_id()) {
                    $message = $item->get_meta('message');

                    break;
                }
            }

            if ($message) {
                $text .= 'Shipping info: '.\htmlspecialchars($message, \ENT_NOQUOTES).'<br/>';
            }

            if ($text) {
                echo '<p>'.$text.'</p>';
            }
        }

        /**
         * Get text string with delivery dates.
         */
        private function get_time_slots_text(string $delivery_date, ?string $delivery_time_from, ?string $delivery_time_to): string
        {
            $formatted_delivery_date = '';
            try {
                $delivery_date_obj = (new \DateTime($delivery_date))->setTimezone(\wp_timezone());
                $formatted_delivery_date = $delivery_date_obj->format($this->wp_date_format());
            } catch (\Exception $e) {
            }

            $text = $formatted_delivery_date;

            if ($delivery_time_from || $delivery_time_to) {
                $formatted_delivery_time_from = '';
                $formatted_delivery_time_to = '';

                if ($delivery_time_from) {
                    try {
                        $delivery_time_from_obj = (new \DateTime($delivery_time_from))->setTimezone(\wp_timezone());
                        $formatted_delivery_time_from = $delivery_time_from_obj->format($this->wp_time_format());
                    } catch (\Exception $e) {
                    }
                }

                if ($delivery_time_to) {
                    try {
                        $delivery_time_to_obj = (new \DateTime($delivery_time_to))->setTimezone(\wp_timezone());
                        $formatted_delivery_time_to = $delivery_time_to_obj->format($this->wp_time_format());
                    } catch (\Exception $e) {
                    }
                }

                if ($formatted_delivery_time_from || $formatted_delivery_time_to) {
                    $text .= 'Delivery time: ';
                    if ($formatted_delivery_time_from) {
                        $text .= $formatted_delivery_time_from;
                    }
                    if ($formatted_delivery_time_from && $formatted_delivery_time_to) {
                        $text .= ' - ';
                    }
                    if ($formatted_delivery_time_to) {
                        $text .= $formatted_delivery_time_to;
                    }
                }
            }

            return $text;
        }

        /**
         * Get text string with delivery dates.
         */
        private function get_estimated_delivery_dates_text(?string $from_date, ?string $to_date): string
        {
            $from = null;
            $to = null;

            // get \DateTime objects
            try {
                $from = $from_date ? (new \DateTime($from_date))->setTimezone(\wp_timezone()) : null;
            } catch (\Exception $e) {
            }

            try {
                $to = $to_date ? (new \DateTime($to_date))->setTimezone(\wp_timezone()) : null;
            } catch (\Exception $e) {
            }

            if ($from && $to) {
                $formatted_from = $from->format($this->wp_date_format());
                $formatted_to = $to->format($this->wp_date_format());

                // do on equal dates
                if ($formatted_from === $formatted_to) {
                    return $formatted_from;
                }

                return $formatted_from.' - '.$formatted_to;
            }

            // if has only 'from' date
            if ($from) {
                return 'From '.$from->format($this->wp_date_format());
            }

            // if has only 'to' date
            if ($to) {
                return 'To '.$to->format($this->wp_date_format());
            }

            return '';
        }

        /**
         * Get text string with delivery days.
         */
        private function get_estimated_delivery_days_text(?string $from_date, ?string $to_date): string
        {
            $from = null;
            $to = null;

            // get \DateTime objects
            try {
                $from = $from_date ? (new \DateTime($from_date))->setTimezone(\wp_timezone()) : null;
            } catch (\Exception $e) {
            }

            try {
                $to = $to_date ? (new \DateTime($to_date))->setTimezone(\wp_timezone()) : null;
            } catch (\Exception $e) {
            }

            if ($from && $to) {
                return 'Estimated Delivery: '.$this->difference_in_days_from_now($from).'-'.$this->difference_in_days_from_now($to).' days';
            }

            // if only 'from' date
            if ($from) {
                $days = $this->difference_in_days_from_now($from);

                return 'Estimated Delivery from: '.$days.' '.($days > 1 ? 'days' : 'day');
            }

            // if only 'to' date
            if ($to) {
                $days = $this->difference_in_days_from_now($to);

                return 'Estimated Delivery to: '.$days.' '.($days > 1 ? 'days' : 'day');
            }

            return '';
        }

        /**
         * Get current store date format.
         */
        private function wp_date_format(): string
        {
            return \get_option('date_format');
        }

        /**
         * Get current store time format.
         */
        private function wp_time_format(): string
        {
            return \get_option('time_format');
        }

        /**
         * Add Origin select.
         */
        public function add_origin_select(): void
        {
            $origins = [
                '' => 'Please select',
            ];

            $terms = \get_terms(OriginsTaxonomy::TAXONOMY_SLUG, [
                'hide_empty' => false,
                'fields' => 'id=>name',
            ]);

            if ($terms && \is_array($terms)) {
                foreach ($terms as $key => $value) {
                    $origins[$key] = $value;
                }
            }

            echo '<div class="options_group">';

            \woocommerce_wp_select([
                'id' => 'origins',
                'name' => 'origins[]',
                'value' => OriginUtils::getInstance()->get_origin_term_ids_from_product(\get_the_ID()),
                'label' => 'Origin',
                'options' => $origins,
                'custom_attributes' => ['multiple' => 'multiple'],
            ]);

            echo '</div>';
        }

        /**
         * Save Origin.
         */
        public function save_origins_select($id, $post): void
        {
            $old_origin_ids = OriginUtils::getInstance()->get_origin_term_ids_from_product($id);
            $new_origins_ids = $_POST['origins'];

            // remove product from last origin
            if ($old_origin_ids) {
                \wp_remove_object_terms($id, $old_origin_ids, OriginsTaxonomy::TAXONOMY_SLUG);
            }

            // append product to new origin
            if ($new_origins_ids) {
                \wp_set_post_terms($id, \array_map('intval', $new_origins_ids), OriginsTaxonomy::TAXONOMY_SLUG, true);
            }
        }

        /**
         * Validate selected rate if has no error.
         */
        public function validate_selected_rate(array $data, \WP_Error $errors): void
        {
            $chosen_shipping_methods = \WC()->session->get('chosen_shipping_methods', []);

            if (!$chosen_shipping_methods || !\is_array($chosen_shipping_methods)) {
                return;
            }

            foreach ($chosen_shipping_methods as $chosen_method) {
                // The array of shipping methods enabled for the current shipping zone:
                $shipping_methods = \WC()->session->get('shipping_for_package_0', [])['rates'];

                foreach ($shipping_methods as $shipping_rate) {
                    if ($shipping_rate->get_id() === $chosen_method) {
                        $meta = $shipping_rate->get_meta_data();

                        if ($meta['has_error'] ?? false) {
                            $errors->add('validation', \__('Chosen Shipping method is not available.'));

                            return;
                        }
                    }
                }
            }
        }

        /**
         * Change checkout rate HTML if it's Calcurates rate.
         */
        public function filter_woocommerce_cart_shipping_method_full_label(string $label, \WC_Shipping_Rate $rate): string
        {
            if (false === \strpos($rate->get_id(), \WC_Calcurates_Shipping_Method::CODE.':')) {
                return $label;
            }

            $shipping_method_options = \get_option('woocommerce_'.\WC_Calcurates_Shipping_Method::CODE.'_settings', true);
            $meta = $rate->get_meta_data();

            // rate image
            $image = '';
            if ($meta['rate_image']) {
                $image = '<img src="'.\htmlspecialchars($meta['rate_image']).'" class="calcurates-checkout__shipping-rate-image" />';
            }

            // info message
            $info_message = '';
            if ('description' === $shipping_method_options['info_messages_display_settings'] && $meta['message']) {
                $info_message = '<div class="calcurates-checkout__shipping-rate-message">'.\htmlspecialchars($meta['message'], \ENT_NOQUOTES).'</div>';
            }

            // delivery dates
            $delivery_dates = '';
            if ('description' === $shipping_method_options['delivery_dates_display_mode'] && ($meta['delivery_date_from'] || $meta['delivery_date_to'])) {
                if ('quantity' === $shipping_method_options['delivery_dates_display_format']) {
                    $delivery_dates_text = $this->get_estimated_delivery_days_text($meta['delivery_date_from'], $meta['delivery_date_to']);
                } else {
                    $delivery_dates_text = $this->get_estimated_delivery_dates_text($meta['delivery_date_from'], $meta['delivery_date_to']);
                }

                $date_selector = '';
                if ($meta['time_slots']) {
                    $date_selector = '<div class="calcurates-checkout__shipping-rate-date-select-label">Delivery date
                    <input id="'.\htmlspecialchars($this->rate_id_to_css_id($rate->get_id())).'" class="calcurates-checkout__shipping-rate-date-select" placeholder="Select delivery date" data-delivery-date-from="'.\htmlspecialchars($meta['delivery_date_from'] ?? '').'" data-delivery-date-to="'.\htmlspecialchars($meta['delivery_date_to'] ?? '').'" data-time-slot-date-required="'.\htmlspecialchars($meta['time_slot_date_required'] ?? '0').'" data-time-slot-time-required="'.\htmlspecialchars($meta['time_slot_time_required'] ?? '0').'" data-time-slots="'.\htmlspecialchars(\json_encode($meta['time_slots'], \JSON_UNESCAPED_SLASHES)).'" type="text" readonly="readonly"/>
                    <input class="calcurates-checkout__shipping-rate-date-original-utc" name="'.\htmlspecialchars(self::$delivery_date_meta_name).'" type="text" hidden="hidden"/>
                    </div>';
                }

                $delivery_dates = '<div class="calcurates-checkout__shipping-rate-dates">
                    '.\htmlspecialchars($delivery_dates_text, \ENT_NOQUOTES).$date_selector.'
                </div>';
            }

            return $image.'<span class="calcurates-checkout__shipping-rate-text'.($meta['has_error'] ? ' calcurates-checkout__shipping-rate-text_has-error' : '').'">'.$label.' '.$info_message.' '.$delivery_dates.'</span>';
        }

        private function difference_in_days_from_now(\DateTime $date): string
        {
            $now = new \DateTime('now', \wp_timezone());
            $interval = $now->diff($date);

            return $interval->format('%a');
        }

        private function rate_id_to_css_id(string $rate_id): string
        {
            $data = \str_replace(':', '-', $rate_id);
            $data = \str_replace('_', '-', $data);
            $data = \str_replace('calcurates', 'calcurates-datepicker', $data);

            return $data;
        }
    }
}
