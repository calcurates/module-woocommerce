<?php # -*- coding: utf-8 -*-

namespace Inpsyde\WPRESTStarter\Common\Route;

use Inpsyde\WPRESTStarter\Common\Arguments;

/**
 * Interface for all implementations of extensible route options.
 *
 * @package Inpsyde\WPRESTStarter\Common\Route
 * @since   1.1.0
 */
interface ExtensibleOptions extends Arguments {

	/**
	 * Adds the given route options as new entry to the internal options.
	 *
	 * @since 1.1.0
	 * @since 2.0.0 Require $options to be an array.
	 *
	 * @param array $options Route options.
	 *
	 * @return ExtensibleOptions Options object.
	 */
	public function add( array $options ): ExtensibleOptions;
}
