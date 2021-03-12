<?php
namespace Calcurates\RESTAPI\Routes;

use Calcurates\RESTAPI\Routes\Endpoints\EndpointsArguments\WooCommmerceSettingsReadEndpointArguments;
use Calcurates\RESTAPI\Routes\Endpoints\WooCommmerceSettingsReadEndpoint;
use Calcurates\RESTAPI\Routes\Factory\PermissionCallback;
use Inpsyde\WPRESTStarter\Core\Route\Collection;
use Inpsyde\WPRESTStarter\Core\Route\Options;
use Inpsyde\WPRESTStarter\Core\Route\Registry;
use Inpsyde\WPRESTStarter\Core\Route\Route;

class WooCommmerceSettingsRoutes
{

    public static function register_route()
    {
        $namespace = 'calcurates/v1';

        $permission = new PermissionCallback();

        // Create a new route collection.
        $routes = new Collection();

        $route = new Route(
            'woocommers-settings',
            Options::from_arguments(
                new WooCommmerceSettingsReadEndpoint(),
                new WooCommmerceSettingsReadEndpointArguments(),
                'GET',
                [
                    'permission_callback' => $permission->check_api_key(),
                ]
            )
        );

        $routes->add($route);

        (new Registry($namespace))->register_routes($routes);
    }

}
