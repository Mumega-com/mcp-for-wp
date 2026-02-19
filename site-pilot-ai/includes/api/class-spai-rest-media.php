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

		// Bulk upload from URLs
		register_rest_route(
			$this->namespace,
			'/media/bulk',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'bulk_upload' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'urls' => array(
							'description' => __( 'Array of URLs to upload.', 'site-pilot-ai' ),
							'type'        => 'array',
							'items'       => array(
								'type' => 'string',
							),
						),
						'items' => array(
							'description' => __( 'Array of items with url, title, alt.', 'site-pilot-ai' ),
							'type'        => 'array',
							'items'       => array(
								'type' => 'object',
							),
						),
					),
				),
			)
		);

		// Upload from Base64
		register_rest_route(
			$this->namespace,
			'/media/from-base64',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'upload_from_base64' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'data'     => array(
							'description' => __( 'Base64-encoded file content. Optionally prefixed with data URI (e.g., data:image/png;base64,...).', 'site-pilot-ai' ),
							'type'        => 'string',
							'required'    => true,
						),
						'filename' => array(
							'description' => __( 'Filename with extension (e.g., logo.png).', 'site-pilot-ai' ),
							'type'        => 'string',
							'required'    => true,
						),
						'title'    => array(
							'description' => __( 'Media title.', 'site-pilot-ai' ),
							'type'        => 'string',
						),
						'alt'      => array(
							'description' => __( 'Alt text.', 'site-pilot-ai' ),
							'type'        => 'string',
						),
					),
				),
			)
		);

		// Delete media.
		register_rest_route(
			$this->namespace,
			'/media/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_media' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'force' => array(
							'description' => __( 'Permanently delete instead of trashing.', 'site-pilot-ai' ),
							'type'        => 'boolean',
							'default'     => false,
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

	/**
	 * Bulk upload media from URLs.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function bulk_upload( $request ) {
		$this->log_activity( 'bulk_upload', $request );

		$urls  = $request->get_param( 'urls' );
		$items = $request->get_param( 'items' );

		// Normalize input - support both 'urls' array and 'items' array
		$to_upload = array();

		if ( ! empty( $items ) && is_array( $items ) ) {
			foreach ( $items as $item ) {
				if ( ! empty( $item['url'] ) ) {
					$to_upload[] = array(
						'url'      => $item['url'],
						'title'    => isset( $item['title'] ) ? $item['title'] : null,
						'alt'      => isset( $item['alt'] ) ? $item['alt'] : null,
						'filename' => isset( $item['filename'] ) ? $item['filename'] : null,
					);
				}
			}
		} elseif ( ! empty( $urls ) && is_array( $urls ) ) {
			foreach ( $urls as $url ) {
				$to_upload[] = array(
					'url'      => $url,
					'title'    => null,
					'alt'      => null,
					'filename' => null,
				);
			}
		}

		if ( empty( $to_upload ) ) {
			return $this->error_response(
				'missing_urls',
				__( 'Provide either "urls" array or "items" array with url properties.', 'site-pilot-ai' ),
				400
			);
		}

		// Limit bulk uploads
		$max_uploads = 20;
		if ( count( $to_upload ) > $max_uploads ) {
			return $this->error_response(
				'too_many_files',
				/* translators: %d: maximum number of files */
				sprintf( __( 'Maximum %d files per request.', 'site-pilot-ai' ), $max_uploads ),
				400
			);
		}

		$results = array(
			'success' => array(),
			'failed'  => array(),
		);

		foreach ( $to_upload as $item ) {
			$args = array(
				'title'    => $item['title'],
				'alt'      => $item['alt'],
				'filename' => $item['filename'],
			);

			$result = $this->media->upload_from_url( $item['url'], $args );

			if ( is_wp_error( $result ) ) {
				$results['failed'][] = array(
					'url'   => $item['url'],
					'error' => $result->get_error_message(),
				);
			} else {
				$results['success'][] = $result;
			}
		}

		return $this->success_response( array(
			'uploaded'     => count( $results['success'] ),
			'failed'       => count( $results['failed'] ),
			'media'        => $results['success'],
			'errors'       => $results['failed'],
		), 201 );
	}

	/**
	 * Upload media from Base64.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function upload_from_base64( $request ) {
		$this->log_activity( 'upload_from_base64', $request );

		$data     = $request->get_param( 'data' );
		$filename = $request->get_param( 'filename' );

		if ( empty( $data ) || empty( $filename ) ) {
			return $this->error_response(
				'missing_params',
				__( 'Both "data" (Base64 string) and "filename" are required.', 'site-pilot-ai' ),
				400
			);
		}

		$args = array(
			'title' => $request->get_param( 'title' ),
			'alt'   => $request->get_param( 'alt' ),
		);

		$result = $this->media->upload_from_base64( $data, $filename, $args );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success_response( $result, 201 );
	}

	/**
	 * Delete a media attachment.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function delete_media( $request ) {
		$this->log_activity( 'delete_media', $request );

		$attachment_id = absint( $request->get_param( 'id' ) );
		$force = (bool) $request->get_param( 'force' );

		$result = $this->media->delete_media( $attachment_id, $force );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success_response( $result );
	}
}
