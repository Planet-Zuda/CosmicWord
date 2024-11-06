<?php
/**
 * Plugin Name: Cosmic Code Plugin Search
 * Description: Securely embeds the Cosmic Code plugin repository search iframe
 * Version: 1.0
 * Author: CosmicWord
 * Author URI: https://cosmicword.com
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Cosmic_Plugin_Search {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', array($this, 'add_menu_page'), 11);
        add_action('admin_init', array($this, 'add_security_headers'));
        add_action('admin_menu', array($this, 'remove_add_new_plugin_submenu'), 99);
    }

    // Add the missing method that was causing the error
    public function add_security_headers() {
        // Add security headers for the iframe
        if (isset($_GET['page']) && $_GET['page'] === 'cosmic-plugin-search') {
            header('Content-Security-Policy: frame-ancestors \'self\' https://cosmicword.com');
            header('X-Frame-Options: SAMEORIGIN');
            header('X-Content-Type-Options: nosniff');
        }
    }

    public function add_menu_page() {
        add_plugins_page(
            'Cosmic Plugin Search',
            'Add New plugin Using Cosmic Search',
            'install_plugins',
            'cosmic-plugin-search',
            array($this, 'render_search_page')
        );
    }

    public function remove_add_new_plugin_submenu() {
        remove_submenu_page('plugins.php', 'plugin-install.php');
    }

    public function render_search_page() {
        ?>
        <div class="wrap">
            <h1>Cosmic Plugin Search</h1>
            <div class="cosmic-search-container" style="margin-top: 20px;">
                <iframe 
                    src="https://cosmicword.com/cosmic-code/"
                    style="width: 100%; height: 800px; border: 1px solid #ddd; border-radius: 4px;"
                    title="Cosmic Code Plugin Search"
                    sandbox="allow-same-origin allow-scripts allow-forms allow-downloads allow-popups"
                >
                </iframe>
            </div>
        </div>
        <?php
    }
}

// Initialize the plugin
function cosmic_plugin_search_init() {
    Cosmic_Plugin_Search::get_instance();
}
cosmic_plugin_search_init();