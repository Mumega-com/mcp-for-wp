<?php
/**
 * Multilanguage REST API Controller
 *
 * @package SitePilotAI_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multilanguage REST controller class.
 */
class Spai_REST_Multilang {

	use Spai_API_Auth;

	/**
	 * Multilang handler instance.
	 *
	 * @var Spai_Multilang
	 */
	private $handler;

	/**
	 * Constructor.
	 *
	 * @param Spai_Multilang $handler Handler instance.
	 */
	public function __construct( $handler ) {
		$this->handler = $handler;
	}

	/**
	 * Register REST routes.
	 */
	public function register_routes() {
		$namespace = 'site-pilot-ai/v1';

		// Languages info.
		register_rest_route(
			$namespace,
			'/languages',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_languages' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// Set current language.
		register_rest_route(
			$namespace,
			'/languages/current',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'set_current_language' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'language' => array(
						'required'    => true,
						'type'        => 'string',
						'description' => __( 'Language code to set.', 'site-pilot-ai-pro' ),
					),
				),
			)
		);

		// Post translations.
		register_rest_route(
			$namespace,
			'/posts/(?P<id>\d+)/translations',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_post_translations' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_post_translation' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => $this->get_translation_args(),
				),
			)
		);

		// Page translations.
		register_rest_route(
			$namespace,
			'/pages/(?P<id>\d+)/translations',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_post_translations' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_post_translation' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => $this->get_translation_args(),
				),
			)
		);
	}

	/**
	 * Get translation creation arguments.
	 *
	 * @return array
	 */
	private function get_translation_args() {
		return array(
			'language' => array(
				'required'    => true,
				'type'        => 'string',
				'description' => __( 'Target language code.', 'site-pilot-ai-pro' ),
			),
			'title'    => array(
				'type'        => 'string',
				'description' => __( 'Translated title.', 'site-pilot-ai-pro' ),
			),
			'content'  => array(
				'type'        => 'string',
				'description' => __( 'Translated content.', 'site-pilot-ai-pro' ),
			),
			'excerpt'  => array(
				'type'        => 'string',
				'description' => __( 'Translated excerpt.', 'site-pilot-ai-pro' ),
			),
			'status'   => array(
				'type'        => 'string',
				'default'     => 'draft',
				'enum'        => array( 'draft', 'publish', 'pending', 'private' ),
				'description' => __( 'Post status for translation.', 'site-pilot-ai-pro' ),
			),
		);
	}

	/**
	 * Get languages information.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_languages() {
		if ( ! $this->handler->is_active() ) {
			return rest_ensure_response( array(
				'active'    => false,
				'plugin'    => null,
				'languages' => array(),
				'message'   => __( 'No multilingual plugin detected.', 'site-pilot-ai-pro' ),
			) );
		}

		return rest_ensure_response( $this->handler->get_language_info() );
	}

	/**
	 * Set current language for subsequent API calls.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function set_current_language( $request ) {
		if ( ! $this->handler->is_active() ) {
			return new WP_Error(
				'no_multilang',
				__( 'No multilingual plugin is active.', 'site-pilot-ai-pro' ),
				array( 'status' => 400 )
			);
		}

		$language = $request->get_param( 'language' );

		// Validate language code.
		$languages = wp_list_pluck( $this->handler->get_languages(), 'code' );
		if ( ! in_array( $language, $languages, true ) ) {
			return new WP_Error(
				'invalid_language',
				__( 'Invalid language code.', 'site-pilot-ai-pro' ),
				array( 'status' => 400 )
			);
		}

		$this->handler->set_language_filter( $language );

		return rest_ensure_response( array(
			'success'          => true,
			'current_language' => $language,
			'message'          => sprintf( __( 'Language set to %s.', 'site-pilot-ai-pro' ), $language ),
		) );
	}

	/**
	 * Get translations for a post.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_post_translations( $request ) {
		$post_id = $request->get_param( 'id' );

		// Verify post exists.
		$post = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error(
				'not_found',
				__( 'Post not found.', 'site-pilot-ai-pro' ),
				array( 'status' => 404 )
			);
		}

		if ( ! $this->handler->is_active() ) {
			return rest_ensure_response( array(
				'post_id'      => $post_id,
				'active'       => false,
				'plugin'       => null,
				'translations' => array(),
				'message'      => __( 'No multilingual plugin detected.', 'site-pilot-ai-pro' ),
			) );
		}

		$translations = $this->handler->get_detailed_translations( $post_id );
		$post_language = $this->handler->get_post_language( $post_id );

		return rest_ensure_response( array(
			'post_id'       => $post_id,
			'post_type'     => $post->post_type,
			'post_language' => $post_language,
			'plugin'        => $this->handler->get_plugin(),
			'translations'  => $translations,
			'missing'       => $this->get_missing_translations( $post_id, $translations ),
		) );
	}

	/**
	 * Create a translation for a post.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_post_translation( $request ) {
		$post_id  = $request->get_param( 'id' );
		$language = $request->get_param( 'language' );

		// Verify post exists.
		$post = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error(
				'not_found',
				__( 'Post not found.', 'site-pilot-ai-pro' ),
				array( 'status' => 404 )
			);
		}

		if ( ! $this->handler->is_active() ) {
			return new WP_Error(
				'no_multilang',
				__( 'No multilingual plugin is active.', 'site-pilot-ai-pro' ),
				array( 'status' => 400 )
			);
		}

		$data = array(
			'title'   => $request->get_param( 'title' ),
			'content' => $request->get_param( 'content' ),
			'excerpt' => $request->get_param( 'excerpt' ),
			'status'  => $request->get_param( 'status' ),
		);

		$result = $this->handler->create_post_translation( $post_id, $language, $data );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$new_post = get_post( $result );

		return rest_ensure_response( array(
			'success'           => true,
			'original_post_id'  => $post_id,
			'translation_id'    => $result,
			'language'          => $language,
			'title'             => $new_post->post_title,
			'status'            => $new_post->post_status,
			'permalink'         => get_permalink( $result ),
			'edit_link'         => get_edit_post_link( $result, 'raw' ),
		) );
	}

	/**
	 * Get languages that are missing translations.
	 *
	 * @param int   $post_id      Post ID.
	 * @param array $translations Current translations.
	 * @return array
	 */
	private function get_missing_translations( $post_id, $translations ) {
		$all_languages = $this->handler->get_languages();
		$translated_codes = array_keys( $translations );
		$missing = array();

		foreach ( $all_languages as $lang ) {
			if ( ! in_array( $lang['code'], $translated_codes, true ) ) {
				$missing[] = array(
					'code' => $lang['code'],
					'name' => $lang['name'],
				);
			}
		}

		return $missing;
	}
}
