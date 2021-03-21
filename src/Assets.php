<?php

namespace Calcurates;

use Calcurates\Basic;

// Stop direct HTTP access.
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists(Assets::class)) {
    /**
     * Register styles.
     */
    class Assets
    {

        /**
         * Register stylesheet
         *
         * @param string $file_name
         *
         * @return void
         */
        public function register_style(string $file_name): bool
        {
            return wp_register_style(Basic::get_plugin_text_domain(), plugins_url('/assets/css/' . $file_name, __FILE__));
        }

        /**
         * Add stylesheet
         *
         * @param  mixed $file_name
         * @return void
         */
        public function enqueue_styles()
        {
            if ($this->register_style('calcurates-checkout.css') && (is_cart() || is_checkout())) {
                wp_enqueue_style(Basic::get_plugin_text_domain());
            }
        }

    }
}
