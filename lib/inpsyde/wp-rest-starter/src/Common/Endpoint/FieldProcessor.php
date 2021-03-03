<?php # -*- coding: utf-8 -*-

namespace Inpsyde\WPRESTStarter\Common\Endpoint;

/**
 * Interface for all field processor implementations.
 *
 * @package Inpsyde\WPRESTStarter\Common\Endpoint
 * @since   2.0.0
 * @since   3.0.0 Renamed "get_extended_properties()" to "add_fields_to_properties()".
 */
interface FieldProcessor {

	/**
	 * Returns the given properties with added data of all schema-aware fields registered for the given object type.
	 *
	 * @see   \WP_REST_Controller::add_additional_fields_schema
	 * @since 3.0.0
	 *
	 * @param array  $properties  Schema properties definition.
	 * @param string $object_type Object type.
	 *
	 * @return array Properties with added data of all schema-aware fields registered for the given object type.
	 */
	public function add_fields_to_properties( array $properties, string $object_type ): array;
}
