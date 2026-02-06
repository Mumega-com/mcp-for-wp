<?php
/**
 * PHPUnit bootstrap with lightweight WordPress stubs.
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

if ( ! defined( 'SPAI_VERSION' ) ) {
	define( 'SPAI_VERSION', 'test' );
}

$GLOBALS['spai_test_options'] = array();
$GLOBALS['spai_test_transients'] = array();
$GLOBALS['spai_test_current_user'] = 0;
$GLOBALS['spai_test_users'] = array(
	(object) array( 'ID' => 2, 'roles' => array( 'spai_api_agent' ) ),
);

class WP_Error {
	private $code;
	private $message;
	private $data;

	public function __construct( $code = '', $message = '', $data = null ) {
		$this->code    = $code;
		$this->message = $message;
		$this->data    = $data;
	}

	public function get_error_code() {
		return $this->code;
	}

	public function get_error_message() {
		return $this->message;
	}

	public function get_error_data() {
		return $this->data;
	}
}

class WP_REST_Response {
	private $data;
	private $status;
	private $headers = array();

	public function __construct( $data = null, $status = 200 ) {
		$this->data   = $data;
		$this->status = $status;
	}

	public function get_data() {
		return $this->data;
	}

	public function set_data( $data ) {
		$this->data = $data;
	}

	public function get_status() {
		return $this->status;
	}

	public function header( $name, $value ) {
		$this->headers[ $name ] = $value;
	}

	public function get_headers() {
		return $this->headers;
	}
}

class WP_REST_Request {
	private $method;
	private $route;
	private $params;
	private $headers;
	private $json_params;

	public function __construct( $method = 'GET', $route = '/', $params = array(), $headers = array(), $json_params = null ) {
		$this->method     = strtoupper( $method );
		$this->route      = $route;
		$this->params     = is_array( $params ) ? $params : array();
		$this->headers    = is_array( $headers ) ? $headers : array();
		$this->json_params = $json_params;
	}

	public function get_method() {
		return $this->method;
	}

	public function get_route() {
		return $this->route;
	}

	public function get_param( $key ) {
		return isset( $this->params[ $key ] ) ? $this->params[ $key ] : null;
	}

	public function set_param( $key, $value ) {
		$this->params[ $key ] = $value;
	}

	public function get_params() {
		return $this->params;
	}

	public function get_header( $name ) {
		$target = strtolower( $name );
		foreach ( $this->headers as $header => $value ) {
			if ( strtolower( $header ) === $target ) {
				return $value;
			}
		}
		return null;
	}

	public function set_header( $name, $value ) {
		$this->headers[ $name ] = $value;
	}

	public function get_json_params() {
		return $this->json_params;
	}
}

class WP_REST_Server {
	const READABLE  = 'GET';
	const CREATABLE = 'POST';
	const EDITABLE  = 'POST,PUT,PATCH';
	const DELETABLE = 'DELETE';
}

function __( $text ) {
	return $text;
}

function is_wp_error( $value ) {
	return $value instanceof WP_Error;
}

function sanitize_text_field( $value ) {
	return trim( (string) $value );
}

function sanitize_key( $value ) {
	$value = strtolower( (string) $value );
	return preg_replace( '/[^a-z0-9_\\-]/', '', $value );
}

function sanitize_email( $value ) {
	return filter_var( $value, FILTER_SANITIZE_EMAIL );
}

function esc_url_raw( $value ) {
	return filter_var( (string) $value, FILTER_SANITIZE_URL );
}

function is_email( $value ) {
	return (bool) filter_var( $value, FILTER_VALIDATE_EMAIL );
}

function wp_unslash( $value ) {
	return is_string( $value ) ? stripslashes( $value ) : $value;
}

function wp_set_current_user( $user_id ) {
	$GLOBALS['spai_test_current_user'] = (int) $user_id;
}

function get_users( $args = array() ) {
	$users = isset( $GLOBALS['spai_test_users'] ) ? $GLOBALS['spai_test_users'] : array();
	if ( empty( $args['role'] ) ) {
		return $users;
	}

	$filtered = array();
	foreach ( $users as $user ) {
		if ( ! empty( $user->roles ) && in_array( $args['role'], $user->roles, true ) ) {
			$filtered[] = $user;
		}
	}

	return $filtered;
}

function current_user_can( $capability ) {
	// Test harness grants all permissions to service user.
	return 0 !== (int) $GLOBALS['spai_test_current_user'];
}

function get_option( $name, $default = false ) {
	return array_key_exists( $name, $GLOBALS['spai_test_options'] ) ? $GLOBALS['spai_test_options'][ $name ] : $default;
}

function update_option( $name, $value ) {
	$GLOBALS['spai_test_options'][ $name ] = $value;
	return true;
}

function delete_option( $name ) {
	unset( $GLOBALS['spai_test_options'][ $name ] );
	return true;
}

function set_transient( $name, $value, $expiration ) {
	$GLOBALS['spai_test_transients'][ $name ] = array(
		'value'   => $value,
		'expires' => time() + (int) $expiration,
	);
	return true;
}

function get_transient( $name ) {
	if ( ! isset( $GLOBALS['spai_test_transients'][ $name ] ) ) {
		return false;
	}

	$item = $GLOBALS['spai_test_transients'][ $name ];
	if ( $item['expires'] <= time() ) {
		unset( $GLOBALS['spai_test_transients'][ $name ] );
		return false;
	}

	return $item['value'];
}

function delete_transient( $name ) {
	unset( $GLOBALS['spai_test_transients'][ $name ] );
	return true;
}

function current_time( $type ) {
	if ( 'mysql' === $type ) {
		return gmdate( 'Y-m-d H:i:s' );
	}
	return time();
}

function wp_hash_password( $password ) {
	return password_hash( (string) $password, PASSWORD_BCRYPT );
}

function wp_check_password( $password, $hash ) {
	$hash = (string) $hash;
	if ( 0 === strpos( $hash, '$2' ) || 0 === strpos( $hash, '$argon' ) ) {
		return password_verify( (string) $password, $hash );
	}
	return hash_equals( $hash, (string) $password );
}

function wp_json_encode( $value, $flags = 0 ) {
	return json_encode( $value, $flags );
}

function wp_parse_args( $args, $defaults = array() ) {
	$args = is_array( $args ) ? $args : array();
	return array_merge( $defaults, $args );
}

function register_rest_route() {
	return true;
}

function rest_do_request() {
	return new WP_REST_Response( array( 'ok' => true ), 200 );
}

require_once dirname( __DIR__ ) . '/includes/traits/trait-spai-api-auth.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-spai-sanitization.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-spai-logging.php';
require_once dirname( __DIR__ ) . '/includes/class-spai-rate-limiter.php';
require_once dirname( __DIR__ ) . '/includes/api/class-spai-rest-api.php';
require_once dirname( __DIR__ ) . '/includes/api/class-spai-rest-mcp.php';
