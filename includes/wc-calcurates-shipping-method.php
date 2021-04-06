<?php

use Calcurates\Basic;
use Calcurates\Calcurates\Calcurates;
use Calcurates\Calcurates\CalcuratesClient;
use Calcurates\Calcurates\Rates\Rates;
use Calcurates\Calcurates\RequestsBodyBuilders\RatesRequestBodyBuilder;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

class WC_Calcurates_Shipping_Method extends WC_Shipping_Method
{
    public const CODE = 'calcurates';

    /**
     * Debug mode
     *
     * @var string
     */
    private $debug_mode;

    /**
     * Key for that module API access
     *
     * @var string
     */
    private $plugin_api_key;

    /**
     * @var string
     */
    private $generate_new_api_key;

    /**
     * Calcurates API URL
     *
     * @var string
     */
    private $calcurates_api_url;

    /**
     * Calcurates API access key
     *
     * @var string
     */
    private $calcurates_api_key;

    /**
     * Tax view type
     *
     * @var string
     */
    private $tax_mode;


    public function __construct($instance_id = 0)
    {
        parent::__construct($instance_id);

        $this->id = self::CODE;
        $this->method_title = __('Calcurates Shipping Method');
        $this->method_description = __('Calcurates Shipping Method');

        $this->enabled = 'yes';
        $this->title = 'Calcurates Shipping Method';

        $this->supports = array(
            'shipping-zones',
            'settings',
        );

        $this->init();
    }

    public function init(): void
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
        \add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
    }

    public function init_form_fields(): void
    {
        $this->form_fields = array(
            'calcurates_api_url' => array(
                'title' => __('Calcurates Api URL', 'woocommerce'),
                'type' => 'text',
                'default' => 'https://api.calcurates.com',
                'desc_tip' => false,
            ),
            'calcurates_api_key' => array(
                'title' => __('Calcurates Api Key', 'woocommerce'),
                'type' => 'text',
                'description' => __('Copy your Api Key from Calcurates panel', 'woocommerce'),
                'default' => "",
                'desc_tip' => false,
            ),
            'plugin_api_key' => array(
                'title' => __('Plugin Api Key', 'woocommerce'),
                'type' => 'text',
                'description' => __('Copy this Api Key to Calcurates panel', 'woocommerce'),
                'default' => \get_option(Basic::get_prefix() . 'key'),
                'desc_tip' => false,
                'custom_attributes' => array(
                    'readonly' => 'readonly',
                ),
            ),
            'generate_new_api_key' => array(
                'title' => __('Generate new Plugin Api Key', 'woocommerce'),
                'type' => 'checkbox',
                'description' => __('Check and save changes to generate new Plugin Api Key', 'woocommerce'),
                'desc_tip' => false,
            ),
            'debug_mode' => array(
                'title' => __('Debug', 'woocommerce'),
                'type' => 'select',
                'default' => 'off',
                'options' => array(
                    'off' => 'Off',
                    'errors' => 'Log errors only',
                    'all' => 'Log all data',
                ),
            ),
            'tax_mode' => array(
                'title' => __('Display rates with tax & duties', 'woocommerce'),
                'type' => 'select',
                'default' => 'tax_included',
                'options' => array(
                    'tax_included' => 'Duties & tax included',
                    'without_tax' => 'Without duties & tax',
                    'both' => 'Both',
                ),
            ),
        );
    }

    /**
     * @inheritdoc
     */
    public function calculate_shipping($package = array()): void
    {
        $rates = $this->get_rates($package);

        if (!empty($rates)) {
            foreach ($rates as $rate) {
                $this->add_rate($rate);
            }
        }
    }

    /**
     * Get rates
     *
     * @param array $package Package array.
     * @return array
     */
    private function get_rates(array $package = array()): array
    {
        if (!$this->instance_id) {
            return array();
        }

        $calcurates_client = new CalcuratesClient($this->calcurates_api_key, $this->calcurates_api_url, $this->debug_mode);

        $rates_request_body_builder = new RatesRequestBodyBuilder($package);

        $rates_tools = new Rates($this->tax_mode, $package);

        return (new Calcurates($calcurates_client, $rates_request_body_builder, $rates_tools))->get_rates();
    }

    /**
     * @inheritdoc
     */
    public function process_admin_options(): bool
    {
        parent::process_admin_options();

        // TODO: needs refactor
        if (\array_key_exists('woocommerce_' . $this->id . '_generate_new_api_key', $_POST) && $_POST['woocommerce_' . $this->id . '_generate_new_api_key'] === 1) {
            $this->update_option('generate_new_api_key', 'no');
            $key = \wc_rand_hash();
            \update_option(Basic::get_prefix() . 'key', $key);
            $this->update_option('plugin_api_key', $key);
        }

        return true;
    }
}