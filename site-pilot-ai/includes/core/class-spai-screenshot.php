<?php
/**
 * Screenshot handler
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle screenshot operations using WordPress mshots service.
 */
class Spai_Screenshot {

	use Spai_Sanitization;

	/**
	 * mshots service base URL.
	 *
	 * @var string
	 */
	private $mshots_base = 'https://s0.wp.com/mshots/v1/';

	/**
	 * Take a screenshot of a URL.
	 *
	 * Uses WordPress.com mshots service for zero-dependency screenshots.
	 *
	 * @param string $url    URL to screenshot.
	 * @param array  $args   Options: width, height, save_to_media, title.
	 * @return array|WP_Error Screenshot data or error.
	 */
	public function capture( $url, $args = array() ) {
		$url = esc_url_raw( $url );
		if ( empty( $url ) ) {
			return new WP_Error(
				'invalid_url',
				__( 'A valid URL is required.', 'site-pilot-ai' ),
				array( 'status' => 400 )
			);
		}

		// SSRF protection.
		if ( class_exists( 'Spai_Security' ) ) {
			$ssrf_check = Spai_Security::validate_external_url( $url );
			if ( is_wp_error( $ssrf_check ) ) {
				return $ssrf_check;
			}
		}

		$width  = isset( $args['width'] ) ? absint( $args['width'] ) : 1280;
		$height = isset( $args['height'] ) ? absint( $args['height'] ) : 960;

		// Clamp dimensions.
		$width  = max( 320, min( 1920, $width ) );
		$height = max( 240, min( 1440, $height ) );

		// Build mshots URL.
		$screenshot_url = $this->mshots_base . rawurlencode( $url ) . '?w=' . $width . '&h=' . $height;

		// Option 1: Return the mshots URL directly (fastest, no server load).
		$result = array(
			'success'        => true,
			'url'            => $url,
			'screenshot_url' => $screenshot_url,
			'width'          => $width,
			'height'         => $height,
			'service'        => 'wordpress-mshots',
			'note'           => 'First request triggers generation. Screenshot may take 10-30 seconds to appear. Retry the URL if you get a placeholder.',
		);

		// Option 2: If requested, also download and save to media library.
		if ( ! empty( $args['save_to_media'] ) ) {
			$saved = $this->save_screenshot_to_media( $screenshot_url, $url, $args );
			if ( ! is_wp_error( $saved ) ) {
				$result['media'] = $saved;
			} else {
				$result['media_error'] = $saved->get_error_message();
				$result['note']        = 'Screenshot URL generated but saving to media library failed. The mshots service may still be generating the image — try again in 15-30 seconds.';
			}
		}

		return $result;
	}

	/**
	 * Save a screenshot to the media library.
	 *
	 * @param string $screenshot_url mshots URL.
	 * @param string $source_url     Original page URL.
	 * @param array  $args           Additional args.
	 * @return array|WP_Error Media data or error.
	 */
	private function save_screenshot_to_media( $screenshot_url, $source_url, $args = array() ) {
		if ( ! function_exists( 'media_sideload_image' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}

		// Wait briefly for mshots to generate.
		sleep( 3 );

		$tmp = download_url( $screenshot_url, 30 );

		if ( is_wp_error( $tmp ) ) {
			return $tmp;
		}

		// Generate a clean filename.
		$parsed   = wp_parse_url( $source_url );
		$host     = isset( $parsed['host'] ) ? sanitize_file_name( $parsed['host'] ) : 'screenshot';
		$filename = 'screenshot-' . $host . '-' . gmdate( 'Ymd-His' ) . '.jpg';

		$file_array = array(
			'name'     => $filename,
			'tmp_name' => $tmp,
		);

		$title = isset( $args['title'] )
			? sanitize_text_field( $args['title'] )
			: sprintf( 'Screenshot of %s', $source_url );

		$attachment_id = media_handle_sideload( $file_array, 0, $title );

		if ( file_exists( $tmp ) ) {
			wp_delete_file( $tmp );
		}

		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		// Set alt text.
		update_post_meta(
			$attachment_id,
			'_wp_attachment_image_alt',
			sprintf( 'Screenshot of %s', esc_url( $source_url ) )
		);

		$attachment = get_post( $attachment_id );

		return array(
			'id'    => $attachment_id,
			'title' => $attachment->post_title,
			'url'   => wp_get_attachment_url( $attachment_id ),
		);
	}
}
