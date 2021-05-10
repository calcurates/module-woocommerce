<?php

declare(strict_types=1);

namespace Calcurates;

use Calcurates\Origins\OriginsTaxonomy;
use Calcurates\RESTAPI\WoocommerceOriginsRESTController;
use Calcurates\RESTAPI\WoocommerceSettingsRESTController;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

if (!\class_exists(WCCalcurates::class)) {
    /**
     * Base plugin class.
     */
    class WCCalcurates
    {
        /**
         * @var WCBootstrap
         */
        private $wc_bootstrap;
        /**
         * @var Assets
         */
        private $assets;
        /**
         * @var OriginsTaxonomy
         */
        private $origins_taxonomy;

        public function __construct()
        {
            $this->origins_taxonomy = new OriginsTaxonomy();
            $this->wc_bootstrap = new WCBootstrap();
            $this->assets = new Assets();
        }

        public function run(): void
        {
            $this->origins_taxonomy->init();
            $this->restapi_register_routes();
            $this->woocommerce_bootstrap();
            $this->enqueue_styles();
        }

        /**
         * Register REST API routes.
         */
        public function restapi_register_routes(): void
        {
            add_action('rest_api_init', [new WoocommerceSettingsRESTController(), 'register_routes']);
            add_action('rest_api_init', [new WoocommerceOriginsRESTController(), 'register_routes']);
        }

        /**
         * Set WC hooks and add new shipping method.
         */
        public function woocommerce_bootstrap(): void
        {
            $this->wc_bootstrap->run();
        }

        /**
         * Register CSS styles.
         */
        public function enqueue_styles(): void
        {
            add_action('wp_enqueue_scripts', [$this->assets, 'enqueue_styles']);
        }
    }
}
