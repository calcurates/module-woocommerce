<?php # -*- coding: utf-8 -*-

namespace Inpsyde\WPRESTStarter\Common\Field;

/**
 * Interface for all field updater implementations.
 *
 * @package Inpsyde\WPRESTStarter\Common\Field
 * @since   1.0.0
 */
interface Updater {

	/**
	 * Updates the value of the field with the given name of the given object to the given value.
	 *
	 * @since 1.0.0
	 * @since 2.0.0 Added optional $request and $object_type parameters.
	 *
	 * @param mixed            $value       New field value.
	 * @param object           $object      Object data.
	 * @param string           $field_name  Field name.
	 * @param \WP_REST_Request $request     Optional. Request object. Defaults to null.
	 * @param string           $object_type Optional. Object type. Defaults to empty string.
	 *
	 * @return bool Whether or not the field was updated successfully.
	 */
	public function update_value(
		$value,
		$object,
		string $field_name,
		\WP_REST_Request $request = null,
		string $object_type = ''
	): bool;
}
