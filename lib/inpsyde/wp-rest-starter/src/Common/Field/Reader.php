<?php # -*- coding: utf-8 -*-

namespace Inpsyde\WPRESTStarter\Common\Field;

/**
 * Interface for all field reader implementations.
 *
 * @package Inpsyde\WPRESTStarter\Common\Field
 * @since   1.0.0
 */
interface Reader {

	/**
	 * Returns the value of the field with the given name of the given object.
	 *
	 * @since 1.0.0
	 * @since 2.0.0 Added optional $object_type parameter.
	 *
	 * @param array            $object      Object data in array form.
	 * @param string           $field_name  Field name.
	 * @param \WP_REST_Request $request     Request object.
	 * @param string           $object_type Optional. Object type. Defaults to empty string.
	 *
	 * @return mixed Field value.
	 */
	public function get_value(
		array $object,
		string $field_name,
		\WP_REST_Request $request,
		string $object_type = ''
	);
}
