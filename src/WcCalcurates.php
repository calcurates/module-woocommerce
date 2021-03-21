<?php

namespace Calcurates;

use Calcurates\Assets;
use Calcurates\RESTAPI\Routes\MultisiteRoutes;
use Calcurates\RESTAPI\Routes\WooCommmerceSettingsRoutes;
use Calcurates\WCBootstarp;

// Stop direct HTTP access.
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists(WcCalcurates::class)) {
    /**
     * Base plugin class
     */
    class WcCalcurates
    {

        protected $wc_bootsrap;
        protected $assets;
        protected $wc_settings_routes;
        protected $multisite_routes;

        public function __construct()
        {
            $this->wc_bootsrap = new WCBootstarp();
            $this->wc_settings_routes = new WooCommmerceSettingsRoutes();
            $this->multisite_routes = new MultisiteRoutes();
            $this->assets = new Assets();
        }
        /**
         * run
         *
         * @return void
         */
        public function run()
        {
            $this->restapi_register_routes();
            $this->woocommerce_bootstrap();
            $this->register_styles();
        }

        /**
         * Register REST API routes
         *
         * @return void
         */
        public function restapi_register_routes()
        {
            add_action('rest_api_init', [$this->multisite_routes, 'register_route']);
            add_action('rest_api_init', [$this->wc_settings_routes, 'register_route']);
        }

        /**
         * Set WC hooks and add new shipping method
         *
         * @return void
         */
        public function woocommerce_bootstrap()
        {
            $this->wc_bootsrap->run();
        }

        /**
         * Register CSS styles
         *
         * @return void
         */
        public function register_styles()
        {
            $this->assets->register_style('calcurates-checkout.css');
        }
    }
}
