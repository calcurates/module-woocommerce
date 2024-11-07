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
        private static $date_picker_script_name = 'air-datepicker';
        private static $php_date_formatter_script_name = 'php-date-formatter';

        /**
         * Register stylesheet.
         */
        private function register_style(string $styles_name, string $src): bool
        {
            $styles_url = \plugins_url($src, __DIR__);

            $styles_url = \apply_filters('wc_calcurates_load_style', $styles_url);

            return \wp_register_style($styles_name, $styles_url);
        }

        /**
         * Register JS.
         */
        private function register_js(string $script_name, string $src, array $deps = []): bool
        {
            $js_url = \plugins_url($src, __DIR__);

            $js_url = \apply_filters('wc_calcurates_load_js', $js_url);

            return \wp_register_script($script_name, $js_url, $deps);
        }

        /**
         * Add stylesheet.
         */
        public function enqueue_styles(): void
        {
            if ($this->register_style(WCCalcurates::get_plugin_text_domain(), '/assets/css/calcurates-checkout.css') && (\is_cart() || \is_checkout())) {
                \wp_enqueue_style(WCCalcurates::get_plugin_text_domain());
            }

            if ($this->register_style(self::$date_picker_script_name, '/assets/lib/air-datepicker/air-datepicker.css') && (\is_cart() || \is_checkout())) {
                \wp_enqueue_style(self::$date_picker_script_name);
            }
        }

        /**
         * Add JS scripts.
         */
        public function enqueue_js(): void
        {
            if ($this->register_js(WCCalcurates::get_plugin_text_domain(), '/assets/js/calcurates-checkout.js', ['jquery', 'wc-cart', 'wc-checkout', self::$date_picker_script_name]) && (\is_cart() || \is_checkout())) {
                // provide global vars
                $date = \current_datetime();
                $utcOffset = $date->format('Z');

                \wp_add_inline_script(WCCalcurates::get_plugin_text_domain(), 'var CALCURATES_GLOBAL = '.\json_encode(
                    [
                        'pluginDir' => \plugin_dir_url(__DIR__),
                        'lang' => \substr(\get_locale(), 0, 2),
                        'wpTimeZoneOffsetSeconds' => $utcOffset,
                        'dateFormat' => \get_option('date_format'),
                        'timeFormat' => \get_option('time_format'),
                    ]
                ).';');

                \wp_enqueue_script(WCCalcurates::get_plugin_text_domain());
            }

            if ($this->register_js(self::$php_date_formatter_script_name, '/assets/lib/php-date-formatter.min.js') && (\is_cart() || \is_checkout())) {
                \wp_enqueue_script(self::$php_date_formatter_script_name);
            }

            if ($this->register_js(self::$date_picker_script_name, '/assets/lib/air-datepicker/air-datepicker.js') && (\is_cart() || \is_checkout())) {
                \wp_enqueue_script(self::$date_picker_script_name);
            }
        }
    }
}
