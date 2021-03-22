<?php

/**
 * Plugin Name:  WooCommerce Calcurates
 */

namespace Calcurates;

// Stop direct HTTP access.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Composer autoload.
 */
require_once plugin_dir_path(__FILE__) . 'lib/autoload.php';

/**
 * Register activation hook
 */
register_activation_hook(__FILE__, [__NAMESPACE__ . '\Activator', 'activate']);

(new WCCalcurates())->run();
