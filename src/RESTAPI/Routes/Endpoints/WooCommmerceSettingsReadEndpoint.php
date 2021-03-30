<?php
namespace Calcurates\RESTAPI\Routes\Endpoints;

use Inpsyde\WPRESTStarter\Common\Endpoint;
use Inpsyde\WPRESTStarter\Common\Request\FieldProcessor;
use Inpsyde\WPRESTStarter\Core;
use Inpsyde\WPRESTStarter\Core\Field\Field;
use Inpsyde\WPRESTStarter\Factory\ResponseFactory;

// Stop direct HTTP access.
if (!defined('ABSPATH')) {
    exit;
}

class WooCommmerceSettingsReadEndpoint implements Endpoint\RequestHandler
{

    /**
     * @var FieldProcessor
     */
    private $field_processor;

    /**
     * @var string
     */
    private $object_type;

    /**
     * @var ResponseFactory
     */
    private $response_factory;

    /**
     * Constructor. Sets up the properties.
     * @param Endpoint\Schema $schema           Optional. Schema object. Defaults to null.
     * @param FieldProcessor  $field_processor  Optional. Field processor object. Defaults to null.
     * @param ResponseFactory $response_factory Optional. Response factory object. Defaults to null.
     */
    public function __construct(
        Endpoint\Schema $schema = null,
        FieldProcessor $field_processor = null,
        ResponseFactory $response_factory = null
    ) {

        $this->object_type = $schema ? $schema->title() : '';

        $this->field_processor = $field_processor ?? new Core\Request\FieldProcessor();

        $this->response_factory = $response_factory ?? new ResponseFactory();
    }

    /**
     * Handles the given request object and returns the according response object.
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response Response.
     */
    public function handle_request(\WP_REST_Request $request): \WP_REST_Response
    {
        $data['time_zone'] = get_option('timezone_string');
        $data['gmt_offset'] = get_option('gmt_offset');
        $data['currency'] = get_woocommerce_currency();
        $data['weight_unit'] = get_option('woocommerce_weight_unit');
        $data['dimension_unit'] = get_option('woocommerce_dimension_unit');
        $data['attrs'][] = $this->get_terms();
        $data['attrs'][] = $this->get_tags();
        $product_attrs = $this->get_attrs();
        $data['attrs'] = array_merge($data['attrs'], $product_attrs);

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

        return $this->response_factory->create([
            $data,
            200,
        ]);

    }

    private function get_terms()
    {
        $data = [
            'title' => 'Categories',
            'name' => 'categories',
            'field_type' => 'collection',
            'can_multi' => true,
            'values' => [],
        ];

        $terms = get_terms('product_cat', array(
            'hide_empty' => false,
        ));

        foreach ((array) $terms as $term) {
            # code...

            $data['values'][] = [
                'value' => $term->term_id,
                'title' => $term->name,
            ];
        }

        return $data;
    }
    private function get_tags()
    {
        $data = [
            'title' => 'Tags',
            'name' => 'tags',
            'field_type' => 'collection',
            'can_multi' => true,
            'values' => [],
        ];

        $terms = get_terms('product_tag', array(
            'hide_empty' => false,
        ));

        foreach ((array) $terms as $term) {
            # code...

            $data['values'][] = [
                'value' => $term->term_id,
                'title' => $term->name,
            ];
        }

        return $data;
    }
    private function get_attrs()
    {

        $attrs = [];

        $attributes = array();
        $attribute_taxonomies = wc_get_attribute_taxonomies();

        foreach ($attribute_taxonomies as $attribute) {
            $taxonomy = wc_attribute_taxonomy_name($attribute->attribute_name);

            if (taxonomy_exists($taxonomy)) {
                $data = [
                    'title' => $attribute->attribute_label,
                    'name' => $attribute->attribute_name,
                    'field_type' => 'collection',
                    'can_multi' => true,
                    'values' => [],
                ];

                $terms = get_terms($taxonomy, array(
                    'hide_empty' => false,
                ));

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
