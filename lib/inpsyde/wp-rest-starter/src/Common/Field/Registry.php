<?php # -*- coding: utf-8 -*-

namespace Inpsyde\WPRESTStarter\Common\Field;

/**
 * Interface for all field registry implementations.
 *
 * @package Inpsyde\WPRESTStarter\Common\Field
 * @since   1.0.0
 */
interface Registry {

	/**
	 * Action name.
	 *
	 * When using this, pass the field collection object as first and only argument.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_REGISTER = 'wp_rest_starter.register_fields';

	/**
	 * Registers the given fields.
	 *
	 * @since 1.0.0
	 *
	 * @param Collection $fields Field collection object.
	 *
	 * @return void
	 */
	public function register_fields( Collection $fields );
}
