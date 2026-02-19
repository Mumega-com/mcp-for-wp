<?php
/**
 * Integration Manager
 *
 * Stores and retrieves encrypted API keys for third-party AI providers.
 *
 * @package SitePilotAI
 * @since   1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages third-party AI provider integrations.
 */
class Spai_Integration_Manager {

	/**
	 * Singleton instance.
	 *
	 * @var Spai_Integration_Manager|null
	 */
	private static $instance = null;

	/**
	 * WP option name.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'spai_integrations';

	/**
	 * Supported providers.
	 *
	 * @var array
	 */
	const PROVIDERS = array(
		'openai'     => array(
			'name'       => 'OpenAI',
			'url'        => 'https://platform.openai.com/api-keys',
			'key_prefix' => 'sk-',
			'tier'       => 'pro',
		),
		'gemini'     => array(
			'name'       => 'Google Gemini',
			'url'        => 'https://aistudio.google.com/apikey',
			'key_prefix' => '',
			'tier'       => 'pro',
		),
		'elevenlabs' => array(
			'name'       => 'ElevenLabs',
			'url'        => 'https://elevenlabs.io/settings/api-keys',
			'key_prefix' => '',
			'tier'       => 'pro',
		),
		'pexels'     => array(
			'name'       => 'Pexels',
			'url'        => 'https://www.pexels.com/api/',
			'key_prefix' => '',
			'tier'       => 'free',
		),
	);

	/**
	 * Provider capability mapping for auto-selection.
	 *
	 * @var array
	 */
	const CAPABILITY_PROVIDERS = array(
		'image_generation' => array( 'openai', 'gemini' ),
		'vision'           => array( 'openai', 'gemini' ),
		'text'             => array( 'openai', 'gemini' ),
		'tts'              => array( 'elevenlabs' ),
		'stock_photos'     => array( 'pexels' ),
	);

	/**
	 * Get singleton instance.
	 *
	 * @return Spai_Integration_Manager
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Store an API key for a provider.
	 *
	 * @param string $provider Provider slug.
	 * @param string $key      Plaintext API key.
	 * @return bool True on success.
	 */
	public function set_provider_key( $provider, $key ) {
		if ( ! isset( self::PROVIDERS[ $provider ] ) ) {
			return false;
		}

		$encryption = Spai_Encryption::get_instance();
		$encrypted  = $encryption->encrypt( $key );
		if ( false === $encrypted ) {
			return false;
		}

		$data = get_option( self::OPTION_NAME, array() );
		if ( ! is_array( $data ) ) {
			$data = array();
		}

		$data[ $provider ] = array(
			'key_encrypted' => $encrypted,
			'configured_at' => current_time( 'mysql' ),
			'last_tested'   => null,
			'test_status'   => null,
		);

		return update_option( self::OPTION_NAME, $data );
	}

	/**
	 * Get decrypted API key for a provider.
	 *
	 * @param string $provider Provider slug.
	 * @return string|false Plaintext key or false.
	 */
	public function get_provider_key( $provider ) {
		$data = get_option( self::OPTION_NAME, array() );
		if ( ! is_array( $data ) || empty( $data[ $provider ]['key_encrypted'] ) ) {
			return false;
		}

		$encryption = Spai_Encryption::get_instance();
		return $encryption->decrypt( $data[ $provider ]['key_encrypted'] );
	}

	/**
	 * Remove API key for a provider.
	 *
	 * @param string $provider Provider slug.
	 * @return bool True on success.
	 */
	public function remove_provider_key( $provider ) {
		$data = get_option( self::OPTION_NAME, array() );
		if ( ! is_array( $data ) || ! isset( $data[ $provider ] ) ) {
			return false;
		}

		unset( $data[ $provider ] );
		return update_option( self::OPTION_NAME, $data );
	}

	/**
	 * Check if a provider has a configured key.
	 *
	 * @param string $provider Provider slug.
	 * @return bool
	 */
	public function has_provider_key( $provider ) {
		$data = get_option( self::OPTION_NAME, array() );
		return is_array( $data ) && ! empty( $data[ $provider ]['key_encrypted'] );
	}

	/**
	 * Test a provider connection.
	 *
	 * @param string $provider Provider slug.
	 * @return array{success: bool, message: string}
	 */
	public function test_provider( $provider ) {
		$key = $this->get_provider_key( $provider );
		if ( false === $key ) {
			return array(
				'success' => false,
				'message' => __( 'No API key configured for this provider.', 'site-pilot-ai' ),
			);
		}

		$provider_instance = $this->get_provider_instance( $provider );
		if ( ! $provider_instance ) {
			return array(
				'success' => false,
				'message' => __( 'Unknown provider.', 'site-pilot-ai' ),
			);
		}

		$result = $provider_instance->test_connection();

		// Update test status.
		$data = get_option( self::OPTION_NAME, array() );
		if ( is_array( $data ) && isset( $data[ $provider ] ) ) {
			$data[ $provider ]['last_tested'] = current_time( 'mysql' );
			$data[ $provider ]['test_status'] = $result['success'] ? 'ok' : 'failed';
			update_option( self::OPTION_NAME, $data );
		}

		return $result;
	}

	/**
	 * Get list of available providers with status.
	 *
	 * @return array
	 */
	public function get_available_providers() {
		$data      = get_option( self::OPTION_NAME, array() );
		$providers = array();

		foreach ( self::PROVIDERS as $slug => $info ) {
			$stored = is_array( $data ) && isset( $data[ $slug ] ) ? $data[ $slug ] : array();
			$providers[ $slug ] = array(
				'name'          => $info['name'],
				'url'           => $info['url'],
				'tier'          => $info['tier'],
				'configured'    => ! empty( $stored['key_encrypted'] ),
				'configured_at' => isset( $stored['configured_at'] ) ? $stored['configured_at'] : null,
				'last_tested'   => isset( $stored['last_tested'] ) ? $stored['last_tested'] : null,
				'test_status'   => isset( $stored['test_status'] ) ? $stored['test_status'] : null,
			);
		}

		return $providers;
	}

	/**
	 * Get the preferred provider for a capability.
	 *
	 * Returns the first configured provider that supports the capability.
	 *
	 * @param string $capability Capability (image_generation, vision, text, tts, stock_photos).
	 * @return string|null Provider slug or null.
	 */
	public function get_preferred_provider( $capability ) {
		if ( ! isset( self::CAPABILITY_PROVIDERS[ $capability ] ) ) {
			return null;
		}

		foreach ( self::CAPABILITY_PROVIDERS[ $capability ] as $provider ) {
			if ( $this->has_provider_key( $provider ) ) {
				return $provider;
			}
		}

		return null;
	}

	/**
	 * Get a provider instance.
	 *
	 * @param string $provider Provider slug.
	 * @return object|null Provider instance.
	 */
	public function get_provider_instance( $provider ) {
		$key = $this->get_provider_key( $provider );
		if ( false === $key ) {
			return null;
		}

		switch ( $provider ) {
			case 'openai':
				return new Spai_Provider_OpenAI( $key );
			case 'gemini':
				return new Spai_Provider_Gemini( $key );
			case 'elevenlabs':
				return new Spai_Provider_ElevenLabs( $key );
			case 'pexels':
				return new Spai_Provider_Pexels( $key );
			default:
				return null;
		}
	}
}
