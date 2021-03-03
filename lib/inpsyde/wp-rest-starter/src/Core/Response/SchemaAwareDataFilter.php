<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\WPRESTStarter\Core\Response;

use Inpsyde\WPRESTStarter\Common\Endpoint\Schema;
use Inpsyde\WPRESTStarter\Common\Response\DataFilter;

/**
 * Schema-aware response data filter implementation.
 *
 * @package Inpsyde\WPRESTStarter\Core\Response
 * @since   2.0.0
 */
final class SchemaAwareDataFilter implements DataFilter {

	/**
	 * @var array
	 */
	private $properties;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 2.0.0
	 *
	 * @param Schema $schema Schema object.
	 */
	public function __construct( Schema $schema ) {

		$this->properties = $schema->properties();
	}

	/**
	 * Returns the given response data filtered according to the given context.
	 *
	 * @see   \WP_REST_Controller::filter_response_by_context
	 * @since 2.0.0
	 *
	 * @param array  $data    Unfiltered response data.
	 * @param string $context Optional. Context. Defaults to 'view'.
	 *
	 * @return array Filtered response data.
	 */
	public function filter_data( array $data, string $context = 'view' ): array {

		foreach ( $data as $key => $value ) {
			if ( empty( $this->properties[ $key ] ) ) {
				continue;
			}

			$property = $this->properties[ $key ];
			if ( empty( $property['context'] ) ) {
				continue;
			}

			if ( ! \in_array( $context, (array) $property['context'], true ) ) {
				unset( $data[ $key ] );

				continue;
			}

			if ( empty( $property['properties'] ) ) {
				continue;
			}

			if ( empty( $property['type'] ) || 'object' !== $property['type'] ) {
				continue;
			}

			foreach ( $property['properties'] as $name => $details ) {
				if ( empty( $details['context'] ) || \in_array( $context, (array) $details['context'], true ) ) {
					continue;
				}

				unset( $data[ $key ][ $name ] );
			}
		}

		return $data;
	}
}
