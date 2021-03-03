<?php # -*- coding: utf-8 -*-

namespace Inpsyde\WPRESTStarter\Common\Response;

/**
 * Interface for all response data filter implementations.
 *
 * @package Inpsyde\WPRESTStarter\Common\Response
 * @since   2.0.0
 */
interface DataFilter {

	/**
	 * Returns the given data filtered according to the given context.
	 *
	 * @see   \WP_REST_Controller::filter_response_by_context
	 * @since 2.0.0
	 *
	 * @param array  $data    Unfiltered response data.
	 * @param string $context Optional. Context. Defaults to 'view'.
	 *
	 * @return array Filtered data.
	 */
	public function filter_data( array $data, string $context = 'view' ): array;
}
