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

class MultisiteRoutesReadEndpoint implements Endpoint\RequestHandler
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
        $data = [];

        if (is_multisite()) {
            $args = array(
                'public' => 1,
                'archived' => 0,
                'mature' => 0,
                'spam' => 0,
                'deleted' => 0,
                'fields' => 'ids',
            );

            $sites = get_sites($args);

            foreach ($sites as $site) {
                $blog_details = get_blog_details();
                $data[] = [
                    'site_id' => $blog_details->site_id,
                    'title' => $blog_details->blogname,
                    'url' => $blog_details->home,
                ];
            }
        } else {
            $data[] = [
                'site_id' => 1,
                'title' => get_bloginfo('name'),
                'url' => home_url(),
            ];
        }

        return $this->response_factory->create([
            $data,
            200,
        ]);
    }
}
