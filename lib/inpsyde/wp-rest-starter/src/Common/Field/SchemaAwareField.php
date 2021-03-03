<?php # -*- coding: utf-8 -*-

namespace Inpsyde\WPRESTStarter\Common\Field;

use Inpsyde\WPRESTStarter\Common\Schema;

/**
 * Interface for all implementations of schema-aware fields.
 *
 * @package Inpsyde\WPRESTStarter\Common\Field
 * @since   1.1.0
 */
interface SchemaAwareField extends Field {

	/**
	 * Sets the schema callback in the options to the according callback on the given schema object.
	 *
	 * @since 1.1.0
	 *
	 * @param Schema $schema Optional. Schema object. Defaults to null.
	 *
	 * @return SchemaAwareField Field object.
	 */
	public function set_schema( Schema $schema = null ): SchemaAwareField;
}
