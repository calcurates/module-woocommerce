<?php

declare(strict_types=1);

namespace Calcurates;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

if (!\class_exists(Basic::class)) {
    /**
     * Storage of basic information.
     */
    class Basic
    {
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
