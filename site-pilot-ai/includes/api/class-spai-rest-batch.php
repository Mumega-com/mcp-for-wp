<?php
/**
 * Batch REST Controller
 *
 * Allows multiple REST API operations in a single request for efficiency.
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Batch REST controller.
 *
 * Processes multiple REST API operations in a single HTTP request.
 * Each operation is executed sequentially with the same authentication.
 */
class Spai_REST_Batch extends Spai_REST_API {

	use Spai_Api_Auth;
	use Spai_Sanitization;
	use Spai_Logging;

	/**
	 * Maximum number of operations allowed per batch.
	 *
	 * @var int
	 */
	const MAX_OPERATIONS = 25;

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/batch',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'handle_batch' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'operations' => array(
							'description' => __( 'Array of operations to execute.', 'site-pilot-ai' ),
							'type'        => 'array',
							'required'    => true,
							'items'       => array(
								'type'       => 'object',
								'properties' => array(
									'method' => array(
										'type'     => 'string',
										'enum'     => array( 'GET', 'POST', 'PUT', 'DELETE' ),
										'required' => true,
									),
									'path'   => array(
										'type'     => 'string',
										'required' => true,
									),
									'body'   => array(
										'type' => 'object',
									),
								),
							),
						),
					),
				),
			)
		);
	}

	/**
	 * Handle batch request.
	 *
	 * Processes multiple operations sequentially and returns results.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function handle_batch( $request ) {
		$this->log_activity( 'batch_request', $request );

		$operations = $request->get_param( 'operations' );

		// Validate operations array
		if ( ! is_array( $operations ) || empty( $operations ) ) {
			return $this->error_response(
				'invalid_operations',
				__( 'Operations must be a non-empty array.', 'site-pilot-ai' ),
				400
			);
		}

		// Check batch size limit
		if ( count( $operations ) > self::MAX_OPERATIONS ) {
			return $this->error_response(
				'batch_too_large',
				sprintf(
					/* translators: %d: maximum number of operations */
					__( 'Batch contains too many operations. Maximum %d allowed.', 'site-pilot-ai' ),
					self::MAX_OPERATIONS
				),
				400
			);
		}

		// Get API key from current request for forwarding
		$api_key = $this->get_api_key_from_request( $request );

		$results = array();

		// Execute each operation sequentially
		foreach ( $operations as $index => $operation ) {
			$result    = $this->execute_operation( $operation, $index, $api_key );
			$results[] = $result;
		}

		return $this->success_response(
			array(
				'results' => $results,
				'total'   => count( $results ),
			)
		);
	}

	/**
	 * Execute a single operation.
	 *
	 * @param array  $operation Operation details.
	 * @param int    $index     Operation index.
	 * @param string $api_key   API key for authentication.
	 * @return array Operation result.
	 */
	private function execute_operation( $operation, $index, $api_key ) {
		// Validate operation structure
		if ( ! is_array( $operation ) ) {
			return array(
				'index'  => $index,
				'status' => 400,
				'data'   => array(
					'code'    => 'invalid_operation',
					'message' => __( 'Operation must be an object.', 'site-pilot-ai' ),
				),
			);
		}

		$method = isset( $operation['method'] ) ? strtoupper( $operation['method'] ) : '';
		$path   = isset( $operation['path'] ) ? $operation['path'] : '';
		$body   = isset( $operation['body'] ) ? $operation['body'] : array();

		// Validate method
		if ( ! in_array( $method, array( 'GET', 'POST', 'PUT', 'DELETE' ), true ) ) {
			return array(
				'index'  => $index,
				'status' => 400,
				'data'   => array(
					'code'    => 'invalid_method',
					'message' => __( 'Method must be GET, POST, PUT, or DELETE.', 'site-pilot-ai' ),
				),
			);
		}

		// Validate path
		if ( empty( $path ) || ! is_string( $path ) ) {
			return array(
				'index'  => $index,
				'status' => 400,
				'data'   => array(
					'code'    => 'invalid_path',
					'message' => __( 'Path must be a non-empty string.', 'site-pilot-ai' ),
				),
			);
		}

		// Ensure path starts with /
		if ( '/' !== substr( $path, 0, 1 ) ) {
			$path = '/' . $path;
		}

		// Build full route
		$route = '/site-pilot-ai/v1' . $path;

		// Create internal REST request
		$internal_request = new WP_REST_Request( $method, $route );

		// Set body parameters for POST/PUT
		if ( in_array( $method, array( 'POST', 'PUT' ), true ) && is_array( $body ) ) {
			foreach ( $body as $key => $value ) {
				$internal_request->set_param( $key, $value );
			}
			$internal_request->set_body_params( $body );
		}

		// Set query parameters for GET/DELETE
		if ( in_array( $method, array( 'GET', 'DELETE' ), true ) && is_array( $body ) ) {
			foreach ( $body as $key => $value ) {
				$internal_request->set_param( $key, $value );
			}
			$internal_request->set_query_params( $body );
		}

		// Copy authentication and mark as batch sub-request (skips rate limiting).
		if ( $api_key ) {
			$internal_request->set_header( 'X-API-Key', $api_key );
		}
		$internal_request->set_header( 'X-SPAI-Batch-Sub-Request', '1' );

		// Execute the request
		$response = rest_do_request( $internal_request );

		// Extract response data
		$status = $response->get_status();
		$data   = $response->get_data();

		return array(
			'index'  => $index,
			'status' => $status,
			'data'   => $data,
		);
	}
}
