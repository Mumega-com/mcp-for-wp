<?php
/**
 * Pages REST Controller
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pages REST controller.
 */
class Spai_REST_Pages extends Spai_REST_API {

	/**
	 * Pages handler.
	 *
	 * @var Spai_Pages
	 */
	private $pages;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->pages = new Spai_Pages();
	}

	/**
	 * Register routes.
	 */
	public function register_routes() {
		// List pages
		register_rest_route(
			$this->namespace,
			'/pages',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'list_pages' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array_merge(
						$this->get_pagination_args(),
						array(
							'status' => array(
								'description' => __( 'Page status filter.', 'site-pilot-ai' ),
								'type'        => 'string',
								'default'     => 'any',
							),
							'parent' => array(
								'description' => __( 'Parent page ID.', 'site-pilot-ai' ),
								'type'        => 'integer',
							),
						)
					),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_page' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'title'    => array(
							'description' => __( 'Page title.', 'site-pilot-ai' ),
							'type'        => 'string',
							'required'    => true,
						),
						'content'  => array(
							'description' => __( 'Page content.', 'site-pilot-ai' ),
							'type'        => 'string',
							'default'     => '',
						),
						'status'   => array(
							'description' => __( 'Page status.', 'site-pilot-ai' ),
							'type'        => 'string',
							'enum'        => array( 'publish', 'draft', 'pending', 'private' ),
							'default'     => 'draft',
						),
						'parent'   => array(
							'description' => __( 'Parent page ID.', 'site-pilot-ai' ),
							'type'        => 'integer',
						),
						'template' => array(
							'description' => __( 'Page template.', 'site-pilot-ai' ),
							'type'        => 'string',
						),
					),
				),
			)
		);

		// Single page
		register_rest_route(
			$this->namespace,
			'/pages/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_page' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_page' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_page' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'force' => array(
							'description' => __( 'Bypass trash and force deletion.', 'site-pilot-ai' ),
							'type'        => 'boolean',
							'default'     => false,
						),
					),
				),
			)
		);

		// Page templates list
		register_rest_route(
			$this->namespace,
			'/templates/page',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_page_templates' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);
	}

	/**
	 * List pages.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function list_pages( $request ) {
		$this->log_activity( 'list_pages', $request );

		$args = $this->sanitize_query_args( $request->get_params() );

		if ( $request->get_param( 'parent' ) ) {
			$args['post_parent'] = absint( $request->get_param( 'parent' ) );
		}

		$result = $this->pages->list_pages( $args );

		return $this->success_response( $result );
	}

	/**
	 * Get single page.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function get_page( $request ) {
		$this->log_activity( 'get_page', $request );

		$page_id = $request->get_param( 'id' );
		$result = $this->pages->get_page( $page_id );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success_response( $result );
	}

	/**
	 * Create page.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function create_page( $request ) {
		$this->log_activity( 'create_page', $request );

		$data = $request->get_params();
		$result = $this->pages->create_page( $data );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success_response( $result, 201 );
	}

	/**
	 * Update page.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function update_page( $request ) {
		$this->log_activity( 'update_page', $request );

		$page_id = $request->get_param( 'id' );
		$data = $request->get_params();
		unset( $data['id'] );

		$result = $this->pages->update_page( $page_id, $data );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success_response( $result );
	}

	/**
	 * Delete page.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function delete_page( $request ) {
		$this->log_activity( 'delete_page', $request );

		$page_id = absint( $request->get_param( 'id' ) );
		$force   = (bool) $request->get_param( 'force' );

		$result = $this->pages->delete_page( $page_id, $force );

		if ( is_wp_error( $result ) ) {
			return $this->error_response( $result->get_error_code(), $result->get_error_message(), 400 );
		}

		return $this->success_response( array(
			'deleted' => true,
			'id'      => $page_id,
			'trashed' => ! $force,
		) );
	}

	/**
	 * Get available page templates.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function get_page_templates( $request ) {
		$this->log_activity( 'get_page_templates', $request );

		$templates = wp_get_theme()->get_page_templates();

		$formatted = array(
			array(
				'slug' => 'default',
				'name' => __( 'Default Template', 'site-pilot-ai' ),
			),
		);

		foreach ( $templates as $slug => $name ) {
			$formatted[] = array(
				'slug' => $slug,
				'name' => $name,
			);
		}

		return $this->success_response( array(
			'templates' => $formatted,
			'total'     => count( $formatted ),
		) );
	}
}
