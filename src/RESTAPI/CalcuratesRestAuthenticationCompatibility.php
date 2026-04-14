<?php

declare(strict_types=1);

namespace Calcurates\RESTAPI;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

if (!\class_exists(CalcuratesRestAuthenticationCompatibility::class)) {
    /**
     * Lets Calcurates REST routes reach permission_callback when a theme blocks
     * unauthenticated REST via rest_authentication_errors (priority ~10).
     */
    class CalcuratesRestAuthenticationCompatibility
    {
        private const NAMESPACE_PREFIX = '/calcurates/v1';

        /**
         * Priority below typical theme callbacks so we pass true before they require login.
         */
        private const FILTER_PRIORITY = 5;

        public static function register(): void
        {
            \add_filter(
                'rest_authentication_errors',
                [self::class, 'allow_namespace_without_wp_session'],
                self::FILTER_PRIORITY,
                2
            );
        }

        /**
         * @param bool|\WP_Error|null $result
         *
         * @return bool|\WP_Error|null
         */
        public static function allow_namespace_without_wp_session($result, $request = null)
        {
            if (true === $result || \is_wp_error($result)) {
                return $result;
            }

            if (!self::is_request_to_calcurates_v1($request)) {
                return $result;
            }

            return true;
        }

        private static function is_request_to_calcurates_v1($request): bool
        {
            if (!$request instanceof \WP_REST_Request) {
                return false;
            }

            $route = $request->get_route();

            return self::NAMESPACE_PREFIX === $route || \str_starts_with($route, self::NAMESPACE_PREFIX.'/');
        }
    }
}
