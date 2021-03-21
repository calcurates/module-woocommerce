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
    }

    public function init()
    {

        $this->init_form_fields();
        $this->init_settings();

        $this->calcurates_api_key = $this->get_option('calcurates_api_key');
        $this->debug_mode = $this->get_option('debug_mode');
        $this->plugin_api_key = $this->get_option('plugin_api_key');
        $this->generate_new_api_key = $this->get_option('generate_new_api_key');

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
            'plugin_api_key' => [
                'title' => __('Plugin Api Key', 'woocommerce'),
                'type' => 'text',
                'description' => __('Copy this Api Key to Calcurates panel', 'woocommerce'),
                'default' => get_option('wc_calcurates_key'),
                'desc_tip' => false,
                'custom_attributes' => [
                    'readonly' => 'readonly',
                ],
            ],
            'generate_new_api_key' => [
                'title' => __('Generate new Plugin Api Key', 'woocommerce'),
                'type' => 'checkbox',
                'description' => __('Check and save changes to generate new Plugin Api Key', 'woocommerce'),
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
            'package' => $package,
        ];
        return CalcuratesConnector::get_rates($args);
    }

    public function process_admin_options()
    {
        parent::process_admin_options();

        // TODO: needs refactor
        if (array_key_exists('woocommerce_calcurates_generate_new_api_key', $_POST) && $_POST['woocommerce_calcurates_generate_new_api_key'] == 1) {

            $this->update_option('generate_new_api_key', 'no');
            $key = wc_rand_hash();
            update_option('wc_calcurates_key', $key);
            $this->update_option('plugin_api_key', $key);
        }

    }
}
