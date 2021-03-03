<?php # -*- coding: utf-8 -*-

namespace Inpsyde\WPRESTStarter\Common\Response;

/**
 * Interface for all response data access implementations.
 *
 * @package Inpsyde\WPRESTStarter\Common\Response
 * @since   2.0.0
 */
interface DataAccess {

	/**
	 * Returns an array holding the data as well as the defined links of the given response object.
	 *
	 * @see   \WP_REST_Controller::prepare_response_for_collection
	 * @since 2.0.0
	 *
	 * @param \WP_REST_Response $response Response object.
	 *
	 * @return array The array holding the data as well as the defined links of the given response object.
	 */
	public function get_data( \WP_REST_Response $response ): array;
}
