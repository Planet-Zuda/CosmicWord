<?php
// wp-includes/cosmic/cosmic-plugin-manager.php

if (!defined('ABSPATH')) {
    exit;
}

if (defined('COSMIC_PLUGIN_MANAGER_LOADED')) {
    return;
}
define('COSMIC_PLUGIN_MANAGER_LOADED', true);

class CosmicWord_Plugin_Manager {
    private static $instance = null;
    private $plugin_api_url = 'https://forkedplugin.com/slurper/plugin_metadata.json';
    private $plugin_download_base = 'https://forkedplugin.com/slurper/plugins/';

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_menu_pages'), 99);
            add_action('admin_init', array($this, 'handle_actions'));
            add_filter('install_plugins_tabs', array($this, 'modify_plugin_tabs'));
            add_filter('install_plugins_table_api_args_cosmic', array($this, 'modify_api_args'));
        }
    }

    public function modify_plugin_tabs($tabs) {
        $tabs['cosmic'] = __('CosmicWord', 'cosmicword');
        return $tabs;
    }

    public function modify_api_args($args) {
        return array(
            'page' => 1,
            'per_page' => 30,
            'locale' => get_locale(),
        );
    }

    public function add_menu_pages() {
        global $submenu;
        remove_submenu_page('plugins.php', 'plugin-install.php');
        
        add_submenu_page(
            'plugins.php',
            __('Add New', 'cosmicword'),
            __('Add New', 'cosmicword'),
            'install_plugins',
            'cosmic-plugins',
            array($this, 'render_plugin_page')
        );
    }

    // ... [rest of your existing methods] ...

    public function render_plugin_page() {
        if (!current_user_can('install_plugins')) {
            wp_die(__('Sorry, you are not allowed to install plugins on this site.'));
        }

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Add Plugins', 'cosmicword') . '</h1>';
        
        $this->display_admin_notices();
        
        $plugins = $this->get_plugin_list();
        
        if (empty($plugins)) {
            echo '<div class="error"><p>' . 
                 esc_html__('Unable to reach the CosmicWord plugin repository. Please try again later.', 'cosmicword') . 
                 '</p></div>';
            return;
        }

        echo '<style>
            .cosmic-plugin-card {
                padding: 20px;
                margin-bottom: 20px;
                background: #fff;
                border: 1px solid #ccd0d4;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
            }
            .cosmic-plugin-actions {
                margin-top: 10px;
            }
        </style>';

        foreach ($plugins as $plugin) {
            echo '<div class="cosmic-plugin-card">';
            echo '<h3>' . esc_html($plugin['name']) . '</h3>';
            echo '<p>' . esc_html($plugin['description']) . '</p>';
            echo '<div class="cosmic-plugin-actions">';
            echo '<form method="post" action="' . esc_url(admin_url('admin.php?page=cosmic-plugins&action=install_plugin')) . '">';
            wp_nonce_field('cosmic_install_plugin_action', 'cosmic_install_plugin_nonce');
            echo '<input type="hidden" name="plugin_url" value="' . 
                  esc_url($this->plugin_download_base . $plugin['slug'] . '.zip') . '">';
            echo '<input type="hidden" name="activate_plugin" value="1">';
            echo '<input type="submit" class="button button-primary" value="' . 
                  esc_attr__('Install and Activate', 'cosmicword') . '">';
            echo '</form>';
            echo '</div></div>';
        }
        echo '</div>';
    }

    private function get_plugin_list() {
        $cache_key = 'cosmic_plugin_list';
        $plugins = get_transient($cache_key);

        if (false === $plugins) {
            $response = wp_remote_get($this->plugin_api_url, array(
                'timeout' => 15,
                'sslverify' => true,
            ));

            if (!is_wp_error($response) && 200 === wp_remote_retrieve_response_code($response)) {
                $plugins = json_decode(wp_remote_retrieve_body($response), true);
                set_transient($cache_key, $plugins, HOUR_IN_SECONDS);
            } else {
                $plugins = array();
            }
        }

        return $plugins;
    }

    public function handle_plugin_installation() {
        check_admin_referer('cosmic_install_plugin_action', 'cosmic_install_plugin_nonce');

        if (!current_user_can('install_plugins')) {
            wp_die(__('Sorry, you are not allowed to install plugins on this site.'));
        }

        if (empty($_POST['plugin_url'])) {
            wp_die(__('No plugin URL provided.'));
        }

        $plugin_url = esc_url_raw($_POST['plugin_url']);
        $allowed_host = parse_url($this->plugin_download_base, PHP_URL_HOST);
        
        if (parse_url($plugin_url, PHP_URL_HOST) !== $allowed_host) {
            wp_die(__('Invalid plugin source.'));
        }

        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

        $upgrader = new Plugin_Upgrader(new Plugin_Installer_Skin());
        $installed = $upgrader->install($plugin_url);

        if (true === $installed && !empty($_POST['activate_plugin'])) {
            $plugin_file = $upgrader->plugin_info();
            if ($plugin_file) {
                activate_plugin($plugin_file);
            }
        }

        wp_redirect(admin_url('plugins.php?cosmic_installed=1'));
        exit;
    }

    public function display_admin_notices() {
        if (!empty($_GET['cosmic_installed'])) {
            echo '<div class="notice notice-success is-dismissible"><p>' . 
                 esc_html__('Plugin installed successfully.', 'cosmicword') . 
                 '</p></div>';
        }
    }
}

// Initialize only on admin
if (is_admin()) {
    add_action('plugins_loaded', function() {
        CosmicWord_Plugin_Manager::get_instance();
    }, 5);
}