<?php # -*- coding: utf-8 -*-

namespace Inpsyde\WPRESTStarter\Common;

/**
 * Interface for all arguments implementations.
 *
 * @package Inpsyde\WPRESTStarter\Common
 * @since   1.0.0
 */
interface Arguments {

	/**
	 * Returns the arguments in array form.
	 *
	 * @since 1.0.0
	 *
	 * @return array[] Arguments array.
	 */
	public function to_array(): array;
}
