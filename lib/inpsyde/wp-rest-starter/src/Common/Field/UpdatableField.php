<?php # -*- coding: utf-8 -*-

namespace Inpsyde\WPRESTStarter\Common\Field;

/**
 * Interface for all implementations of updatable fields.
 *
 * @package Inpsyde\WPRESTStarter\Common\Field
 * @since   1.1.0
 */
interface UpdatableField extends Field {

	/**
	 * Sets the callback for updating the field value to the according callback on the given field updater object.
	 *
	 * @since 1.1.0
	 *
	 * @param Updater $updater Optional. Field updater object. Defaults to null.
	 *
	 * @return UpdatableField Field object.
	 */
	public function set_update_callback( Updater $updater = null ): UpdatableField;
}
