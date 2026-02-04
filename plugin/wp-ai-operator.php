<?php
/**
 * Plugin Name: WP AI Operator
 * Plugin URI: https://github.com/Digidinc/wp-ai-operator
 * Description: Control your WordPress site with AI (Claude Code / Claude Desktop). Microkernel architecture with pluggable extensions for content, SEO, forms, and page builders.
 * Version: 2.0.0
 * Author: DigID
 * Author URI: https://digid.ca
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WPAIOperator {

    private $api_key;
    private $version = '2.0.0';
    private $namespace = 'wp-ai-operator/v1';

    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('rest_api_init', array($this, 'register_api_routes'));
        add_action('admin_menu', array($this, 'admin_menu'));
        register_activation_hook(__FILE__, array($this, 'activate_plugin'));
    }

    public function init() {
        $this->api_key = get_option('wpaio_api_key', '');
        if (empty($this->api_key)) {
            $this->generate_api_key();
        }
    }

    public function activate_plugin() {
        $this->generate_api_key();

        // Create analytics table
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpaio_analytics';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            action varchar(100) NOT NULL,
            data longtext,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            ip_address varchar(45),
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private function generate_api_key() {
        $this->api_key = 'wpaio_' . wp_generate_password(32, false);
        update_option('wpaio_api_key', $this->api_key);
    }

    public function verify_api_key($request) {
        $provided_key = $request->get_header('X-API-Key');
        if (empty($provided_key)) {
            $provided_key = $request->get_param('api_key');
        }
        $this->log_activity('api_access', array('endpoint' => $request->get_route()));
        return $provided_key === $this->api_key;
    }

    public function register_api_routes() {
        // ==================== CORE ROUTES ====================

        // Site info
        register_rest_route($this->namespace, '/site-info', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_site_info'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        // Analytics
        register_rest_route($this->namespace, '/analytics', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_analytics'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        // Plugin detection
        register_rest_route($this->namespace, '/plugins', array(
            'methods' => 'GET',
            'callback' => array($this, 'detect_plugins'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        // Posts
        register_rest_route($this->namespace, '/posts', array(
            array('methods' => 'GET', 'callback' => array($this, 'get_posts'), 'permission_callback' => array($this, 'verify_api_key')),
            array('methods' => 'POST', 'callback' => array($this, 'create_post'), 'permission_callback' => array($this, 'verify_api_key')),
        ));

        register_rest_route($this->namespace, '/posts/(?P<id>\d+)', array(
            array('methods' => 'GET', 'callback' => array($this, 'get_post'), 'permission_callback' => array($this, 'verify_api_key')),
            array('methods' => 'PUT', 'callback' => array($this, 'update_post'), 'permission_callback' => array($this, 'verify_api_key')),
            array('methods' => 'DELETE', 'callback' => array($this, 'delete_post'), 'permission_callback' => array($this, 'verify_api_key')),
        ));

        // Pages
        register_rest_route($this->namespace, '/pages', array(
            array('methods' => 'GET', 'callback' => array($this, 'get_pages'), 'permission_callback' => array($this, 'verify_api_key')),
            array('methods' => 'POST', 'callback' => array($this, 'create_page'), 'permission_callback' => array($this, 'verify_api_key')),
        ));

        register_rest_route($this->namespace, '/pages/(?P<id>\d+)', array(
            array('methods' => 'GET', 'callback' => array($this, 'get_page'), 'permission_callback' => array($this, 'verify_api_key')),
            array('methods' => 'PUT', 'callback' => array($this, 'update_page'), 'permission_callback' => array($this, 'verify_api_key')),
        ));

        // Media
        register_rest_route($this->namespace, '/media', array(
            'methods' => 'POST',
            'callback' => array($this, 'upload_media'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        register_rest_route($this->namespace, '/media/from-url', array(
            'methods' => 'POST',
            'callback' => array($this, 'upload_media_from_url'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        // Drafts
        register_rest_route($this->namespace, '/drafts', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_drafts'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        register_rest_route($this->namespace, '/drafts/delete-all', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_all_drafts'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        // ==================== SEO ROUTES ====================

        register_rest_route($this->namespace, '/seo/(?P<id>\d+)', array(
            array('methods' => 'GET', 'callback' => array($this, 'get_seo'), 'permission_callback' => array($this, 'verify_api_key')),
            array('methods' => 'POST', 'callback' => array($this, 'set_seo'), 'permission_callback' => array($this, 'verify_api_key')),
        ));

        register_rest_route($this->namespace, '/seo/(?P<id>\d+)/analyze', array(
            'methods' => 'GET',
            'callback' => array($this, 'analyze_seo'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        register_rest_route($this->namespace, '/seo/bulk', array(
            'methods' => 'POST',
            'callback' => array($this, 'bulk_seo'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        register_rest_route($this->namespace, '/seo/plugin', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_seo_plugin'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        // ==================== FORMS ROUTES ====================

        register_rest_route($this->namespace, '/forms', array(
            array('methods' => 'GET', 'callback' => array($this, 'list_forms'), 'permission_callback' => array($this, 'verify_api_key')),
            array('methods' => 'POST', 'callback' => array($this, 'create_form'), 'permission_callback' => array($this, 'verify_api_key')),
        ));

        register_rest_route($this->namespace, '/forms/plugins', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_form_plugins'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        register_rest_route($this->namespace, '/forms/(?P<id>[^/]+)', array(
            array('methods' => 'GET', 'callback' => array($this, 'get_form'), 'permission_callback' => array($this, 'verify_api_key')),
            array('methods' => 'PUT', 'callback' => array($this, 'update_form'), 'permission_callback' => array($this, 'verify_api_key')),
            array('methods' => 'DELETE', 'callback' => array($this, 'delete_form'), 'permission_callback' => array($this, 'verify_api_key')),
        ));

        register_rest_route($this->namespace, '/forms/(?P<id>[^/]+)/submissions', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_form_submissions'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        register_rest_route($this->namespace, '/forms/(?P<id>[^/]+)/submit', array(
            'methods' => 'POST',
            'callback' => array($this, 'submit_form'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        // ==================== ELEMENTOR ROUTES ====================

        register_rest_route($this->namespace, '/elementor/(?P<id>\d+)', array(
            array('methods' => 'GET', 'callback' => array($this, 'get_elementor'), 'permission_callback' => array($this, 'verify_api_key')),
            array('methods' => 'POST', 'callback' => array($this, 'set_elementor'), 'permission_callback' => array($this, 'verify_api_key')),
        ));

        register_rest_route($this->namespace, '/elementor/templates', array(
            'methods' => 'GET',
            'callback' => array($this, 'list_elementor_templates'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        register_rest_route($this->namespace, '/elementor/(?P<id>\d+)/apply-template', array(
            'methods' => 'POST',
            'callback' => array($this, 'apply_elementor_template'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        register_rest_route($this->namespace, '/elementor/landing-page', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_landing_page'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        register_rest_route($this->namespace, '/elementor/(?P<id>\d+)/sections', array(
            'methods' => 'POST',
            'callback' => array($this, 'add_elementor_section'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        register_rest_route($this->namespace, '/elementor/(?P<id>\d+)/widgets/(?P<widget_id>[^/]+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_elementor_widget'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        register_rest_route($this->namespace, '/elementor/globals', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_elementor_globals'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        register_rest_route($this->namespace, '/elementor/(?P<id>\d+)/clone', array(
            'methods' => 'POST',
            'callback' => array($this, 'clone_elementor_page'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));
    }

    // ==================== CORE HANDLERS ====================

    public function get_site_info($request) {
        $site_info = array(
            'site_name' => get_bloginfo('name'),
            'site_url' => get_site_url(),
            'admin_email' => get_option('admin_email'),
            'wordpress_version' => get_bloginfo('version'),
            'theme' => wp_get_theme()->get('Name'),
            'active_plugins' => $this->get_active_plugins_info(),
            'post_count' => wp_count_posts()->publish,
            'page_count' => wp_count_posts('page')->publish,
            'user_count' => count_users()['total_users'],
            'plugin_version' => $this->version,
            'capabilities' => $this->get_capabilities(),
        );

        return rest_ensure_response($site_info);
    }

    private function get_active_plugins_info() {
        $plugins = array();
        foreach (get_option('active_plugins', array()) as $plugin) {
            $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
            $plugins[] = array(
                'name' => $plugin_data['Name'],
                'version' => $plugin_data['Version'],
                'slug' => dirname($plugin),
            );
        }
        return $plugins;
    }

    private function get_capabilities() {
        return array(
            'elementor' => defined('ELEMENTOR_VERSION'),
            'elementor_pro' => defined('ELEMENTOR_PRO_VERSION'),
            'woocommerce' => class_exists('WooCommerce'),
            'yoast' => defined('WPSEO_VERSION'),
            'rankmath' => class_exists('RankMath'),
            'aioseo' => defined('AIOSEO_VERSION'),
            'seopress' => defined('SEOPRESS_VERSION'),
            'cf7' => class_exists('WPCF7'),
            'wpforms' => class_exists('WPForms'),
            'gravityforms' => class_exists('GFForms'),
            'ninjaforms' => class_exists('Ninja_Forms'),
        );
    }

    public function detect_plugins($request) {
        return rest_ensure_response($this->get_capabilities());
    }

    public function get_analytics($request) {
        global $wpdb;
        $days = intval($request->get_param('days')) ?: 30;
        $table_name = $wpdb->prefix . 'wpaio_analytics';

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT action, COUNT(*) as count, DATE(timestamp) as date
             FROM $table_name
             WHERE timestamp >= DATE_SUB(NOW(), INTERVAL %d DAY)
             GROUP BY action, DATE(timestamp)
             ORDER BY timestamp DESC",
            $days
        ));

        return rest_ensure_response(array(
            'period' => $days . ' days',
            'total_posts' => wp_count_posts()->publish,
            'total_drafts' => wp_count_posts()->draft,
            'total_pages' => wp_count_posts('page')->publish,
            'activity' => $results,
        ));
    }

    // Posts handlers
    public function get_posts($request) {
        $args = array(
            'post_type' => 'post',
            'post_status' => $request->get_param('status') ?: 'publish',
            'posts_per_page' => $request->get_param('per_page') ?: 10,
            'paged' => $request->get_param('page') ?: 1,
        );

        if ($request->get_param('category')) {
            $args['cat'] = $request->get_param('category');
        }

        $posts = get_posts($args);
        $formatted = array_map(array($this, 'format_post'), $posts);
        return rest_ensure_response($formatted);
    }

    public function get_post($request) {
        $post = get_post($request->get_param('id'));
        if (!$post) {
            return new WP_Error('not_found', 'Post not found', array('status' => 404));
        }
        return rest_ensure_response($this->format_post($post));
    }

    private function format_post($post) {
        return array(
            'id' => $post->ID,
            'title' => $post->post_title,
            'content' => $post->post_content,
            'excerpt' => $post->post_excerpt,
            'status' => $post->post_status,
            'date' => $post->post_date,
            'modified' => $post->post_modified,
            'slug' => $post->post_name,
            'url' => get_permalink($post->ID),
            'categories' => wp_get_post_categories($post->ID, array('fields' => 'names')),
            'tags' => wp_get_post_tags($post->ID, array('fields' => 'names')),
            'featured_image' => get_the_post_thumbnail_url($post->ID, 'full'),
            'author' => get_the_author_meta('display_name', $post->post_author),
        );
    }

    public function create_post($request) {
        $post_data = array(
            'post_title' => sanitize_text_field($request->get_param('title')),
            'post_content' => wp_kses_post($request->get_param('content')),
            'post_excerpt' => sanitize_textarea_field($request->get_param('excerpt') ?: ''),
            'post_status' => in_array($request->get_param('status'), array('publish', 'draft', 'private')) ? $request->get_param('status') : 'draft',
            'post_type' => 'post',
            'post_author' => get_current_user_id() ?: 1,
        );

        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        if ($request->get_param('categories')) {
            wp_set_post_categories($post_id, $request->get_param('categories'));
        }

        if ($request->get_param('tags')) {
            wp_set_post_tags($post_id, $request->get_param('tags'));
        }

        if ($request->get_param('featured_image')) {
            set_post_thumbnail($post_id, $request->get_param('featured_image'));
        }

        $this->log_activity('create_post', array('post_id' => $post_id));

        return rest_ensure_response(array(
            'success' => true,
            'post_id' => $post_id,
            'url' => get_permalink($post_id),
        ));
    }

    public function update_post($request) {
        $post_id = $request->get_param('id');
        $post = get_post($post_id);

        if (!$post) {
            return new WP_Error('not_found', 'Post not found', array('status' => 404));
        }

        $post_data = array('ID' => $post_id);

        if ($request->get_param('title')) {
            $post_data['post_title'] = sanitize_text_field($request->get_param('title'));
        }
        if ($request->get_param('content')) {
            $post_data['post_content'] = wp_kses_post($request->get_param('content'));
        }
        if ($request->get_param('excerpt')) {
            $post_data['post_excerpt'] = sanitize_textarea_field($request->get_param('excerpt'));
        }
        if ($request->get_param('status')) {
            $post_data['post_status'] = $request->get_param('status');
        }

        $result = wp_update_post($post_data);

        if (is_wp_error($result)) {
            return $result;
        }

        $this->log_activity('update_post', array('post_id' => $post_id));

        return rest_ensure_response(array(
            'success' => true,
            'post_id' => $post_id,
            'url' => get_permalink($post_id),
        ));
    }

    public function delete_post($request) {
        $post_id = $request->get_param('id');
        $force = $request->get_param('force') === true || $request->get_param('force') === 'true';

        $result = wp_delete_post($post_id, $force);

        if (!$result) {
            return new WP_Error('delete_failed', 'Failed to delete post', array('status' => 500));
        }

        $this->log_activity('delete_post', array('post_id' => $post_id, 'force' => $force));

        return rest_ensure_response(array(
            'success' => true,
            'post_id' => $post_id,
            'action' => $force ? 'deleted' : 'trashed',
        ));
    }

    // Pages handlers
    public function get_pages($request) {
        $args = array(
            'post_type' => 'page',
            'post_status' => $request->get_param('status') ?: array('publish', 'draft'),
            'posts_per_page' => $request->get_param('per_page') ?: 10,
            'paged' => $request->get_param('page') ?: 1,
        );

        $pages = get_posts($args);
        $formatted = array_map(array($this, 'format_page'), $pages);
        return rest_ensure_response($formatted);
    }

    public function get_page($request) {
        $page = get_post($request->get_param('id'));
        if (!$page || $page->post_type !== 'page') {
            return new WP_Error('not_found', 'Page not found', array('status' => 404));
        }
        return rest_ensure_response($this->format_page($page));
    }

    private function format_page($page) {
        return array(
            'id' => $page->ID,
            'title' => $page->post_title,
            'content' => $page->post_content,
            'status' => $page->post_status,
            'date' => $page->post_date,
            'modified' => $page->post_modified,
            'slug' => $page->post_name,
            'url' => get_permalink($page->ID),
            'template' => get_page_template_slug($page->ID),
            'parent' => $page->post_parent,
            'has_elementor' => get_post_meta($page->ID, '_elementor_edit_mode', true) === 'builder',
        );
    }

    public function create_page($request) {
        $page_data = array(
            'post_title' => sanitize_text_field($request->get_param('title')),
            'post_content' => wp_kses_post($request->get_param('content') ?: ''),
            'post_status' => in_array($request->get_param('status'), array('publish', 'draft', 'private')) ? $request->get_param('status') : 'draft',
            'post_type' => 'page',
            'post_author' => get_current_user_id() ?: 1,
        );

        if ($request->get_param('parent')) {
            $page_data['post_parent'] = intval($request->get_param('parent'));
        }

        $page_id = wp_insert_post($page_data);

        if (is_wp_error($page_id)) {
            return $page_id;
        }

        if ($request->get_param('template')) {
            update_post_meta($page_id, '_wp_page_template', $request->get_param('template'));
        }

        if ($request->get_param('elementor_data')) {
            $this->set_elementor_data($page_id, $request->get_param('elementor_data'));
        }

        $this->log_activity('create_page', array('page_id' => $page_id));

        return rest_ensure_response(array(
            'success' => true,
            'page_id' => $page_id,
            'url' => get_permalink($page_id),
        ));
    }

    public function update_page($request) {
        $page_id = $request->get_param('id');
        $page = get_post($page_id);

        if (!$page || $page->post_type !== 'page') {
            return new WP_Error('not_found', 'Page not found', array('status' => 404));
        }

        $page_data = array('ID' => $page_id);

        if ($request->get_param('title')) {
            $page_data['post_title'] = sanitize_text_field($request->get_param('title'));
        }
        if ($request->get_param('content')) {
            $page_data['post_content'] = wp_kses_post($request->get_param('content'));
        }
        if ($request->get_param('status')) {
            $page_data['post_status'] = $request->get_param('status');
        }

        $result = wp_update_post($page_data);

        if (is_wp_error($result)) {
            return $result;
        }

        if ($request->get_param('elementor_data')) {
            $this->set_elementor_data($page_id, $request->get_param('elementor_data'));
        }

        $this->log_activity('update_page', array('page_id' => $page_id));

        return rest_ensure_response(array(
            'success' => true,
            'page_id' => $page_id,
            'url' => get_permalink($page_id),
        ));
    }

    // Media handlers
    public function upload_media($request) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $files = $request->get_file_params();

        if (empty($files['file'])) {
            return new WP_Error('no_file', 'No file provided', array('status' => 400));
        }

        $attachment_id = media_handle_upload('file', 0);

        if (is_wp_error($attachment_id)) {
            return $attachment_id;
        }

        // Set alt text if provided
        if ($request->get_param('alt')) {
            update_post_meta($attachment_id, '_wp_attachment_image_alt', sanitize_text_field($request->get_param('alt')));
        }

        $this->log_activity('upload_media', array('attachment_id' => $attachment_id));

        return rest_ensure_response(array(
            'success' => true,
            'attachment_id' => $attachment_id,
            'url' => wp_get_attachment_url($attachment_id),
            'sizes' => $this->get_image_sizes($attachment_id),
        ));
    }

    public function upload_media_from_url($request) {
        $url = $request->get_param('url');

        if (empty($url)) {
            return new WP_Error('no_url', 'URL is required', array('status' => 400));
        }

        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $temp_file = download_url($url);

        if (is_wp_error($temp_file)) {
            return $temp_file;
        }

        $filename = basename(parse_url($url, PHP_URL_PATH));
        $file_array = array(
            'name' => $filename,
            'tmp_name' => $temp_file,
        );

        $attachment_id = media_handle_sideload($file_array, 0, $request->get_param('title'));

        if (is_wp_error($attachment_id)) {
            @unlink($temp_file);
            return $attachment_id;
        }

        if ($request->get_param('alt')) {
            update_post_meta($attachment_id, '_wp_attachment_image_alt', sanitize_text_field($request->get_param('alt')));
        }

        $this->log_activity('upload_media_from_url', array('attachment_id' => $attachment_id, 'url' => $url));

        return rest_ensure_response(array(
            'success' => true,
            'attachment_id' => $attachment_id,
            'url' => wp_get_attachment_url($attachment_id),
            'sizes' => $this->get_image_sizes($attachment_id),
        ));
    }

    private function get_image_sizes($attachment_id) {
        $sizes = array();
        foreach (get_intermediate_image_sizes() as $size) {
            $image = wp_get_attachment_image_src($attachment_id, $size);
            if ($image) {
                $sizes[$size] = $image[0];
            }
        }
        return $sizes;
    }

    // Drafts handlers
    public function get_drafts($request) {
        $args = array(
            'post_type' => $request->get_param('type') ?: 'any',
            'post_status' => 'draft',
            'posts_per_page' => -1,
        );

        $drafts = get_posts($args);
        $formatted = array();

        foreach ($drafts as $draft) {
            $formatted[] = array(
                'id' => $draft->ID,
                'title' => $draft->post_title,
                'type' => $draft->post_type,
                'date' => $draft->post_date,
                'modified' => $draft->post_modified,
                'author' => get_the_author_meta('display_name', $draft->post_author),
            );
        }

        return rest_ensure_response($formatted);
    }

    public function delete_all_drafts($request) {
        $type = $request->get_param('type') ?: 'post';

        $drafts = get_posts(array(
            'post_type' => $type,
            'post_status' => 'draft',
            'posts_per_page' => -1,
        ));

        $deleted = 0;
        foreach ($drafts as $draft) {
            if (wp_delete_post($draft->ID, true)) {
                $deleted++;
            }
        }

        $this->log_activity('delete_all_drafts', array('type' => $type, 'deleted' => $deleted));

        return rest_ensure_response(array(
            'success' => true,
            'deleted_count' => $deleted,
            'total_drafts' => count($drafts),
        ));
    }

    // ==================== SEO HANDLERS ====================

    public function get_seo($request) {
        $post_id = $request->get_param('id');
        $post = get_post($post_id);

        if (!$post) {
            return new WP_Error('not_found', 'Post not found', array('status' => 404));
        }

        $seo_data = array(
            'post_id' => $post_id,
            'plugin' => $this->detect_seo_plugin(),
        );

        // Get from active SEO plugin
        if (defined('WPSEO_VERSION')) {
            $seo_data['title'] = get_post_meta($post_id, '_yoast_wpseo_title', true);
            $seo_data['description'] = get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
            $seo_data['focus_keyword'] = get_post_meta($post_id, '_yoast_wpseo_focuskw', true);
            $seo_data['canonical'] = get_post_meta($post_id, '_yoast_wpseo_canonical', true);
            $seo_data['robots'] = array(
                'noindex' => get_post_meta($post_id, '_yoast_wpseo_meta-robots-noindex', true),
                'nofollow' => get_post_meta($post_id, '_yoast_wpseo_meta-robots-nofollow', true),
            );
        } elseif (class_exists('RankMath')) {
            $seo_data['title'] = get_post_meta($post_id, 'rank_math_title', true);
            $seo_data['description'] = get_post_meta($post_id, 'rank_math_description', true);
            $seo_data['focus_keyword'] = get_post_meta($post_id, 'rank_math_focus_keyword', true);
            $seo_data['canonical'] = get_post_meta($post_id, 'rank_math_canonical_url', true);
            $seo_data['robots'] = get_post_meta($post_id, 'rank_math_robots', true);
        } elseif (defined('AIOSEO_VERSION')) {
            $aioseo_data = get_post_meta($post_id, '_aioseo_data', true);
            if ($aioseo_data && is_array($aioseo_data)) {
                $seo_data['title'] = $aioseo_data['title'] ?? '';
                $seo_data['description'] = $aioseo_data['description'] ?? '';
                $seo_data['focus_keyword'] = $aioseo_data['keyphrases']['focus']['keyphrase'] ?? '';
            }
        } else {
            // Fallback to custom meta
            $seo_data['title'] = get_post_meta($post_id, '_wpaio_seo_title', true);
            $seo_data['description'] = get_post_meta($post_id, '_wpaio_seo_description', true);
            $seo_data['focus_keyword'] = get_post_meta($post_id, '_wpaio_focus_keyword', true);
        }

        return rest_ensure_response($seo_data);
    }

    public function set_seo($request) {
        $post_id = $request->get_param('id');
        $post = get_post($post_id);

        if (!$post) {
            return new WP_Error('not_found', 'Post not found', array('status' => 404));
        }

        $title = sanitize_text_field($request->get_param('title') ?: '');
        $description = sanitize_textarea_field($request->get_param('description') ?: '');
        $focus_keyword = sanitize_text_field($request->get_param('focus_keyword') ?: '');
        $canonical = esc_url_raw($request->get_param('canonical') ?: '');

        // Save to active SEO plugin
        if (defined('WPSEO_VERSION')) {
            if ($title) update_post_meta($post_id, '_yoast_wpseo_title', $title);
            if ($description) update_post_meta($post_id, '_yoast_wpseo_metadesc', $description);
            if ($focus_keyword) update_post_meta($post_id, '_yoast_wpseo_focuskw', $focus_keyword);
            if ($canonical) update_post_meta($post_id, '_yoast_wpseo_canonical', $canonical);
        }

        if (class_exists('RankMath')) {
            if ($title) update_post_meta($post_id, 'rank_math_title', $title);
            if ($description) update_post_meta($post_id, 'rank_math_description', $description);
            if ($focus_keyword) update_post_meta($post_id, 'rank_math_focus_keyword', $focus_keyword);
            if ($canonical) update_post_meta($post_id, 'rank_math_canonical_url', $canonical);
        }

        // Always save to our custom meta as backup
        update_post_meta($post_id, '_wpaio_seo_title', $title);
        update_post_meta($post_id, '_wpaio_seo_description', $description);
        update_post_meta($post_id, '_wpaio_focus_keyword', $focus_keyword);

        $this->log_activity('set_seo', array('post_id' => $post_id));

        return rest_ensure_response(array(
            'success' => true,
            'post_id' => $post_id,
        ));
    }

    public function analyze_seo($request) {
        $post_id = $request->get_param('id');
        $post = get_post($post_id);

        if (!$post) {
            return new WP_Error('not_found', 'Post not found', array('status' => 404));
        }

        $title = $post->post_title;
        $content = strip_tags($post->post_content);
        $word_count = str_word_count($content);

        // Get SEO data
        $seo_title = get_post_meta($post_id, '_wpaio_seo_title', true) ?: $title;
        $seo_desc = get_post_meta($post_id, '_wpaio_seo_description', true) ?: '';
        $focus_keyword = get_post_meta($post_id, '_wpaio_focus_keyword', true) ?: '';

        $issues = array();
        $score = 100;

        // Title checks
        if (strlen($seo_title) < 30) {
            $issues[] = array('type' => 'warning', 'message' => 'SEO title is too short (< 30 chars)');
            $score -= 10;
        } elseif (strlen($seo_title) > 60) {
            $issues[] = array('type' => 'warning', 'message' => 'SEO title is too long (> 60 chars)');
            $score -= 5;
        }

        // Description checks
        if (empty($seo_desc)) {
            $issues[] = array('type' => 'error', 'message' => 'Meta description is missing');
            $score -= 20;
        } elseif (strlen($seo_desc) < 120) {
            $issues[] = array('type' => 'warning', 'message' => 'Meta description is too short (< 120 chars)');
            $score -= 10;
        } elseif (strlen($seo_desc) > 160) {
            $issues[] = array('type' => 'warning', 'message' => 'Meta description is too long (> 160 chars)');
            $score -= 5;
        }

        // Focus keyword checks
        if (empty($focus_keyword)) {
            $issues[] = array('type' => 'warning', 'message' => 'No focus keyword set');
            $score -= 10;
        } else {
            if (stripos($title, $focus_keyword) === false) {
                $issues[] = array('type' => 'warning', 'message' => 'Focus keyword not in title');
                $score -= 5;
            }
            if (stripos($content, $focus_keyword) === false) {
                $issues[] = array('type' => 'error', 'message' => 'Focus keyword not in content');
                $score -= 15;
            }
        }

        // Content checks
        if ($word_count < 300) {
            $issues[] = array('type' => 'warning', 'message' => 'Content is thin (< 300 words)');
            $score -= 15;
        }

        // Images
        preg_match_all('/<img[^>]+>/i', $post->post_content, $images);
        $image_count = count($images[0]);
        if ($image_count === 0 && $word_count > 300) {
            $issues[] = array('type' => 'info', 'message' => 'Consider adding images to improve engagement');
        }

        // Internal/external links
        preg_match_all('/<a[^>]+href=[\'"]([^\'"]+)[\'"][^>]*>/i', $post->post_content, $links);
        if (count($links[0]) === 0 && $word_count > 300) {
            $issues[] = array('type' => 'info', 'message' => 'Consider adding internal or external links');
        }

        return rest_ensure_response(array(
            'post_id' => $post_id,
            'score' => max(0, $score),
            'word_count' => $word_count,
            'image_count' => $image_count,
            'link_count' => count($links[0] ?? array()),
            'seo_title_length' => strlen($seo_title),
            'meta_description_length' => strlen($seo_desc),
            'issues' => $issues,
        ));
    }

    public function bulk_seo($request) {
        $items = $request->get_param('items');

        if (!is_array($items) || empty($items)) {
            return new WP_Error('invalid_items', 'Items array is required', array('status' => 400));
        }

        $results = array();

        foreach ($items as $item) {
            $post_id = $item['post_id'] ?? null;
            if (!$post_id || !get_post($post_id)) {
                $results[] = array('post_id' => $post_id, 'success' => false, 'error' => 'Post not found');
                continue;
            }

            $title = sanitize_text_field($item['title'] ?? '');
            $description = sanitize_textarea_field($item['description'] ?? '');
            $focus_keyword = sanitize_text_field($item['focus_keyword'] ?? '');

            if ($title) {
                update_post_meta($post_id, '_wpaio_seo_title', $title);
                if (defined('WPSEO_VERSION')) update_post_meta($post_id, '_yoast_wpseo_title', $title);
                if (class_exists('RankMath')) update_post_meta($post_id, 'rank_math_title', $title);
            }

            if ($description) {
                update_post_meta($post_id, '_wpaio_seo_description', $description);
                if (defined('WPSEO_VERSION')) update_post_meta($post_id, '_yoast_wpseo_metadesc', $description);
                if (class_exists('RankMath')) update_post_meta($post_id, 'rank_math_description', $description);
            }

            if ($focus_keyword) {
                update_post_meta($post_id, '_wpaio_focus_keyword', $focus_keyword);
                if (defined('WPSEO_VERSION')) update_post_meta($post_id, '_yoast_wpseo_focuskw', $focus_keyword);
                if (class_exists('RankMath')) update_post_meta($post_id, 'rank_math_focus_keyword', $focus_keyword);
            }

            $results[] = array('post_id' => $post_id, 'success' => true);
        }

        $this->log_activity('bulk_seo', array('count' => count($results)));

        return rest_ensure_response(array(
            'success' => true,
            'results' => $results,
        ));
    }

    public function get_seo_plugin($request) {
        return rest_ensure_response(array(
            'active_plugin' => $this->detect_seo_plugin(),
            'available' => array(
                'yoast' => defined('WPSEO_VERSION'),
                'rankmath' => class_exists('RankMath'),
                'aioseo' => defined('AIOSEO_VERSION'),
                'seopress' => defined('SEOPRESS_VERSION'),
            ),
        ));
    }

    private function detect_seo_plugin() {
        if (defined('WPSEO_VERSION')) return 'yoast';
        if (class_exists('RankMath')) return 'rankmath';
        if (defined('AIOSEO_VERSION')) return 'aioseo';
        if (defined('SEOPRESS_VERSION')) return 'seopress';
        return 'none';
    }

    // ==================== FORMS HANDLERS ====================

    public function list_forms($request) {
        $plugin = $request->get_param('plugin');
        $forms = array();

        // Contact Form 7
        if ((!$plugin || $plugin === 'cf7' || $plugin === 'all') && class_exists('WPCF7')) {
            $cf7_forms = get_posts(array(
                'post_type' => 'wpcf7_contact_form',
                'posts_per_page' => -1,
            ));
            foreach ($cf7_forms as $form) {
                $forms[] = array(
                    'id' => $form->ID,
                    'title' => $form->post_title,
                    'plugin' => 'cf7',
                    'shortcode' => '[contact-form-7 id="' . $form->ID . '"]',
                );
            }
        }

        // WPForms
        if ((!$plugin || $plugin === 'wpforms' || $plugin === 'all') && class_exists('WPForms')) {
            $wpforms = get_posts(array(
                'post_type' => 'wpforms',
                'posts_per_page' => -1,
            ));
            foreach ($wpforms as $form) {
                $forms[] = array(
                    'id' => $form->ID,
                    'title' => $form->post_title,
                    'plugin' => 'wpforms',
                    'shortcode' => '[wpforms id="' . $form->ID . '"]',
                );
            }
        }

        // Gravity Forms
        if ((!$plugin || $plugin === 'gravityforms' || $plugin === 'all') && class_exists('GFForms')) {
            $gf_forms = GFAPI::get_forms();
            foreach ($gf_forms as $form) {
                $forms[] = array(
                    'id' => $form['id'],
                    'title' => $form['title'],
                    'plugin' => 'gravityforms',
                    'shortcode' => '[gravityform id="' . $form['id'] . '"]',
                );
            }
        }

        // Ninja Forms
        if ((!$plugin || $plugin === 'ninjaforms' || $plugin === 'all') && class_exists('Ninja_Forms')) {
            $nf_forms = Ninja_Forms()->form()->get_forms();
            foreach ($nf_forms as $form) {
                $forms[] = array(
                    'id' => $form->get_id(),
                    'title' => $form->get_setting('title'),
                    'plugin' => 'ninjaforms',
                    'shortcode' => '[ninja_form id="' . $form->get_id() . '"]',
                );
            }
        }

        return rest_ensure_response($forms);
    }

    public function get_form($request) {
        $form_id = $request->get_param('id');
        $plugin = $request->get_param('plugin');

        // Auto-detect plugin if not specified
        if (!$plugin) {
            $plugin = $this->detect_form_plugin_for_id($form_id);
        }

        switch ($plugin) {
            case 'cf7':
                if (!class_exists('WPCF7')) {
                    return new WP_Error('plugin_not_active', 'Contact Form 7 is not active', array('status' => 400));
                }
                $form = get_post($form_id);
                if (!$form) {
                    return new WP_Error('not_found', 'Form not found', array('status' => 404));
                }
                return rest_ensure_response(array(
                    'id' => $form->ID,
                    'title' => $form->post_title,
                    'plugin' => 'cf7',
                    'content' => $form->post_content,
                    'mail' => get_post_meta($form_id, '_mail', true),
                ));

            case 'wpforms':
                if (!class_exists('WPForms')) {
                    return new WP_Error('plugin_not_active', 'WPForms is not active', array('status' => 400));
                }
                $form = wpforms()->form->get($form_id);
                if (!$form) {
                    return new WP_Error('not_found', 'Form not found', array('status' => 404));
                }
                $form_data = wpforms_decode($form->post_content);
                return rest_ensure_response(array(
                    'id' => $form->ID,
                    'title' => $form->post_title,
                    'plugin' => 'wpforms',
                    'fields' => $form_data['fields'] ?? array(),
                    'settings' => $form_data['settings'] ?? array(),
                ));

            case 'gravityforms':
                if (!class_exists('GFForms')) {
                    return new WP_Error('plugin_not_active', 'Gravity Forms is not active', array('status' => 400));
                }
                $form = GFAPI::get_form($form_id);
                if (!$form) {
                    return new WP_Error('not_found', 'Form not found', array('status' => 404));
                }
                return rest_ensure_response(array(
                    'id' => $form['id'],
                    'title' => $form['title'],
                    'plugin' => 'gravityforms',
                    'fields' => $form['fields'],
                    'notifications' => $form['notifications'] ?? array(),
                    'confirmations' => $form['confirmations'] ?? array(),
                ));

            default:
                return new WP_Error('unknown_plugin', 'Could not determine form plugin', array('status' => 400));
        }
    }

    public function create_form($request) {
        $plugin = $request->get_param('plugin');
        $title = sanitize_text_field($request->get_param('title'));
        $fields = $request->get_param('fields');

        if (!$plugin || !$title) {
            return new WP_Error('missing_params', 'Plugin and title are required', array('status' => 400));
        }

        switch ($plugin) {
            case 'cf7':
                if (!class_exists('WPCF7')) {
                    return new WP_Error('plugin_not_active', 'Contact Form 7 is not active', array('status' => 400));
                }

                // Build CF7 form content from fields
                $form_content = $this->build_cf7_content($fields);

                $form_id = wp_insert_post(array(
                    'post_type' => 'wpcf7_contact_form',
                    'post_title' => $title,
                    'post_content' => $form_content,
                    'post_status' => 'publish',
                ));

                if (is_wp_error($form_id)) {
                    return $form_id;
                }

                // Set default mail settings
                $settings = $request->get_param('settings') ?: array();
                $mail = array(
                    'subject' => $settings['email_subject'] ?? 'New submission from ' . $title,
                    'sender' => '[your-email]',
                    'recipient' => $settings['email_to'] ?? get_option('admin_email'),
                    'body' => '[your-message]',
                    'additional_headers' => '',
                    'attachments' => '',
                    'use_html' => false,
                );
                update_post_meta($form_id, '_mail', $mail);

                $this->log_activity('create_form', array('form_id' => $form_id, 'plugin' => 'cf7'));

                return rest_ensure_response(array(
                    'success' => true,
                    'form_id' => $form_id,
                    'shortcode' => '[contact-form-7 id="' . $form_id . '"]',
                ));

            case 'wpforms':
                if (!class_exists('WPForms')) {
                    return new WP_Error('plugin_not_active', 'WPForms is not active', array('status' => 400));
                }

                $form_data = array(
                    'fields' => $this->build_wpforms_fields($fields),
                    'settings' => array(
                        'form_title' => $title,
                        'submit_text' => $request->get_param('settings')['submit_button_text'] ?? 'Submit',
                    ),
                );

                $form_id = wp_insert_post(array(
                    'post_type' => 'wpforms',
                    'post_title' => $title,
                    'post_content' => wpforms_encode($form_data),
                    'post_status' => 'publish',
                ));

                $this->log_activity('create_form', array('form_id' => $form_id, 'plugin' => 'wpforms'));

                return rest_ensure_response(array(
                    'success' => true,
                    'form_id' => $form_id,
                    'shortcode' => '[wpforms id="' . $form_id . '"]',
                ));

            default:
                return new WP_Error('unsupported_plugin', 'Form creation not supported for this plugin', array('status' => 400));
        }
    }

    private function build_cf7_content($fields) {
        if (!is_array($fields)) return '';

        $content = '';
        foreach ($fields as $field) {
            $type = $field['type'] ?? 'text';
            $name = sanitize_title($field['name'] ?? $field['label'] ?? 'field');
            $label = $field['label'] ?? ucfirst($name);
            $required = !empty($field['required']) ? '*' : '';

            switch ($type) {
                case 'email':
                    $content .= "<label>{$label}\n[email{$required} {$name}]</label>\n\n";
                    break;
                case 'textarea':
                    $content .= "<label>{$label}\n[textarea{$required} {$name}]</label>\n\n";
                    break;
                case 'select':
                    $options = implode(' ', array_map(function($opt) { return '"' . $opt . '"'; }, $field['options'] ?? array()));
                    $content .= "<label>{$label}\n[select{$required} {$name} {$options}]</label>\n\n";
                    break;
                case 'checkbox':
                    $options = implode(' ', array_map(function($opt) { return '"' . $opt . '"'; }, $field['options'] ?? array()));
                    $content .= "<label>{$label}\n[checkbox{$required} {$name} {$options}]</label>\n\n";
                    break;
                default:
                    $content .= "<label>{$label}\n[text{$required} {$name}]</label>\n\n";
            }
        }

        $content .= "[submit \"Submit\"]";
        return $content;
    }

    private function build_wpforms_fields($fields) {
        if (!is_array($fields)) return array();

        $wpforms_fields = array();
        $id = 1;

        foreach ($fields as $field) {
            $type = $field['type'] ?? 'text';
            $wpforms_fields[$id] = array(
                'id' => $id,
                'type' => $type,
                'label' => $field['label'] ?? 'Field ' . $id,
                'required' => !empty($field['required']) ? '1' : '0',
            );

            if (isset($field['placeholder'])) {
                $wpforms_fields[$id]['placeholder'] = $field['placeholder'];
            }

            if (in_array($type, array('select', 'checkbox', 'radio')) && isset($field['options'])) {
                $wpforms_fields[$id]['choices'] = array();
                foreach ($field['options'] as $i => $option) {
                    $wpforms_fields[$id]['choices'][$i] = array('label' => $option);
                }
            }

            $id++;
        }

        return $wpforms_fields;
    }

    public function update_form($request) {
        // Simplified - would need per-plugin implementation
        return new WP_Error('not_implemented', 'Form update not yet implemented', array('status' => 501));
    }

    public function delete_form($request) {
        $form_id = $request->get_param('id');
        $plugin = $request->get_param('plugin') ?: $this->detect_form_plugin_for_id($form_id);

        switch ($plugin) {
            case 'cf7':
            case 'wpforms':
                $result = wp_delete_post($form_id, true);
                if (!$result) {
                    return new WP_Error('delete_failed', 'Failed to delete form', array('status' => 500));
                }
                break;

            case 'gravityforms':
                if (class_exists('GFAPI')) {
                    GFAPI::delete_form($form_id);
                }
                break;

            default:
                return new WP_Error('unknown_plugin', 'Could not determine form plugin', array('status' => 400));
        }

        $this->log_activity('delete_form', array('form_id' => $form_id, 'plugin' => $plugin));

        return rest_ensure_response(array('success' => true, 'form_id' => $form_id));
    }

    public function get_form_submissions($request) {
        $form_id = $request->get_param('id');
        $plugin = $request->get_param('plugin') ?: $this->detect_form_plugin_for_id($form_id);
        $per_page = $request->get_param('per_page') ?: 20;
        $page = $request->get_param('page') ?: 1;

        $submissions = array();

        switch ($plugin) {
            case 'cf7':
                // CF7 doesn't store submissions by default, check for Flamingo
                if (class_exists('Flamingo_Inbound_Message')) {
                    $messages = get_posts(array(
                        'post_type' => 'flamingo_inbound',
                        'posts_per_page' => $per_page,
                        'paged' => $page,
                        'meta_query' => array(
                            array(
                                'key' => '_channel',
                                'value' => 'contact-form-7',
                            ),
                        ),
                    ));
                    foreach ($messages as $msg) {
                        $submissions[] = array(
                            'id' => $msg->ID,
                            'date' => $msg->post_date,
                            'fields' => get_post_meta($msg->ID, '_field_values', true),
                        );
                    }
                }
                break;

            case 'wpforms':
                if (function_exists('wpforms_get_entries')) {
                    $entries = wpforms_get_entries(array(
                        'form_id' => $form_id,
                        'number' => $per_page,
                        'offset' => ($page - 1) * $per_page,
                    ));
                    foreach ($entries as $entry) {
                        $submissions[] = array(
                            'id' => $entry->entry_id,
                            'date' => $entry->date,
                            'fields' => json_decode($entry->fields, true),
                        );
                    }
                }
                break;

            case 'gravityforms':
                if (class_exists('GFAPI')) {
                    $entries = GFAPI::get_entries($form_id, array(), null, array(
                        'offset' => ($page - 1) * $per_page,
                        'page_size' => $per_page,
                    ));
                    foreach ($entries as $entry) {
                        $submissions[] = array(
                            'id' => $entry['id'],
                            'date' => $entry['date_created'],
                            'fields' => $entry,
                        );
                    }
                }
                break;
        }

        return rest_ensure_response(array(
            'form_id' => $form_id,
            'plugin' => $plugin,
            'page' => $page,
            'per_page' => $per_page,
            'submissions' => $submissions,
        ));
    }

    public function submit_form($request) {
        // Programmatic form submission - plugin specific
        return new WP_Error('not_implemented', 'Programmatic form submission not yet implemented', array('status' => 501));
    }

    public function get_form_plugins($request) {
        return rest_ensure_response(array(
            'active' => $this->detect_active_form_plugins(),
            'available' => array(
                'cf7' => class_exists('WPCF7'),
                'wpforms' => class_exists('WPForms'),
                'gravityforms' => class_exists('GFForms'),
                'ninjaforms' => class_exists('Ninja_Forms'),
                'elementor' => defined('ELEMENTOR_PRO_VERSION'),
            ),
        ));
    }

    private function detect_form_plugin_for_id($form_id) {
        if (get_post_type($form_id) === 'wpcf7_contact_form') return 'cf7';
        if (get_post_type($form_id) === 'wpforms') return 'wpforms';
        return null;
    }

    private function detect_active_form_plugins() {
        $plugins = array();
        if (class_exists('WPCF7')) $plugins[] = 'cf7';
        if (class_exists('WPForms')) $plugins[] = 'wpforms';
        if (class_exists('GFForms')) $plugins[] = 'gravityforms';
        if (class_exists('Ninja_Forms')) $plugins[] = 'ninjaforms';
        return $plugins;
    }

    // ==================== ELEMENTOR HANDLERS ====================

    public function get_elementor($request) {
        $page_id = $request->get_param('id');

        if (!get_post($page_id)) {
            return new WP_Error('not_found', 'Page not found', array('status' => 404));
        }

        return rest_ensure_response(array(
            'page_id' => $page_id,
            'elementor_data' => get_post_meta($page_id, '_elementor_data', true),
            'edit_mode' => get_post_meta($page_id, '_elementor_edit_mode', true),
            'template_type' => get_post_meta($page_id, '_elementor_template_type', true),
            'page_settings' => get_post_meta($page_id, '_elementor_page_settings', true),
        ));
    }

    public function set_elementor($request) {
        $page_id = $request->get_param('id');
        $elementor_data = $request->get_param('elementor_data');

        if (!get_post($page_id)) {
            return new WP_Error('not_found', 'Page not found', array('status' => 404));
        }

        $this->set_elementor_data($page_id, $elementor_data);
        $this->log_activity('set_elementor', array('page_id' => $page_id));

        return rest_ensure_response(array('success' => true, 'page_id' => $page_id));
    }

    private function set_elementor_data($page_id, $data) {
        update_post_meta($page_id, '_elementor_data', $data);
        update_post_meta($page_id, '_elementor_edit_mode', 'builder');
        update_post_meta($page_id, '_elementor_template_type', 'wp-page');
        update_post_meta($page_id, '_elementor_version', defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : '3.0.0');

        // Clear Elementor cache
        if (class_exists('\Elementor\Plugin')) {
            \Elementor\Plugin::$instance->files_manager->clear_cache();
        }
    }

    public function list_elementor_templates($request) {
        if (!defined('ELEMENTOR_VERSION')) {
            return new WP_Error('elementor_not_active', 'Elementor is not active', array('status' => 400));
        }

        $type = $request->get_param('type');

        $args = array(
            'post_type' => 'elementor_library',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        );

        if ($type && $type !== 'all') {
            $args['meta_query'] = array(
                array(
                    'key' => '_elementor_template_type',
                    'value' => $type,
                ),
            );
        }

        $templates = get_posts($args);
        $formatted = array();

        foreach ($templates as $template) {
            $formatted[] = array(
                'id' => $template->ID,
                'title' => $template->post_title,
                'type' => get_post_meta($template->ID, '_elementor_template_type', true),
                'date' => $template->post_date,
                'thumbnail' => get_the_post_thumbnail_url($template->ID, 'medium'),
            );
        }

        return rest_ensure_response($formatted);
    }

    public function apply_elementor_template($request) {
        $page_id = $request->get_param('id');
        $template_id = $request->get_param('template_id');
        $mode = $request->get_param('mode') ?: 'replace';

        if (!get_post($page_id)) {
            return new WP_Error('not_found', 'Page not found', array('status' => 404));
        }

        $template_data = get_post_meta($template_id, '_elementor_data', true);
        if (!$template_data) {
            return new WP_Error('template_not_found', 'Template not found or has no data', array('status' => 404));
        }

        if ($mode === 'replace') {
            $this->set_elementor_data($page_id, $template_data);
        } else {
            $existing_data = get_post_meta($page_id, '_elementor_data', true);
            $existing = $existing_data ? json_decode($existing_data, true) : array();
            $template = json_decode($template_data, true);

            if (!is_array($template)) {
                return new WP_Error('invalid_template', 'Template data is invalid', array('status' => 400));
            }

            if ($mode === 'prepend') {
                $merged = array_merge($template, $existing);
            } else { // append
                $merged = array_merge($existing, $template);
            }

            $this->set_elementor_data($page_id, json_encode($merged));
        }

        $this->log_activity('apply_elementor_template', array('page_id' => $page_id, 'template_id' => $template_id, 'mode' => $mode));

        return rest_ensure_response(array('success' => true, 'page_id' => $page_id));
    }

    public function create_landing_page($request) {
        $title = sanitize_text_field($request->get_param('title'));
        $headline = sanitize_text_field($request->get_param('headline'));
        $subheadline = sanitize_text_field($request->get_param('subheadline') ?: '');
        $cta_text = sanitize_text_field($request->get_param('cta_text'));
        $cta_url = esc_url_raw($request->get_param('cta_url'));
        $hero_image_id = intval($request->get_param('hero_image_id') ?: 0);
        $features = $request->get_param('features') ?: array();
        $testimonials = $request->get_param('testimonials') ?: array();
        $colors = $request->get_param('colors') ?: array();

        // Create page
        $page_id = wp_insert_post(array(
            'post_title' => $title,
            'post_type' => 'page',
            'post_status' => 'draft',
            'post_author' => get_current_user_id() ?: 1,
        ));

        if (is_wp_error($page_id)) {
            return $page_id;
        }

        // Build Elementor data
        $sections = array();

        // Hero section
        $sections[] = $this->create_hero_section($headline, $subheadline, $cta_text, $cta_url, $hero_image_id, $colors);

        // Features section
        if (!empty($features)) {
            $sections[] = $this->create_features_section($features, $colors);
        }

        // Testimonials section
        if (!empty($testimonials)) {
            $sections[] = $this->create_testimonials_section($testimonials, $colors);
        }

        // CTA section
        $sections[] = $this->create_cta_section($cta_text, $cta_url, $colors);

        $this->set_elementor_data($page_id, json_encode($sections));

        $this->log_activity('create_landing_page', array('page_id' => $page_id));

        return rest_ensure_response(array(
            'success' => true,
            'page_id' => $page_id,
            'url' => get_permalink($page_id),
            'edit_url' => admin_url('post.php?post=' . $page_id . '&action=elementor'),
        ));
    }

    private function create_hero_section($headline, $subheadline, $cta_text, $cta_url, $image_id, $colors) {
        $bg_color = $colors['primary'] ?? '#1a1a2e';
        $text_color = $colors['text'] ?? '#ffffff';

        return array(
            'id' => $this->generate_element_id(),
            'elType' => 'section',
            'settings' => array(
                'structure' => '10',
                'min_height' => array('unit' => 'vh', 'size' => 100),
                'background_background' => $image_id ? 'classic' : 'color',
                'background_color' => $bg_color,
                'background_image' => $image_id ? array('url' => wp_get_attachment_url($image_id), 'id' => $image_id) : null,
                'background_overlay_color' => 'rgba(0,0,0,0.5)',
                'content_position' => 'middle',
            ),
            'elements' => array(
                array(
                    'id' => $this->generate_element_id(),
                    'elType' => 'column',
                    'settings' => array('_column_size' => 100),
                    'elements' => array(
                        array(
                            'id' => $this->generate_element_id(),
                            'elType' => 'widget',
                            'widgetType' => 'heading',
                            'settings' => array(
                                'title' => $headline,
                                'align' => 'center',
                                'title_color' => $text_color,
                                'typography_font_size' => array('unit' => 'px', 'size' => 48),
                                'typography_font_weight' => '700',
                            ),
                        ),
                        array(
                            'id' => $this->generate_element_id(),
                            'elType' => 'widget',
                            'widgetType' => 'text-editor',
                            'settings' => array(
                                'editor' => '<p style="text-align:center;color:' . $text_color . '">' . $subheadline . '</p>',
                            ),
                        ),
                        array(
                            'id' => $this->generate_element_id(),
                            'elType' => 'widget',
                            'widgetType' => 'button',
                            'settings' => array(
                                'text' => $cta_text,
                                'link' => array('url' => $cta_url),
                                'align' => 'center',
                                'button_background_color' => $colors['secondary'] ?? '#e94560',
                                'button_text_color' => '#ffffff',
                                'border_radius' => array('unit' => 'px', 'size' => 5),
                                'typography_font_size' => array('unit' => 'px', 'size' => 18),
                            ),
                        ),
                    ),
                ),
            ),
        );
    }

    private function create_features_section($features, $colors) {
        $columns = array();
        $column_count = min(count($features), 4);
        $column_size = floor(100 / $column_count);

        foreach (array_slice($features, 0, 4) as $feature) {
            $columns[] = array(
                'id' => $this->generate_element_id(),
                'elType' => 'column',
                'settings' => array('_column_size' => $column_size),
                'elements' => array(
                    array(
                        'id' => $this->generate_element_id(),
                        'elType' => 'widget',
                        'widgetType' => 'icon-box',
                        'settings' => array(
                            'selected_icon' => array('value' => $feature['icon'] ?? 'fas fa-star', 'library' => 'fa-solid'),
                            'title_text' => $feature['title'] ?? '',
                            'description_text' => $feature['description'] ?? '',
                            'position' => 'top',
                            'primary_color' => $colors['primary'] ?? '#1a1a2e',
                        ),
                    ),
                ),
            );
        }

        return array(
            'id' => $this->generate_element_id(),
            'elType' => 'section',
            'settings' => array(
                'structure' => $this->get_column_structure($column_count),
                'padding' => array('unit' => 'px', 'top' => '80', 'bottom' => '80'),
                'background_color' => $colors['background'] ?? '#ffffff',
            ),
            'elements' => $columns,
        );
    }

    private function create_testimonials_section($testimonials, $colors) {
        $columns = array();
        $column_count = min(count($testimonials), 3);
        $column_size = floor(100 / $column_count);

        foreach (array_slice($testimonials, 0, 3) as $testimonial) {
            $columns[] = array(
                'id' => $this->generate_element_id(),
                'elType' => 'column',
                'settings' => array('_column_size' => $column_size),
                'elements' => array(
                    array(
                        'id' => $this->generate_element_id(),
                        'elType' => 'widget',
                        'widgetType' => 'testimonial',
                        'settings' => array(
                            'testimonial_content' => $testimonial['quote'] ?? '',
                            'testimonial_name' => $testimonial['author'] ?? '',
                            'testimonial_job' => $testimonial['role'] ?? '',
                            'testimonial_image' => $testimonial['image_id'] ? array('url' => wp_get_attachment_url($testimonial['image_id']), 'id' => $testimonial['image_id']) : null,
                        ),
                    ),
                ),
            );
        }

        return array(
            'id' => $this->generate_element_id(),
            'elType' => 'section',
            'settings' => array(
                'structure' => $this->get_column_structure($column_count),
                'padding' => array('unit' => 'px', 'top' => '80', 'bottom' => '80'),
                'background_color' => '#f8f9fa',
            ),
            'elements' => $columns,
        );
    }

    private function create_cta_section($cta_text, $cta_url, $colors) {
        return array(
            'id' => $this->generate_element_id(),
            'elType' => 'section',
            'settings' => array(
                'structure' => '10',
                'padding' => array('unit' => 'px', 'top' => '60', 'bottom' => '60'),
                'background_color' => $colors['primary'] ?? '#1a1a2e',
            ),
            'elements' => array(
                array(
                    'id' => $this->generate_element_id(),
                    'elType' => 'column',
                    'settings' => array('_column_size' => 100),
                    'elements' => array(
                        array(
                            'id' => $this->generate_element_id(),
                            'elType' => 'widget',
                            'widgetType' => 'heading',
                            'settings' => array(
                                'title' => 'Ready to Get Started?',
                                'align' => 'center',
                                'title_color' => '#ffffff',
                                'typography_font_size' => array('unit' => 'px', 'size' => 32),
                            ),
                        ),
                        array(
                            'id' => $this->generate_element_id(),
                            'elType' => 'widget',
                            'widgetType' => 'button',
                            'settings' => array(
                                'text' => $cta_text,
                                'link' => array('url' => $cta_url),
                                'align' => 'center',
                                'button_background_color' => $colors['secondary'] ?? '#e94560',
                                'button_text_color' => '#ffffff',
                            ),
                        ),
                    ),
                ),
            ),
        );
    }

    private function get_column_structure($count) {
        $structures = array(1 => '10', 2 => '20', 3 => '30', 4 => '40');
        return $structures[$count] ?? '30';
    }

    public function add_elementor_section($request) {
        $page_id = $request->get_param('id');
        $section_type = $request->get_param('section_type');
        $position = $request->get_param('position') ?: 'end';
        $content = $request->get_param('content') ?: array();

        if (!get_post($page_id)) {
            return new WP_Error('not_found', 'Page not found', array('status' => 404));
        }

        $existing_data = get_post_meta($page_id, '_elementor_data', true);
        $sections = $existing_data ? json_decode($existing_data, true) : array();

        if (!is_array($sections)) {
            $sections = array();
        }

        // Create section based on type
        $new_section = null;
        switch ($section_type) {
            case 'hero':
                $new_section = $this->create_hero_section(
                    $content['headline'] ?? 'Your Headline',
                    $content['subheadline'] ?? '',
                    $content['cta_text'] ?? 'Get Started',
                    $content['cta_url'] ?? '#',
                    $content['image_id'] ?? 0,
                    $content['colors'] ?? array()
                );
                break;

            case 'text':
                $new_section = array(
                    'id' => $this->generate_element_id(),
                    'elType' => 'section',
                    'settings' => array('padding' => array('unit' => 'px', 'top' => '40', 'bottom' => '40')),
                    'elements' => array(
                        array(
                            'id' => $this->generate_element_id(),
                            'elType' => 'column',
                            'settings' => array('_column_size' => 100),
                            'elements' => array(
                                array(
                                    'id' => $this->generate_element_id(),
                                    'elType' => 'widget',
                                    'widgetType' => 'text-editor',
                                    'settings' => array('editor' => $content['text'] ?? ''),
                                ),
                            ),
                        ),
                    ),
                );
                break;

            case 'custom':
                // Allow raw Elementor JSON
                $new_section = $content;
                if (!isset($new_section['id'])) {
                    $new_section['id'] = $this->generate_element_id();
                }
                break;

            default:
                return new WP_Error('invalid_section_type', 'Unknown section type', array('status' => 400));
        }

        // Insert at position
        if ($position === 'start') {
            array_unshift($sections, $new_section);
        } elseif ($position === 'after' && $request->get_param('after_section_id')) {
            $after_id = $request->get_param('after_section_id');
            $inserted = false;
            foreach ($sections as $i => $section) {
                if (isset($section['id']) && $section['id'] === $after_id) {
                    array_splice($sections, $i + 1, 0, array($new_section));
                    $inserted = true;
                    break;
                }
            }
            if (!$inserted) {
                $sections[] = $new_section;
            }
        } else {
            $sections[] = $new_section;
        }

        $this->set_elementor_data($page_id, json_encode($sections));
        $this->log_activity('add_elementor_section', array('page_id' => $page_id, 'type' => $section_type));

        return rest_ensure_response(array(
            'success' => true,
            'page_id' => $page_id,
            'section_id' => $new_section['id'],
        ));
    }

    public function update_elementor_widget($request) {
        $page_id = $request->get_param('id');
        $widget_id = $request->get_param('widget_id');
        $settings = $request->get_param('settings');

        if (!get_post($page_id)) {
            return new WP_Error('not_found', 'Page not found', array('status' => 404));
        }

        $elementor_data = get_post_meta($page_id, '_elementor_data', true);
        if (!$elementor_data) {
            return new WP_Error('no_elementor_data', 'Page has no Elementor data', array('status' => 400));
        }

        $data = json_decode($elementor_data, true);
        $updated = $this->update_widget_recursive($data, $widget_id, $settings);

        if (!$updated) {
            return new WP_Error('widget_not_found', 'Widget not found', array('status' => 404));
        }

        $this->set_elementor_data($page_id, json_encode($data));
        $this->log_activity('update_elementor_widget', array('page_id' => $page_id, 'widget_id' => $widget_id));

        return rest_ensure_response(array('success' => true, 'page_id' => $page_id, 'widget_id' => $widget_id));
    }

    private function update_widget_recursive(&$elements, $widget_id, $settings) {
        foreach ($elements as &$element) {
            if (isset($element['id']) && $element['id'] === $widget_id) {
                $element['settings'] = array_merge($element['settings'] ?? array(), $settings);
                return true;
            }
            if (isset($element['elements']) && is_array($element['elements'])) {
                if ($this->update_widget_recursive($element['elements'], $widget_id, $settings)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function get_elementor_globals($request) {
        if (!defined('ELEMENTOR_VERSION')) {
            return new WP_Error('elementor_not_active', 'Elementor is not active', array('status' => 400));
        }

        $kit_id = get_option('elementor_active_kit');
        $kit_settings = get_post_meta($kit_id, '_elementor_page_settings', true);

        $globals = array(
            'kit_id' => $kit_id,
            'colors' => array(),
            'typography' => array(),
        );

        if (is_array($kit_settings)) {
            // Extract global colors
            if (isset($kit_settings['system_colors'])) {
                foreach ($kit_settings['system_colors'] as $color) {
                    $globals['colors'][$color['_id']] = array(
                        'title' => $color['title'] ?? '',
                        'color' => $color['color'] ?? '',
                    );
                }
            }

            // Extract global fonts
            if (isset($kit_settings['system_typography'])) {
                foreach ($kit_settings['system_typography'] as $font) {
                    $globals['typography'][$font['_id']] = array(
                        'title' => $font['title'] ?? '',
                        'typography_font_family' => $font['typography_font_family'] ?? '',
                        'typography_font_size' => $font['typography_font_size'] ?? '',
                    );
                }
            }

            // Other settings
            $globals['container_width'] = $kit_settings['container_width'] ?? null;
            $globals['space_between_widgets'] = $kit_settings['space_between_widgets'] ?? null;
        }

        return rest_ensure_response($globals);
    }

    public function clone_elementor_page($request) {
        $source_page_id = $request->get_param('id');
        $new_title = sanitize_text_field($request->get_param('new_title'));

        $source = get_post($source_page_id);
        if (!$source) {
            return new WP_Error('not_found', 'Source page not found', array('status' => 404));
        }

        // Create new page
        $new_page_id = wp_insert_post(array(
            'post_title' => $new_title,
            'post_type' => $source->post_type,
            'post_status' => 'draft',
            'post_author' => get_current_user_id() ?: 1,
        ));

        if (is_wp_error($new_page_id)) {
            return $new_page_id;
        }

        // Copy all Elementor meta
        $meta_keys = array(
            '_elementor_data',
            '_elementor_edit_mode',
            '_elementor_template_type',
            '_elementor_version',
            '_elementor_page_settings',
            '_elementor_css',
        );

        foreach ($meta_keys as $key) {
            $value = get_post_meta($source_page_id, $key, true);
            if ($value) {
                update_post_meta($new_page_id, $key, $value);
            }
        }

        // Copy page template
        $template = get_post_meta($source_page_id, '_wp_page_template', true);
        if ($template) {
            update_post_meta($new_page_id, '_wp_page_template', $template);
        }

        $this->log_activity('clone_elementor_page', array('source' => $source_page_id, 'new' => $new_page_id));

        return rest_ensure_response(array(
            'success' => true,
            'page_id' => $new_page_id,
            'url' => get_permalink($new_page_id),
            'edit_url' => admin_url('post.php?post=' . $new_page_id . '&action=elementor'),
        ));
    }

    private function generate_element_id() {
        return substr(md5(uniqid(mt_rand(), true)), 0, 7);
    }

    // ==================== ADMIN UI ====================

    public function admin_menu() {
        add_management_page(
            'WP AI Operator',
            'AI Operator',
            'manage_options',
            'wp-ai-operator',
            array($this, 'admin_page')
        );
    }

    public function admin_page() {
        $capabilities = $this->get_capabilities();
        ?>
        <div class="wrap">
            <h1>🤖 WP AI Operator v<?php echo $this->version; ?></h1>
            <p>Control your WordPress site with AI (Claude Code / Claude Desktop)</p>

            <div class="card" style="max-width: 700px; padding: 20px;">
                <h2>🔑 API Configuration</h2>
                <table class="form-table">
                    <tr>
                        <th>API Key</th>
                        <td>
                            <code id="api-key" style="background: #f0f0f0; padding: 8px 12px; display: inline-block; user-select: all;"><?php echo esc_html($this->api_key); ?></code>
                            <button type="button" class="button button-small" onclick="copyApiKey()">📋 Copy</button>
                        </td>
                    </tr>
                    <tr>
                        <th>Base URL</th>
                        <td><code><?php echo esc_url(get_rest_url(null, $this->namespace . '/')); ?></code></td>
                    </tr>
                </table>
                <p>
                    <button type="button" class="button button-secondary" onclick="regenerateKey()">🔄 Regenerate API Key</button>
                </p>
            </div>

            <div class="card" style="max-width: 700px; padding: 20px; margin-top: 20px;">
                <h2>📦 Detected Capabilities</h2>
                <table class="widefat striped">
                    <thead>
                        <tr><th>Plugin</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($capabilities as $plugin => $active): ?>
                        <tr>
                            <td><?php echo esc_html(ucfirst(str_replace('_', ' ', $plugin))); ?></td>
                            <td><?php echo $active ? '✅ Active' : '❌ Not Active'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card" style="max-width: 700px; padding: 20px; margin-top: 20px;">
                <h2>📡 API Endpoints</h2>
                <details>
                    <summary><strong>Core</strong> - Posts, Pages, Media</summary>
                    <ul style="margin-left: 20px;">
                        <li><code>GET/POST /posts</code> - List/create posts</li>
                        <li><code>GET/PUT/DELETE /posts/{id}</code> - Single post operations</li>
                        <li><code>GET/POST /pages</code> - List/create pages</li>
                        <li><code>GET/PUT /pages/{id}</code> - Single page operations</li>
                        <li><code>POST /media</code> - Upload media (multipart)</li>
                        <li><code>POST /media/from-url</code> - Upload from URL</li>
                        <li><code>GET /drafts</code> - List drafts</li>
                        <li><code>DELETE /drafts/delete-all</code> - Bulk delete</li>
                        <li><code>GET /site-info</code> - Site information</li>
                        <li><code>GET /analytics</code> - Activity analytics</li>
                        <li><code>GET /plugins</code> - Detect plugins</li>
                    </ul>
                </details>
                <details>
                    <summary><strong>SEO</strong> - Yoast, RankMath, AIOSEO</summary>
                    <ul style="margin-left: 20px;">
                        <li><code>GET/POST /seo/{id}</code> - Get/set SEO data</li>
                        <li><code>GET /seo/{id}/analyze</code> - Analyze SEO score</li>
                        <li><code>POST /seo/bulk</code> - Bulk SEO update</li>
                        <li><code>GET /seo/plugin</code> - Detect SEO plugin</li>
                    </ul>
                </details>
                <details>
                    <summary><strong>Forms</strong> - CF7, WPForms, Gravity Forms</summary>
                    <ul style="margin-left: 20px;">
                        <li><code>GET /forms</code> - List all forms</li>
                        <li><code>POST /forms</code> - Create form</li>
                        <li><code>GET/PUT/DELETE /forms/{id}</code> - Form operations</li>
                        <li><code>GET /forms/{id}/submissions</code> - Get entries</li>
                        <li><code>GET /forms/plugins</code> - Detect form plugins</li>
                    </ul>
                </details>
                <details>
                    <summary><strong>Elementor</strong> - Page Builder</summary>
                    <ul style="margin-left: 20px;">
                        <li><code>GET/POST /elementor/{id}</code> - Get/set page data</li>
                        <li><code>GET /elementor/templates</code> - List templates</li>
                        <li><code>POST /elementor/{id}/apply-template</code> - Apply template</li>
                        <li><code>POST /elementor/landing-page</code> - Create landing page</li>
                        <li><code>POST /elementor/{id}/sections</code> - Add section</li>
                        <li><code>PUT /elementor/{id}/widgets/{widget_id}</code> - Update widget</li>
                        <li><code>GET /elementor/globals</code> - Global colors/fonts</li>
                        <li><code>POST /elementor/{id}/clone</code> - Clone page</li>
                    </ul>
                </details>
            </div>

            <div class="card" style="max-width: 700px; padding: 20px; margin-top: 20px;">
                <h2>🧪 Quick Test</h2>
                <pre style="background: #1e1e1e; color: #d4d4d4; padding: 15px; overflow-x: auto; border-radius: 4px;">curl -s "<?php echo get_rest_url(null, $this->namespace . '/site-info'); ?>" \
  -H "X-API-Key: <?php echo esc_html($this->api_key); ?>"</pre>
            </div>
        </div>

        <script>
        function copyApiKey() {
            const key = document.getElementById('api-key').textContent;
            navigator.clipboard.writeText(key).then(() => {
                alert('API key copied to clipboard!');
            });
        }
        function regenerateKey() {
            if (confirm('Are you sure? This will invalidate the current API key.')) {
                location.href = '<?php echo admin_url('admin.php?page=wp-ai-operator&regenerate=1'); ?>';
            }
        }
        </script>
        <?php

        if (isset($_GET['regenerate']) && $_GET['regenerate'] == '1') {
            $this->generate_api_key();
            echo '<script>alert("API key regenerated!"); location.href = "' . admin_url('admin.php?page=wp-ai-operator') . '";</script>';
        }
    }

    private function log_activity($action, $data = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpaio_analytics';

        $wpdb->insert(
            $table_name,
            array(
                'action' => $action,
                'data' => json_encode($data),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            )
        );
    }
}

// Initialize
new WPAIOperator();
