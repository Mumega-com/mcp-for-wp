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
}
