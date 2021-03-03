<?php # -*- coding: utf-8 -*-

namespace Inpsyde\WPRESTStarter\Common\Field;

/**
 * Interface for all field implementations.
 *
 * @package Inpsyde\WPRESTStarter\Common\Field
 * @since   1.0.0
 * @since   3.0.0 Removed "get_" prefix from getters.
 */
interface Field {

	/**
	 * Returns the field definition (i.e., callbacks and schema).
	 *
	 * @see   register_rest_field()
	 * @since 3.0.0
	 *
	 * @return array Field definition.
	 */
	public function definition(): array;

	/**
	 * Returns the name of the field.
	 *
	 * @see   register_rest_field()
	 * @since 3.0.0
	 *
	 * @return string Field name.
	 */
	public function name(): string;
}
