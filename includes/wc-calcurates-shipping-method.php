<?php

declare(strict_types=1);

use Calcurates\HttpClient\CalcuratesHttpClient;
use Calcurates\HttpClient\RequestsBodyBuilders\RatesRequestBodyBuilder;
use Calcurates\Rates\Rates;
use Calcurates\WCCalcurates;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

class WC_Calcurates_Shipping_Method extends WC_Shipping_Method
{
    public const CODE = 'calcurates';

    /**
     * Debug mode.
     *
     * @var string
     */
    private $debug_mode;

    /**
     * Key for that module API access.
     *
     * @var string
     */
    private $plugin_api_key;

    /**
     * @var string
     */
    private $generate_new_api_key;

    /**
     * Calcurates API URL.
     *
     * @var string
     */
    private $calcurates_api_url;

    /**
     * Calcurates API access key.
     *
     * @var string
     */
    private $calcurates_api_key;

    /**
     * Tax view type.
     *
     * @var string
     */
    private $tax_mode;

    /**
     * {@inheritdoc}
     */
    public function __construct($instance_id = 0)
    {
        parent::__construct($instance_id);

        $this->id = self::CODE;
        $this->method_title = \__('Calcurates Shipping Method');
        $this->method_description = \__('Calcurates Shipping Method');

        $this->enabled = 'yes';
        $this->title = 'Calcurates Shipping Method';

        $this->supports = [
            'shipping-zones',
            'settings',
        ];

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
        \add_action('woocommerce_update_options_shipping_'.$this->id, [$this, 'process_admin_options']);
    }

    public function init_form_fields(): void
    {
        $this->form_fields = [
            'calcurates_api_url' => [
                'title' => \__('Calcurates Api URL', 'woocommerce'),
                'type' => 'text',
                'default' => 'https://api.calcurates.com',
                'desc_tip' => false,
            ],
            'calcurates_api_key' => [
                'title' => \__('Calcurates Api Key', 'woocommerce'),
                'type' => 'text',
                'description' => \__('Copy your Api Key from Calcurates panel', 'woocommerce'),
                'default' => '',
                'desc_tip' => false,
            ],
            'plugin_api_key' => [
                'title' => \__('Plugin Api Key', 'woocommerce'),
                'type' => 'text',
                'description' => \__('Copy this Api Key to Calcurates panel', 'woocommerce'),
                'default' => \get_option(WCCalcurates::get_prefix().'key'),
                'desc_tip' => false,
                'custom_attributes' => [
                    'readonly' => 'readonly',
                ],
            ],
            'generate_new_api_key' => [
                'title' => \__('Generate new Plugin Api Key', 'woocommerce'),
                'type' => 'checkbox',
                'description' => \__('Check and save changes to generate new Plugin Api Key', 'woocommerce'),
                'desc_tip' => false,
            ],
            'debug_mode' => [
                'title' => \__('Debug', 'woocommerce'),
                'type' => 'select',
                'default' => 'off',
                'options' => [
                    'off' => 'Off',
                    'errors' => 'Log errors only',
                    'all' => 'Log all data',
                ],
            ],
            'tax_mode' => [
                'title' => \__('Display rates with tax & duties', 'woocommerce'),
                'type' => 'select',
                'default' => 'tax_included',
                'options' => [
                    'tax_included' => 'Duties & tax included',
                    'without_tax' => 'Without duties & tax',
                    'both' => 'Both',
                ],
            ],
            'delivery_dates_display_mode' => [
                'title' => \__('Delivery dates display mode', 'woocommerce'),
                'type' => 'select',
                'default' => 'description',
                'options' => [
                    'description' => 'Show as a description',
                    'none' => 'Do not show',
                ],
            ],
            'delivery_dates_display_format' => [
                'title' => \__('Delivery dates display format', 'woocommerce'),
                'type' => 'select',
                'default' => 'dates',
                'options' => [
                    'dates' => 'Dates',
                    'quantity' => 'Qty of days in transit',
                ],
            ],
            'info_messages_display_settings' => [
                'title' => \__('Info messages display settings', 'woocommerce'),
                'type' => 'select',
                'default' => 'description',
                'options' => [
                    'description' => 'Show as a description',
                    'none' => 'Do not show',
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function calculate_shipping($package = []): void
    {
        $rates = $this->get_rates($package);

        if ($rates) {
            foreach ($rates as $rate) {
                $this->add_rate($rate);
            }
        }
    }

    /**
     * Get rates.
     *
     * @param array $package package array
     */
    private function get_rates(array $package): array
    {
        if (!$this->instance_id) {
            return [];
        }

        $rates_request_body_builder = new RatesRequestBodyBuilder($package);
        // build body for the request
        $rates_request_body = $rates_request_body_builder->build();

        $calcurates_client = new CalcuratesHttpClient($this->calcurates_api_key, $this->calcurates_api_url, $this->debug_mode);
        // get request results
        $response = $calcurates_client->get_rates($rates_request_body);
        if (!$response) {
            return [];
        }

        $rates = new Rates($response, $this->tax_mode, $package);
        $rates->apply_tax_mode();

        return $rates->convert_rates_to_wc_rates();
    }

    /**
     * {@inheritdoc}
     */
    public function process_admin_options(): bool
    {
        parent::process_admin_options();

        // TODO: needs refactor
        $key = 'woocommerce_'.$this->id.'_generate_new_api_key';
        if (isset($_POST[$key]) && '1' === $_POST[$key]) {
            $this->update_option('generate_new_api_key', 'no');
            $hash = \wc_rand_hash();
            \update_option(WCCalcurates::get_prefix().'key', $hash);
            $this->update_option('plugin_api_key', $hash);
        }

        return true;
    }
}
