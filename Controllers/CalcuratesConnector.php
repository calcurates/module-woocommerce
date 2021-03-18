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

        $coupons = WC()->cart->get_coupons();
        $coupon = reset($coupons);

        $data = [
            'promoCode' => $coupon ? $coupon->get_code() : null, // FIXME coud be few coupons
            'shipTo' => self::prepare_ship_to_data(),
            "products" => self::prepare_products_data($package),
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
                'id' => 'calcurates:' . $rate->id,
                'label' => $rate->name,
                'cost' => $rate->rate->cost,
                'package' => $package,
                'taxes' => is_numeric($rate->rate->tax) ? [$rate->rate->tax] : '',
                'meta_data' => [
                    'message' => $rate->message,
                ],
            ];
        }

        foreach ($response->shippingOptions->freeShipping as $rate) {
            if ($rate->success) {
                $ready_rates[] = [
                    'id' => 'calcurates:' . $rate->id,
                    'label' => $rate->name,
                    'cost' => 0,
                    'package' => $package,
                    'taxes' => is_numeric($rate->rate->tax) ? [$rate->rate->tax] : '',
                    'meta_data' => [
                        'message' => $rate->message,
                        'delivery_date_from' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->from : null,
                        'delivery_date_to' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->to : null,
                    ],
                ];
            }
        }

        foreach ($response->shippingOptions->tableRates as $table_rate) {

            if ($table_rate->success) {

                if ($table_rate->methods && is_array($table_rate->methods)) {

                    foreach ($table_rate->methods as $rate) {

                        if ($rate->success) {
                            $ready_rates[] = [
                                'id' => 'calcurates:' . $rate->id,
                                'label' => $rate->name,
                                'cost' => $rate->rate->cost,
                                'package' => $package,
                                'taxes' => is_numeric($rate->rate->tax) ? [$rate->rate->tax] : '',
                                'meta_data' => [
                                    'message' => $table_rate->message,
                                    'delivery_date_from' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->from : null,
                                    'delivery_date_to' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->to : null,
                                ],
                            ];
                        }

                    }
                }

            }
        }

        if ($debug == 'all') {
            Logger::log('$ready_rates', (array) $ready_rates);
        }

        return $ready_rates;
    }

    public static function prepare_ship_to_data()
    {
        $contact_name = null;
        $country_code = null;
        $customer_session_data = WC()->session->get('customer');
        $ship_to_different_address = WC()->session->get('ship_to_different_address') ?? 0;
        $postcode = $ship_to_different_address ? ($customer_session_data['shipping_postcode'] ?: "string"): ($customer_session_data['postcode'] ?: "string");
        $first_name = $ship_to_different_address ? ($customer_session_data['shipping_first_name'] ?: null): ($customer_session_data['first_name'] ?: null);
        $last_name = $ship_to_different_address ? ($customer_session_data['shipping_last_name'] ?: null): ($customer_session_data['last_name'] ?: null);
        $company = $ship_to_different_address ? ($customer_session_data['shipping_company'] ?: null): ($customer_session_data['company'] ?: null);
        $phone = $customer_session_data['phone'] ?: null;
        $state = $ship_to_different_address ? ($customer_session_data['shipping_state'] ?: null): ($customer_session_data['state'] ?: null);
        $city = $ship_to_different_address ? ($customer_session_data['shipping_city'] ?: null): ($customer_session_data['city'] ?: null);
        $addr_1 = $ship_to_different_address ? ($customer_session_data['shipping_address_1'] ?: null): ($customer_session_data['address_1'] ?: null);
        $addr_2 = $ship_to_different_address ? ($customer_session_data['shipping_address_2'] ?: null): ($customer_session_data['address_2'] ?: null);

        if ($first_name) {
            $contact_name .= $first_name;
        }
        if ($last_name) {
            $contact_name .= " " . $last_name;
        }

        if (\array_key_exists('shipping_country', (array) $customer_session_data) && $customer_session_data['shipping_country']) {
            $country_code = $customer_session_data['shipping_country'];
        } else {
            $default_location = wc_get_customer_default_location();

            if ($default_location['country']) {
                $country_code = $default_location['country'];
            }
        }

        $ship_to = [
            'country' => $country_code,
            'city' => $city, // FIXME it could be empty in WC but in api it requires even as empty param,
            'contactName' => $contact_name,
            'companyName' => $company,
            'contactPhone' => $phone,
            'regionCode' => null,
            'regionName' => $state,
            'postalCode' => $postcode, // FIXME it could be empty in WC but in api it requires
            'addressLine1' => $addr_1,
            'addressLine2' => $addr_2,
        ];

        return $ship_to;
    }

    public static function prepare_products_data($package)
    {

        $products = [];

        foreach ($package['contents'] as $cart_product) {

            $product = $cart_product['data'];

            if ($product->is_virtual() || $product->is_downloadable()) {
                continue;
            }

            $data = [
                "quoteItemId" => $cart_product['product_id'], // FIXME rename later to product_id or id
                "sku" => $product->get_sku() ?: null,
                "priceWithTax" => $cart_product['line_total'] + $cart_product['line_tax'],
                "priceWithoutTax" => $cart_product['line_total'],
                "discountAmount" => 0,
                "quantity" => $cart_product['quantity'],
                "weight" => (float) $product->get_weight(),
                "length" => (float) $product->get_length(),
                "width" => (float) $product->get_width(),
                "height" => (float) $product->get_height(),
                "inventories" => [
                    [
                        "source" => null,
                        "quantity" => $product->get_stock_quantity(),
                    ],
                ],
                "attributes" => [
                    "date_created" => $product->get_date_created() ? $product->get_date_created()->getTimestamp() : null,
                    "date_modified" => $product->get_date_modified() ? $product->get_date_modified()->getTimestamp() : null,
                    "status" => $product->get_status(),
                    "is_featured" => $product->is_featured(),
                    "catalog_visibility" => $product->get_catalog_visibility(),
                    "price" => (float) $product->get_price(),
                    "regular_price" => (float) $product->get_regular_price(),
                    "sale_price" => (float) $product->get_sale_price(),
                    "date_on_sale_from" => $product->get_date_on_sale_from() ? $product->get_date_on_sale_from()->getTimestamp() : null,
                    "date_on_sale_to" => $product->get_date_on_sale_to() ? $product->get_date_on_sale_to()->getTimestamp() : null,
                    "total_sales" => $product->get_total_sales(),
                    "managing_stock" => $product->managing_stock(),
                    "is_in_stock" => $product->is_in_stock(),
                    "backorders_allowed" => $product->backorders_allowed(),
                    "low_stock_amount" => $product->get_low_stock_amount() ?: null,
                    "is_sold_individually" => $product->is_sold_individually(),
                    "purchase_note" => $product->get_purchase_note(),
                    "virtual" => $product->is_virtual(),
                    "downloadable" => $product->is_downloadable(),
                    "categories" => $product->get_category_ids(),
                    "tags" => $product->get_tag_ids(),
                ],
            ];

            if ($cart_product['variation_id'] && $product->get_parent_id()) { // variation

                $parent_product = wc_get_product($product->get_parent_id());
                $wc_product_attrs = $parent_product->get_attributes();

                if (empty($data['attributes']['categories'])) {
                    $data['attributes']['categories'] = $parent_product->get_category_ids();
                }

                if (empty($data['attributes']['tags'])) {
                    $data['attributes']['tags'] = $parent_product->get_tag_ids();
                }

                foreach ($product->get_variation_attributes(false) as $taxonomy => $terms_slug) {

                    if ($terms_slug) {

                        $term_obj = get_term_by('slug', $terms_slug, $taxonomy);

                        if ($term_obj) {
                            $term_id = $term_obj->term_id;
                            $data['attributes']['variation'][] = $term_obj->term_id;
                        }
                    }

                }

            } else {
                $wc_product_attrs = $product->get_attributes();
            }

            foreach ($wc_product_attrs as $attr_name => $attr_obj) {
                if ($attr_obj->is_taxonomy()) {
                    $data['attributes'][$attr_name] = $attr_obj->get_options();
                }
            }

            $products[] = $data;
        }
        return $products;
    }

}
