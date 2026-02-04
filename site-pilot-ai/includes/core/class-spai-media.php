<?php
/**
 * Media handler
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle media operations.
 */
class Spai_Media {

	use Spai_Sanitization;

	/**
	 * Upload media from file.
	 *
	 * @param array $file File data from $_FILES.
	 * @param array $args Additional arguments.
	 * @return array|WP_Error Attachment data or error.
	 */
	public function upload_file( $file, $args = array() ) {
		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		if ( ! function_exists( 'media_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}

		// Validate file
		if ( empty( $file['tmp_name'] ) ) {
			return new WP_Error(
				'no_file',
				__( 'No file uploaded.', 'site-pilot-ai' ),
				array( 'status' => 400 )
			);
		}

		// Upload the file
		$upload = wp_handle_upload(
			$file,
			array( 'test_form' => false )
		);

		if ( isset( $upload['error'] ) ) {
			return new WP_Error(
				'upload_error',
				$upload['error'],
				array( 'status' => 400 )
			);
		}

		// Create attachment
		$attachment = array(
			'post_mime_type' => $upload['type'],
			'post_title'     => isset( $args['title'] ) ? sanitize_text_field( $args['title'] ) : sanitize_file_name( pathinfo( $file['name'], PATHINFO_FILENAME ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		$attachment_id = wp_insert_attachment( $attachment, $upload['file'] );

		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		// Generate metadata
		$metadata = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
		wp_update_attachment_metadata( $attachment_id, $metadata );

		// Set alt text
		if ( ! empty( $args['alt'] ) ) {
			update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $args['alt'] ) );
		}

		// Set caption
		if ( ! empty( $args['caption'] ) ) {
			wp_update_post(
				array(
					'ID'           => $attachment_id,
					'post_excerpt' => sanitize_textarea_field( $args['caption'] ),
				)
			);
		}

		return $this->format_attachment( $attachment_id );
	}

	/**
	 * Upload media from URL.
	 *
	 * @param string $url  External URL.
	 * @param array  $args Additional arguments.
	 * @return array|WP_Error Attachment data or error.
	 */
	public function upload_from_url( $url, $args = array() ) {
		if ( ! function_exists( 'media_sideload_image' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}

		// Validate URL
		$url = esc_url_raw( $url );
		if ( empty( $url ) ) {
			return new WP_Error(
				'invalid_url',
				__( 'Invalid URL provided.', 'site-pilot-ai' ),
				array( 'status' => 400 )
			);
		}

		// Download the file
		$tmp = download_url( $url );

		if ( is_wp_error( $tmp ) ) {
			return new WP_Error(
				'download_error',
				$tmp->get_error_message(),
				array( 'status' => 400 )
			);
		}

		// Get file info
		$file_array = array(
			'name'     => isset( $args['filename'] ) ? sanitize_file_name( $args['filename'] ) : basename( wp_parse_url( $url, PHP_URL_PATH ) ),
			'tmp_name' => $tmp,
		);

		// If no extension, try to detect
		if ( ! pathinfo( $file_array['name'], PATHINFO_EXTENSION ) ) {
			$mime = mime_content_type( $tmp );
			$ext = $this->mime_to_extension( $mime );
			if ( $ext ) {
				$file_array['name'] .= '.' . $ext;
			}
		}

		// Upload
		$attachment_id = media_handle_sideload( $file_array, 0, isset( $args['title'] ) ? sanitize_text_field( $args['title'] ) : '' );

		// Clean up temp file
		if ( file_exists( $tmp ) ) {
			wp_delete_file( $tmp );
		}

		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		// Set alt text
		if ( ! empty( $args['alt'] ) ) {
			update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $args['alt'] ) );
		}

		// Set caption
		if ( ! empty( $args['caption'] ) ) {
			wp_update_post(
				array(
					'ID'           => $attachment_id,
					'post_excerpt' => sanitize_textarea_field( $args['caption'] ),
				)
			);
		}

		return $this->format_attachment( $attachment_id );
	}

	/**
	 * List media.
	 *
	 * @param array $args Query arguments.
	 * @return array Media list.
	 */
	public function list_media( $args = array() ) {
		$defaults = array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 20,
			'paged'          => 1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		$args = wp_parse_args( $args, $defaults );

		// Filter by mime type
		if ( ! empty( $args['mime_type'] ) ) {
			$args['post_mime_type'] = sanitize_mime_type( $args['mime_type'] );
		}

		$query = new WP_Query( $args );
		$media = array();

		foreach ( $query->posts as $attachment ) {
			$media[] = $this->format_attachment( $attachment->ID );
		}

		return array(
			'media'    => $media,
			'total'    => $query->found_posts,
			'pages'    => $query->max_num_pages,
			'page'     => absint( $args['paged'] ),
			'per_page' => absint( $args['posts_per_page'] ),
		);
	}

	/**
	 * Format attachment for API response.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return array Formatted attachment.
	 */
	protected function format_attachment( $attachment_id ) {
		$attachment = get_post( $attachment_id );
		$metadata = wp_get_attachment_metadata( $attachment_id );

		$data = array(
			'id'          => $attachment_id,
			'title'       => $attachment->post_title,
			'caption'     => $attachment->post_excerpt,
			'alt'         => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
			'mime_type'   => $attachment->post_mime_type,
			'url'         => wp_get_attachment_url( $attachment_id ),
			'date'        => $attachment->post_date,
		);

		// Add image sizes if available
		if ( $metadata && ! empty( $metadata['width'] ) ) {
			$data['width'] = $metadata['width'];
			$data['height'] = $metadata['height'];

			$data['sizes'] = array();
			if ( ! empty( $metadata['sizes'] ) ) {
				foreach ( $metadata['sizes'] as $size => $size_data ) {
					$src = wp_get_attachment_image_src( $attachment_id, $size );
					if ( $src ) {
						$data['sizes'][ $size ] = array(
							'url'    => $src[0],
							'width'  => $src[1],
							'height' => $src[2],
						);
					}
				}
			}
		}

		return $data;
	}

	/**
	 * Convert mime type to file extension.
	 *
	 * @param string $mime Mime type.
	 * @return string|false Extension or false.
	 */
	protected function mime_to_extension( $mime ) {
		$map = array(
			'image/jpeg' => 'jpg',
			'image/png'  => 'png',
			'image/gif'  => 'gif',
			'image/webp' => 'webp',
			'image/svg+xml' => 'svg',
			'application/pdf' => 'pdf',
		);

		return isset( $map[ $mime ] ) ? $map[ $mime ] : false;
	}
}
