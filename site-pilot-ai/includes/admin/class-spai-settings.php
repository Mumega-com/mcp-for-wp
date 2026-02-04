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

		// Updates section
		add_settings_section(
			'spai_updates_section',
			__( 'Plugin Updates', 'site-pilot-ai' ),
			array( $this, 'render_updates_section' ),
			'spai_settings'
		);

		// GitHub Token
		add_settings_field(
			'github_token',
			__( 'GitHub Token', 'site-pilot-ai' ),
			array( $this, 'render_password_field' ),
			'spai_settings',
			'spai_updates_section',
			array(
				'id'          => 'github_token',
				'description' => __( 'Personal access token for GitHub releases (required for private repositories). Create one at GitHub > Settings > Developer settings > Personal access tokens.', 'site-pilot-ai' ),
				'placeholder' => 'ghp_xxxxxxxxxxxxxxxxxxxx',
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
			'github_token'       => '',
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
	 * Sanitize settings.
	 *
	 * @param array $input Input values.
	 * @return array Sanitized values.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		$sanitized['enable_logging'] = ! empty( $input['enable_logging'] );

		$sanitized['log_retention_days'] = isset( $input['log_retention_days'] )
			? min( 365, max( 1, absint( $input['log_retention_days'] ) ) )
			: 30;

		$sanitized['allowed_origins'] = isset( $input['allowed_origins'] )
			? sanitize_textarea_field( $input['allowed_origins'] )
			: '';

		$sanitized['github_token'] = isset( $input['github_token'] )
			? sanitize_text_field( $input['github_token'] )
			: '';

		return $sanitized;
	}

	/**
	 * Render general section.
	 */
	public function render_general_section() {
		echo '<p>' . esc_html__( 'Configure general plugin settings.', 'site-pilot-ai' ) . '</p>';
	}

	/**
	 * Render updates section.
	 */
	public function render_updates_section() {
		echo '<p>' . esc_html__( 'Configure plugin update settings. A GitHub token is required to receive updates from private repositories.', 'site-pilot-ai' ) . '</p>';
	}

	/**
	 * Render checkbox field.
	 *
	 * @param array $args Field arguments.
	 */
	public function render_checkbox_field( $args ) {
		$settings = $this->get_settings();
		$value = isset( $settings[ $args['id'] ] ) ? $settings[ $args['id'] ] : false;

		printf(
			'<label><input type="checkbox" name="%s[%s]" value="1" %s /> %s</label>',
			esc_attr( self::OPTION_NAME ),
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
		$settings = $this->get_settings();
		$value = isset( $settings[ $args['id'] ] ) ? $settings[ $args['id'] ] : '';

		printf(
			'<input type="number" name="%s[%s]" value="%s" min="%d" max="%d" class="small-text" /> %s',
			esc_attr( self::OPTION_NAME ),
			esc_attr( $args['id'] ),
			esc_attr( $value ),
			esc_attr( $args['min'] ),
			esc_attr( $args['max'] ),
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
		$settings = $this->get_settings();
		$value = isset( $settings[ $args['id'] ] ) ? $settings[ $args['id'] ] : '';

		printf(
			'<textarea name="%s[%s]" rows="3" class="large-text" placeholder="%s">%s</textarea>',
			esc_attr( self::OPTION_NAME ),
			esc_attr( $args['id'] ),
			esc_attr( $args['placeholder'] ?? '' ),
			esc_textarea( $value )
		);

		if ( ! empty( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
		}
	}

	/**
	 * Render password field.
	 *
	 * @param array $args Field arguments.
	 */
	public function render_password_field( $args ) {
		$settings = $this->get_settings();
		$value = isset( $settings[ $args['id'] ] ) ? $settings[ $args['id'] ] : '';
		$masked = ! empty( $value ) ? str_repeat( '*', min( strlen( $value ), 20 ) ) . substr( $value, -4 ) : '';

		printf(
			'<input type="password" name="%s[%s]" value="%s" class="regular-text" placeholder="%s" autocomplete="off" />',
			esc_attr( self::OPTION_NAME ),
			esc_attr( $args['id'] ),
			esc_attr( $value ),
			esc_attr( $args['placeholder'] ?? '' )
		);

		if ( ! empty( $value ) ) {
			printf( ' <span class="description">%s</span>', esc_html__( 'Token saved', 'site-pilot-ai' ) );
		}

		if ( ! empty( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
		}
	}

	/**
	 * Get GitHub token.
	 *
	 * @return string Token or empty string.
	 */
	public static function get_github_token() {
		$settings = get_option( self::OPTION_NAME, array() );
		return isset( $settings['github_token'] ) ? $settings['github_token'] : '';
	}
}
