<?php

declare(strict_types=1);

/**
 * Plugin Name:       WooCommerce Calcurates
 * Plugin URI:        https://github.com/calcurates/module-woocommerce
 * Description:       Connect your WooCommerce with Calcurates. Take full control of your shipping displayed at the checkout.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.1.3
 * Author:            Calcurates s.r.o.
 * Author URI:        https://calcurates.com
 */

namespace Calcurates;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

/**
 * Composer autoload.
 */
require_once plugin_dir_path(__FILE__).'lib/autoload.php';

/*
 * Register activation hook
 */
register_activation_hook(__FILE__, [__NAMESPACE__.'\Activator', 'activate']);

(new WCCalcurates())->run();
