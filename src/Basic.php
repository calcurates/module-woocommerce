<?php

namespace Calcurates;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

if (!\class_exists(Basic::class)) {
    /**
     * Storage of basic information
     */
    class Basic
    {
        /**
         * Get version.
         */
        public static function get_version(): string
        {
            return '0.0.1';
        }

        /**
         * String to prefix option names, settings, etc. in shared spaces.
         */
        public static function get_prefix(): string
        {
            return 'wc_calcurates_';
        }

        /**
         * Plugin text domain
         */
        public static function get_plugin_text_domain(): string
        {
            return 'wc-calcurates';
        }

        /**
         * Get plugin dir path
         */
        public static function get_plugin_dir_path(): string
        {
            return \trailingslashit(\realpath(__DIR__ . \DIRECTORY_SEPARATOR . '..'));
        }
    }
}
