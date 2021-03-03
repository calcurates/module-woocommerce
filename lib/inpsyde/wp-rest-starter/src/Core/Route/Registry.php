<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\WPRESTStarter\Core\Route;

use Inpsyde\WPRESTStarter\Common;

/**
 * Registry implementation for routes in a common namespace.
 *
 * @package Inpsyde\WPRESTStarter\Core\Route
 * @since   1.0.0
 * @since   2.0.0 Made the class final.
 */
final class Registry implements Common\Route\Registry {

	/**
	 * @var string
	 */
	private $namespace;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 1.0.0
	 *
	 * @param string $namespace Namespace.
	 */
	public function __construct( string $namespace ) {

		$this->namespace = $namespace;
	}

	/**
	 * Registers the given routes.
	 *
	 * @since 1.0.0
	 *
	 * @param Common\Route\Collection $routes Route collection object.
	 *
	 * @return void
	 */
	public function register_routes( Common\Route\Collection $routes ) {

		/**
		 * Fires right before the routes are registered.
		 *
		 * @since 1.0.0
		 *
		 * @param Common\Route\Collection $routes    Route collection object.
		 * @param string                  $namespace Namespace.
		 */
		\do_action( Common\Route\Registry::ACTION_REGISTER, $routes, $this->namespace );

		/** @var Common\Route\Route $route */
		foreach ( $routes as $route ) {
			\register_rest_route( $this->namespace, $route->url(), $route->options() );
		}
	}
}
