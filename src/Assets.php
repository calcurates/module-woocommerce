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
            $styles_url = plugins_url('/assets/css/' . $file_name, __FILE__);

            $styles_url = apply_filters('wc_calcurates_load_style', $styles_url);

            return wp_register_style(Basic::get_plugin_text_domain(), $styles_url);
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
