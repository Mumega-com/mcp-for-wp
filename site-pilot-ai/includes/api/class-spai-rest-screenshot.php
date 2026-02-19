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
						'url'           => array(
							'description' => __( 'URL to screenshot.', 'site-pilot-ai' ),
							'type'        => 'string',
							'required'    => true,
							'format'      => 'uri',
						),
						'width'         => array(
							'description' => __( 'Screenshot width (320-1920).', 'site-pilot-ai' ),
							'type'        => 'integer',
							'default'     => 1280,
						),
						'height'        => array(
							'description' => __( 'Screenshot height (240-1440).', 'site-pilot-ai' ),
							'type'        => 'integer',
							'default'     => 960,
						),
						'save_to_media' => array(
							'description' => __( 'Also save screenshot to media library.', 'site-pilot-ai' ),
							'type'        => 'boolean',
							'default'     => false,
						),
						'title'         => array(
							'description' => __( 'Title for saved media.', 'site-pilot-ai' ),
							'type'        => 'string',
						),
						'webhook_url'   => array(
							'description' => __( 'Webhook URL to notify when screenshot is ready (async mode).', 'site-pilot-ai' ),
							'type'        => 'string',
							'format'      => 'uri',
							'required'    => false,
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

		$url         = $request->get_param( 'url' );
		$webhook_url = $request->get_param( 'webhook_url' );
		$args        = array(
			'width'         => $request->get_param( 'width' ),
			'height'        => $request->get_param( 'height' ),
			'save_to_media' => $request->get_param( 'save_to_media' ),
			'title'         => $request->get_param( 'title' ),
		);

		$result = $this->screenshot->capture( $url, $args );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Async mode: webhook notification when screenshot is ready.
		if ( ! empty( $webhook_url ) ) {
			// Cloudflare returns base64 immediately — fire webhook now, no verification needed.
			if ( ! empty( $result['screenshot'] ) ) {
				$webhook_data = array(
					'url'       => $url,
					'status'    => 'ready',
					'service'   => 'cloudflare-browser',
					'format'    => isset( $result['format'] ) ? $result['format'] : 'png',
					'timestamp' => current_time( 'c' ),
				);

				if ( ! empty( $result['media'] ) ) {
					$webhook_data['media'] = $result['media'];
				}

				$this->screenshot->fire_screenshot_webhook( $webhook_url, $webhook_data );

				return $this->success_response(
					array(
						'status'  => 'ready',
						'service' => 'cloudflare-browser',
						'message' => __( 'Screenshot captured and webhook fired.', 'site-pilot-ai' ),
						'media'   => isset( $result['media'] ) ? $result['media'] : null,
					)
				);
			}

			// mshots fallback — schedule async verification.
			if ( ! empty( $result['screenshot_url'] ) ) {
				$this->screenshot->schedule_verification(
					$url,
					$result['screenshot_url'],
					$webhook_url,
					$args
				);

				return $this->success_response(
					array(
						'status'         => 'pending',
						'screenshot_url' => $result['screenshot_url'],
						'message'        => __( 'Screenshot queued. Webhook will fire when ready.', 'site-pilot-ai' ),
					)
				);
			}
		}

		// Sync mode: return screenshot URL immediately (original behavior).
		return $this->success_response( $result );
	}
}
