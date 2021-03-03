<?php # -*- coding: utf-8 -*-

namespace Inpsyde\WPRESTStarter\Common\Field;

/**
 * Interface for all field access implementations.
 *
 * @package Inpsyde\WPRESTStarter\Common\Field
 * @since   1.0.0
 */
interface Access {

	/**
	 * Returns the definition of all registered fields for the given resource.
	 *
	 * @see   \WP_REST_Controller::get_additional_fields
	 * @since 1.0.0
	 *
	 * @param string $resource Optional. Resource name (e.g., post). Defaults to empty string.
	 *
	 * @return array[] Field definitions.
	 */
	public function get_fields( string $resource = '' ): array;
}
