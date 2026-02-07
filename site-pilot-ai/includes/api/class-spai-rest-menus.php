<?php
/**
 * Menus REST Controller
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Menus REST controller.
 *
 * Supports creating a menu, adding page items, and assigning it to a theme location.
 */
class Spai_REST_Menus extends Spai_REST_API {

	/**
	 * Register routes.
	 */
	public function register_routes() {
		// List theme menu locations.
		register_rest_route(
			$this->namespace,
			'/menus/locations',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'list_locations' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		// Create and set up a menu in one call.
		register_rest_route(
			$this->namespace,
			'/menus/setup',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'setup_menu' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'name'      => array(
							'description' => __( 'Menu name.', 'site-pilot-ai' ),
							'type'        => 'string',
							'required'    => true,
						),
						'location'  => array(
							'description' => __( 'Theme menu location key to assign.', 'site-pilot-ai' ),
							'type'        => 'string',
						),
						'page_ids'  => array(
							'description' => __( 'Page IDs to add as menu items.', 'site-pilot-ai' ),
							'type'        => 'array',
							'items'       => array( 'type' => 'integer' ),
							'default'     => array(),
						),
						'overwrite' => array(
							'description' => __( 'If true, creates a new menu even if one with same name exists.', 'site-pilot-ai' ),
							'type'        => 'boolean',
							'default'     => false,
						),
					),
				),
			)
		);
	}

	/**
	 * List theme menu locations.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response Response.
	 */
	public function list_locations( $request ) {
		$this->log_activity( 'list_menu_locations', $request );

		$locations = get_registered_nav_menus();
		$current   = get_nav_menu_locations();

		$out = array();
		foreach ( (array) $locations as $key => $label ) {
			$menu_id = isset( $current[ $key ] ) ? absint( $current[ $key ] ) : 0;
			$menu    = $menu_id ? wp_get_nav_menu_object( $menu_id ) : null;

			$out[] = array(
				'key'         => (string) $key,
				'label'       => (string) $label,
				'assigned'    => $menu_id > 0,
				'menu_id'     => $menu_id,
				'menu_name'   => $menu && isset( $menu->name ) ? (string) $menu->name : null,
				'menu_slug'   => $menu && isset( $menu->slug ) ? (string) $menu->slug : null,
				'menu_count'  => $menu && isset( $menu->count ) ? (int) $menu->count : null,
			);
		}

		return $this->success_response( array(
			'locations' => $out,
		) );
	}

	/**
	 * Create and populate a menu, optionally assigning it to a location.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function setup_menu( $request ) {
		$this->log_activity( 'setup_menu', $request );

		$name      = sanitize_text_field( (string) $request->get_param( 'name' ) );
		$location  = sanitize_key( (string) $request->get_param( 'location' ) );
		$page_ids  = (array) $request->get_param( 'page_ids' );
		$overwrite = (bool) $request->get_param( 'overwrite' );

		if ( '' === $name ) {
			return $this->error_response(
				'missing_name',
				__( 'Menu name is required.', 'site-pilot-ai' ),
				400
			);
		}

		$existing = wp_get_nav_menu_object( $name );
		$menu_id  = $existing && ! $overwrite ? (int) $existing->term_id : 0;

		if ( ! $menu_id ) {
			$menu_id = wp_create_nav_menu( $name );
		}

		if ( is_wp_error( $menu_id ) ) {
			return $this->error_response(
				'menu_create_failed',
				$menu_id->get_error_message(),
				500
			);
		}

		$added = array();
		foreach ( $page_ids as $page_id ) {
			$page_id = absint( $page_id );
			if ( $page_id <= 0 ) {
				continue;
			}

			$page = get_post( $page_id );
			if ( ! $page || 'page' !== $page->post_type ) {
				continue;
			}

			$item_id = wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-object-id' => $page_id,
					'menu-item-object'    => 'page',
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
				)
			);

			if ( ! is_wp_error( $item_id ) ) {
				$added[] = array(
					'item_id' => (int) $item_id,
					'page_id' => (int) $page_id,
					'title'   => (string) $page->post_title,
				);
			}
		}

		$assigned = false;
		if ( '' !== $location ) {
			$registered = get_registered_nav_menus();
			if ( isset( $registered[ $location ] ) ) {
				$locations = get_nav_menu_locations();
				$locations[ $location ] = $menu_id;
				set_theme_mod( 'nav_menu_locations', $locations );
				$assigned = true;
			}
		}

		$menu = wp_get_nav_menu_object( $menu_id );

		return $this->success_response( array(
			'menu'     => array(
				'id'    => (int) $menu_id,
				'name'  => $menu && isset( $menu->name ) ? (string) $menu->name : $name,
				'slug'  => $menu && isset( $menu->slug ) ? (string) $menu->slug : null,
				'count' => $menu && isset( $menu->count ) ? (int) $menu->count : null,
			),
			'added'    => $added,
			'location' => array(
				'key'      => '' !== $location ? $location : null,
				'assigned' => $assigned,
			),
		) );
	}
}

