<?php
/**
 * Tests for Spai_Elementor_Basic.
 *
 * @package SitePilotAI\Tests
 */

class Test_Elementor_Basic extends PHPUnit\Framework\TestCase {

	/** @var Spai_Elementor_Basic */
	private $elementor;

	protected function setUp(): void {
		$this->elementor = new Spai_Elementor_Basic();

		$GLOBALS['_spai_test_posts'] = array();
		$GLOBALS['_spai_test_meta']  = array();
	}

	// ── validate_post ──────────────────────────────────────────

	public function test_validate_post_returns_error_for_missing_post() {
		$result = $this->elementor->validate_post( 999 );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'not_found', $result->get_error_code() );
	}

	public function test_validate_post_returns_error_for_unsupported_type() {
		$post = (object) array( 'ID' => 10, 'post_type' => 'attachment', 'post_title' => 'Image' );
		$GLOBALS['_spai_test_posts'][10] = $post;

		$result = $this->elementor->validate_post( 10 );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'not_found', $result->get_error_code() );
	}

	/**
	 * @dataProvider allowed_post_types_provider
	 */
	public function test_validate_post_accepts_allowed_types( $post_type ) {
		$post = (object) array( 'ID' => 1, 'post_type' => $post_type, 'post_title' => 'Test' );
		$GLOBALS['_spai_test_posts'][1] = $post;

		$result = $this->elementor->validate_post( 1 );

		$this->assertNotInstanceOf( WP_Error::class, $result );
		$this->assertSame( $post_type, $result->post_type );
	}

	public static function allowed_post_types_provider() {
		return array(
			'page'              => array( 'page' ),
			'post'              => array( 'post' ),
			'elementor_library' => array( 'elementor_library' ),
		);
	}

	// ── get_elementor_data ─────────────────────────────────────

	public function test_get_elementor_data_returns_error_when_not_active() {
		$result = $this->elementor->get_elementor_data( 1 );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'elementor_not_active', $result->get_error_code() );
	}

	public function test_get_elementor_data_returns_error_for_invalid_post() {
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			define( 'ELEMENTOR_VERSION', '3.20.0' );
		}

		$result = $this->elementor->get_elementor_data( 999 );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'not_found', $result->get_error_code() );
	}

	public function test_get_elementor_data_accepts_elementor_library() {
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			define( 'ELEMENTOR_VERSION', '3.20.0' );
		}

		$post = (object) array( 'ID' => 127, 'post_type' => 'elementor_library', 'post_title' => 'Header' );
		$GLOBALS['_spai_test_posts'][127] = $post;
		$GLOBALS['_spai_test_meta'][127]  = array(
			'_elementor_data'          => '[{"id":"abc"}]',
			'_elementor_edit_mode'     => 'builder',
			'_elementor_template_type' => 'header',
		);

		$result = $this->elementor->get_elementor_data( 127 );

		$this->assertIsArray( $result );
		$this->assertSame( 127, $result['page_id'] );
		$this->assertSame( 'Header', $result['title'] );
		$this->assertTrue( $result['has_elementor'] );
		$this->assertSame( 'header', $result['template_type'] );
	}

	// ── set_elementor_data ─────────────────────────────────────

	public function test_set_elementor_data_rejects_invalid_json() {
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			define( 'ELEMENTOR_VERSION', '3.20.0' );
		}

		$post = (object) array( 'ID' => 5, 'post_type' => 'page', 'post_title' => 'Test Page' );
		$GLOBALS['_spai_test_posts'][5] = $post;

		$result = $this->elementor->set_elementor_data( 5, array(
			'elementor_data' => '{invalid json',
		) );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'invalid_json', $result->get_error_code() );
	}

	public function test_set_elementor_data_rejects_empty_data() {
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			define( 'ELEMENTOR_VERSION', '3.20.0' );
		}

		$post = (object) array( 'ID' => 5, 'post_type' => 'page', 'post_title' => 'Test Page' );
		$GLOBALS['_spai_test_posts'][5] = $post;

		$result = $this->elementor->set_elementor_data( 5, array() );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'no_data', $result->get_error_code() );
	}
}
