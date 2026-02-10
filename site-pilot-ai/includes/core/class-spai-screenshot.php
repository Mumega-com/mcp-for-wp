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
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'spai_verify_screenshot', array( $this, 'handle_verification_cron' ), 10, 5 );
	}

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

	/**
	 * Schedule async verification for screenshot readiness.
	 *
	 * @param string $url            Original page URL.
	 * @param string $screenshot_url mshots URL to verify.
	 * @param string $webhook_url    Webhook URL to notify when ready.
	 * @param array  $args           Additional args (save_to_media, title, etc).
	 */
	public function schedule_verification( $url, $screenshot_url, $webhook_url, $args = array() ) {
		wp_schedule_single_event(
			time() + 5,
			'spai_verify_screenshot',
			array( $url, $screenshot_url, $webhook_url, $args, 0 )
		);
	}

	/**
	 * Check if screenshot is ready (not placeholder).
	 *
	 * @param string $screenshot_url mshots URL to check.
	 * @return bool True if ready, false if still placeholder.
	 */
	public function verify_screenshot_ready( $screenshot_url ) {
		$response = wp_remote_get(
			$screenshot_url,
			array(
				'timeout' => 10,
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$content_length = wp_remote_retrieve_header( $response, 'content-length' );

		// Real screenshots are typically > 5KB, placeholders are much smaller.
		return $content_length > 5000;
	}

	/**
	 * Fire webhook with screenshot data.
	 *
	 * @param string $webhook_url Webhook URL.
	 * @param array  $data        Payload data.
	 * @return array|WP_Error Response data or error.
	 */
	public function fire_screenshot_webhook( $webhook_url, $data ) {
		// SSRF protection.
		if ( class_exists( 'Spai_Security' ) ) {
			$ssrf_check = Spai_Security::validate_external_url( $webhook_url );
			if ( is_wp_error( $ssrf_check ) ) {
				return $ssrf_check;
			}
		}

		$body = wp_json_encode( $data );

		$response = wp_remote_post(
			$webhook_url,
			array(
				'timeout'     => 15,
				'redirection' => 0,
				'sslverify'   => true,
				'headers'     => array(
					'Content-Type'  => 'application/json',
					'X-SPAI-Event'  => 'screenshot.ready',
				),
				'body'        => $body,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );

		return array(
			'success'       => $code >= 200 && $code < 300,
			'response_code' => $code,
			'response_body' => wp_remote_retrieve_body( $response ),
		);
	}

	/**
	 * Handle scheduled screenshot verification (cron callback).
	 *
	 * @param string $url            Original page URL.
	 * @param string $screenshot_url mshots URL.
	 * @param string $webhook_url    Webhook URL.
	 * @param array  $args           Additional args.
	 * @param int    $retry_count    Current retry attempt.
	 */
	public function handle_verification_cron( $url, $screenshot_url, $webhook_url, $args, $retry_count ) {
		$is_ready = $this->verify_screenshot_ready( $screenshot_url );

		if ( $is_ready ) {
			// Screenshot is ready - prepare webhook payload.
			$webhook_data = array(
				'url'            => $url,
				'screenshot_url' => $screenshot_url,
				'status'         => 'ready',
				'timestamp'      => current_time( 'c' ),
			);

			// Optionally save to media library.
			if ( ! empty( $args['save_to_media'] ) ) {
				$media_result = $this->save_screenshot_to_media( $screenshot_url, $url, $args );
				if ( ! is_wp_error( $media_result ) ) {
					$webhook_data['media'] = $media_result;
				} else {
					$webhook_data['media_error'] = $media_result->get_error_message();
				}
			}

			// Fire webhook.
			$this->fire_screenshot_webhook( $webhook_url, $webhook_data );

		} elseif ( $retry_count < 6 ) {
			// Not ready yet - reschedule check in 10 seconds.
			wp_schedule_single_event(
				time() + 10,
				'spai_verify_screenshot',
				array( $url, $screenshot_url, $webhook_url, $args, $retry_count + 1 )
			);

		} else {
			// Max retries reached - fire webhook with timeout status.
			$webhook_data = array(
				'url'            => $url,
				'screenshot_url' => $screenshot_url,
				'status'         => 'timeout',
				'message'        => __( 'Screenshot generation timed out after 1 minute. The URL may be unreachable or mshots service may be slow.', 'site-pilot-ai' ),
				'timestamp'      => current_time( 'c' ),
			);

			$this->fire_screenshot_webhook( $webhook_url, $webhook_data );
		}
	}
}
