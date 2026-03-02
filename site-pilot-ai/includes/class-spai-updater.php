<?php
/**
 * Self-hosted Plugin Updater
 *
 * Checks sitepilotai.mumega.com/downloads/version.json for new versions
 * and integrates with WordPress's built-in update system.
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin updater class.
 */
class Spai_Updater {

	/**
	 * URL to the version JSON file.
	 *
	 * @var string
	 */
	private $version_url = 'https://raw.githubusercontent.com/Digidinc/wp-ai-operator/main/version.json';

	/**
	 * Plugin basename (e.g. site-pilot-ai/site-pilot-ai.php).
	 *
	 * @var string
	 */
	private $plugin_basename;

	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	private $plugin_slug = 'site-pilot-ai';

	/**
	 * Current plugin version.
	 *
	 * @var string
	 */
	private $current_version;

	/**
	 * Cached remote data.
	 *
	 * @var object|null
	 */
	private $remote_data = null;

	/**
	 * Cache key for the transient.
	 *
	 * @var string
	 */
	private $cache_key = 'spai_update_check';

	/**
	 * Cache duration in seconds (12 hours).
	 *
	 * @var int
	 */
	private $cache_duration = 43200;

	/**
	 * Initialize the updater.
	 */
	public function __construct() {
		$this->plugin_basename = SPAI_PLUGIN_BASENAME;
		$this->current_version = SPAI_VERSION;

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_update' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_info' ), 20, 3 );
		add_action( 'upgrader_process_complete', array( $this, 'clear_cache' ), 10, 2 );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
	}

	/**
	 * Fetch remote version data.
	 *
	 * Checks the `spai_update_info` option first (set via MCP deploy),
	 * then falls back to the remote version.json URL.
	 *
	 * @param bool $force_refresh Force a fresh check.
	 * @return object|false Remote data object or false on failure.
	 */
	private function get_remote_data( $force_refresh = false ) {
		if ( null !== $this->remote_data && ! $force_refresh ) {
			return $this->remote_data;
		}

		if ( ! $force_refresh ) {
			$cached = get_transient( $this->cache_key );
			if ( false !== $cached ) {
				$this->remote_data = $cached;
				return $this->remote_data;
			}
		}

		$data = null;

		// Check option-based override first (set via MCP wp_update_option).
		$option_data = get_option( 'spai_update_info' );
		if ( ! empty( $option_data ) ) {
			if ( is_string( $option_data ) ) {
				$option_data = json_decode( $option_data, true );
			}
			if ( is_array( $option_data ) && ! empty( $option_data['version'] ) ) {
				$data = (object) $option_data;
			}
		}

		// Fall back to remote version.json.
		if ( empty( $data ) ) {
			$response = wp_remote_get(
				$this->version_url,
				array(
					'timeout' => 10,
					'headers' => array(
						'Accept' => 'application/json',
					),
				)
			);

			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
				return false;
			}

			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body );
		}

		if ( empty( $data ) || empty( $data->version ) ) {
			return false;
		}

		$this->remote_data = $data;
		set_transient( $this->cache_key, $data, $this->cache_duration );

		return $this->remote_data;
	}

	/**
	 * Check for plugin updates.
	 *
	 * Hooks into pre_set_site_transient_update_plugins.
	 *
	 * @param object $transient Update transient data.
	 * @return object Modified transient data.
	 */
	public function check_for_update( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$remote = $this->get_remote_data();

		if ( false === $remote ) {
			return $transient;
		}

		if ( version_compare( $this->current_version, $remote->version, '<' ) ) {
			$update              = new stdClass();
			$update->slug        = $this->plugin_slug;
			$update->plugin      = $this->plugin_basename;
			$update->new_version = $remote->version;
			$update->url         = isset( $remote->homepage ) ? $remote->homepage : 'https://sitepilotai.mumega.com';
			$update->package     = $remote->download_url;
			$update->tested      = isset( $remote->tested ) ? $remote->tested : '';
			$update->requires    = isset( $remote->requires ) ? $remote->requires : '';
			$update->requires_php = isset( $remote->requires_php ) ? $remote->requires_php : '';

			if ( isset( $remote->icons ) ) {
				$update->icons = (array) $remote->icons;
			}

			if ( isset( $remote->banners ) ) {
				$update->banners = (array) $remote->banners;
			}

			$transient->response[ $this->plugin_basename ] = $update;
		} else {
			// No update available — add to no_update to show "up to date".
			$item              = new stdClass();
			$item->slug        = $this->plugin_slug;
			$item->plugin      = $this->plugin_basename;
			$item->new_version = $this->current_version;
			$item->url         = isset( $remote->homepage ) ? $remote->homepage : '';
			$item->package     = '';

			if ( isset( $remote->icons ) ) {
				$item->icons = (array) $remote->icons;
			}

			if ( isset( $remote->banners ) ) {
				$item->banners = (array) $remote->banners;
			}

			$transient->no_update[ $this->plugin_basename ] = $item;
		}

		return $transient;
	}

	/**
	 * Provide plugin information for the "View Details" modal.
	 *
	 * Hooks into plugins_api.
	 *
	 * @param false|object|array $result The result object or array.
	 * @param string             $action The API action.
	 * @param object             $args   Plugin API arguments.
	 * @return false|object
	 */
	public function plugin_info( $result, $action, $args ) {
		if ( 'plugin_information' !== $action ) {
			return $result;
		}

		if ( $this->plugin_slug !== $args->slug ) {
			return $result;
		}

		$remote = $this->get_remote_data();

		if ( false === $remote ) {
			return $result;
		}

		$info                = new stdClass();
		$info->name          = isset( $remote->name ) ? $remote->name : 'Mumega Site Pilot AI';
		$info->slug          = $this->plugin_slug;
		$info->version       = $remote->version;
		$info->author        = isset( $remote->author ) ? sprintf( '<a href="%s">%s</a>', esc_url( isset( $remote->author_homepage ) ? $remote->author_homepage : '' ), esc_html( $remote->author ) ) : '';
		$info->homepage      = isset( $remote->homepage ) ? $remote->homepage : '';
		$info->requires      = isset( $remote->requires ) ? $remote->requires : '';
		$info->tested        = isset( $remote->tested ) ? $remote->tested : '';
		$info->requires_php  = isset( $remote->requires_php ) ? $remote->requires_php : '';
		$info->download_link = $remote->download_url;

		if ( isset( $remote->sections ) ) {
			$info->sections = (array) $remote->sections;
		}

		if ( isset( $remote->banners ) ) {
			$info->banners = (array) $remote->banners;
		}

		if ( isset( $remote->icons ) ) {
			$info->icons = (array) $remote->icons;
		}

		return $info;
	}

	/**
	 * Clear update cache after upgrade.
	 *
	 * @param object $upgrader WP_Upgrader instance.
	 * @param array  $options  Upgrade options.
	 */
	public function clear_cache( $upgrader, $options ) {
		if ( 'update' === $options['action'] && 'plugin' === $options['type'] ) {
			delete_transient( $this->cache_key );
			$this->remote_data = null;
		}
	}

	/**
	 * Add "Check for updates" link to plugin row.
	 *
	 * @param array  $links Plugin row meta links.
	 * @param string $file  Plugin file path.
	 * @return array Modified links.
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( $this->plugin_basename === $file ) {
			$links[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( wp_nonce_url( admin_url( 'update-core.php?force-check=1' ), 'force-check' ) ),
				esc_html__( 'Check for updates', 'site-pilot-ai' )
			);
		}
		return $links;
	}
}
