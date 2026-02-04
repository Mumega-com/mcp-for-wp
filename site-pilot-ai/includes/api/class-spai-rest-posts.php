<?php
/**
 * Posts REST Controller
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Posts REST controller.
 */
class Spai_REST_Posts extends Spai_REST_API {

	/**
	 * Posts handler.
	 *
	 * @var Spai_Posts
	 */
	private $posts;

	/**
	 * Drafts handler.
	 *
	 * @var Spai_Drafts
	 */
	private $drafts;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->posts = new Spai_Posts();
		$this->drafts = new Spai_Drafts();
	}

	/**
	 * Register routes.
	 */
	public function register_routes() {
		// List posts
		register_rest_route(
			$this->namespace,
			'/posts',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'list_posts' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array_merge(
						$this->get_pagination_args(),
						array(
							'status'   => array(
								'description' => __( 'Post status filter.', 'site-pilot-ai' ),
								'type'        => 'string',
								'default'     => 'publish',
							),
							'category' => array(
								'description' => __( 'Category ID filter.', 'site-pilot-ai' ),
								'type'        => 'integer',
							),
							'search'   => array(
								'description' => __( 'Search term.', 'site-pilot-ai' ),
								'type'        => 'string',
							),
						)
					),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_post' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => $this->get_post_args(),
				),
			)
		);

		// Single post
		register_rest_route(
			$this->namespace,
			'/posts/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_post' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_post' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_post' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'force' => array(
							'description' => __( 'Force permanent deletion.', 'site-pilot-ai' ),
							'type'        => 'boolean',
							'default'     => false,
						),
					),
				),
			)
		);

		// Drafts
		register_rest_route(
			$this->namespace,
			'/drafts',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'list_drafts' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'type' => array(
							'description' => __( 'Post type filter.', 'site-pilot-ai' ),
							'type'        => 'string',
							'enum'        => array( 'post', 'page', 'all' ),
							'default'     => 'all',
						),
					),
				),
			)
		);

		// Delete all drafts
		register_rest_route(
			$this->namespace,
			'/drafts/delete-all',
			array(
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_all_drafts' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'type'  => array(
							'description' => __( 'Post type filter.', 'site-pilot-ai' ),
							'type'        => 'string',
							'enum'        => array( 'post', 'page', 'all' ),
							'default'     => 'all',
						),
						'force' => array(
							'description' => __( 'Permanently delete.', 'site-pilot-ai' ),
							'type'        => 'boolean',
							'default'     => false,
						),
					),
				),
			)
		);
	}

	/**
	 * List posts.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function list_posts( $request ) {
		$this->log_activity( 'list_posts', $request );

		$args = $this->sanitize_query_args( $request->get_params() );
		$result = $this->posts->list_posts( $args );

		return $this->success_response( $result );
	}

	/**
	 * Get single post.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function get_post( $request ) {
		$this->log_activity( 'get_post', $request );

		$post_id = $request->get_param( 'id' );
		$result = $this->posts->get_post( $post_id );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success_response( $result );
	}

	/**
	 * Create post.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function create_post( $request ) {
		$this->log_activity( 'create_post', $request );

		$data = $request->get_params();
		$result = $this->posts->create_post( $data );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success_response( $result, 201 );
	}

	/**
	 * Update post.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function update_post( $request ) {
		$this->log_activity( 'update_post', $request );

		$post_id = $request->get_param( 'id' );
		$data = $request->get_params();
		unset( $data['id'] );

		$result = $this->posts->update_post( $post_id, $data );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success_response( $result );
	}

	/**
	 * Delete post.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function delete_post( $request ) {
		$this->log_activity( 'delete_post', $request );

		$post_id = $request->get_param( 'id' );
		$force = $request->get_param( 'force' );

		$result = $this->posts->delete_post( $post_id, $force );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success_response( $result );
	}

	/**
	 * List drafts.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function list_drafts( $request ) {
		$this->log_activity( 'list_drafts', $request );

		$args = array(
			'type' => $request->get_param( 'type' ),
		);

		$result = $this->drafts->list_drafts( $args );

		return $this->success_response( $result );
	}

	/**
	 * Delete all drafts.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function delete_all_drafts( $request ) {
		$this->log_activity( 'delete_all_drafts', $request );

		$args = array(
			'type'  => $request->get_param( 'type' ),
			'force' => $request->get_param( 'force' ),
		);

		$result = $this->drafts->delete_all_drafts( $args );

		return $this->success_response( $result );
	}
}
