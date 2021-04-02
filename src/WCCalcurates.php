<?php

namespace Calcurates;

use Calcurates\Assets;
use Calcurates\WCBootstarp;
use Calcurates\RESTAPI\Woocommerce_Settings_REST_Controller;

// Stop direct HTTP access.
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists(WCCalcurates::class)) {
    /**
     * Base plugin class
     */
    class WCCalcurates
    {
        protected $wc_bootsrap;
        protected $assets;
        protected $wc_settings_routes;

        public function __construct()
        {
            $this->wc_bootsrap = new WCBootstarp();
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
            $this->enqueue_styles();
        }

        /**
         * Register REST API routes
         *
         * @return void
         */
        public function restapi_register_routes()
        {
            add_action('rest_api_init', [new Woocommerce_Settings_REST_Controller(), 'register_routes']);
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
        public function enqueue_styles()
        {
            add_action('wp_enqueue_scripts', [$this->assets, 'enqueue_styles']);
        }
    }
}
