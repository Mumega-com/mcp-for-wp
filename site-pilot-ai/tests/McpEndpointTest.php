<?php

use PHPUnit\Framework\TestCase;

final class McpEndpointTest extends TestCase {
	protected function setUp(): void {
		$GLOBALS['spai_test_options'] = array();
		$GLOBALS['spai_test_transients'] = array();
	}

	public function test_initialize_advertises_resources_capability(): void {
		$controller = new Spai_REST_MCP();
		$request    = new WP_REST_Request(
			'POST',
			'/site-pilot-ai/v1/mcp',
			array(),
			array(),
			array(
				'jsonrpc' => '2.0',
				'id'      => 1,
				'method'  => 'initialize',
				'params'  => array(
					'clientInfo' => array(
						'name'    => 'phpunit',
						'version' => '1.0',
					),
				),
			)
		);

		$response = $controller->handle_mcp( $request );
		$data     = $response->get_data();

		$this->assertArrayHasKey( 'result', $data );
		$this->assertArrayHasKey( 'capabilities', $data['result'] );
		$this->assertArrayHasKey( 'resources', $data['result']['capabilities'] );
	}

	public function test_resources_list_returns_empty_list(): void {
		$controller = new Spai_REST_MCP();
		$request    = new WP_REST_Request(
			'POST',
			'/site-pilot-ai/v1/mcp',
			array(),
			array(),
			array(
				'jsonrpc' => '2.0',
				'id'      => 2,
				'method'  => 'resources/list',
				'params'  => array(),
			)
		);

		$response = $controller->handle_mcp( $request );
		$data     = $response->get_data();

		$this->assertSame( array(), $data['result']['resources'] );
	}

	public function test_tools_list_includes_annotations_hints(): void {
		$controller = new Spai_REST_MCP();
		$request    = new WP_REST_Request(
			'POST',
			'/site-pilot-ai/v1/mcp',
			array(),
			array(),
			array(
				'jsonrpc' => '2.0',
				'id'      => 3,
				'method'  => 'tools/list',
				'params'  => array(),
			)
		);

		$response = $controller->handle_mcp( $request );
		$data     = $response->get_data();
		$tools    = $data['result']['tools'];

		$by_name = array();
		foreach ( $tools as $tool ) {
			$by_name[ $tool['name'] ] = $tool;
		}

		$this->assertArrayHasKey( 'wp_site_info', $by_name );
		$this->assertArrayHasKey( 'annotations', $by_name['wp_site_info'] );
		$this->assertArrayHasKey( 'readOnlyHint', $by_name['wp_site_info']['annotations'] );
		$this->assertArrayHasKey( 'openWorldHint', $by_name['wp_site_info']['annotations'] );
		$this->assertArrayHasKey( 'destructiveHint', $by_name['wp_site_info']['annotations'] );
		$this->assertTrue( $by_name['wp_site_info']['annotations']['readOnlyHint'] );
		$this->assertFalse( $by_name['wp_site_info']['annotations']['openWorldHint'] );
		$this->assertFalse( $by_name['wp_site_info']['annotations']['destructiveHint'] );

		$this->assertArrayHasKey( 'wp_delete_post', $by_name );
		$this->assertTrue( $by_name['wp_delete_post']['annotations']['destructiveHint'] );
		$this->assertFalse( $by_name['wp_delete_post']['annotations']['readOnlyHint'] );

		$this->assertArrayHasKey( 'wp_search', $by_name );
		$this->assertTrue( $by_name['wp_search']['annotations']['readOnlyHint'] );
		$this->assertArrayHasKey( 'wp_fetch', $by_name );
		$this->assertTrue( $by_name['wp_fetch']['annotations']['readOnlyHint'] );
	}
}
