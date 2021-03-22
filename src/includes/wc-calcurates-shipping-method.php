<?php

use Calcurates\Basic;
use Calcurates\Calcurates\Calcurates;

// Stop direct HTTP access.
if (!defined('ABSPATH')) {
    exit;
}

class WC_Calcurates_Shipping_Method extends WC_Shipping_Method
{

    private $calcurates;

    /**
     * __construct
     *
     * @param  int $instance_id
     * @return void
     */
    public function __construct(int $instance_id = 0)
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

    /**
     * init
     *
     * @return void
     */
    public function init()
    {

        $this->init_form_fields();
        $this->init_settings();

        $this->calcurates_api_key = $this->get_option('calcurates_api_key');
        $this->debug_mode = $this->get_option('debug_mode');
        $this->plugin_api_key = $this->get_option('plugin_api_key');
        $this->generate_new_api_key = $this->get_option('generate_new_api_key');
        $this->calcurates_api_url = $this->get_option('calcurates_api_url');
        $this->tax_mode = $this->get_option('tax_mode');

        // Save settings in admin if you have any defined
        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
    }

    /**
     * init_form_fields
     *
     * @return void
     */
    public function init_form_fields()
    {
        $this->form_fields = [
            'calcurates_api_url' => [
                'title' => __('Calcurates Api URL', 'woocommerce'),
                'type' => 'text',
                'default' => Basic::get_api_url(),
                'desc_tip' => false,
            ],
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
            'tax_mode' => [
                'title' => __('Taxes', 'woocommerce'),
                'type' => 'select',
                'default' => 'tax_included',
                'options' => [
                    'tax_included' => 'Duties & tax included',
                    'without_tax' => 'Without duties & tax',
                    'both' => 'Both',
                ],
            ],
        ];
    }

    /**
     * Calculate shipping
     *
     * @param  array $package
     * @return void
     */
    public function calculate_shipping($package = [])
    {

        $rates = $this->get_rates($package);

        if ($rates !== false) {
            foreach ($rates as $rate) {
                $this->add_rate($rate);
            }
        }

    }

    /**
     * Get rates
     *
     * @param  mixed $package
     * @return void
     */
    public function get_rates(array $package = [])
    {

        if (!$this->instance_id) {
            return false;
        }

        return (new Calcurates($this->calcurates_api_key, $this->calcurates_api_url ?: Basic::get_api_url(), $package, $this->debug_mode, $this->tax_mode))->get_rates();
    }

    /**
     * process_admin_options
     *
     * @return void
     */
    public function process_admin_options()
    {
        parent::process_admin_options();

        // TODO: needs refactor
        if (array_key_exists('woocommerce_' . $this->id . '_generate_new_api_key', $_POST) && $_POST['woocommerce_' . $this->id . '_generate_new_api_key'] == 1) {

            $this->update_option('generate_new_api_key', 'no');
            $key = wc_rand_hash();
            update_option(Basic::get_prefix() . 'key', $key);
            $this->update_option('plugin_api_key', $key);
        }

    }
}
