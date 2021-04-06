<?php
namespace Calcurates;

use Calcurates\Basic;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

if (!\class_exists(WCBootstrap::class)) {
    /**
     * Changes WC by adding some new features through hooks
     */
    class WCBootstrap
    {
        public function run(): void
        {
            // Create Calcurates shipping method
            \add_action('init', array($this, 'init_shipping'));

            // Add feature to add to session ship_to_different_address checkbox status on checkout
            \add_action('woocommerce_checkout_update_order_review', array($this, 'ship_to_different_address_set_session'));

            // add text after rate on checkout
            \add_action('woocommerce_after_shipping_rate', array($this, 'add_data_after_shipping_rate'), 10, 2);

            // add text to order email
            \add_action('woocommerce_email_after_order_table', array($this, 'add_shipping_data_after_order_table_in_email'), 10, 4);
        }

        public function init_shipping(): void
        {
            if (\class_exists('WC_Shipping_Method')) {
                require_once Basic::get_plugin_dir_path().'includes/wc-calcurates-shipping-method.php';
                \add_filter('woocommerce_shipping_methods', array($this, 'add_calcurates_shipping'));
            }
        }

        /**
         * Shipping methods register themselves by returning their main class name through the woocommerce_shipping_methods filter.
         *
         * @param  array<string, string> $methods
         * @return array<string, string>
         */
        public function add_calcurates_shipping(array $methods): array
        {
            $methods[\WC_Calcurates_Shipping_Method::CODE] = 'WC_Calcurates_Shipping_Method';
            return $methods;
        }

        public function ship_to_different_address_set_session(string $data): string
        {
            $data_array = array();

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

        public function add_data_after_shipping_rate(\WC_Shipping_Rate $rate, int $index): void
        {
            if (false === \strpos($rate->get_id(), 'calcurates:')) {
                return;
            }

            $meta = $rate->get_meta_data();

            $text = null;

            // shipping rate description
            if (isset($meta['message'])) {
                $text .= '<div class="calcurates-checkout__shipping-rate-message">' . $meta['message'] . '</div>';
            }

            // shipping rate dates, use \DateTime objects
            $estimated_delivery_date_text = $this->get_estimated_delivery_date_text($meta['delivery_date_from'], $meta['delivery_date_to']);
            
            if($estimated_delivery_date_text){
                $text .= '<div class="calcurates-checkout__shipping-rate-dates">Estimated delivery date: ' . $estimated_delivery_date_text . '</div>';
            }

            if ($text) {
                echo '<div class="calcurates-checkout__shipping-rate-description">' . $text . '</div>';
            }
        }

        public function add_shipping_data_after_order_table_in_email(\WC_Order $order, bool $sent_to_admin, string $plain_text, \WC_Email $email): void
        {
            $message = null;
            $delivery_date_from = null;
            $delivery_date_to = null;
            $estimated_delivery_date = '';
            $text = '';

            /** @var \WC_Order_Item_Shipping $item */
            foreach ($order->get_items('shipping') as $item) {
                if ($item->get_method_id() === \WC_Calcurates_Shipping_Method::CODE) {
                    $message = $item->get_meta('message');
                    $delivery_date_from = $item->get_meta('delivery_date_from');
                    $delivery_date_to = $item->get_meta('delivery_date_to');
                    break;
                }
            }

            if ($message) {
                $text .= "Shipping info: " . $message . "<br/>";
            }

            $estimated_delivery_date = $this->get_estimated_delivery_date_text($delivery_date_from, $delivery_date_to);

            if ($estimated_delivery_date) {
                $text .= "Estimated delivery date: " . $estimated_delivery_date;
            }

            if ($text) {
                echo "<p>" . $text . "</p>";
            }
        }

        /**
         * Get text string with delivery dates
         *
         * @param string|null $from
         * @param string|null $to
         */
        private function get_estimated_delivery_date_text($from_date, $to_date): string
        {
            $from = null;
            $to = null;

            // get  \DateTime objects 
            try{
                $from = $from_date ? new \DateTime($from_date) : null;
            }catch(\Exception $e){
                
            }

            try{
                $to = $to_date ? new \DateTime($to_date) : null;
            }catch(\Exception $e){
                
            }

            // if no \DateTime objects
            if(!$from instanceof \DateTime && !$to instanceof \DateTime){
                return '';
            }

            // if both \DateTime objects
            if($from instanceof \DateTime && $to instanceof \DateTime){
                // set WP time zones
                $from->setTimezone(\wp_timezone());
                $to->setTimezone(\wp_timezone());
                
                // do on equal dates
                if ($from->format('U') === $to->format('U')) {
                    return $from->format($this->wp_datetime_fromat());
                }

                return $from->format($this->wp_datetime_fromat()). ' - ' . $to->format($this->wp_datetime_fromat());
            }

            // if has only 'from' date
            if($from instanceof \DateTime){
                // set WP time zone
                $from->setTimezone(\wp_timezone());

                return 'From '.$from->format($this->wp_datetime_fromat());
            }

            // if has only 'to' date
            if($to instanceof \DateTime){
                // set WP time zone
                $to->setTimezone(\wp_timezone());

                return 'To '.$to->format($this->wp_datetime_fromat());
            }
        }

        /**
         * Get current store date and time formats
         */
        private function wp_datetime_fromat(): string
        {
            return \get_option('date_format') . " " . \get_option('time_format');
        }
    }


}
