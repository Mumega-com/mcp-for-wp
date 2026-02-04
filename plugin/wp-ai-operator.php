<?php
/**
 * Plugin Name: WP AI Operator
 * Plugin URI: https://github.com/Digidinc/wp-ai-operator
 * Description: Control your WordPress site with AI (Claude Code / Claude Desktop). REST API for content, pages, Elementor, SEO, and media.
 * Version: 1.0.0
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
    private $version = '1.0.0';
    private $update_server = 'https://github.com/Digidinc/wp-ai-operator/releases/latest';

    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('rest_api_init', array($this, 'register_api_routes'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

        // Auto-update hooks
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_updates'));
        add_filter('plugins_api', array($this, 'plugin_info'), 20, 3);

        // Generate API key on activation
        register_activation_hook(__FILE__, array($this, 'activate_plugin'));
    }

    public function init() {
        $this->api_key = get_option('digid_api_key', '');
        if (empty($this->api_key)) {
            $this->generate_api_key();
        }
    }

    public function activate_plugin() {
        $this->generate_api_key();

        // Create custom database table for analytics
        global $wpdb;
        $table_name = $wpdb->prefix . 'digid_analytics';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            action varchar(100) NOT NULL,
            data longtext,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            ip_address varchar(45),
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private function generate_api_key() {
        $this->api_key = 'digid_' . wp_generate_password(32, false);
        update_option('digid_api_key', $this->api_key);
    }

    // AUTO-UPDATE SYSTEM
    public function check_for_updates($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $plugin_slug = plugin_basename(__FILE__);
        $plugin_data = get_plugin_data(__FILE__);
        $current_version = $plugin_data['Version'];

        $remote_version = $this->get_remote_version();

        if (version_compare($current_version, $remote_version, '<')) {
            $transient->response[$plugin_slug] = (object) array(
                'slug' => $plugin_slug,
                'new_version' => $remote_version,
                'url' => $this->update_server,
                'package' => $this->update_server . '/download-plugin'
            );
        }

        return $transient;
    }

    private function get_remote_version() {
        $request = wp_remote_get($this->update_server . '/version-check');

        if (!is_wp_error($request) && wp_remote_retrieve_response_code($request) === 200) {
            $body = wp_remote_retrieve_body($request);
            $data = json_decode($body, true);
            return $data['version'] ?? $this->version;
        }

        return $this->version;
    }

    public function plugin_info($result, $action, $args) {
        if ($action !== 'plugin_information') {
            return $result;
        }

        if (!isset($args->slug) || $args->slug !== plugin_basename(__FILE__)) {
            return $result;
        }

        $request = wp_remote_get($this->update_server . '/plugin-info');

        if (!is_wp_error($request) && wp_remote_retrieve_response_code($request) === 200) {
            $body = wp_remote_retrieve_body($request);
            return json_decode($body);
        }

        return $result;
    }

    public function register_api_routes() {
        // Posts management
        register_rest_route('digid/v1', '/posts', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_posts'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        register_rest_route('digid/v1', '/posts', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_post'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        register_rest_route('digid/v1', '/posts/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_post'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        register_rest_route('digid/v1', '/posts/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_post'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        // Pages management
        register_rest_route('digid/v1', '/pages', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_pages'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        register_rest_route('digid/v1', '/pages', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_page'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        register_rest_route('digid/v1', '/pages/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_page'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        // Elementor management
        register_rest_route('digid/v1', '/elementor/(?P<id>\d+)', array(
            'methods' => 'GET,POST',
            'callback' => array($this, 'manage_elementor'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        // Plugin update endpoint
        register_rest_route('digid/v1', '/plugin-update', array(
            'methods' => 'POST',
            'callback' => array($this, 'remote_update_plugin'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        // Drafts management
        register_rest_route('digid/v1', '/drafts', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_drafts'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        register_rest_route('digid/v1', '/drafts/delete-all', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_all_drafts'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        // Media management
        register_rest_route('digid/v1', '/media', array(
            'methods' => 'POST',
            'callback' => array($this, 'upload_media'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        // SEO management
        register_rest_route('digid/v1', '/seo/(?P<id>\d+)', array(
            'methods' => 'GET,POST',
            'callback' => array($this, 'manage_seo'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        // Analytics
        register_rest_route('digid/v1', '/analytics', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_analytics'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        // Site info
        register_rest_route('digid/v1', '/site-info', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_site_info'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));
    }

    public function verify_api_key($request) {
        $provided_key = $request->get_header('X-API-Key');

        if (empty($provided_key)) {
            $provided_key = $request->get_param('api_key');
        }

        $this->log_activity('api_access_attempt', array(
            'provided_key' => substr($provided_key, 0, 10) . '...',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ));

        return $provided_key === $this->api_key;
    }

    // PAGES METHODS
    public function get_pages($request) {
        $args = array(
            'post_type' => 'page',
            'post_status' => array('publish', 'draft'),
            'posts_per_page' => $request->get_param('per_page') ?: 10,
            'paged' => $request->get_param('page') ?: 1,
        );

        $pages = get_posts($args);
        $formatted_pages = array();

        foreach ($pages as $page) {
            $formatted_pages[] = array(
                'id' => $page->ID,
                'title' => $page->post_title,
                'content' => $page->post_content,
                'status' => $page->post_status,
                'date' => $page->post_date,
                'modified' => $page->post_modified,
                'slug' => $page->post_name,
                'url' => get_permalink($page->ID),
                'template' => get_page_template_slug($page->ID),
                'elementor_data' => get_post_meta($page->ID, '_elementor_data', true),
                'elementor_edit_mode' => get_post_meta($page->ID, '_elementor_edit_mode', true),
            );
        }

        $this->log_activity('get_pages', array('count' => count($formatted_pages)));

        return rest_ensure_response($formatted_pages);
    }

    public function create_page($request) {
        $page_data = array(
            'post_title' => sanitize_text_field($request->get_param('title')),
            'post_content' => wp_kses_post($request->get_param('content')),
            'post_status' => in_array($request->get_param('status'), array('publish', 'draft', 'private')) ? $request->get_param('status') : 'draft',
            'post_type' => 'page',
            'post_author' => 1,
        );

        $page_id = wp_insert_post($page_data);

        if (is_wp_error($page_id)) {
            return new WP_Error('page_creation_failed', $page_id->get_error_message(), array('status' => 500));
        }

        if ($request->get_param('template')) {
            update_post_meta($page_id, '_wp_page_template', $request->get_param('template'));
        }

        if ($request->get_param('elementor_data')) {
            update_post_meta($page_id, '_elementor_data', $request->get_param('elementor_data'));
            update_post_meta($page_id, '_elementor_edit_mode', 'builder');
            update_post_meta($page_id, '_elementor_template_type', 'wp-page');
            update_post_meta($page_id, '_elementor_version', '3.0.0');
        }

        $this->log_activity('create_page', array('page_id' => $page_id, 'title' => $page_data['post_title']));

        return rest_ensure_response(array(
            'success' => true,
            'page_id' => $page_id,
            'url' => get_permalink($page_id),
            'edit_url' => get_edit_post_link($page_id, 'raw')
        ));
    }

    public function update_page($request) {
        $page_id = $request->get_param('id');

        if (!get_post($page_id) || get_post_type($page_id) !== 'page') {
            return new WP_Error('page_not_found', 'Page not found', array('status' => 404));
        }

        $page_data = array('ID' => $page_id);

        if ($request->get_param('title')) {
            $page_data['post_title'] = sanitize_text_field($request->get_param('title'));
        }

        if ($request->get_param('content')) {
            $page_data['post_content'] = wp_kses_post($request->get_param('content'));
        }

        if ($request->get_param('status')) {
            $page_data['post_status'] = in_array($request->get_param('status'), array('publish', 'draft', 'private')) ? $request->get_param('status') : 'draft';
        }

        $result = wp_update_post($page_data);

        if (is_wp_error($result)) {
            return new WP_Error('page_update_failed', $result->get_error_message(), array('status' => 500));
        }

        if ($request->get_param('elementor_data')) {
            update_post_meta($page_id, '_elementor_data', $request->get_param('elementor_data'));
        }

        $this->log_activity('update_page', array('page_id' => $page_id));

        return rest_ensure_response(array(
            'success' => true,
            'page_id' => $page_id,
            'url' => get_permalink($page_id)
        ));
    }

    public function manage_elementor($request) {
        $page_id = $request->get_param('id');

        if (!get_post($page_id)) {
            return new WP_Error('page_not_found', 'Page not found', array('status' => 404));
        }

        if ($request->get_method() === 'POST') {
            $elementor_data = $request->get_param('elementor_data');
            if ($elementor_data) {
                update_post_meta($page_id, '_elementor_data', $elementor_data);
                update_post_meta($page_id, '_elementor_edit_mode', 'builder');

                $this->log_activity('update_elementor', array('page_id' => $page_id));

                return rest_ensure_response(array('success' => true, 'page_id' => $page_id));
            }
        } else {
            $elementor_data = array(
                'page_id' => $page_id,
                'elementor_data' => get_post_meta($page_id, '_elementor_data', true),
                'edit_mode' => get_post_meta($page_id, '_elementor_edit_mode', true),
                'template_type' => get_post_meta($page_id, '_elementor_template_type', true),
            );

            return rest_ensure_response($elementor_data);
        }

        return new WP_Error('invalid_request', 'Invalid request', array('status' => 400));
    }

    // REMOTE UPDATE METHOD
    public function remote_update_plugin($request) {
        $plugin_url = $request->get_param('plugin_url');

        if (empty($plugin_url)) {
            return new WP_Error('missing_url', 'Plugin URL is required', array('status' => 400));
        }

        $temp_file = download_url($plugin_url);

        if (is_wp_error($temp_file)) {
            return $temp_file;
        }

        require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');

        $upgrader = new Plugin_Upgrader();
        $result = $upgrader->install($temp_file);

        if (is_wp_error($result)) {
            return $result;
        }

        $plugin_file = plugin_basename(__FILE__);
        activate_plugin($plugin_file);

        $this->log_activity('plugin_updated', array('version' => $this->version));

        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Plugin updated successfully',
            'version' => $this->version
        ));
    }

    // POSTS METHODS
    public function get_posts($request) {
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $request->get_param('per_page') ?: 10,
            'paged' => $request->get_param('page') ?: 1,
        );

        $posts = get_posts($args);
        $formatted_posts = array();

        foreach ($posts as $post) {
            $formatted_posts[] = array(
                'id' => $post->ID,
                'title' => $post->post_title,
                'content' => $post->post_content,
                'excerpt' => $post->post_excerpt,
                'status' => $post->post_status,
                'date' => $post->post_date,
                'modified' => $post->post_modified,
                'slug' => $post->post_name,
                'url' => get_permalink($post->ID),
                'categories' => wp_get_post_categories($post->ID),
                'tags' => wp_get_post_tags($post->ID),
                'featured_image' => get_the_post_thumbnail_url($post->ID, 'full'),
            );
        }

        $this->log_activity('get_posts', array('count' => count($formatted_posts)));

        return rest_ensure_response($formatted_posts);
    }

    public function create_post($request) {
        $post_data = array(
            'post_title' => sanitize_text_field($request->get_param('title')),
            'post_content' => wp_kses_post($request->get_param('content')),
            'post_excerpt' => sanitize_textarea_field($request->get_param('excerpt')),
            'post_status' => in_array($request->get_param('status'), array('publish', 'draft', 'private')) ? $request->get_param('status') : 'draft',
            'post_type' => 'post',
            'post_author' => 1,
        );

        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {
            return new WP_Error('post_creation_failed', $post_id->get_error_message(), array('status' => 500));
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

        $this->log_activity('create_post', array('post_id' => $post_id, 'title' => $post_data['post_title']));

        return rest_ensure_response(array(
            'success' => true,
            'post_id' => $post_id,
            'url' => get_permalink($post_id),
            'edit_url' => get_edit_post_link($post_id, 'raw')
        ));
    }

    public function update_post($request) {
        $post_id = $request->get_param('id');

        if (!get_post($post_id)) {
            return new WP_Error('post_not_found', 'Post not found', array('status' => 404));
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
            $post_data['post_status'] = in_array($request->get_param('status'), array('publish', 'draft', 'private')) ? $request->get_param('status') : 'draft';
        }

        $result = wp_update_post($post_data);

        if (is_wp_error($result)) {
            return new WP_Error('post_update_failed', $result->get_error_message(), array('status' => 500));
        }

        $this->log_activity('update_post', array('post_id' => $post_id));

        return rest_ensure_response(array(
            'success' => true,
            'post_id' => $post_id,
            'url' => get_permalink($post_id)
        ));
    }

    public function delete_post($request) {
        $post_id = $request->get_param('id');

        if (!get_post($post_id)) {
            return new WP_Error('post_not_found', 'Post not found', array('status' => 404));
        }

        $force_delete = $request->get_param('force') === 'true';
        $result = wp_delete_post($post_id, $force_delete);

        if (!$result) {
            return new WP_Error('post_deletion_failed', 'Failed to delete post', array('status' => 500));
        }

        $this->log_activity('delete_post', array('post_id' => $post_id, 'force' => $force_delete));

        return rest_ensure_response(array(
            'success' => true,
            'post_id' => $post_id,
            'action' => $force_delete ? 'permanently_deleted' : 'moved_to_trash'
        ));
    }

    public function get_drafts($request) {
        $args = array(
            'post_type' => 'post',
            'post_status' => 'draft',
            'posts_per_page' => -1,
        );

        $drafts = get_posts($args);
        $formatted_drafts = array();

        foreach ($drafts as $draft) {
            $formatted_drafts[] = array(
                'id' => $draft->ID,
                'title' => $draft->post_title,
                'content' => $draft->post_content,
                'date' => $draft->post_date,
                'modified' => $draft->post_modified,
                'author' => get_the_author_meta('display_name', $draft->post_author),
            );
        }

        $this->log_activity('get_drafts', array('count' => count($formatted_drafts)));

        return rest_ensure_response($formatted_drafts);
    }

    public function delete_all_drafts($request) {
        $args = array(
            'post_type' => 'post',
            'post_status' => 'draft',
            'posts_per_page' => -1,
        );

        $drafts = get_posts($args);
        $deleted_count = 0;

        foreach ($drafts as $draft) {
            if (wp_delete_post($draft->ID, true)) {
                $deleted_count++;
            }
        }

        $this->log_activity('delete_all_drafts', array('deleted_count' => $deleted_count, 'total_drafts' => count($drafts)));

        return rest_ensure_response(array(
            'success' => true,
            'deleted_count' => $deleted_count,
            'total_drafts' => count($drafts)
        ));
    }

    public function upload_media($request) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $files = $request->get_file_params();

        if (empty($files['file'])) {
            return new WP_Error('no_file', 'No file provided', array('status' => 400));
        }

        $file = $files['file'];
        $attachment_id = media_handle_upload('file', 0);

        if (is_wp_error($attachment_id)) {
            return new WP_Error('upload_failed', $attachment_id->get_error_message(), array('status' => 500));
        }

        $this->log_activity('upload_media', array('attachment_id' => $attachment_id, 'filename' => $file['name']));

        return rest_ensure_response(array(
            'success' => true,
            'attachment_id' => $attachment_id,
            'url' => wp_get_attachment_url($attachment_id),
            'filename' => get_the_title($attachment_id)
        ));
    }

    public function manage_seo($request) {
        $post_id = $request->get_param('id');

        if (!get_post($post_id)) {
            return new WP_Error('post_not_found', 'Post not found', array('status' => 404));
        }

        if ($request->get_method() === 'POST') {
            $seo_title = sanitize_text_field($request->get_param('seo_title'));
            $seo_description = sanitize_textarea_field($request->get_param('seo_description'));
            $focus_keyword = sanitize_text_field($request->get_param('focus_keyword'));

            update_post_meta($post_id, '_digid_seo_title', $seo_title);
            update_post_meta($post_id, '_digid_seo_description', $seo_description);
            update_post_meta($post_id, '_digid_focus_keyword', $focus_keyword);

            // Also update Yoast if available
            if (defined('WPSEO_VERSION')) {
                update_post_meta($post_id, '_yoast_wpseo_title', $seo_title);
                update_post_meta($post_id, '_yoast_wpseo_metadesc', $seo_description);
                update_post_meta($post_id, '_yoast_wpseo_focuskw', $focus_keyword);
            }

            // Also update RankMath if available
            if (class_exists('RankMath')) {
                update_post_meta($post_id, 'rank_math_title', $seo_title);
                update_post_meta($post_id, 'rank_math_description', $seo_description);
                update_post_meta($post_id, 'rank_math_focus_keyword', $focus_keyword);
            }

            $this->log_activity('update_seo', array('post_id' => $post_id));

            return rest_ensure_response(array('success' => true, 'post_id' => $post_id));
        } else {
            $seo_data = array(
                'post_id' => $post_id,
                'seo_title' => get_post_meta($post_id, '_digid_seo_title', true),
                'seo_description' => get_post_meta($post_id, '_digid_seo_description', true),
                'focus_keyword' => get_post_meta($post_id, '_digid_focus_keyword', true),
            );

            return rest_ensure_response($seo_data);
        }
    }

    public function get_analytics($request) {
        global $wpdb;

        $days = intval($request->get_param('days')) ?: 30;
        $table_name = $wpdb->prefix . 'digid_analytics';

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT action, COUNT(*) as count, DATE(timestamp) as date
             FROM $table_name
             WHERE timestamp >= DATE_SUB(NOW(), INTERVAL %d DAY)
             GROUP BY action, DATE(timestamp)
             ORDER BY timestamp DESC",
            $days
        ));

        $analytics = array(
            'period' => $days . ' days',
            'total_posts' => wp_count_posts()->publish,
            'total_drafts' => wp_count_posts()->draft,
            'total_pages' => wp_count_posts('page')->publish,
            'activity' => $results,
            'plugin_version' => $this->version,
        );

        return rest_ensure_response($analytics);
    }

    public function get_site_info($request) {
        $site_info = array(
            'site_name' => get_bloginfo('name'),
            'site_url' => get_site_url(),
            'admin_email' => get_option('admin_email'),
            'wordpress_version' => get_bloginfo('version'),
            'theme' => wp_get_theme()->get('Name'),
            'active_plugins' => count(get_option('active_plugins', array())),
            'post_count' => wp_count_posts()->publish,
            'page_count' => wp_count_posts('page')->publish,
            'user_count' => count_users()['total_users'],
            'plugin_version' => $this->version,
            'has_elementor' => defined('ELEMENTOR_VERSION'),
            'elementor_version' => defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : null,
            'has_woocommerce' => class_exists('WooCommerce'),
        );

        return rest_ensure_response($site_info);
    }

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
        ?>
        <div class="wrap">
            <h1>WP AI Operator v<?php echo $this->version; ?></h1>
            <p>Control your WordPress site with AI (Claude Code / Claude Desktop)</p>

            <div class="card" style="max-width: 600px; padding: 20px;">
                <h2>API Configuration</h2>
                <table class="form-table">
                    <tr>
                        <th>API Key</th>
                        <td><code style="background: #f0f0f0; padding: 5px 10px;"><?php echo esc_html($this->api_key); ?></code></td>
                    </tr>
                    <tr>
                        <th>Base URL</th>
                        <td><code><?php echo esc_url(get_rest_url(null, 'digid/v1/')); ?></code></td>
                    </tr>
                </table>
                <p>
                    <button type="button" class="button button-secondary" onclick="regenerateKey()">Regenerate API Key</button>
                </p>
            </div>

            <div class="card" style="max-width: 600px; padding: 20px; margin-top: 20px;">
                <h2>Available Endpoints</h2>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li><code>GET/POST /posts</code> - Manage posts</li>
                    <li><code>PUT/DELETE /posts/{id}</code> - Update/delete post</li>
                    <li><code>GET/POST /pages</code> - Manage pages</li>
                    <li><code>PUT /pages/{id}</code> - Update page</li>
                    <li><code>GET/POST /elementor/{id}</code> - Elementor data</li>
                    <li><code>GET /drafts</code> - List drafts</li>
                    <li><code>DELETE /drafts/delete-all</code> - Bulk delete</li>
                    <li><code>POST /media</code> - Upload media</li>
                    <li><code>GET/POST /seo/{id}</code> - SEO metadata</li>
                    <li><code>GET /analytics</code> - Site analytics</li>
                    <li><code>GET /site-info</code> - Site information</li>
                </ul>
            </div>

            <div class="card" style="max-width: 600px; padding: 20px; margin-top: 20px;">
                <h2>Quick Test</h2>
                <p>Test your API connection:</p>
                <pre style="background: #1e1e1e; color: #d4d4d4; padding: 15px; overflow-x: auto;">curl -s "<?php echo get_rest_url(null, 'digid/v1/site-info'); ?>" \
  -H "X-API-Key: <?php echo esc_html($this->api_key); ?>"</pre>
            </div>
        </div>

        <script>
        function regenerateKey() {
            if (confirm('Are you sure? This will invalidate the current API key.')) {
                location.href = '<?php echo admin_url('admin.php?page=wp-ai-operator&regenerate=1'); ?>';
            }
        }
        </script>
        <?php

        if (isset($_GET['regenerate']) && $_GET['regenerate'] == '1') {
            $this->generate_api_key();
            echo '<div class="notice notice-success"><p>API key regenerated successfully!</p></div>';
            echo '<script>location.href = "' . admin_url('admin.php?page=wp-ai-operator') . '";</script>';
        }
    }

    public function enqueue_scripts() {
        // Frontend scripts if needed
    }

    private function log_activity($action, $data = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'digid_analytics';

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

// Initialize the plugin
new WPAIOperator();
