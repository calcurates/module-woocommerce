<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\WPRESTStarter\Core\Field;

use Inpsyde\WPRESTStarter\Common;

/**
 * Registry implementation for fields.
 *
 * @package Inpsyde\WPRESTStarter\Core\Field
 * @since   1.0.0
 * @since   2.0.0 Made the class final.
 */
final class Registry implements Common\Field\Registry {

	/**
	 * Registers the given fields.
	 *
	 * @since 1.0.0
	 *
	 * @param Common\Field\Collection $fields Field collection object.
	 *
	 * @return void
	 */
	public function register_fields( Common\Field\Collection $fields ) {

		if ( ! \function_exists( 'register_rest_field' ) ) {
			if ( \defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				\trigger_error( 'Function register_rest_field() not available. Cannot register additional fields.' );
			}

			return;
		}

		/**
		 * Fires right before the fields are registered.
		 *
		 * @since 1.0.0
		 *
		 * @param Common\Field\Collection $fields Field collection object.
		 */
		\do_action( Common\Field\Registry::ACTION_REGISTER, $fields );

		foreach ( $fields as $resource => $resource_fields ) {
			/** @var Common\Field\Field $field */
			foreach ( $resource_fields as $field_name => $field ) {
				\register_rest_field( $resource, $field_name, $field->definition() );
			}
		}
	}
}
