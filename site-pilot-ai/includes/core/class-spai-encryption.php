<?php
/**
 * Encryption for third-party API keys
 *
 * Uses libsodium (PHP 7.2+) for reversible encryption of external service
 * API keys. Unlike our own SPAI keys which are one-way hashed, third-party
 * keys need to be decrypted for outgoing API calls.
 *
 * @package SitePilotAI
 * @since   1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sodium-based encryption helper.
 */
class Spai_Encryption {

	/**
	 * Singleton instance.
	 *
	 * @var Spai_Encryption|null
	 */
	private static $instance = null;

	/**
	 * Cached derived key.
	 *
	 * @var string|null
	 */
	private $key = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Spai_Encryption
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Check if sodium encryption is available.
	 *
	 * @return bool
	 */
	public static function is_available() {
		return function_exists( 'sodium_crypto_secretbox' )
			&& function_exists( 'sodium_crypto_secretbox_open' )
			&& function_exists( 'sodium_crypto_generichash' );
	}

	/**
	 * Encrypt a plaintext string.
	 *
	 * @param string $plaintext Value to encrypt.
	 * @return string|false Base64-encoded nonce+ciphertext, or false on failure.
	 */
	public function encrypt( $plaintext ) {
		if ( ! self::is_available() ) {
			return false;
		}

		$key   = $this->get_key();
		$nonce = random_bytes( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );

		try {
			$ciphertext = sodium_crypto_secretbox( $plaintext, $nonce, $key );
		} catch ( \Exception $e ) {
			return false;
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		return base64_encode( $nonce . $ciphertext );
	}

	/**
	 * Decrypt an encrypted string.
	 *
	 * @param string $encoded Base64-encoded nonce+ciphertext from encrypt().
	 * @return string|false Plaintext or false on tamper/failure.
	 */
	public function decrypt( $encoded ) {
		if ( ! self::is_available() ) {
			return false;
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		$decoded = base64_decode( $encoded, true );
		if ( false === $decoded ) {
			return false;
		}

		$nonce_length = SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;
		if ( strlen( $decoded ) < $nonce_length + SODIUM_CRYPTO_SECRETBOX_MACBYTES ) {
			return false;
		}

		$nonce      = substr( $decoded, 0, $nonce_length );
		$ciphertext = substr( $decoded, $nonce_length );
		$key        = $this->get_key();

		try {
			$plaintext = sodium_crypto_secretbox_open( $ciphertext, $nonce, $key );
		} catch ( \Exception $e ) {
			return false;
		}

		return $plaintext;
	}

	/**
	 * Derive a 32-byte encryption key from WordPress auth salt.
	 *
	 * @return string 32-byte key.
	 */
	private function get_key() {
		if ( null !== $this->key ) {
			return $this->key;
		}

		$salt = defined( 'AUTH_SALT' ) ? AUTH_SALT : 'spai-default-salt';
		$this->key = sodium_crypto_generichash( $salt, '', SODIUM_CRYPTO_SECRETBOX_KEYBYTES );

		return $this->key;
	}
}
