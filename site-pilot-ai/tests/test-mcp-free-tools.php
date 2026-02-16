<?php
/**
 * Tests for Spai_MCP_Free_Tools.
 *
 * @package SitePilotAI\Tests
 */

class Test_MCP_Free_Tools extends PHPUnit\Framework\TestCase {

	/** @var Spai_MCP_Free_Tools */
	private $registry;

	protected function setUp(): void {
		$this->registry = new Spai_MCP_Free_Tools();
	}

	// ── Tool definitions ───────────────────────────────────────

	public function test_get_tools_returns_array() {
		$tools = $this->registry->get_tools();

		$this->assertIsArray( $tools );
		$this->assertNotEmpty( $tools );
	}

	public function test_all_expected_menu_tools_present() {
		$tools = $this->registry->get_tools();
		$names = array_column( $tools, 'name' );

		$expected = array(
			'wp_list_menus',
			'wp_list_menu_locations',
			'wp_setup_menu',
			'wp_list_menu_items',
			'wp_add_menu_item',
			'wp_update_menu_item',
			'wp_delete_menu_item',
			'wp_reorder_menu_items',
		);

		foreach ( $expected as $tool_name ) {
			$this->assertContains( $tool_name, $names, "Missing tool: {$tool_name}" );
		}
	}

	public function test_wp_list_media_tool_present() {
		$tools = $this->registry->get_tools();
		$names = array_column( $tools, 'name' );

		$this->assertContains( 'wp_list_media', $names );
	}

	// ── Tool map coverage ──────────────────────────────────────

	public function test_tool_map_covers_all_tools() {
		$tools   = $this->registry->get_tools();
		$map     = $this->registry->get_tool_map();
		$names   = array_column( $tools, 'name' );

		$missing = array();
		foreach ( $names as $name ) {
			if ( ! isset( $map[ $name ] ) ) {
				$missing[] = $name;
			}
		}

		$this->assertEmpty(
			$missing,
			'Tools missing from tool map: ' . implode( ', ', $missing )
		);
	}

	public function test_tool_map_has_valid_methods() {
		$map            = $this->registry->get_tool_map();
		$valid_methods  = array( 'GET', 'POST', 'PUT', 'PATCH', 'DELETE' );

		foreach ( $map as $name => $entry ) {
			$this->assertArrayHasKey( 'method', $entry, "No method for tool: {$name}" );
			$this->assertArrayHasKey( 'route', $entry, "No route for tool: {$name}" );
			$this->assertContains(
				$entry['method'],
				$valid_methods,
				"Invalid method '{$entry['method']}' for tool: {$name}"
			);
		}
	}

	// ── Annotations ────────────────────────────────────────────

	public function test_delete_menu_item_is_destructive() {
		$annotations = $this->registry->get_tool_annotations( 'wp_delete_menu_item' );

		$this->assertTrue( $annotations['destructiveHint'] );
	}

	public function test_list_menus_is_read_only() {
		$annotations = $this->registry->get_tool_annotations( 'wp_list_menus' );

		$this->assertTrue( $annotations['readOnlyHint'] );
		$this->assertFalse( $annotations['destructiveHint'] );
	}

	public function test_list_media_is_read_only() {
		$annotations = $this->registry->get_tool_annotations( 'wp_list_media' );

		$this->assertTrue( $annotations['readOnlyHint'] );
	}

	public function test_add_menu_item_is_not_read_only() {
		$annotations = $this->registry->get_tool_annotations( 'wp_add_menu_item' );

		$this->assertFalse( $annotations['readOnlyHint'] );
	}

	// ── Tool schema structure ──────────────────────────────────

	public function test_each_tool_has_required_fields() {
		$tools = $this->registry->get_tools();

		foreach ( $tools as $tool ) {
			$this->assertArrayHasKey( 'name', $tool );
			$this->assertArrayHasKey( 'description', $tool );
			$this->assertArrayHasKey( 'inputSchema', $tool );
			$this->assertArrayHasKey( 'annotations', $tool );
			$this->assertNotEmpty( $tool['name'] );
			$this->assertNotEmpty( $tool['description'] );
		}
	}
}
