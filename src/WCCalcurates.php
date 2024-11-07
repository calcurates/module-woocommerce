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
        private WCBootstrap $wc_bootstrap;
        private Assets $assets;
        private OriginsTaxonomy $origins_taxonomy;

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
            \add_action('rest_api_init', [new WoocommerceSettingsRESTController(), 'register_routes']);
            \add_action('rest_api_init', [new WoocommerceOriginsRESTController(), 'register_routes']);
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
            \add_action('wp_enqueue_scripts', [$this->assets, 'enqueue_styles']);
            \add_action('wp_enqueue_scripts', [$this->assets, 'enqueue_js']);
        }

        /**
         * Get version.
         */
        public static function get_version(): string
        {
            static $version = null;
            if ($version) {
                return $version;
            }

            $composer = \file_get_contents(self::get_plugin_dir_path().'composer.json');
            if (false !== $composer) {
                $json = \json_decode($composer, true);
                $version = $json['version'];
            } else {
                $version = 'dev';
            }

            return $version;
        }

        /**
         * String to prefix option names, settings, etc. in shared spaces.
         */
        public static function get_prefix(): string
        {
            return 'wc_calcurates_';
        }

        /**
         * Plugin text domain.
         */
        public static function get_plugin_text_domain(): string
        {
            return 'wc-calcurates';
        }

        /**
         * Get plugin dir path.
         */
        public static function get_plugin_dir_path(): string
        {
            return \trailingslashit(\dirname(__DIR__));
        }
    }
}
