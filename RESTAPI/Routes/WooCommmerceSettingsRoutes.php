<?php
namespace RESTAPI\Routes;

use Inpsyde\WPRESTStarter\Core\Route\Collection;
use Inpsyde\WPRESTStarter\Core\Route\Options;
use Inpsyde\WPRESTStarter\Core\Route\Registry;
use Inpsyde\WPRESTStarter\Core\Route\Route;
use RESTAPI\Routes\Endpoints\EndpointsArguments\WooCommmerceSettingsReadEndpointArguments;
use RESTAPI\Routes\Endpoints\WooCommmerceSettingsReadEndpoint;
use RESTAPI\Routes\Factory;
use RESTAPI\Routes\Factory\PermissionCallback;

class WooCommmerceSettingsRoutes
{

    public static function register_route()
    {
        $namespace = 'calcurates/v1/';

        $permission = new Factory\PermissionCallback();

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
