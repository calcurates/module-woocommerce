<?php

declare(strict_types=1);

namespace Calcurates;

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
        private $wc_bootsrap;
        /**
         * @var Assets
         */
        private $assets;

        public function __construct()
        {
            $this->wc_bootsrap = new WCBootstrap();
            $this->assets = new Assets();
        }

        public function run(): void
        {
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
        }

        /**
         * Set WC hooks and add new shipping method.
         */
        public function woocommerce_bootstrap(): void
        {
            $this->wc_bootsrap->run();
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
