<?php
/**
 * REST API Base Controller
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base REST API controller.
 */
abstract class Spai_REST_API {

	use Spai_Api_Auth;
	use Spai_Sanitization;
	use Spai_Logging;

	/**
	 * REST namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'site-pilot-ai/v1';

	/**
	 * Register routes.
	 */
	abstract public function register_routes();

	/**
	 * Check if request has valid API key.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool|WP_Error True if valid.
	 */
	public function check_permission( $request ) {
		return $this->verify_api_key( $request );
	}

	/**
	 * Prepare success response.
	 *
	 * @param mixed $data Response data.
	 * @param int   $status HTTP status code.
	 * @return WP_REST_Response Response object.
	 */
	protected function success_response( $data, $status = 200 ) {
		return new WP_REST_Response( $data, $status );
	}

	/**
	 * Prepare error response.
	 *
	 * @param string $code    Error code.
	 * @param string $message Error message.
	 * @param int    $status  HTTP status code.
	 * @return WP_Error Error object.
	 */
	protected function error_response( $code, $message, $status = 400 ) {
		return new WP_Error( $code, $message, array( 'status' => $status ) );
	}

	/**
	 * Get pagination args schema.
	 *
	 * @return array Schema.
	 */
	protected function get_pagination_args() {
		return array(
			'per_page' => array(
				'description' => __( 'Maximum number of items per page.', 'site-pilot-ai' ),
				'type'        => 'integer',
				'default'     => 10,
				'minimum'     => 1,
				'maximum'     => 100,
			),
			'page'     => array(
				'description' => __( 'Current page number.', 'site-pilot-ai' ),
				'type'        => 'integer',
				'default'     => 1,
				'minimum'     => 1,
			),
		);
	}

	/**
	 * Get common post args schema.
	 *
	 * @return array Schema.
	 */
	protected function get_post_args() {
		return array(
			'title'   => array(
				'description' => __( 'Post title.', 'site-pilot-ai' ),
				'type'        => 'string',
				'required'    => true,
			),
			'content' => array(
				'description' => __( 'Post content.', 'site-pilot-ai' ),
				'type'        => 'string',
				'default'     => '',
			),
			'status'  => array(
				'description' => __( 'Post status.', 'site-pilot-ai' ),
				'type'        => 'string',
				'enum'        => array( 'publish', 'draft', 'pending', 'private' ),
				'default'     => 'draft',
			),
			'excerpt' => array(
				'description' => __( 'Post excerpt.', 'site-pilot-ai' ),
				'type'        => 'string',
				'default'     => '',
			),
		);
	}
}
