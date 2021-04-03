<?php

namespace Calcurates;

use Calcurates\Assets;
use Calcurates\RESTAPI\WoocommerceSettingsRESTController;
use Calcurates\WCBootstarp;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

if (!\class_exists(WCCalcurates::class)) {
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
        public function run(): void
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
        public function restapi_register_routes(): void
        {
            \add_action('rest_api_init', array(new WoocommerceSettingsRESTController(), 'register_routes'));
        }

        /**
         * Set WC hooks and add new shipping method
         *
         * @return void
         */
        public function woocommerce_bootstrap(): void
        {
            $this->wc_bootsrap->run();
        }

        /**
         * Register CSS styles
         *
         * @return void
         */
        public function enqueue_styles(): void
        {
            \add_action('wp_enqueue_scripts', array($this->assets, 'enqueue_styles'));
        }
    }
}
