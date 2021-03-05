<?php
namespace Calcurates\RESTAPI\Routes\Endpoints;

use Inpsyde\WPRESTStarter\Common\Endpoint;
use Inpsyde\WPRESTStarter\Common\Request\FieldProcessor;
use Inpsyde\WPRESTStarter\Core;
use Inpsyde\WPRESTStarter\Core\Field\Field;
use Inpsyde\WPRESTStarter\Factory\ResponseFactory;

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

        // $data['categories'] = $this->get_terms();
        // $data['tags'] = $this->get_tags();
        // $data['attrs'] = $this->get_attrs();

        return $this->response_factory->create([
            $data,
            200,
        ]);

    }

    private function get_terms()
    {
        $data = [
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
                'id' => $term->term_id,
                'title' => $term->name,
                'parent' => $term->parent,
            ];
        }

        return $data;
    }
    private function get_tags()
    {
        $data = [
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
                'id' => $term->term_id,
                'title' => $term->name,
                'parent' => $term->parent,
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
                        'id' => $term->term_id,
                        'title' => $term->name,
                    ];
                }

                $attrs[] = $data;
            }
        }

        return $attrs;
    }
}
