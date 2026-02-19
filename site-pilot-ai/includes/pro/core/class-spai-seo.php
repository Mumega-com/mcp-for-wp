<?php
/**
 * SEO Handler
 *
 * @package SitePilotAI_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SEO functionality.
 *
 * Provides unified interface for Yoast SEO, RankMath, AIOSEO, and SEOPress.
 */
class Spai_SEO {

	/**
	 * Check if Yoast SEO is active.
	 *
	 * @return bool
	 */
	public function is_yoast_active() {
		return defined( 'WPSEO_VERSION' );
	}

	/**
	 * Check if RankMath is active.
	 *
	 * @return bool
	 */
	public function is_rankmath_active() {
		return class_exists( 'RankMath' );
	}

	/**
	 * Check if AIOSEO is active.
	 *
	 * @return bool
	 */
	public function is_aioseo_active() {
		return defined( 'AIOSEO_VERSION' );
	}

	/**
	 * Check if SEOPress is active.
	 *
	 * @return bool
	 */
	public function is_seopress_active() {
		return defined( 'SEOPRESS_VERSION' );
	}

	/**
	 * Get active SEO plugin.
	 *
	 * @return string|null Plugin identifier or null.
	 */
	public function get_active_plugin() {
		if ( $this->is_yoast_active() ) {
			return 'yoast';
		}
		if ( $this->is_rankmath_active() ) {
			return 'rankmath';
		}
		if ( $this->is_aioseo_active() ) {
			return 'aioseo';
		}
		if ( $this->is_seopress_active() ) {
			return 'seopress';
		}
		return null;
	}

	/**
	 * Get SEO status and detected plugins.
	 *
	 * @return array Status information.
	 */
	public function get_status() {
		$active = $this->get_active_plugin();

		return array(
			'active_plugin' => $active,
			'plugins'       => array(
				'yoast'    => array(
					'active'  => $this->is_yoast_active(),
					'version' => $this->is_yoast_active() ? WPSEO_VERSION : null,
				),
				'rankmath' => array(
					'active'  => $this->is_rankmath_active(),
					'version' => $this->is_rankmath_active() && defined( 'RANK_MATH_VERSION' ) ? RANK_MATH_VERSION : null,
				),
				'aioseo'   => array(
					'active'  => $this->is_aioseo_active(),
					'version' => $this->is_aioseo_active() ? AIOSEO_VERSION : null,
				),
				'seopress' => array(
					'active'  => $this->is_seopress_active(),
					'version' => $this->is_seopress_active() ? SEOPRESS_VERSION : null,
				),
			),
		);
	}

	/**
	 * Get SEO data for a post.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $plugin  Optional. Force specific plugin.
	 * @return array|WP_Error SEO data or error.
	 */
	public function get_post_seo( $post_id, $plugin = null ) {
		$post = get_post( $post_id );

		if ( ! $post ) {
			return new WP_Error( 'not_found', __( 'Post not found.', 'site-pilot-ai' ) );
		}

		$plugin = $plugin ?: $this->get_active_plugin();

		if ( ! $plugin ) {
			return new WP_Error( 'no_seo_plugin', __( 'No SEO plugin is active.', 'site-pilot-ai' ) );
		}

		$method = 'get_' . $plugin . '_data';
		if ( method_exists( $this, $method ) ) {
			return $this->$method( $post_id );
		}

		return new WP_Error( 'unsupported_plugin', __( 'SEO plugin not supported.', 'site-pilot-ai' ) );
	}

	/**
	 * Update SEO data for a post.
	 *
	 * @param int    $post_id Post ID.
	 * @param array  $data    SEO data.
	 * @param string $plugin  Optional. Force specific plugin.
	 * @return array|WP_Error Updated data or error.
	 */
	public function update_post_seo( $post_id, $data, $plugin = null ) {
		$post = get_post( $post_id );

		if ( ! $post ) {
			return new WP_Error( 'not_found', __( 'Post not found.', 'site-pilot-ai' ) );
		}

		$plugin = $plugin ?: $this->get_active_plugin();

		if ( ! $plugin ) {
			return new WP_Error( 'no_seo_plugin', __( 'No SEO plugin is active.', 'site-pilot-ai' ) );
		}

		$method = 'set_' . $plugin . '_data';
		if ( method_exists( $this, $method ) ) {
			return $this->$method( $post_id, $data );
		}

		return new WP_Error( 'unsupported_plugin', __( 'SEO plugin not supported.', 'site-pilot-ai' ) );
	}

	/**
	 * Get Yoast SEO data.
	 *
	 * @param int $post_id Post ID.
	 * @return array SEO data.
	 */
	private function get_yoast_data( $post_id ) {
		return array(
			'plugin'          => 'yoast',
			'title'           => get_post_meta( $post_id, '_yoast_wpseo_title', true ),
			'description'     => get_post_meta( $post_id, '_yoast_wpseo_metadesc', true ),
			'focus_keyword'   => get_post_meta( $post_id, '_yoast_wpseo_focuskw', true ),
			'canonical'       => get_post_meta( $post_id, '_yoast_wpseo_canonical', true ),
			'og_title'        => get_post_meta( $post_id, '_yoast_wpseo_opengraph-title', true ),
			'og_description'  => get_post_meta( $post_id, '_yoast_wpseo_opengraph-description', true ),
			'og_image'        => get_post_meta( $post_id, '_yoast_wpseo_opengraph-image', true ),
			'twitter_title'   => get_post_meta( $post_id, '_yoast_wpseo_twitter-title', true ),
			'twitter_description' => get_post_meta( $post_id, '_yoast_wpseo_twitter-description', true ),
			'twitter_image'   => get_post_meta( $post_id, '_yoast_wpseo_twitter-image', true ),
			'robots_index'    => get_post_meta( $post_id, '_yoast_wpseo_meta-robots-noindex', true ),
			'robots_follow'   => get_post_meta( $post_id, '_yoast_wpseo_meta-robots-nofollow', true ),
			'schema_type'     => get_post_meta( $post_id, '_yoast_wpseo_schema_page_type', true ),
			'cornerstone'     => get_post_meta( $post_id, '_yoast_wpseo_is_cornerstone', true ),
		);
	}

	/**
	 * Set Yoast SEO data.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $data    SEO data.
	 * @return array Updated data.
	 */
	private function set_yoast_data( $post_id, $data ) {
		$meta_map = array(
			'title'           => '_yoast_wpseo_title',
			'description'     => '_yoast_wpseo_metadesc',
			'focus_keyword'   => '_yoast_wpseo_focuskw',
			'canonical'       => '_yoast_wpseo_canonical',
			'og_title'        => '_yoast_wpseo_opengraph-title',
			'og_description'  => '_yoast_wpseo_opengraph-description',
			'og_image'        => '_yoast_wpseo_opengraph-image',
			'twitter_title'   => '_yoast_wpseo_twitter-title',
			'twitter_description' => '_yoast_wpseo_twitter-description',
			'twitter_image'   => '_yoast_wpseo_twitter-image',
			'robots_index'    => '_yoast_wpseo_meta-robots-noindex',
			'robots_follow'   => '_yoast_wpseo_meta-robots-nofollow',
			'schema_type'     => '_yoast_wpseo_schema_page_type',
			'cornerstone'     => '_yoast_wpseo_is_cornerstone',
		);

		foreach ( $data as $key => $value ) {
			if ( isset( $meta_map[ $key ] ) ) {
				if ( empty( $value ) ) {
					delete_post_meta( $post_id, $meta_map[ $key ] );
				} else {
					update_post_meta( $post_id, $meta_map[ $key ], sanitize_text_field( $value ) );
				}
			}
		}

		return $this->get_yoast_data( $post_id );
	}

	/**
	 * Get RankMath SEO data.
	 *
	 * @param int $post_id Post ID.
	 * @return array SEO data.
	 */
	private function get_rankmath_data( $post_id ) {
		return array(
			'plugin'          => 'rankmath',
			'title'           => get_post_meta( $post_id, 'rank_math_title', true ),
			'description'     => get_post_meta( $post_id, 'rank_math_description', true ),
			'focus_keyword'   => get_post_meta( $post_id, 'rank_math_focus_keyword', true ),
			'canonical'       => get_post_meta( $post_id, 'rank_math_canonical_url', true ),
			'og_title'        => get_post_meta( $post_id, 'rank_math_facebook_title', true ),
			'og_description'  => get_post_meta( $post_id, 'rank_math_facebook_description', true ),
			'og_image'        => get_post_meta( $post_id, 'rank_math_facebook_image', true ),
			'twitter_title'   => get_post_meta( $post_id, 'rank_math_twitter_title', true ),
			'twitter_description' => get_post_meta( $post_id, 'rank_math_twitter_description', true ),
			'twitter_image'   => get_post_meta( $post_id, 'rank_math_twitter_image', true ),
			'robots'          => get_post_meta( $post_id, 'rank_math_robots', true ),
			'schema_type'     => get_post_meta( $post_id, 'rank_math_rich_snippet', true ),
			'pillar_content'  => get_post_meta( $post_id, 'rank_math_pillar_content', true ),
			'seo_score'       => get_post_meta( $post_id, 'rank_math_seo_score', true ),
		);
	}

	/**
	 * Set RankMath SEO data.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $data    SEO data.
	 * @return array Updated data.
	 */
	private function set_rankmath_data( $post_id, $data ) {
		$meta_map = array(
			'title'           => 'rank_math_title',
			'description'     => 'rank_math_description',
			'focus_keyword'   => 'rank_math_focus_keyword',
			'canonical'       => 'rank_math_canonical_url',
			'og_title'        => 'rank_math_facebook_title',
			'og_description'  => 'rank_math_facebook_description',
			'og_image'        => 'rank_math_facebook_image',
			'twitter_title'   => 'rank_math_twitter_title',
			'twitter_description' => 'rank_math_twitter_description',
			'twitter_image'   => 'rank_math_twitter_image',
			'schema_type'     => 'rank_math_rich_snippet',
			'pillar_content'  => 'rank_math_pillar_content',
		);

		// Handle robots separately (array).
		if ( isset( $data['robots'] ) ) {
			$robots = is_array( $data['robots'] ) ? $data['robots'] : array( $data['robots'] );
			update_post_meta( $post_id, 'rank_math_robots', $robots );
		}

		foreach ( $data as $key => $value ) {
			if ( isset( $meta_map[ $key ] ) ) {
				if ( empty( $value ) ) {
					delete_post_meta( $post_id, $meta_map[ $key ] );
				} else {
					update_post_meta( $post_id, $meta_map[ $key ], sanitize_text_field( $value ) );
				}
			}
		}

		return $this->get_rankmath_data( $post_id );
	}

	/**
	 * Get AIOSEO data.
	 *
	 * @param int $post_id Post ID.
	 * @return array SEO data.
	 */
	private function get_aioseo_data( $post_id ) {
		global $wpdb;

		// AIOSEO stores data in a separate table.
		$table = $wpdb->prefix . 'aioseo_posts';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from $wpdb->prefix.
		$aioseo_data = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $table WHERE post_id = %d",
				$post_id
			),
			ARRAY_A
		);

		if ( ! $aioseo_data ) {
			return array(
				'plugin'          => 'aioseo',
				'title'           => '',
				'description'     => '',
				'focus_keyword'   => '',
				'canonical'       => '',
				'og_title'        => '',
				'og_description'  => '',
				'og_image'        => '',
				'twitter_title'   => '',
				'twitter_description' => '',
				'twitter_image'   => '',
			);
		}

		return array(
			'plugin'          => 'aioseo',
			'title'           => $aioseo_data['title'] ?? '',
			'description'     => $aioseo_data['description'] ?? '',
			'focus_keyword'   => $aioseo_data['keyphrases'] ?? '',
			'canonical'       => $aioseo_data['canonical_url'] ?? '',
			'og_title'        => $aioseo_data['og_title'] ?? '',
			'og_description'  => $aioseo_data['og_description'] ?? '',
			'og_image'        => $aioseo_data['og_image_custom_url'] ?? '',
			'twitter_title'   => $aioseo_data['twitter_title'] ?? '',
			'twitter_description' => $aioseo_data['twitter_description'] ?? '',
			'twitter_image'   => $aioseo_data['twitter_image_custom_url'] ?? '',
			'robots_noindex'  => $aioseo_data['robots_noindex'] ?? false,
			'robots_nofollow' => $aioseo_data['robots_nofollow'] ?? false,
			'seo_score'       => $aioseo_data['seo_score'] ?? 0,
		);
	}

	/**
	 * Set AIOSEO data.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $data    SEO data.
	 * @return array Updated data.
	 */
	private function set_aioseo_data( $post_id, $data ) {
		global $wpdb;

		$table = $wpdb->prefix . 'aioseo_posts';

		// Check if row exists.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from $wpdb->prefix.
		$exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $table WHERE post_id = %d",
				$post_id
			)
		);

		$db_data = array(
			'post_id' => $post_id,
		);

		$field_map = array(
			'title'           => 'title',
			'description'     => 'description',
			'focus_keyword'   => 'keyphrases',
			'canonical'       => 'canonical_url',
			'og_title'        => 'og_title',
			'og_description'  => 'og_description',
			'og_image'        => 'og_image_custom_url',
			'twitter_title'   => 'twitter_title',
			'twitter_description' => 'twitter_description',
			'twitter_image'   => 'twitter_image_custom_url',
			'robots_noindex'  => 'robots_noindex',
			'robots_nofollow' => 'robots_nofollow',
		);

		foreach ( $data as $key => $value ) {
			if ( isset( $field_map[ $key ] ) ) {
				$db_data[ $field_map[ $key ] ] = sanitize_text_field( $value );
			}
		}

		if ( $exists ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->update( $table, $db_data, array( 'post_id' => $post_id ) );
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->insert( $table, $db_data );
		}

		return $this->get_aioseo_data( $post_id );
	}

	/**
	 * Get SEOPress data.
	 *
	 * @param int $post_id Post ID.
	 * @return array SEO data.
	 */
	private function get_seopress_data( $post_id ) {
		return array(
			'plugin'          => 'seopress',
			'title'           => get_post_meta( $post_id, '_seopress_titles_title', true ),
			'description'     => get_post_meta( $post_id, '_seopress_titles_desc', true ),
			'focus_keyword'   => get_post_meta( $post_id, '_seopress_analysis_target_kw', true ),
			'canonical'       => get_post_meta( $post_id, '_seopress_robots_canonical', true ),
			'og_title'        => get_post_meta( $post_id, '_seopress_social_fb_title', true ),
			'og_description'  => get_post_meta( $post_id, '_seopress_social_fb_desc', true ),
			'og_image'        => get_post_meta( $post_id, '_seopress_social_fb_img', true ),
			'twitter_title'   => get_post_meta( $post_id, '_seopress_social_twitter_title', true ),
			'twitter_description' => get_post_meta( $post_id, '_seopress_social_twitter_desc', true ),
			'twitter_image'   => get_post_meta( $post_id, '_seopress_social_twitter_img', true ),
			'robots_noindex'  => get_post_meta( $post_id, '_seopress_robots_index', true ),
			'robots_nofollow' => get_post_meta( $post_id, '_seopress_robots_follow', true ),
			'primary_category' => get_post_meta( $post_id, '_seopress_robots_primary_cat', true ),
		);
	}

	/**
	 * Set SEOPress data.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $data    SEO data.
	 * @return array Updated data.
	 */
	private function set_seopress_data( $post_id, $data ) {
		$meta_map = array(
			'title'           => '_seopress_titles_title',
			'description'     => '_seopress_titles_desc',
			'focus_keyword'   => '_seopress_analysis_target_kw',
			'canonical'       => '_seopress_robots_canonical',
			'og_title'        => '_seopress_social_fb_title',
			'og_description'  => '_seopress_social_fb_desc',
			'og_image'        => '_seopress_social_fb_img',
			'twitter_title'   => '_seopress_social_twitter_title',
			'twitter_description' => '_seopress_social_twitter_desc',
			'twitter_image'   => '_seopress_social_twitter_img',
			'robots_noindex'  => '_seopress_robots_index',
			'robots_nofollow' => '_seopress_robots_follow',
			'primary_category' => '_seopress_robots_primary_cat',
		);

		foreach ( $data as $key => $value ) {
			if ( isset( $meta_map[ $key ] ) ) {
				if ( empty( $value ) ) {
					delete_post_meta( $post_id, $meta_map[ $key ] );
				} else {
					update_post_meta( $post_id, $meta_map[ $key ], sanitize_text_field( $value ) );
				}
			}
		}

		return $this->get_seopress_data( $post_id );
	}

	/**
	 * Bulk update SEO for multiple posts.
	 *
	 * @param array $updates Array of [ 'post_id' => ID, 'data' => SEO data ].
	 * @return array Results for each post.
	 */
	public function bulk_update( $updates ) {
		$results = array();

		foreach ( $updates as $update ) {
			$post_id = absint( $update['post_id'] );
			$data    = $update['data'] ?? array();

			$result = $this->update_post_seo( $post_id, $data );

			if ( is_wp_error( $result ) ) {
				$results[] = array(
					'post_id' => $post_id,
					'success' => false,
					'error'   => $result->get_error_message(),
				);
			} else {
				$results[] = array(
					'post_id' => $post_id,
					'success' => true,
					'data'    => $result,
				);
			}
		}

		return $results;
	}

	/**
	 * Analyze SEO for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return array Analysis results.
	 */
	public function analyze_post( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post ) {
			return new WP_Error( 'not_found', __( 'Post not found.', 'site-pilot-ai' ) );
		}

		$seo_data = $this->get_post_seo( $post_id );
		$analysis = array(
			'post_id'      => $post_id,
			'title'        => $post->post_title,
			'issues'       => array(),
			'warnings'     => array(),
			'suggestions'  => array(),
		);

		// Check title length.
		$title = ! empty( $seo_data['title'] ) ? $seo_data['title'] : $post->post_title;
		$title_len = strlen( $title );
		if ( $title_len < 30 ) {
			$analysis['warnings'][] = __( 'SEO title is too short (under 30 characters).', 'site-pilot-ai' );
		} elseif ( $title_len > 60 ) {
			$analysis['warnings'][] = __( 'SEO title is too long (over 60 characters).', 'site-pilot-ai' );
		}

		// Check meta description.
		$desc = $seo_data['description'] ?? '';
		if ( empty( $desc ) ) {
			$analysis['issues'][] = __( 'Missing meta description.', 'site-pilot-ai' );
		} else {
			$desc_len = strlen( $desc );
			if ( $desc_len < 120 ) {
				$analysis['warnings'][] = __( 'Meta description is too short (under 120 characters).', 'site-pilot-ai' );
			} elseif ( $desc_len > 160 ) {
				$analysis['warnings'][] = __( 'Meta description is too long (over 160 characters).', 'site-pilot-ai' );
			}
		}

		// Check focus keyword.
		$keyword = $seo_data['focus_keyword'] ?? '';
		if ( empty( $keyword ) ) {
			$analysis['suggestions'][] = __( 'Consider adding a focus keyword.', 'site-pilot-ai' );
		} else {
			// Check if keyword is in title.
			if ( stripos( $title, $keyword ) === false ) {
				$analysis['warnings'][] = __( 'Focus keyword not found in SEO title.', 'site-pilot-ai' );
			}
			// Check if keyword is in description.
			if ( ! empty( $desc ) && stripos( $desc, $keyword ) === false ) {
				$analysis['suggestions'][] = __( 'Consider adding focus keyword to meta description.', 'site-pilot-ai' );
			}
		}

		// Check content length.
		$content_len = str_word_count( wp_strip_all_tags( $post->post_content ) );
		if ( $content_len < 300 ) {
			$analysis['warnings'][] = sprintf(
				/* translators: %d: word count */
				__( 'Content is short (%d words). Consider expanding to at least 300 words.', 'site-pilot-ai' ),
				$content_len
			);
		}

		// Score calculation.
		$score = 100;
		$score -= count( $analysis['issues'] ) * 20;
		$score -= count( $analysis['warnings'] ) * 10;
		$score -= count( $analysis['suggestions'] ) * 5;
		$analysis['score'] = max( 0, $score );

		return $analysis;
	}
}
