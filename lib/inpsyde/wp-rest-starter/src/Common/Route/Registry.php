<?php # -*- coding: utf-8 -*-

namespace Inpsyde\WPRESTStarter\Common\Route;

/**
 * Interface for all route registry implementations.
 *
 * @package Inpsyde\WPRESTStarter\Common\Route
 * @since   1.0.0
 */
interface Registry {

	/**
	 * Action name.
	 *
	 * When using this, pass the route collection object as first, and the namespace as second argument.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_REGISTER = 'wp_rest_starter.register_routes';

	/**
	 * Registers the given routes.
	 *
	 * @since 1.0.0
	 *
	 * @param Collection $routes Route collection object.
	 *
	 * @return void
	 */
	public function register_routes( Collection $routes );
}
