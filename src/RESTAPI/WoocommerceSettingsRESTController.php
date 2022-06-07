<?php

declare(strict_types=1);

namespace Calcurates\RESTAPI;

use Calcurates\WCCalcurates;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

if (!\class_exists(WoocommerceSettingsRESTController::class)) {
    /**
     * Calcurates sync settings REST API controller.
     */
    class WoocommerceSettingsRESTController extends \WP_REST_Controller
    {
        public function __construct()
        {
            $this->namespace = 'calcurates/v1';
            $this->rest_base = 'woocommers-settings';
        }

        /**
         * Register routes.
         */
        public function register_routes(): void
        {
            \register_rest_route($this->namespace, '/'.$this->rest_base, [
                [
                    'methods' => 'GET',
                    'callback' => [$this, 'get_data'],
                    'permission_callback' => [$this, 'permissions_check'],
                ],
                // TODO: need schema
            ]);
        }

        /**
         * Check if request is allowed.
         */
        public function permissions_check(\WP_REST_Request $request): bool
        {
            $x_api_key = $request->get_header('X_API_KEY');

            return $x_api_key && $x_api_key === \get_option(WCCalcurates::get_prefix().'key');
        }

        /**
         * Get response result.
         */
        public function get_data(\WP_REST_Request $request): array
        {
            // TODO: refactor array building in oop manner
            $data['time_zone'] = \wc_timezone_string();
            $data['website_id'] = \get_current_blog_id();
            $data['currency'] = \get_woocommerce_currency();
            $data['weight_unit'] = \get_option('woocommerce_weight_unit');
            $data['dimension_unit'] = \get_option('woocommerce_dimension_unit');
            $data['customer_roles'] = [
                [
                    'value' => 'customer',
                    'title' => 'Customer',
                ],
                [
                    'value' => 'guest',
                    'title' => 'Guest',
                ],
            ];
            $data['attrs'][] = $this->get_terms();
            $data['attrs'][] = $this->get_tags();
            $product_attrs = $this->get_attrs();
            $data['attrs'] = \array_merge($data['attrs'], $product_attrs);

            $data['attrs'][] = [
                'title' => 'SKU',
                'name' => 'sku',
                'field_type' => 'string',
            ];

            // date_created
            $data['attrs'][] = [
                'title' => 'Date created',
                'name' => 'date_created',
                'field_type' => 'number',
            ];

            // date_modified
            $data['attrs'][] = [
                'title' => 'Date modified',
                'name' => 'date_modified',
                'field_type' => 'number',
            ];

            // featured
            $data['attrs'][] = [
                'title' => 'Featured',
                'name' => 'is_featured',
                'field_type' => 'bool',
            ];

            // price
            $data['attrs'][] = [
                'title' => 'Price',
                'name' => 'price',
                'field_type' => 'number',
            ];

            // regular_price
            $data['attrs'][] = [
                'title' => 'Regular price',
                'name' => 'regular_price',
                'field_type' => 'number',
            ];

            // sale_price
            $data['attrs'][] = [
                'title' => 'Sale price',
                'name' => 'sale_price',
                'field_type' => 'number',
            ];

            // date_on_sale_from
            $data['attrs'][] = [
                'title' => 'Date on sale from',
                'name' => 'date_on_sale_from',
                'field_type' => 'number',
            ];

            // date_on_sale_to
            $data['attrs'][] = [
                'title' => 'Date on sale to',
                'name' => 'date_on_sale_to',
                'field_type' => 'number',
            ];

            // total_sales
            $data['attrs'][] = [
                'title' => 'Total sales',
                'name' => 'total_sales',
                'field_type' => 'number',
            ];

            // manage_stock
            $data['attrs'][] = [
                'title' => 'Managing stock',
                'name' => 'managing_stock',
                'field_type' => 'bool',
            ];

            // is_in_stock
            $data['attrs'][] = [
                'title' => 'In stock',
                'name' => 'is_in_stock',
                'field_type' => 'bool',
            ];

            // backorders
            $data['attrs'][] = [
                'title' => 'Backorders',
                'name' => 'backorders_allowed',
                'field_type' => 'bool',
            ];

            // low_stock_amount
            $data['attrs'][] = [
                'title' => 'Low stock amount',
                'name' => 'low_stock_amount',
                'field_type' => 'number',
            ];

            // is_sold_individually
            $data['attrs'][] = [
                'title' => 'Sold individually',
                'name' => 'is_sold_individually',
                'field_type' => 'bool',
            ];

            // weight
            $data['attrs'][] = [
                'title' => 'Weight',
                'name' => 'weight',
                'field_type' => 'number',
            ];

            // length
            $data['attrs'][] = [
                'title' => 'Length',
                'name' => 'length',
                'field_type' => 'number',
            ];

            // width
            $data['attrs'][] = [
                'title' => 'Width',
                'name' => 'width',
                'field_type' => 'number',
            ];

            // height
            $data['attrs'][] = [
                'title' => 'Height',
                'name' => 'height',
                'field_type' => 'number',
            ];

            // shipping class
            $data['attrs'][] = $this->get_shipping_classes();

            return $data;
        }

        /**
         * Get shipping classes data.
         */
        private function get_shipping_classes(): array
        {
            $data = [
                'title' => 'Shipping class',
                'name' => 'shipping_class',
                'field_type' => 'collection',
                'can_multi' => false,
                'values' => [],
            ];

            $terms = \get_terms('product_shipping_class', [
                'hide_empty' => false,
            ]);

            foreach ((array) $terms as $term) {
                $data['values'][] = [
                    'value' => $term->slug,
                    'title' => $term->name,
                ];
            }

            return $data;
        }

        /**
         * Get categories data.
         */
        private function get_terms(): array
        {
            $data = [
                'title' => 'Categories',
                'name' => 'categories',
                'field_type' => 'collection',
                'can_multi' => true,
                'values' => [],
            ];

            $terms = \get_terms('product_cat', [
                'hide_empty' => false,
            ]);

            foreach ((array) $terms as $term) {
                // code...

                $data['values'][] = [
                    'value' => $term->term_id,
                    'title' => $term->name,
                ];
            }

            return $data;
        }

        /**
         * Get tags data.
         */
        private function get_tags(): array
        {
            $data = [
                'title' => 'Tags',
                'name' => 'tags',
                'field_type' => 'collection',
                'can_multi' => true,
                'values' => [],
            ];

            $terms = \get_terms('product_tag', [
                'hide_empty' => false,
            ]);

            foreach ((array) $terms as $term) {
                // code...

                $data['values'][] = [
                    'value' => $term->term_id,
                    'title' => $term->name,
                ];
            }

            return $data;
        }

        /**
         * Get attrs data.
         */
        private function get_attrs(): array
        {
            $attrs = [];

            $attribute_taxonomies = \wc_get_attribute_taxonomies();

            foreach ($attribute_taxonomies as $attribute) {
                $taxonomy = \wc_attribute_taxonomy_name($attribute->attribute_name);

                if (\taxonomy_exists($taxonomy)) {
                    $data = [
                        'title' => $attribute->attribute_label,
                        'name' => 'pa_'.$attribute->attribute_name,
                        'field_type' => 'collection',
                        'can_multi' => true,
                        'values' => [],
                    ];

                    $terms = \get_terms($taxonomy, [
                        'hide_empty' => false,
                    ]);

                    foreach ((array) $terms as $term) {
                        $data['values'][] = [
                            'value' => $term->term_id,
                            'title' => $term->name,
                        ];
                    }

                    $attrs[] = $data;
                }
            }

            return $attrs;
        }
    }
}
