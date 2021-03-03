<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\WPRESTStarter\Core\Field;

use Inpsyde\WPRESTStarter\Common;

/**
 * Non-caching field access implementation.
 *
 * @package Inpsyde\WPRESTStarter\Core\Field
 * @since   1.0.0
 * @since   2.0.0 Made the class final.
 */
final class Access implements Common\Field\Access {

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
	public function get_fields( string $resource = '' ): array {

		if ( empty( $GLOBALS['wp_rest_additional_fields'][ $resource ] ) ) {
			return [];
		}

		return (array) $GLOBALS['wp_rest_additional_fields'][ $resource ];
	}
}
