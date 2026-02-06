<?php

use PHPUnit\Framework\TestCase;

final class RateLimiterTest extends TestCase {
	protected function setUp(): void {
		$GLOBALS['spai_test_options'] = array();
		$GLOBALS['spai_test_transients'] = array();
		$this->resetLimiterSingleton();
	}

	public function test_stale_window_is_reinitialized_before_limit_check(): void {
		update_option(
			'spai_rate_limit_settings',
			array(
				'enabled'             => true,
				'requests_per_minute' => 1,
				'requests_per_hour'   => 100,
				'burst_limit'         => 10,
				'whitelist'           => array(),
			)
		);

		$identifier = 'client-a';
		$minute_key = 'spai_rate_' . md5( $identifier . '_minute' );
		$hour_key   = 'spai_rate_' . md5( $identifier . '_hour' );

		set_transient( $minute_key, array( 'count' => 1, 'reset' => time() - 30 ), 120 );
		set_transient( $hour_key, array( 'count' => 0, 'reset' => time() + 3000 ), 3000 );

		$limiter = Spai_Rate_Limiter::get_instance();
		$result  = $limiter->check_limit( $identifier );

		$this->assertTrue( $result );
	}

	public function test_retry_after_is_never_negative(): void {
		update_option(
			'spai_rate_limit_settings',
			array(
				'enabled'             => true,
				'requests_per_minute' => 1,
				'requests_per_hour'   => 100,
				'burst_limit'         => 10,
				'whitelist'           => array(),
			)
		);

		$limiter = Spai_Rate_Limiter::get_instance();
		$this->assertTrue( $limiter->check_limit( 'client-b' ) );

		$error = $limiter->check_limit( 'client-b' );
		$this->assertInstanceOf( WP_Error::class, $error );
		$this->assertSame( 'rate_limit_exceeded', $error->get_error_code() );
		$this->assertGreaterThanOrEqual( 0, (int) $error->get_error_data()['retry_after'] );
	}

	private function resetLimiterSingleton(): void {
		$ref  = new ReflectionClass( Spai_Rate_Limiter::class );
		$prop = $ref->getProperty( 'instance' );
		$prop->setAccessible( true );
		$prop->setValue( null );
	}
}
