<?php
/**
 * Media REST Controller
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media REST controller.
 */
class Spai_REST_Media extends Spai_REST_API {

	/**
	 * Media handler.
	 *
	 * @var Spai_Media
	 */
	private $media;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->media = new Spai_Media();
	}

	/**
	 * Register routes.
	 */
	public function register_routes() {
		// Upload media
		register_rest_route(
			$this->namespace,
			'/media',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'list_media' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array_merge(
						$this->get_pagination_args(),
						array(
							'mime_type' => array(
								'description' => __( 'Filter by mime type.', 'site-pilot-ai' ),
								'type'        => 'string',
							),
						)
					),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'upload_media' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'title' => array(
							'description' => __( 'Media title.', 'site-pilot-ai' ),
							'type'        => 'string',
						),
						'alt'   => array(
							'description' => __( 'Alt text.', 'site-pilot-ai' ),
							'type'        => 'string',
						),
					),
				),
			)
		);

		// Upload from URL
		register_rest_route(
			$this->namespace,
			'/media/from-url',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'upload_from_url' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'url'      => array(
							'description' => __( 'External URL.', 'site-pilot-ai' ),
							'type'        => 'string',
							'required'    => true,
							'format'      => 'uri',
						),
						'title'    => array(
							'description' => __( 'Media title.', 'site-pilot-ai' ),
							'type'        => 'string',
						),
						'alt'      => array(
							'description' => __( 'Alt text.', 'site-pilot-ai' ),
							'type'        => 'string',
						),
						'filename' => array(
							'description' => __( 'Custom filename.', 'site-pilot-ai' ),
							'type'        => 'string',
						),
					),
				),
			)
		);
	}

	/**
	 * List media.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function list_media( $request ) {
		$this->log_activity( 'list_media', $request );

		$args = array(
			'posts_per_page' => $request->get_param( 'per_page' ) ?: 20,
			'paged'          => $request->get_param( 'page' ) ?: 1,
			'mime_type'      => $request->get_param( 'mime_type' ),
		);

		$result = $this->media->list_media( $args );

		return $this->success_response( $result );
	}

	/**
	 * Upload media file.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function upload_media( $request ) {
		$this->log_activity( 'upload_media', $request );

		$files = $request->get_file_params();

		if ( empty( $files['file'] ) ) {
			return $this->error_response(
				'no_file',
				__( 'No file uploaded. Send file as multipart/form-data with "file" field.', 'site-pilot-ai' ),
				400
			);
		}

		$args = array(
			'title' => $request->get_param( 'title' ),
			'alt'   => $request->get_param( 'alt' ),
		);

		$result = $this->media->upload_file( $files['file'], $args );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success_response( $result, 201 );
	}

	/**
	 * Upload media from URL.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function upload_from_url( $request ) {
		$this->log_activity( 'upload_from_url', $request );

		$url = $request->get_param( 'url' );

		if ( empty( $url ) ) {
			return $this->error_response(
				'missing_url',
				__( 'URL is required.', 'site-pilot-ai' ),
				400
			);
		}

		$args = array(
			'title'    => $request->get_param( 'title' ),
			'alt'      => $request->get_param( 'alt' ),
			'filename' => $request->get_param( 'filename' ),
		);

		$result = $this->media->upload_from_url( $url, $args );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success_response( $result, 201 );
	}
}
