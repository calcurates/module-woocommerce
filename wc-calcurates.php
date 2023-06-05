<?php

declare(strict_types=1);

/**
 * Plugin Name:       Calcurates for WooCommerce
 * Plugin URI:        https://github.com/calcurates/module-woocommerce
 * Description:       Connect your WooCommerce with Calcurates. Take full control of your shipping displayed at the checkout.
 * Version:           1.5.10
 * Requires at least: 5.2
 * Requires PHP:      7.2.5
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
