<?php

declare(strict_types=1);

namespace Calcurates\HttpClient\RequestsBodyBuilders;

use Calcurates\Origins\OriginUtils;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

class RatesRequestBodyBuilder
{
    /**
     * Package from basket.
     *
     * @var array package array
     */
    private array $package;

    /**
     * Stored WPML language.
     */
    private ?string $wpml_language = null;

    public function __construct(array $package)
    {
        $this->package = $package;
    }

    /**
     * Build request body.
     *
     * @return array{
     *     promoCode: string|null,
     *     shipTo: array{
     *          country: string,
     *          city: string,
     *          contactName: string|null,
     *          companyName: string|null,
     *          contactPhone: string|null,
     *          regionCode: string|null,
     *          regionName: string|null,
     *          postalCode: string,
     *          addressLine1: string|null,
     *          addressLine2: string|null,
     *     }[],
     *     products: array{
     *         quoteItemId: int,
     *         sku: string,
     *         price: float,
     *         quantity: int,
     *         weight: float,
     *         origins: array{origin: string}[]|null,
     *         attributes: array<string, string>[],
     *     }[],
     *     customerGroup: string,
     *     estimate: bool,
     * }
     */
    public function build(): array
    {
        $is_wpml_available = $this->is_wpml_available();
        if ($is_wpml_available) {
            $this->wpml_store_language();
        }

        $coupons = \WC()->cart->get_coupons();
        $coupon = \reset($coupons);

        $data = [
            'promoCode' => $coupon ? $coupon->get_code() : null, // FIXME: could be few coupons
            'shipTo' => $this->prepare_ship_to_data(),
            'products' => $this->prepare_products_data(),
            'customerGroup' => \is_user_logged_in() ? 'customer' : 'guest',
            'estimate' => \is_checkout() ? false : true,
        ];

        if ($is_wpml_available) {
            $this->wpml_restore_language();
        }

        return $data;
    }

    /**
     * Prepare shipping data.
     */
    public function prepare_ship_to_data(): array
    {
        if (isset($_POST['post_data']) && $_POST['post_data']) {
            $post_data = [];
            \parse_str(\rawurldecode($_POST['post_data']), $post_data);

            if (isset($post_data['ship_to_different_address']) && $post_data['ship_to_different_address']) {
                $postcode = $post_data['shipping_postcode'] ?: $post_data['billing_postcode'];
                $first_name = $post_data['shipping_first_name'] ?: $post_data['billing_first_name'];
                $last_name = $post_data['shipping_last_name'] ?: $post_data['billing_last_name'];
                $company = $post_data['shipping_company'] ?: $post_data['billing_company'];
                $phone = $post_data['billing_phone']; // not exists in shipping_*
                $state = $post_data['shipping_state'] ?: $post_data['billing_state'];
                $city = $post_data['shipping_city'] ?: $post_data['billing_city'];
                $addr_1 = $post_data['shipping_address_1'] ?: $post_data['billing_address_1'];
                $addr_2 = $post_data['shipping_address_2'] ?: $post_data['billing_address_2'];
                $country_code = $post_data['shipping_country'] ?: $post_data['billing_country'];
            } else {
                $postcode = $post_data['billing_postcode'];
                $first_name = $post_data['billing_first_name'];
                $last_name = $post_data['billing_last_name'];
                $company = $post_data['billing_company'];
                $phone = $post_data['billing_phone'];
                $state = $post_data['billing_state'];
                $city = $post_data['billing_city'];
                $addr_1 = $post_data['billing_address_1'];
                $addr_2 = $post_data['billing_address_2'];
                $country_code = $post_data['billing_country'];
            }
        } else {
            $customer_session_data = \WC()->session->get('customer', []);

            $postcode = $customer_session_data['shipping_postcode'] ?: $customer_session_data['postcode'];
            $first_name = $customer_session_data['shipping_first_name'] ?: $customer_session_data['first_name'];
            $last_name = $customer_session_data['shipping_last_name'] ?: $customer_session_data['last_name'];
            $company = $customer_session_data['shipping_company'] ?: $customer_session_data['company'];
            $phone = $customer_session_data['shipping_phone'] ?: $customer_session_data['phone'];
            $state = $customer_session_data['shipping_state'] ?: $customer_session_data['state'];
            $city = $customer_session_data['shipping_city'] ?: $customer_session_data['city'];
            $addr_1 = $customer_session_data['shipping_address_1'] ?: $customer_session_data['address_1'];
            $addr_2 = $customer_session_data['shipping_address_2'] ?: $customer_session_data['address_2'];
            $country_code = $customer_session_data['shipping_country'] ?: $customer_session_data['country'];
        }

        if ($first_name || $last_name) {
            $contact_name = '';
            if ($first_name) {
                $contact_name .= $first_name;
            }
            if ($first_name && $last_name) {
                $contact_name .= ' ';
            }
            if ($last_name) {
                $contact_name .= $last_name;
            }
        } else {
            $contact_name = null;
        }

        return [
            'country' => $country_code,
            'city' => $city,
            'contactName' => $contact_name,
            'companyName' => $company,
            'contactPhone' => $phone,
            'regionCode' => $state,
            'regionName' => $this->get_state_name_by_code($country_code, $state),
            'postalCode' => $postcode,
            'addressLine1' => $addr_1,
            'addressLine2' => $addr_2,
        ];
    }

    /**
     * Prepare products data.
     */
    public function prepare_products_data(): array
    {
        $products = [];
        foreach ($this->package['contents'] as $cart_product) {
            /** @var \WC_Product $product */
            $product = $cart_product['data'];

            if ($product->is_virtual() || $product->is_downloadable()) {
                continue;
            }

            $origin_codes = OriginUtils::getInstance()->get_origin_codes_from_product($cart_product['product_id']);

            $is_backorder = false;
            if (null !== $product->get_stock_quantity()) {
                $is_backorder = $product->backorders_allowed() && $cart_product['quantity'] > $product->get_stock_quantity();
            }

            $data = [
                'quoteItemId' => $cart_product['product_id'],
                'sku' => $product->get_sku(),
                'price' => $cart_product['quantity'] ? ($cart_product['line_total'] / $cart_product['quantity']) : 0.0,
                'quantity' => $cart_product['quantity'],
                'weight' => (float) $product->get_weight(),
                'origins' => $origin_codes ? \array_map(static function (string $code): array {
                    return ['origin' => $code];
                }, $origin_codes) : null,
                'attributes' => [
                    'sku' => $product->get_sku(),
                    'length' => (float) $product->get_length(),
                    'width' => (float) $product->get_width(),
                    'height' => (float) $product->get_height(),
                    'date_created' => $product->get_date_created() ? $product->get_date_created()->getTimestamp() : null,
                    'date_modified' => $product->get_date_modified() ? $product->get_date_modified()->getTimestamp() : null,
                    'status' => $product->get_status(),
                    'is_featured' => $product->is_featured(),
                    'catalog_visibility' => $product->get_catalog_visibility(),
                    'price' => (float) $product->get_price(),
                    'regular_price' => (float) $product->get_regular_price(),
                    'sale_price' => (float) $product->get_sale_price(),
                    'date_on_sale_from' => $product->get_date_on_sale_from() ? $product->get_date_on_sale_from()->getTimestamp() : null,
                    'date_on_sale_to' => $product->get_date_on_sale_to() ? $product->get_date_on_sale_to()->getTimestamp() : null,
                    'total_sales' => $product->get_total_sales(),
                    'managing_stock' => $product->managing_stock(),
                    'is_in_stock' => $product->is_in_stock(),
                    'backorders_allowed' => $product->backorders_allowed(),
                    'is_backorder' => $is_backorder,
                    'low_stock_amount' => $product->get_low_stock_amount() ?: null,
                    'is_sold_individually' => $product->is_sold_individually(),
                    'purchase_note' => $product->get_purchase_note(),
                    'virtual' => $product->is_virtual(),
                    'downloadable' => $product->is_downloadable(),
                    'categories' => $product->get_category_ids(),
                    'tags' => $product->get_tag_ids(),
                    'shipping_class' => $product->get_shipping_class(),
                ],
            ];

            if ($product instanceof \WC_Product_Variation && $product->get_parent_id()) { // variation
                $parent_product = \wc_get_product($product->get_parent_id());
                /** @var array<string, \WC_Product_Attribute> $wc_product_attrs */
                $wc_product_attrs = $parent_product->get_attributes();

                if (empty($data['attributes']['categories'])) {
                    $data['attributes']['categories'] = $parent_product->get_category_ids();
                }

                if (empty($data['attributes']['tags'])) {
                    $data['attributes']['tags'] = $parent_product->get_tag_ids();
                }

                foreach ($product->get_variation_attributes(false) as $taxonomy => $terms_slug) {
                    if ($terms_slug) {
                        $term_obj = \get_term_by('slug', $terms_slug, $taxonomy);

                        if ($term_obj) {
                            $data['attributes']['variation'][] = $term_obj->term_id;
                        }
                    }
                }
            } else {
                /** @var array<string, \WC_Product_Attribute> $wc_product_attrs */
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

    /**
     * Get state name by code.
     */
    private function get_state_name_by_code(?string $country_code, ?string $state_code): ?string
    {
        if (!$country_code || !$state_code) {
            return null;
        }

        $states = \WC()->countries->get_states($country_code);

        if ($states && isset($states[$state_code])) {
            return $states[$state_code];
        }

        return null;
    }

    /**
     * Set default language for WPML plugin.
     */
    private function wpml_store_language(): void
    {
        $this->wpml_language = \apply_filters('wpml_current_language', null);

        $default_lang = \apply_filters('wpml_default_language', null);
        \do_action('wpml_switch_language', $default_lang);
    }

    /**
     * Restore WPML plugin language.
     */
    private function wpml_restore_language(): void
    {
        \do_action('wpml_switch_language', $this->wpml_language);
    }

    private function is_wpml_available(): bool
    {
        return \has_action('wpml_switch_language');
    }
}
