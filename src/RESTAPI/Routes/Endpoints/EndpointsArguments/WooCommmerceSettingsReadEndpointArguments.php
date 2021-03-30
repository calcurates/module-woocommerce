<?php
namespace Calcurates\RESTAPI\Routes\Endpoints\EndpointsArguments;

use Inpsyde\WPRESTStarter\Common\Arguments;
use Inpsyde\WPRESTStarter\Factory\ErrorFactory;

// Stop direct HTTP access.
if (!defined('ABSPATH')) {
    exit;
}

class WooCommmerceSettingsReadEndpointArguments implements Arguments
{

    /**
     * @var ErrorFactory
     */
    private $error_factory;
    private $common_args;

    /**
     * Constructor. Sets up the properties.
     *
     * @since 1.0.0
     *
     * @param ErrorFactory $error_factory Optional. Error factory object. Defaults to null.
     */
    public function __construct(ErrorFactory $error_factory = null)
    {
        $this->error_factory = $error_factory ?? new ErrorFactory();
    }

    /**
     * Returns the arguments in array form.
     *
     * @return array[] Arguments array.
     */
    public function to_array(): array
    {
        return [];
    }
}
