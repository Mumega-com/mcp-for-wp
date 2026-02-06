<?php
/**
 * WooCommerce REST API Controller
 *
 * @package SitePilotAI_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce REST controller class.
 */
class Spai_REST_WooCommerce {

	use Spai_API_Auth;

	/**
	 * WooCommerce handler instance.
	 *
	 * @var Spai_WooCommerce
	 */
	private $handler;

	/**
	 * Constructor.
	 *
	 * @param Spai_WooCommerce $handler Handler instance.
	 */
	public function __construct( $handler ) {
		$this->handler = $handler;
	}

	/**
	 * Register REST routes.
	 */
	public function register_routes() {
		$namespace = 'site-pilot-ai/v1';

		// Status.
		register_rest_route(
			$namespace,
			'/woocommerce/status',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_status' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// Products.
		register_rest_route(
			$namespace,
			'/woocommerce/products',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_products' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => $this->get_products_args(),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_product' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => $this->get_product_create_args(),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/woocommerce/products/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_product' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_product' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_product' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'force' => array(
							'type'        => 'boolean',
							'default'     => false,
							'description' => __( 'Permanently delete the product.', 'site-pilot-ai-pro' ),
						),
					),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/woocommerce/products/categories',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_product_categories' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			$namespace,
			'/woocommerce/products/tags',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_product_tags' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// Orders.
		register_rest_route(
			$namespace,
			'/woocommerce/orders',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_orders' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => $this->get_orders_args(),
			)
		);

		register_rest_route(
			$namespace,
			'/woocommerce/orders/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_order' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_order' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'status' => array(
							'type'        => 'string',
							'description' => __( 'Order status.', 'site-pilot-ai-pro' ),
						),
						'note' => array(
							'type'        => 'string',
							'description' => __( 'Order note to add.', 'site-pilot-ai-pro' ),
						),
						'note_customer' => array(
							'type'        => 'boolean',
							'default'     => false,
							'description' => __( 'Send note to customer.', 'site-pilot-ai-pro' ),
						),
					),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/woocommerce/orders/statuses',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_order_statuses' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// Customers.
		register_rest_route(
			$namespace,
			'/woocommerce/customers',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_customers' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => $this->get_customers_args(),
			)
		);

		register_rest_route(
			$namespace,
			'/woocommerce/customers/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_customer' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// Analytics.
		register_rest_route(
			$namespace,
			'/woocommerce/analytics',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_analytics' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => $this->get_analytics_args(),
			)
		);
	}

	// =========================================================================
	// Route Arguments
	// =========================================================================

	/**
	 * Get products query arguments.
	 *
	 * @return array
	 */
	private function get_products_args() {
		return array(
			'per_page'     => array(
				'type'        => 'integer',
				'default'     => 50,
				'minimum'     => 1,
				'maximum'     => 100,
				'description' => __( 'Items per page.', 'site-pilot-ai-pro' ),
			),
			'page'         => array(
				'type'        => 'integer',
				'default'     => 1,
				'minimum'     => 1,
				'description' => __( 'Page number.', 'site-pilot-ai-pro' ),
			),
			'status'       => array(
				'type'        => 'string',
				'default'     => 'publish',
				'enum'        => array( 'publish', 'draft', 'pending', 'private', 'any' ),
				'description' => __( 'Product status.', 'site-pilot-ai-pro' ),
			),
			'type'         => array(
				'type'        => 'string',
				'enum'        => array( 'simple', 'variable', 'grouped', 'external' ),
				'description' => __( 'Product type.', 'site-pilot-ai-pro' ),
			),
			'category'     => array(
				'type'        => 'string',
				'description' => __( 'Category slug.', 'site-pilot-ai-pro' ),
			),
			'tag'          => array(
				'type'        => 'string',
				'description' => __( 'Tag slug.', 'site-pilot-ai-pro' ),
			),
			'search'       => array(
				'type'        => 'string',
				'description' => __( 'Search term.', 'site-pilot-ai-pro' ),
			),
			'sku'          => array(
				'type'        => 'string',
				'description' => __( 'Exact SKU match.', 'site-pilot-ai-pro' ),
			),
			'stock_status' => array(
				'type'        => 'string',
				'enum'        => array( 'instock', 'outofstock', 'onbackorder' ),
				'description' => __( 'Stock status.', 'site-pilot-ai-pro' ),
			),
			'orderby'      => array(
				'type'        => 'string',
				'default'     => 'date',
				'enum'        => array( 'date', 'title', 'price', 'popularity', 'rating' ),
				'description' => __( 'Order by field.', 'site-pilot-ai-pro' ),
			),
			'order'        => array(
				'type'        => 'string',
				'default'     => 'DESC',
				'enum'        => array( 'ASC', 'DESC' ),
				'description' => __( 'Sort order.', 'site-pilot-ai-pro' ),
			),
		);
	}

	/**
	 * Get product create arguments.
	 *
	 * @return array
	 */
	private function get_product_create_args() {
		return array(
			'name'              => array(
				'type'        => 'string',
				'required'    => true,
				'description' => __( 'Product name.', 'site-pilot-ai-pro' ),
			),
			'type'              => array(
				'type'        => 'string',
				'default'     => 'simple',
				'enum'        => array( 'simple', 'variable', 'grouped', 'external' ),
				'description' => __( 'Product type.', 'site-pilot-ai-pro' ),
			),
			'status'            => array(
				'type'        => 'string',
				'default'     => 'publish',
				'enum'        => array( 'publish', 'draft', 'pending', 'private' ),
				'description' => __( 'Product status.', 'site-pilot-ai-pro' ),
			),
			'description'       => array(
				'type'        => 'string',
				'description' => __( 'Product description.', 'site-pilot-ai-pro' ),
			),
			'short_description' => array(
				'type'        => 'string',
				'description' => __( 'Short description.', 'site-pilot-ai-pro' ),
			),
			'sku'               => array(
				'type'        => 'string',
				'description' => __( 'Product SKU.', 'site-pilot-ai-pro' ),
			),
			'regular_price'     => array(
				'type'        => 'string',
				'description' => __( 'Regular price.', 'site-pilot-ai-pro' ),
			),
			'sale_price'        => array(
				'type'        => 'string',
				'description' => __( 'Sale price.', 'site-pilot-ai-pro' ),
			),
			'manage_stock'      => array(
				'type'        => 'boolean',
				'default'     => false,
				'description' => __( 'Manage stock.', 'site-pilot-ai-pro' ),
			),
			'stock_quantity'    => array(
				'type'        => 'integer',
				'description' => __( 'Stock quantity.', 'site-pilot-ai-pro' ),
			),
			'stock_status'      => array(
				'type'        => 'string',
				'enum'        => array( 'instock', 'outofstock', 'onbackorder' ),
				'description' => __( 'Stock status.', 'site-pilot-ai-pro' ),
			),
			'categories'        => array(
				'type'        => 'array',
				'items'       => array( 'type' => 'string' ),
				'description' => __( 'Category names or IDs.', 'site-pilot-ai-pro' ),
			),
			'tags'              => array(
				'type'        => 'array',
				'items'       => array( 'type' => 'string' ),
				'description' => __( 'Tag names or IDs.', 'site-pilot-ai-pro' ),
			),
			'image_id'          => array(
				'type'        => 'integer',
				'description' => __( 'Main image attachment ID.', 'site-pilot-ai-pro' ),
			),
			'gallery_image_ids' => array(
				'type'        => 'array',
				'items'       => array( 'type' => 'integer' ),
				'description' => __( 'Gallery image attachment IDs.', 'site-pilot-ai-pro' ),
			),
			'virtual'           => array(
				'type'        => 'boolean',
				'default'     => false,
				'description' => __( 'Virtual product.', 'site-pilot-ai-pro' ),
			),
			'downloadable'      => array(
				'type'        => 'boolean',
				'default'     => false,
				'description' => __( 'Downloadable product.', 'site-pilot-ai-pro' ),
			),
		);
	}

	/**
	 * Get orders query arguments.
	 *
	 * @return array
	 */
	private function get_orders_args() {
		return array(
			'per_page' => array(
				'type'        => 'integer',
				'default'     => 50,
				'minimum'     => 1,
				'maximum'     => 100,
				'description' => __( 'Items per page.', 'site-pilot-ai-pro' ),
			),
			'page'     => array(
				'type'        => 'integer',
				'default'     => 1,
				'minimum'     => 1,
				'description' => __( 'Page number.', 'site-pilot-ai-pro' ),
			),
			'status'   => array(
				'type'        => 'string',
				'default'     => 'any',
				'description' => __( 'Order status.', 'site-pilot-ai-pro' ),
			),
			'customer' => array(
				'type'        => 'integer',
				'description' => __( 'Customer ID.', 'site-pilot-ai-pro' ),
			),
			'after'    => array(
				'type'        => 'string',
				'format'      => 'date-time',
				'description' => __( 'Orders after date (ISO 8601).', 'site-pilot-ai-pro' ),
			),
			'before'   => array(
				'type'        => 'string',
				'format'      => 'date-time',
				'description' => __( 'Orders before date (ISO 8601).', 'site-pilot-ai-pro' ),
			),
		);
	}

	/**
	 * Get customers query arguments.
	 *
	 * @return array
	 */
	private function get_customers_args() {
		return array(
			'per_page' => array(
				'type'        => 'integer',
				'default'     => 50,
				'minimum'     => 1,
				'maximum'     => 100,
				'description' => __( 'Items per page.', 'site-pilot-ai-pro' ),
			),
			'page'     => array(
				'type'        => 'integer',
				'default'     => 1,
				'minimum'     => 1,
				'description' => __( 'Page number.', 'site-pilot-ai-pro' ),
			),
			'search'   => array(
				'type'        => 'string',
				'description' => __( 'Search term.', 'site-pilot-ai-pro' ),
			),
			'orderby'  => array(
				'type'        => 'string',
				'default'     => 'registered',
				'enum'        => array( 'registered', 'display_name', 'user_login', 'user_email' ),
				'description' => __( 'Order by field.', 'site-pilot-ai-pro' ),
			),
			'order'    => array(
				'type'        => 'string',
				'default'     => 'DESC',
				'enum'        => array( 'ASC', 'DESC' ),
				'description' => __( 'Sort order.', 'site-pilot-ai-pro' ),
			),
		);
	}

	/**
	 * Get analytics query arguments.
	 *
	 * @return array
	 */
	private function get_analytics_args() {
		return array(
			'period'   => array(
				'type'        => 'string',
				'default'     => 'month',
				'enum'        => array( 'day', 'week', 'month', 'year' ),
				'description' => __( 'Time period.', 'site-pilot-ai-pro' ),
			),
			'date_min' => array(
				'type'        => 'string',
				'format'      => 'date-time',
				'description' => __( 'Start date (ISO 8601).', 'site-pilot-ai-pro' ),
			),
			'date_max' => array(
				'type'        => 'string',
				'format'      => 'date-time',
				'description' => __( 'End date (ISO 8601).', 'site-pilot-ai-pro' ),
			),
		);
	}

	// =========================================================================
	// Route Callbacks
	// =========================================================================

	/**
	 * Get WooCommerce status.
	 *
	 * @return WP_REST_Response
	 */
	public function get_status() {
		return rest_ensure_response( $this->handler->get_status() );
	}

	/**
	 * Get products.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_products( $request ) {
		$args = array(
			'per_page'     => $request->get_param( 'per_page' ),
			'page'         => $request->get_param( 'page' ),
			'status'       => $request->get_param( 'status' ),
			'type'         => $request->get_param( 'type' ),
			'category'     => $request->get_param( 'category' ),
			'tag'          => $request->get_param( 'tag' ),
			'search'       => $request->get_param( 'search' ),
			'sku'          => $request->get_param( 'sku' ),
			'stock_status' => $request->get_param( 'stock_status' ),
			'orderby'      => $request->get_param( 'orderby' ),
			'order'        => $request->get_param( 'order' ),
		);

		return rest_ensure_response( $this->handler->get_products( $args ) );
	}

	/**
	 * Get single product.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_product( $request ) {
		$result = $this->handler->get_product( $request->get_param( 'id' ) );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( $result );
	}

	/**
	 * Create product.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_product( $request ) {
		$data = $request->get_json_params();

		$result = $this->handler->create_product( $data );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( $result );
	}

	/**
	 * Update product.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_product( $request ) {
		$id   = $request->get_param( 'id' );
		$data = $request->get_json_params();

		$result = $this->handler->update_product( $id, $data );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( $result );
	}

	/**
	 * Delete product.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_product( $request ) {
		$id    = $request->get_param( 'id' );
		$force = $request->get_param( 'force' );

		$result = $this->handler->delete_product( $id, $force );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( array(
			'deleted' => true,
			'id'      => $id,
		) );
	}

	/**
	 * Get product categories.
	 *
	 * @return WP_REST_Response
	 */
	public function get_product_categories() {
		return rest_ensure_response( $this->handler->get_product_categories() );
	}

	/**
	 * Get product tags.
	 *
	 * @return WP_REST_Response
	 */
	public function get_product_tags() {
		return rest_ensure_response( $this->handler->get_product_tags() );
	}

	/**
	 * Get orders.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_orders( $request ) {
		$args = array(
			'per_page' => $request->get_param( 'per_page' ),
			'page'     => $request->get_param( 'page' ),
			'status'   => $request->get_param( 'status' ),
			'customer' => $request->get_param( 'customer' ),
			'after'    => $request->get_param( 'after' ),
			'before'   => $request->get_param( 'before' ),
		);

		return rest_ensure_response( $this->handler->get_orders( $args ) );
	}

	/**
	 * Get single order.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_order( $request ) {
		$result = $this->handler->get_order( $request->get_param( 'id' ) );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( $result );
	}

	/**
	 * Update order.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_order( $request ) {
		$id   = $request->get_param( 'id' );
		$data = array(
			'status'        => $request->get_param( 'status' ),
			'note'          => $request->get_param( 'note' ),
			'note_customer' => $request->get_param( 'note_customer' ),
		);

		$result = $this->handler->update_order( $id, $data );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( $result );
	}

	/**
	 * Get order statuses.
	 *
	 * @return WP_REST_Response
	 */
	public function get_order_statuses() {
		return rest_ensure_response( $this->handler->get_order_statuses() );
	}

	/**
	 * Get customers.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_customers( $request ) {
		$args = array(
			'per_page' => $request->get_param( 'per_page' ),
			'page'     => $request->get_param( 'page' ),
			'search'   => $request->get_param( 'search' ),
			'orderby'  => $request->get_param( 'orderby' ),
			'order'    => $request->get_param( 'order' ),
		);

		return rest_ensure_response( $this->handler->get_customers( $args ) );
	}

	/**
	 * Get single customer.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_customer( $request ) {
		$result = $this->handler->get_customer( $request->get_param( 'id' ) );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( $result );
	}

	/**
	 * Get analytics.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_analytics( $request ) {
		$args = array(
			'period'   => $request->get_param( 'period' ),
			'date_min' => $request->get_param( 'date_min' ),
			'date_max' => $request->get_param( 'date_max' ),
		);

		$result = $this->handler->get_analytics( $args );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( $result );
	}
}
