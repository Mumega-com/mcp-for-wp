<?php

use PHPUnit\Framework\TestCase;

class Spai_Api_Auth_Test_Harness {
	use Spai_Api_Auth;
}

final class ApiAuthTest extends TestCase {
	protected function setUp(): void {
		$GLOBALS['spai_test_options'] = array();
		$GLOBALS['spai_test_transients'] = array();
		$GLOBALS['spai_test_current_user'] = 0;
	}

	public function test_query_parameter_key_is_not_accepted(): void {
		$auth = new Spai_Api_Auth_Test_Harness();
		update_option( 'spai_api_key', wp_hash_password( 'spai_valid' ) );

		$request = new WP_REST_Request(
			'GET',
			'/site-pilot-ai/v1/site-info',
			array( 'api_key' => 'spai_valid' ),
			array()
		);

		$result = $auth->verify_api_key( $request );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'missing_api_key', $result->get_error_code() );
	}

	public function test_read_scope_key_cannot_write(): void {
		$auth = new Spai_Api_Auth_Test_Harness();
		$created = $auth->create_scoped_api_key( 'read only', array( 'read' ) );

		$request = new WP_REST_Request(
			'POST',
			'/site-pilot-ai/v1/posts',
			array(),
			array( 'X-API-Key' => $created['key'] )
		);

		$result = $auth->verify_api_key( $request );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'insufficient_scope', $result->get_error_code() );
		$this->assertSame( 403, $result->get_error_data()['status'] );
	}

	public function test_write_scope_key_can_write(): void {
		$auth = new Spai_Api_Auth_Test_Harness();
		$created = $auth->create_scoped_api_key( 'writer', array( 'write' ) );

		$request = new WP_REST_Request(
			'POST',
			'/site-pilot-ai/v1/posts',
			array(),
			array( 'X-API-Key' => $created['key'] )
		);

		$result = $auth->verify_api_key( $request );

		$this->assertTrue( $result );
		$this->assertNotSame( 0, $GLOBALS['spai_test_current_user'] );
	}

	public function test_plaintext_legacy_key_is_hashed_when_migrated(): void {
		$auth = new Spai_Api_Auth_Test_Harness();
		update_option( 'spai_api_key', 'spai_legacy_plain' );

		$request = new WP_REST_Request(
			'GET',
			'/site-pilot-ai/v1/site-info',
			array(),
			array( 'X-API-Key' => 'spai_legacy_plain' )
		);

		$result = $auth->verify_api_key( $request );

		$this->assertTrue( $result );

		$legacy = (string) get_option( 'spai_api_key' );
		$this->assertNotSame( 'spai_legacy_plain', $legacy );
		$this->assertSame( 0, strpos( $legacy, '$' ) );

		$keys = get_option( 'spai_api_keys', array() );
		$this->assertNotEmpty( $keys );
		$this->assertNotSame( 'spai_legacy_plain', $keys[0]['hash'] );
		$this->assertTrue( wp_check_password( 'spai_legacy_plain', $keys[0]['hash'] ) );
	}

	public function test_oauth_bearer_token_with_read_scope_authenticates_read_requests(): void {
		$auth = new Spai_Api_Auth_Test_Harness();
		update_option(
			'spai_settings',
			array(
				'oauth_enabled'            => true,
				'oauth_client_id'          => 'site_pilot_ai',
				'oauth_client_secret_hash' => wp_hash_password( 'secret' ),
				'oauth_token_ttl'          => 3600,
			)
		);

		$token_response = $auth->issue_oauth_access_token( array( 'read' ), 3600 );

		$request = new WP_REST_Request(
			'GET',
			'/site-pilot-ai/v1/site-info',
			array(),
			array( 'Authorization' => 'Bearer ' . $token_response['access_token'] )
		);

		$result = $auth->verify_api_key( $request );
		$this->assertTrue( $result );
	}

	public function test_oauth_bearer_token_with_read_scope_cannot_write(): void {
		$auth = new Spai_Api_Auth_Test_Harness();
		update_option(
			'spai_settings',
			array(
				'oauth_enabled'            => true,
				'oauth_client_id'          => 'site_pilot_ai',
				'oauth_client_secret_hash' => wp_hash_password( 'secret' ),
				'oauth_token_ttl'          => 3600,
			)
		);

		$token_response = $auth->issue_oauth_access_token( array( 'read' ), 3600 );

		$request = new WP_REST_Request(
			'POST',
			'/site-pilot-ai/v1/posts',
			array(),
			array( 'Authorization' => 'Bearer ' . $token_response['access_token'] )
		);

		$result = $auth->verify_api_key( $request );
		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'insufficient_scope', $result->get_error_code() );
	}
}
