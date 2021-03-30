<?php
namespace Calcurates\RESTAPI\Routes;

use Calcurates\Contracts\RESTAPI\Routes\RestRouteInterface;
use Calcurates\RESTAPI\Routes\Endpoints\EndpointsArguments\MultisiteRoutesReadEndpointArguments;
use Calcurates\RESTAPI\Routes\Endpoints\MultisiteRoutesReadEndpoint;
use Calcurates\RESTAPI\Routes\Factory\PermissionCallback;
use Inpsyde\WPRESTStarter\Core\Route\Collection;
use Inpsyde\WPRESTStarter\Core\Route\Options;
use Inpsyde\WPRESTStarter\Core\Route\Registry;
use Inpsyde\WPRESTStarter\Core\Route\Route;

// Stop direct HTTP access.
if (!defined('ABSPATH')) {
    exit;
}
class MultisiteRoutes implements RestRouteInterface
{

    public function register_route()
    {
        $namespace = 'calcurates/v1';

        $permission = new PermissionCallback();

        // Create a new route collection.
        $routes = new Collection();

        $route = new Route(
            'websites',
            Options::from_arguments(
                new MultisiteRoutesReadEndpoint(),
                new MultisiteRoutesReadEndpointArguments(),
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
