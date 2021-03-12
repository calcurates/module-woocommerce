<?php

defined('ABSPATH') || exit;

use Calcurates\Controllers\CalcuratesConnector;

class WC_Calcurates_Shipping_Method extends WC_Shipping_Method
{

    public function __construct($instance_id = 0)
    {
        $this->id = 'calcurates';
        $this->instance_id = absint($instance_id);
        $this->method_title = __('Calcurates Shipping Method');
        $this->method_description = __('Calcurates Shipping Method');

        $this->enabled = "yes";
        $this->title = "Calcurates Shipping Method";

        $this->supports = array(
            'shipping-zones',
            // 'instance-settings',
            'settings',
        );

        $this->init();

        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
    }

    public function init()
    {

        $this->init_form_fields();
        $this->init_settings();

        $this->calcurates_api_key = $this->get_option('calcurates_api_key');
        $this->debug_mode = $this->get_option('debug_mode');

        // Save settings in admin if you have any defined
        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
    }

    public function init_form_fields()
    {
        $this->form_fields = [
            'calcurates_api_key' => [
                'title' => __('Calcurates Api Key', 'woocommerce'),
                'type' => 'text',
                'description' => __('Copy your Api Key from Calcurates panel', 'woocommerce'),
                'default' => "",
                'desc_tip' => false,
            ],
            'debug_mode' => [
                'title' => __('Debug', 'woocommerce'),
                'type' => 'select',
                'default' => 'off',
                'options' => [
                    'off' => 'Off',
                    'errors' => 'Log errors only',
                    'all' => 'Log all data',
                ],
            ],
        ];
    }

    public function calculate_shipping($package = [])
    {

        $rates = $this->get_rates($package);

        if ($rates !== false) {
            foreach ($rates as $rate) {
                $this->add_rate($rate);
            }
        }

    }

    public function get_rates($package = [])
    {

        if (!$this->instance_id) {
            return false;
        }

        $args = [
            'api_key' => $this->calcurates_api_key,
            'debug_mode' => $this->debug_mode,
        ];
        return CalcuratesConnector::get_rates($args, $package);
    }

}
