<?php
/**
 * Screenshot REST Controller
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Screenshot REST controller.
 */
class Spai_REST_Screenshot extends Spai_REST_API {

	/**
	 * Screenshot handler.
	 *
	 * @var Spai_Screenshot
	 */
	private $screenshot;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->screenshot = new Spai_Screenshot();
	}

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/screenshot',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'take_screenshot' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'url'            => array(
							'description' => __( 'URL to screenshot.', 'site-pilot-ai' ),
							'type'        => 'string',
							'required'    => true,
							'format'      => 'uri',
						),
						'width'          => array(
							'description' => __( 'Screenshot width (320-1920).', 'site-pilot-ai' ),
							'type'        => 'integer',
							'default'     => 1280,
						),
						'height'         => array(
							'description' => __( 'Screenshot height (240-1440).', 'site-pilot-ai' ),
							'type'        => 'integer',
							'default'     => 960,
						),
						'save_to_media'  => array(
							'description' => __( 'Also save screenshot to media library.', 'site-pilot-ai' ),
							'type'        => 'boolean',
							'default'     => false,
						),
						'title'          => array(
							'description' => __( 'Title for saved media.', 'site-pilot-ai' ),
							'type'        => 'string',
						),
					),
				),
			)
		);
	}

	/**
	 * Take a screenshot.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function take_screenshot( $request ) {
		$this->log_activity( 'screenshot', $request );

		$url  = $request->get_param( 'url' );
		$args = array(
			'width'         => $request->get_param( 'width' ),
			'height'        => $request->get_param( 'height' ),
			'save_to_media' => $request->get_param( 'save_to_media' ),
			'title'         => $request->get_param( 'title' ),
		);

		$result = $this->screenshot->capture( $url, $args );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success_response( $result );
	}
}
