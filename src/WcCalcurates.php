<?php

namespace Calcurates;

use Calcurates\RESTAPI\Routes\MultisiteRoutes;
use Calcurates\RESTAPI\Routes\WooCommmerceSettingsRoutes;

// Stop direct HTTP access.
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists(WcCalcurates::class)) {
    /**
     * Storage of basic information
     */
    class WcCalcurates
    {

        /**
         * run
         *
         * @return void
         */
        public function run()
        {
            $this->restapi_register_routes();
        }

        /**
         * Register REST API routes
         *
         * @return void
         */
        public function restapi_register_routes()
        {
            add_action('rest_api_init', [new WooCommmerceSettingsRoutes(), 'register_route']);
            add_action('rest_api_init', [new MultisiteRoutes(), 'register_route']);

        }
    }
}
