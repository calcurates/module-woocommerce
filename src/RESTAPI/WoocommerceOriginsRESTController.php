<?php

declare(strict_types=1);

namespace Calcurates\RESTAPI;

use Calcurates\Basic;
use Calcurates\Origins\OriginUtils;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

if (!\class_exists(WoocommerceOriginsRESTController::class)) {
    /**
     * Calcurates origins REST API controller.
     */
    class WoocommerceOriginsRESTController extends \WP_REST_Controller
    {
        public function __construct()
        {
            $this->namespace = 'calcurates/v1';
            $this->rest_base = 'woocommers-origins';
        }

        /**
         * Register routes.
         */
        public function register_routes(): void
        {
            register_rest_route($this->namespace, '/'.$this->rest_base, [
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

            return $x_api_key && $x_api_key === get_option(Basic::get_prefix().'key');
        }

        /**
         * Get response result.
         */
        public function get_data(\WP_REST_Request $request): array
        {
            return [
                'origins' => OriginUtils::getInstance()->get_origins_for_rest(),
            ];
        }
    }
}
