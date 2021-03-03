<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\WPRESTStarter\Factory;

use Inpsyde\WPRESTStarter\Common\Factory\ClassResolver;

/**
 * Factory for WordPress REST response objects.
 *
 * @package Inpsyde\WPRESTStarter\Factory
 * @since   1.0.0
 * @since   2.0.0 Made the class final.
 * @since   3.0.0 Refactored the class to be a non-final standalone factory.
 */
class ResponseFactory {

	/**
	 * @var ClassResolver
	 */
	private $class_resolver;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 1.0.0
	 *
	 * @param string $default_class Optional. Fully qualified name of the default class. Defaults to empty string.
	 */
	public function __construct( string $default_class = '' ) {

		$this->class_resolver = new ClassResolver( \WP_REST_Response::class, $default_class );
	}

	/**
	 * Returns a new WordPress REST response object, instantiated with the given arguments.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $args  Optional. Constructor arguments. Defaults to empty array.
	 * @param string $class Optional. Fully qualified class name. Defaults to empty string.
	 *
	 * @return \WP_REST_Response WordPress REST response object.
	 */
	public function create( array $args = [], string $class = '' ): \WP_REST_Response {

		$class = $this->class_resolver->resolve( $class );

		return new $class( ...$args );
	}
}
