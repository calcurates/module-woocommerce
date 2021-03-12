<?php
namespace Calcurates\Controllers;

use Calcurates\Controllers\Logger;

class CalcuratesConnector
{

    public static function get_rates($args)
    {

        $defaults = [
            'api_key' => '',
            'debug_mode' => 'off',
            'package' => [],
        ];

        $args = wp_parse_args($args, $defaults);

        $api_key = $args['api_key'];
        $debug = $args['debug_mode'];
        $package = $args['package'];

        $ready_rates = [];

        $data = [
            'shipTo' => self::prepare_ship_to_data(),
            "products" => self::prepare_products_data(),
        ];

        $args = [
            'timeout' => 10,
            'method' => 'POST',
            'headers' => [
                'X-API-KEY' => $api_key,
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode($data),
        ];

        if ($debug == 'all') {
            Logger::log('Prepared rates request', (array) $args);
        }

        $result = wp_safe_remote_request('https://staging-api.calcurates.com/api/magento2/rates', $args);

        if (is_wp_error($result) || wp_remote_retrieve_response_code($result) != 200) {

            if ($debug == 'all' || $debug == 'errors') {
                Logger::log('Rates request', (array) $result);
            }

            return false;
        }

        $response = json_decode(wp_remote_retrieve_body($result));

        foreach ($response->shippingOptions->flatRates as $rate) {
            $ready_rates[] = [
                'id' => $rate->id,
                'label' => $rate->name,
                'cost' => $rate->rate->cost,
                'package' => $package,
            ];
        }

        if ($debug == 'all') {
            Logger::log('Rates request', (array) $ready_rates);
        }

        return $ready_rates;
    }

    public static function prepare_ship_to_data()
    {
        $country_code = "";
        $customer_session_data = WC()->session->get('customer');

        if (\array_key_exists('shipping_country', $customer_session_data) && $customer_session_data['shipping_country']) {
            $country_code = $customer_session_data['shipping_country'];
        } else {
            $default_location = wc_get_customer_default_location();

            if ($default_location['country']) {
                $country_code = $default_location['country'];
            }
        }

        $ship_to = [
            'country' => $country_code,
            'postalCode' => "string",
            'city' => "",
        ];

        return $ship_to;
    }

    public static function prepare_products_data()
    {
        $products = [];

        $products = [
            [
                "quoteItemId" => 1,
                "sku" => "string",
                "priceWithTax" => 2014,
                "priceWithoutTax" => 2000,
                "discountAmount" => 0,
                "quantity" => 1,
                "inventories" => [
                    [
                        "source" => "string",
                        "quantity" => 1,
                    ],
                ],
            ],
        ];

        return $products;
    }

}
