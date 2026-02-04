<?php
/**
 * GitHub Plugin Updater
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles plugin updates from GitHub releases.
 */
class Spai_Updater {

	/**
	 * GitHub repository owner.
	 *
	 * @var string
	 */
	private $owner = 'Digidinc';

	/**
	 * GitHub repository name.
	 *
	 * @var string
	 */
	private $repo = 'wp-ai-operator';

	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	private $slug;

	/**
	 * Plugin basename.
	 *
	 * @var string
	 */
	private $basename;

	/**
	 * Current plugin version.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Plugin file name in release zip.
	 *
	 * @var string
	 */
	private $zip_name;

	/**
	 * Cache key for GitHub data.
	 *
	 * @var string
	 */
	private $cache_key;

	/**
	 * Initialize the updater.
	 *
	 * @param string $basename Plugin basename.
	 * @param string $version  Current version.
	 * @param string $zip_name Zip file name in releases.
	 */
	public function __construct( $basename, $version, $zip_name ) {
		$this->basename  = $basename;
		$this->slug      = dirname( $basename );
		$this->version   = $version;
		$this->zip_name  = $zip_name;
		$this->cache_key = 'spai_github_' . md5( $this->slug );

		$this->init_hooks();
	}

	/**
	 * Initialize WordPress hooks.
	 */
	private function init_hooks() {
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_info' ), 20, 3 );
		add_filter( 'upgrader_source_selection', array( $this, 'fix_directory_name' ), 10, 4 );
	}

	/**
	 * Check for plugin updates.
	 *
	 * @param object $transient Update transient.
	 * @return object Modified transient.
	 */
	public function check_update( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$remote = $this->get_remote_data();

		if ( ! $remote || ! isset( $remote['version'] ) ) {
			return $transient;
		}

		if ( version_compare( $this->version, $remote['version'], '<' ) ) {
			$transient->response[ $this->basename ] = (object) array(
				'slug'        => $this->slug,
				'plugin'      => $this->basename,
				'new_version' => $remote['version'],
				'url'         => $remote['url'],
				'package'     => $remote['download_url'],
				'icons'       => array(),
				'banners'     => array(),
				'tested'      => $remote['tested'] ?? '',
				'requires'    => $remote['requires'] ?? '5.0',
				'requires_php' => $remote['requires_php'] ?? '7.4',
			);
		}

		return $transient;
	}

	/**
	 * Provide plugin information for the update popup.
	 *
	 * @param false|object|array $result Result.
	 * @param string             $action API action.
	 * @param object             $args   Arguments.
	 * @return false|object Plugin info or false.
	 */
	public function plugin_info( $result, $action, $args ) {
		if ( 'plugin_information' !== $action ) {
			return $result;
		}

		if ( $this->slug !== $args->slug ) {
			return $result;
		}

		$remote = $this->get_remote_data();

		if ( ! $remote ) {
			return $result;
		}

		return (object) array(
			'name'           => $remote['name'],
			'slug'           => $this->slug,
			'version'        => $remote['version'],
			'author'         => '<a href="https://digid.ca">DigID Inc</a>',
			'author_profile' => 'https://digid.ca',
			'homepage'       => $remote['url'],
			'download_link'  => $remote['download_url'],
			'trunk'          => $remote['download_url'],
			'requires'       => $remote['requires'] ?? '5.0',
			'tested'         => $remote['tested'] ?? '',
			'requires_php'   => $remote['requires_php'] ?? '7.4',
			'last_updated'   => $remote['published'],
			'sections'       => array(
				'description'  => $remote['description'] ?? 'AI-powered WordPress site management API.',
				'changelog'    => $this->format_changelog( $remote['changelog'] ?? '' ),
			),
		);
	}

	/**
	 * Fix the directory name after unzipping.
	 *
	 * GitHub releases extract to repo-name-version, we need plugin-slug.
	 *
	 * @param string       $source        Source directory.
	 * @param string       $remote_source Remote source.
	 * @param \WP_Upgrader $upgrader      Upgrader instance.
	 * @param array        $hook_extra    Extra args.
	 * @return string|WP_Error Fixed source or error.
	 */
	public function fix_directory_name( $source, $remote_source, $upgrader, $hook_extra ) {
		global $wp_filesystem;

		if ( ! isset( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->basename ) {
			return $source;
		}

		$corrected_source = trailingslashit( $remote_source ) . $this->slug . '/';

		if ( $source === $corrected_source ) {
			return $source;
		}

		if ( $wp_filesystem->move( $source, $corrected_source ) ) {
			return $corrected_source;
		}

		return new WP_Error(
			'rename_failed',
			__( 'Unable to rename the update to match the plugin directory.', 'site-pilot-ai' )
		);
	}

	/**
	 * Get remote data from GitHub.
	 *
	 * @return array|false Remote data or false.
	 */
	private function get_remote_data() {
		$cached = get_transient( $this->cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$response = wp_remote_get(
			"https://api.github.com/repos/{$this->owner}/{$this->repo}/releases/latest",
			array(
				'timeout' => 10,
				'headers' => array(
					'Accept' => 'application/vnd.github.v3+json',
				),
			)
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$release = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $release['tag_name'] ) ) {
			return false;
		}

		// Find the correct asset (zip file)
		$download_url = '';
		if ( ! empty( $release['assets'] ) ) {
			foreach ( $release['assets'] as $asset ) {
				if ( strpos( $asset['name'], $this->zip_name ) !== false ) {
					$download_url = $asset['browser_download_url'];
					break;
				}
			}
		}

		if ( empty( $download_url ) ) {
			return false;
		}

		// Parse version from tag (remove 'v' prefix if present)
		$version = ltrim( $release['tag_name'], 'v' );

		$data = array(
			'name'         => 'Site Pilot AI',
			'version'      => $version,
			'url'          => $release['html_url'],
			'download_url' => $download_url,
			'changelog'    => $release['body'] ?? '',
			'published'    => $release['published_at'] ?? '',
			'requires'     => '5.0',
			'requires_php' => '7.4',
			'tested'       => '6.8',
		);

		// Cache for 6 hours
		set_transient( $this->cache_key, $data, 6 * HOUR_IN_SECONDS );

		return $data;
	}

	/**
	 * Format changelog from GitHub release body.
	 *
	 * @param string $body Release body (markdown).
	 * @return string Formatted changelog.
	 */
	private function format_changelog( $body ) {
		if ( empty( $body ) ) {
			return '<p>See GitHub releases for changelog.</p>';
		}

		// Basic markdown to HTML conversion
		$html = esc_html( $body );
		$html = preg_replace( '/^### (.+)$/m', '<h4>$1</h4>', $html );
		$html = preg_replace( '/^## (.+)$/m', '<h3>$1</h3>', $html );
		$html = preg_replace( '/^- (.+)$/m', '<li>$1</li>', $html );
		$html = preg_replace( '/`([^`]+)`/', '<code>$1</code>', $html );
		$html = nl2br( $html );

		return $html;
	}

	/**
	 * Clear update cache.
	 */
	public function clear_cache() {
		delete_transient( $this->cache_key );
	}
}
