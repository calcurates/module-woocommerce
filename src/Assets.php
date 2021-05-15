<?php

declare(strict_types=1);

namespace Calcurates;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

if (!\class_exists(Assets::class)) {
    /**
     * Register styles.
     */
    class Assets
    {
        /**
         * Register stylesheet.
         */
        private function register_style(string $file_name): bool
        {
            $styles_url = \plugins_url('/assets/css/'.$file_name, __DIR__);

            $styles_url = \apply_filters('wc_calcurates_load_style', $styles_url);

            return \wp_register_style(WCCalcurates::get_plugin_text_domain(), $styles_url);
        }

        /**
         * Register JS.
         */
        private function register_js(string $file_name): bool
        {
            $js_url = \plugins_url('/assets/js/'.$file_name, __DIR__);

            $js_url = \apply_filters('wc_calcurates_load_js', $js_url);

            return \wp_register_script(WCCalcurates::get_plugin_text_domain(), $js_url, ['jquery']);
        }

        /**
         * Add stylesheet.
         */
        public function enqueue_styles(): void
        {
            if ($this->register_style('calcurates-checkout.css') && (\is_cart() || \is_checkout())) {
                \wp_enqueue_style(WCCalcurates::get_plugin_text_domain());
            }
        }

        /**
         * Add JS scripts.
         */
        public function enqueue_js(): void
        {
            if ($this->register_js('calcurates-checkout.js') && (\is_cart() || \is_checkout())) {
                \wp_enqueue_script(WCCalcurates::get_plugin_text_domain());
            }
        }
    }
}
