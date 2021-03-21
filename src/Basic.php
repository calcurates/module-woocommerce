<?php

namespace Calcurates;

// Stop direct HTTP access.
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists(Basic::class)) {
    /**
     * Storage of basic information
     */
    class Basic
    {

        /**
         * Get version.
         *
         * @return string
         */
        public static function get_version(): string
        {
            return '1.0.0';
        }

        /**
         * String to prefix option names, settings, etc. in shared spaces.
         *
         * @return string
         */
        public static function get_prefix(): string
        {
            return 'wc_calcurates_';
        }

        /**
         * Plugin text domain
         *
         * @return string
         */
        public static function get_plugin_text_domain(): string
        {
            return 'wc-calcurates';
        }
    }
}
