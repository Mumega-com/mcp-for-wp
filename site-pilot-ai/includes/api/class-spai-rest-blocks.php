<?php
/**
 * REST API Blocks Controller
 *
 * Provides endpoints for Gutenberg block editor operations:
 * get/set parsed blocks, list block types, and list block patterns.
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Blocks REST controller.
 */
class Spai_REST_Blocks extends Spai_REST_API {

	/**
	 * Register routes.
	 */
	public function register_routes() {
		// Get / set parsed blocks for a post.
		register_rest_route(
			$this->namespace,
			'/blocks/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_blocks' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'id' => array(
							'type'              => 'integer',
							'required'          => true,
							'sanitize_callback' => 'absint',
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'set_blocks' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'id' => array(
							'type'              => 'integer',
							'required'          => true,
							'sanitize_callback' => 'absint',
						),
					),
				),
			)
		);

		// List registered block types.
		register_rest_route(
			$this->namespace,
			'/block-types',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'list_block_types' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// List registered block patterns.
		register_rest_route(
			$this->namespace,
			'/block-patterns',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'list_block_patterns' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);
	}

	/**
	 * Get parsed blocks for a post or page.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function get_blocks( $request ) {
		$post_id = $request->get_param( 'id' );
		$post    = get_post( $post_id );

		if ( ! $post ) {
			return $this->error_response( 'not_found', 'Post not found.', 404 );
		}

		$content = $post->post_content;
		$blocks  = parse_blocks( $content );

		// Clean up parsed blocks for API output.
		$cleaned = $this->clean_blocks( $blocks );

		$this->log_activity( 'get_blocks', $request, array( 'post_id' => $post_id ) );

		return $this->success_response(
			array(
				'post_id'     => $post_id,
				'title'       => $post->post_title,
				'block_count' => count( $cleaned ),
				'blocks'      => $cleaned,
				'raw_content' => $content,
			)
		);
	}

	/**
	 * Set blocks for a post or page.
	 *
	 * Accepts either a blocks array (serialized via serialize_blocks)
	 * or raw block content string.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function set_blocks( $request ) {
		$post_id = $request->get_param( 'id' );
		$post    = get_post( $post_id );

		if ( ! $post ) {
			return $this->error_response( 'not_found', 'Post not found.', 404 );
		}

		$blocks  = $request->get_param( 'blocks' );
		$content = $request->get_param( 'content' );

		if ( ! empty( $blocks ) && is_array( $blocks ) ) {
			// Serialize blocks array to HTML content.
			$content = serialize_blocks( $blocks );
		} elseif ( empty( $content ) ) {
			return $this->error_response(
				'missing_data',
				'Provide either a blocks array or content string.',
				400
			);
		}

		$result = wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => $content,
			),
			true
		);

		if ( is_wp_error( $result ) ) {
			return $this->error_response(
				'update_failed',
				$result->get_error_message(),
				500
			);
		}

		// Re-parse to return the saved state.
		$saved_content = get_post_field( 'post_content', $post_id );
		$parsed        = $this->clean_blocks( parse_blocks( $saved_content ) );

		$this->log_activity( 'set_blocks', $request, array( 'post_id' => $post_id ) );

		return $this->success_response(
			array(
				'success'     => true,
				'post_id'     => $post_id,
				'block_count' => count( $parsed ),
				'blocks'      => $parsed,
			)
		);
	}

	/**
	 * List registered block types.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function list_block_types( $request ) {
		if ( ! class_exists( 'WP_Block_Type_Registry' ) ) {
			return $this->error_response(
				'not_available',
				'Block type registry not available.',
				500
			);
		}

		$registry    = WP_Block_Type_Registry::get_instance();
		$block_types = $registry->get_all_registered();
		$result      = array();

		foreach ( $block_types as $name => $block_type ) {
			$item = array(
				'name'     => $name,
				'title'    => ! empty( $block_type->title ) ? $block_type->title : '',
				'category' => ! empty( $block_type->category ) ? $block_type->category : '',
				'icon'     => ! empty( $block_type->icon ) && is_string( $block_type->icon ) ? $block_type->icon : '',
			);

			if ( ! empty( $block_type->description ) ) {
				$item['description'] = $block_type->description;
			}

			if ( ! empty( $block_type->keywords ) ) {
				$item['keywords'] = $block_type->keywords;
			}

			if ( ! empty( $block_type->supports ) ) {
				$item['supports'] = $block_type->supports;
			}

			$result[] = $item;
		}

		$this->log_activity( 'list_block_types', $request );

		return $this->success_response(
			array(
				'total'       => count( $result ),
				'block_types' => $result,
			)
		);
	}

	/**
	 * List registered block patterns.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function list_block_patterns( $request ) {
		if ( ! class_exists( 'WP_Block_Patterns_Registry' ) ) {
			return $this->error_response(
				'not_available',
				'Block patterns registry not available.',
				500
			);
		}

		$registry = WP_Block_Patterns_Registry::get_instance();
		$patterns = $registry->get_all_registered();
		$result   = array();

		foreach ( $patterns as $pattern ) {
			$item = array(
				'name'       => $pattern['name'],
				'title'      => ! empty( $pattern['title'] ) ? $pattern['title'] : '',
				'categories' => ! empty( $pattern['categories'] ) ? $pattern['categories'] : array(),
			);

			if ( ! empty( $pattern['description'] ) ) {
				$item['description'] = $pattern['description'];
			}

			if ( ! empty( $pattern['keywords'] ) ) {
				$item['keywords'] = $pattern['keywords'];
			}

			// Include content so AI can use patterns directly.
			if ( ! empty( $pattern['content'] ) ) {
				$item['content'] = $pattern['content'];
			}

			$result[] = $item;
		}

		$this->log_activity( 'list_block_patterns', $request );

		return $this->success_response(
			array(
				'total'    => count( $result ),
				'patterns' => $result,
			)
		);
	}

	/**
	 * Clean parsed blocks for API output.
	 *
	 * Removes empty blocks and normalizes the structure.
	 *
	 * @param array $blocks Parsed blocks.
	 * @return array Cleaned blocks.
	 */
	private function clean_blocks( $blocks ) {
		$cleaned = array();

		foreach ( $blocks as $block ) {
			// Skip empty/whitespace-only blocks (parser artifacts).
			if ( empty( $block['blockName'] ) && empty( trim( $block['innerHTML'] ?? '' ) ) ) {
				continue;
			}

			$item = array(
				'blockName' => $block['blockName'] ?? null,
				'attrs'     => ! empty( $block['attrs'] ) ? $block['attrs'] : (object) array(),
			);

			if ( ! empty( $block['innerHTML'] ) ) {
				$item['innerHTML'] = $block['innerHTML'];
			}

			// Recursively clean inner blocks.
			if ( ! empty( $block['innerBlocks'] ) ) {
				$item['innerBlocks'] = $this->clean_blocks( $block['innerBlocks'] );
			}

			if ( ! empty( $block['innerContent'] ) ) {
				$item['innerContent'] = $block['innerContent'];
			}

			$cleaned[] = $item;
		}

		return $cleaned;
	}
}
