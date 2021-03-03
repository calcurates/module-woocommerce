<?php # -*- coding: utf-8 -*-

namespace Inpsyde\WPRESTStarter\Common;

/**
 * Interface for all schema implementations.
 *
 * @package Inpsyde\WPRESTStarter\Common
 * @since   1.0.0
 * @since   3.0.0 Renamed "get_schema()" to "definition()".
 */
interface Schema {

	/**
	 * Returns the schema definition.
	 *
	 * @since 3.0.0
	 *
	 * @return array Schema definition.
	 */
	public function definition(): array;
}
