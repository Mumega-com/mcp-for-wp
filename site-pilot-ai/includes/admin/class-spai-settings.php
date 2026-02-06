<?php
/**
 * Settings functionality
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings class.
 */
class Spai_Settings {

	/**
	 * Option name.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'spai_settings';

	/**
	 * Rate-limit option name.
	 *
	 * @var string
	 */
	const RATE_LIMIT_OPTION_NAME = 'spai_rate_limit_settings';

	/**
	 * Register settings.
	 */
	public function register_settings() {
		register_setting(
			'spai_settings_group',
			self::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => $this->get_defaults(),
			)
		);

		register_setting(
			'spai_rate_limit_group',
			self::RATE_LIMIT_OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_rate_limit_settings' ),
				'default'           => $this->get_rate_limit_defaults(),
			)
		);

		// General section
		add_settings_section(
			'spai_general_section',
			__( 'General Settings', 'site-pilot-ai' ),
			array( $this, 'render_general_section' ),
			'spai_settings'
		);

		// Logging
		add_settings_field(
			'enable_logging',
			__( 'Activity Logging', 'site-pilot-ai' ),
			array( $this, 'render_checkbox_field' ),
			'spai_settings',
			'spai_general_section',
			array(
				'id'          => 'enable_logging',
				'description' => __( 'Log API requests for analytics and debugging.', 'site-pilot-ai' ),
			)
		);

		// Log retention
		add_settings_field(
			'log_retention_days',
			__( 'Log Retention', 'site-pilot-ai' ),
			array( $this, 'render_number_field' ),
			'spai_settings',
			'spai_general_section',
			array(
				'id'          => 'log_retention_days',
				'description' => __( 'Number of days to keep activity logs.', 'site-pilot-ai' ),
				'min'         => 1,
				'max'         => 365,
				'suffix'      => __( 'days', 'site-pilot-ai' ),
			)
		);

		// Allowed origins (CORS)
		add_settings_field(
			'allowed_origins',
			__( 'Allowed Origins', 'site-pilot-ai' ),
			array( $this, 'render_textarea_field' ),
			'spai_settings',
			'spai_general_section',
			array(
				'id'          => 'allowed_origins',
				'description' => __( 'Comma-separated list of allowed origins for CORS. Leave empty to allow all.', 'site-pilot-ai' ),
				'placeholder' => 'https://example.com, https://app.example.com',
			)
		);

		add_settings_field(
			'oauth_enabled',
			__( 'OAuth Token Endpoint', 'site-pilot-ai' ),
			array( $this, 'render_checkbox_field' ),
			'spai_settings',
			'spai_general_section',
			array(
				'id'          => 'oauth_enabled',
				'description' => __( 'Enable OAuth2 client credentials at /wp-json/site-pilot-ai/v1/oauth/token.', 'site-pilot-ai' ),
			)
		);

		add_settings_field(
			'oauth_client_id',
			__( 'OAuth Client ID', 'site-pilot-ai' ),
			array( $this, 'render_text_field' ),
			'spai_settings',
			'spai_general_section',
			array(
				'id'          => 'oauth_client_id',
				'description' => __( 'Client ID used for token requests.', 'site-pilot-ai' ),
				'placeholder' => 'site_pilot_ai',
			)
		);

		add_settings_field(
			'oauth_client_secret',
			__( 'OAuth Client Secret', 'site-pilot-ai' ),
			array( $this, 'render_secret_field' ),
			'spai_settings',
			'spai_general_section',
			array(
				'id'          => 'oauth_client_secret',
				'description' => __( 'Set a new secret. Leave empty to keep the existing value.', 'site-pilot-ai' ),
			)
		);

		add_settings_field(
			'oauth_token_ttl',
			__( 'OAuth Token TTL', 'site-pilot-ai' ),
			array( $this, 'render_number_field' ),
			'spai_settings',
			'spai_general_section',
			array(
				'id'          => 'oauth_token_ttl',
				'description' => __( 'Token lifetime in seconds.', 'site-pilot-ai' ),
				'min'         => 300,
				'max'         => 86400,
				'suffix'      => __( 'seconds', 'site-pilot-ai' ),
			)
		);

		// Rate-limiting section.
		add_settings_section(
			'spai_rate_limit_section',
			__( 'Rate Limiting', 'site-pilot-ai' ),
			array( $this, 'render_rate_limit_section' ),
			'spai_rate_limit_settings'
		);

		add_settings_field(
			'enabled',
			__( 'Enable Rate Limiting', 'site-pilot-ai' ),
			array( $this, 'render_checkbox_field' ),
			'spai_rate_limit_settings',
			'spai_rate_limit_section',
			array(
				'option_name' => self::RATE_LIMIT_OPTION_NAME,
				'id'          => 'enabled',
				'description' => __( 'Apply request limits per identifier.', 'site-pilot-ai' ),
			)
		);

		add_settings_field(
			'requests_per_minute',
			__( 'Requests Per Minute', 'site-pilot-ai' ),
			array( $this, 'render_number_field' ),
			'spai_rate_limit_settings',
			'spai_rate_limit_section',
			array(
				'option_name' => self::RATE_LIMIT_OPTION_NAME,
				'id'          => 'requests_per_minute',
				'description' => __( 'Maximum requests allowed per minute.', 'site-pilot-ai' ),
				'min'         => 1,
				'max'         => 100000,
			)
		);

		add_settings_field(
			'requests_per_hour',
			__( 'Requests Per Hour', 'site-pilot-ai' ),
			array( $this, 'render_number_field' ),
			'spai_rate_limit_settings',
			'spai_rate_limit_section',
			array(
				'option_name' => self::RATE_LIMIT_OPTION_NAME,
				'id'          => 'requests_per_hour',
				'description' => __( 'Maximum requests allowed per hour.', 'site-pilot-ai' ),
				'min'         => 1,
				'max'         => 100000,
			)
		);

		add_settings_field(
			'burst_limit',
			__( 'Burst Limit (10s)', 'site-pilot-ai' ),
			array( $this, 'render_number_field' ),
			'spai_rate_limit_settings',
			'spai_rate_limit_section',
			array(
				'option_name' => self::RATE_LIMIT_OPTION_NAME,
				'id'          => 'burst_limit',
				'description' => __( 'Maximum requests allowed in a short burst window.', 'site-pilot-ai' ),
				'min'         => 1,
				'max'         => 100000,
			)
		);

		add_settings_field(
			'whitelist',
			__( 'Whitelist', 'site-pilot-ai' ),
			array( $this, 'render_textarea_field' ),
			'spai_rate_limit_settings',
			'spai_rate_limit_section',
			array(
				'option_name' => self::RATE_LIMIT_OPTION_NAME,
				'id'          => 'whitelist',
				'description' => __( 'Comma or newline-separated identifiers that bypass limits.', 'site-pilot-ai' ),
				'placeholder' => "127.0.0.1\nkey:example-id",
			)
		);
	}

	/**
	 * Get default settings.
	 *
	 * @return array Defaults.
	 */
	public function get_defaults() {
		return array(
			'enable_logging'     => true,
			'log_retention_days' => 30,
			'allowed_origins'    => '',
			'oauth_enabled'      => false,
			'oauth_client_id'    => 'site_pilot_ai',
			'oauth_client_secret_hash' => '',
			'oauth_token_ttl'    => 3600,
		);
	}

	/**
	 * Get default rate-limit settings.
	 *
	 * @return array Defaults.
	 */
	public function get_rate_limit_defaults() {
		return array(
			'enabled'             => true,
			'requests_per_minute' => 60,
			'requests_per_hour'   => 1000,
			'burst_limit'         => 10,
			'whitelist'           => array(),
		);
	}

	/**
	 * Get settings.
	 *
	 * @return array Settings.
	 */
	public function get_settings() {
		return wp_parse_args(
			get_option( self::OPTION_NAME, array() ),
			$this->get_defaults()
		);
	}

	/**
	 * Get rate-limit settings.
	 *
	 * @return array Settings.
	 */
	public function get_rate_limit_settings() {
		return wp_parse_args(
			get_option( self::RATE_LIMIT_OPTION_NAME, array() ),
			$this->get_rate_limit_defaults()
		);
	}

	/**
	 * Sanitize settings.
	 *
	 * @param array $input Input values.
	 * @return array Sanitized values.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();
		$current   = $this->get_settings();

		$sanitized['enable_logging'] = ! empty( $input['enable_logging'] );

		$sanitized['log_retention_days'] = isset( $input['log_retention_days'] )
			? min( 365, max( 1, absint( $input['log_retention_days'] ) ) )
			: 30;

		$sanitized['allowed_origins'] = isset( $input['allowed_origins'] )
			? sanitize_textarea_field( $input['allowed_origins'] )
			: '';

		$sanitized['oauth_enabled'] = ! empty( $input['oauth_enabled'] );
		$sanitized['oauth_client_id'] = isset( $input['oauth_client_id'] ) && '' !== $input['oauth_client_id']
			? sanitize_key( $input['oauth_client_id'] )
			: 'site_pilot_ai';
		$sanitized['oauth_token_ttl'] = isset( $input['oauth_token_ttl'] )
			? min( 86400, max( 300, absint( $input['oauth_token_ttl'] ) ) )
			: 3600;

		$new_secret = isset( $input['oauth_client_secret'] ) ? trim( (string) $input['oauth_client_secret'] ) : '';
		if ( '' !== $new_secret ) {
			$sanitized['oauth_client_secret_hash'] = wp_hash_password( $new_secret );
		} else {
			$sanitized['oauth_client_secret_hash'] = isset( $current['oauth_client_secret_hash'] ) ? (string) $current['oauth_client_secret_hash'] : '';
		}

		return $sanitized;
	}

	/**
	 * Sanitize rate-limit settings.
	 *
	 * @param array $input Input values.
	 * @return array Sanitized values.
	 */
	public function sanitize_rate_limit_settings( $input ) {
		$sanitized = $this->get_rate_limit_defaults();

		$sanitized['enabled'] = ! empty( $input['enabled'] );

		$sanitized['requests_per_minute'] = isset( $input['requests_per_minute'] )
			? max( 1, min( 100000, absint( $input['requests_per_minute'] ) ) )
			: $sanitized['requests_per_minute'];

		$sanitized['requests_per_hour'] = isset( $input['requests_per_hour'] )
			? max( 1, min( 100000, absint( $input['requests_per_hour'] ) ) )
			: $sanitized['requests_per_hour'];

		$sanitized['burst_limit'] = isset( $input['burst_limit'] )
			? max( 1, min( 100000, absint( $input['burst_limit'] ) ) )
			: $sanitized['burst_limit'];

		if ( $sanitized['burst_limit'] > $sanitized['requests_per_minute'] ) {
			$sanitized['burst_limit'] = $sanitized['requests_per_minute'];
		}

		$raw_whitelist = isset( $input['whitelist'] ) ? $input['whitelist'] : array();
		if ( is_string( $raw_whitelist ) ) {
			$raw_whitelist = preg_split( '/[\r\n,]+/', $raw_whitelist );
		}

		if ( is_array( $raw_whitelist ) ) {
			$whitelist = array();
			foreach ( $raw_whitelist as $item ) {
				$item = trim( sanitize_text_field( (string) $item ) );
				if ( '' === $item ) {
					continue;
				}
				$whitelist[] = $item;
			}
			$sanitized['whitelist'] = array_values( array_unique( $whitelist ) );
		}

		return $sanitized;
	}

	/**
	 * Render general section.
	 */
	public function render_general_section() {
		echo '<p>' . esc_html__( 'Configure general plugin settings.', 'site-pilot-ai' ) . '</p>';
	}

	/**
	 * Render rate-limit section.
	 */
	public function render_rate_limit_section() {
		echo '<p>' . esc_html__( 'Configure request throttling and bypass identifiers.', 'site-pilot-ai' ) . '</p>';
	}

	/**
	 * Render checkbox field.
	 *
	 * @param array $args Field arguments.
	 */
	public function render_checkbox_field( $args ) {
		$settings = $this->get_option_settings( $args );
		$value = isset( $settings[ $args['id'] ] ) ? $settings[ $args['id'] ] : false;
		$option_name = isset( $args['option_name'] ) ? $args['option_name'] : self::OPTION_NAME;

		printf(
			'<label><input type="checkbox" name="%s[%s]" value="1" %s /> %s</label>',
			esc_attr( $option_name ),
			esc_attr( $args['id'] ),
			checked( $value, true, false ),
			esc_html( $args['description'] )
		);
	}

	/**
	 * Render number field.
	 *
	 * @param array $args Field arguments.
	 */
	public function render_number_field( $args ) {
		$settings = $this->get_option_settings( $args );
		$value = isset( $settings[ $args['id'] ] ) ? $settings[ $args['id'] ] : '';
		$option_name = isset( $args['option_name'] ) ? $args['option_name'] : self::OPTION_NAME;

		printf(
			'<input type="number" name="%s[%s]" value="%s" min="%d" max="%d" class="small-text" /> %s',
			esc_attr( $option_name ),
			esc_attr( $args['id'] ),
			esc_attr( $value ),
			esc_attr( isset( $args['min'] ) ? $args['min'] : 0 ),
			esc_attr( isset( $args['max'] ) ? $args['max'] : 999999 ),
			esc_html( $args['suffix'] ?? '' )
		);

		if ( ! empty( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
		}
	}

	/**
	 * Render textarea field.
	 *
	 * @param array $args Field arguments.
	 */
	public function render_textarea_field( $args ) {
		$settings = $this->get_option_settings( $args );
		$value = isset( $settings[ $args['id'] ] ) ? $settings[ $args['id'] ] : '';
		$option_name = isset( $args['option_name'] ) ? $args['option_name'] : self::OPTION_NAME;
		if ( is_array( $value ) ) {
			$value = implode( "\n", $value );
		}

		printf(
			'<textarea name="%s[%s]" rows="3" class="large-text" placeholder="%s">%s</textarea>',
			esc_attr( $option_name ),
			esc_attr( $args['id'] ),
			esc_attr( $args['placeholder'] ?? '' ),
			esc_textarea( $value )
		);

		if ( ! empty( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
		}
	}

	/**
	 * Render text field.
	 *
	 * @param array $args Field arguments.
	 */
	public function render_text_field( $args ) {
		$settings = $this->get_option_settings( $args );
		$value = isset( $settings[ $args['id'] ] ) ? $settings[ $args['id'] ] : '';
		$option_name = isset( $args['option_name'] ) ? $args['option_name'] : self::OPTION_NAME;

		printf(
			'<input type="text" name="%s[%s]" value="%s" class="regular-text" placeholder="%s" />',
			esc_attr( $option_name ),
			esc_attr( $args['id'] ),
			esc_attr( $value ),
			esc_attr( $args['placeholder'] ?? '' )
		);

		if ( ! empty( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
		}
	}

	/**
	 * Render secret field.
	 *
	 * @param array $args Field arguments.
	 */
	public function render_secret_field( $args ) {
		$settings = $this->get_option_settings( $args );
		$option_name = isset( $args['option_name'] ) ? $args['option_name'] : self::OPTION_NAME;
		$has_secret = ! empty( $settings['oauth_client_secret_hash'] );

		printf(
			'<input type="password" name="%s[%s]" value="" class="regular-text" autocomplete="new-password" />',
			esc_attr( $option_name ),
			esc_attr( $args['id'] )
		);

		if ( ! empty( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
		}

		if ( $has_secret ) {
			printf( '<p class="description">%s</p>', esc_html__( 'A client secret is already configured.', 'site-pilot-ai' ) );
		}
	}

	/**
	 * Resolve settings array based on field option.
	 *
	 * @param array $args Field arguments.
	 * @return array Settings array.
	 */
	private function get_option_settings( $args ) {
		$option_name = isset( $args['option_name'] ) ? $args['option_name'] : self::OPTION_NAME;

		if ( self::RATE_LIMIT_OPTION_NAME === $option_name ) {
			return $this->get_rate_limit_settings();
		}

		return $this->get_settings();
	}

}
