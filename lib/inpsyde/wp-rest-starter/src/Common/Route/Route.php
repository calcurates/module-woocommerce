<?php # -*- coding: utf-8 -*-

namespace Inpsyde\WPRESTStarter\Common\Route;

/**
 * Interface for all route implementations.
 *
 * @package Inpsyde\WPRESTStarter\Common\Route
 * @since   1.0.0
 * @since   3.0.0 Removed "get_" prefix from getters.
 */
interface Route {

	/**
	 * Returns an array of options for the route, or an array of arrays for multiple HTTP request methods.
	 *
	 * @see   register_rest_route()
	 * @since 3.0.0
	 *
	 * @return array Route options.
	 */
	public function options(): array;

	/**
	 * Returns the base URL of the route.
	 *
	 * @see   register_rest_route()
	 * @since 3.0.0
	 *
	 * @return string Base URL of the route.
	 */
	public function url(): string;
}
