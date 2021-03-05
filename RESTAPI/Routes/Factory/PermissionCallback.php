<?php # -*- coding: utf-8 -*-

declare (strict_types = 1);

namespace Calcurates\RESTAPI\Routes\Factory;

/**
 * Factory for diverse permission callbacks.
 *
 * @package Inpsyde\WPRESTStarter\Factory
 * @since   1.0.0
 */
class PermissionCallback
{

    /**
     * Returns a callback that checks if the current user has all of the given capabilities.
     *
     * @since 1.0.0
     * @since 3.0.0 Replaced single string array argument with variadic string arguments.
     *
     * @param string[] ...$capabilities Capabilities required to get permission.
     *
     * @return \Closure Callback that checks if the current user has all of the given capabilities.
     */
    public function check_api_key(): \Closure
    {

        /**
         * Checks if the current user has specific capabilities.
         *
         * @since 1.0.0
         *
         * @return bool Whether or not the current user has specific capabilities.
         */
        return function (): bool {

            if (isset($_SERVER['HTTP_X_API_KEY'])) {
                if ($_SERVER['HTTP_X_API_KEY'] == get_option(\WC_Calcurates::prefix . 'key')) {
                    return true;
                }
            }

            return false;
        };
    }

}
