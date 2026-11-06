<?php

declare(strict_types=1);

// Not called from WordPress, then exit.
if (!\defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// TODO: implement data cleanup on uninstall:
// - plugin options (wc_calcurates_key, woocommerce_calcurates_settings, per-instance settings)
// - rates request transients
// - origin taxonomy terms and product/origin relationships
// Cleanup is intentionally not implemented yet so the configuration survives a re-install.
