<?php
/**
 * Plugin Name: Article Importer
 * Description: Imports articles from an external server via POST request and creates WordPress posts.
 * Version: 1.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Article_Importer {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_post_import_articles', [$this, 'handle_import']);
    }

    public function add_admin_menu() {
        add_menu_page(
            'Article Importer',
            'Import Articles',
            'manage_options',
            'article-importer',
            [$this, 'import_page'],
            'dashicons-download',
            30
        );
    }

    public function import_page() {
        ?>
        <div class="wrap">
            <h1>Import Articles</h1>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="import_articles">
                <?php submit_button('Import Articles'); ?>
            </form>
        </div>
        <?php
    }

    public function handle_import() {
        // Replace with your real API URL
        $api_url = 'https://example.com/api/articles';

        $response = wp_remote_post($api_url, [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode([
                'topic' => 'technology' // Replace with your actual payload
            ])
        ]);

        if (is_wp_error($response)) {
            wp_die('Request failed: ' . $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $articles = json_decode($body, true);

        if (!is_array($articles)) {
            wp_die('Invalid response from API');
        }

        foreach ($articles as $article) {
            if (!empty($article['title']) && !empty($article['content'])) {
                wp_insert_post([
                    'post_title'   => sanitize_text_field($article['title']),
                    'post_content' => wp_kses_post($article['content']),
                    'post_status'  => 'draft',
                    'post_type'    => 'post',
                ]);
            }
        }

        wp_redirect(admin_url('admin.php?page=article-importer&import=success'));
        exit;
    }
}

new Article_Importer();
