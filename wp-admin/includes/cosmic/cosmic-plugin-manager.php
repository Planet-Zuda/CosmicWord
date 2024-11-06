<?php
if (!defined('ABSPATH')) {
    exit('Direct file access not permitted');
}

if (!current_user_can('activate_plugins')) {
    return;
}

define('COSMIC_PLUGINS_URL', esc_url_raw('https://forkedplugin.com/slurper/plugins/'));
define('COSMIC_METADATA_URL', esc_url_raw('https://forkedplugin.com/slurper/plugin_metadata.json'));
define('COSMIC_PLUGINS_PER_PAGE', 20);

$cosmic_js_dir = path_join(ABSPATH, 'wp-admin/js/cosmicword');
if (!file_exists($cosmic_js_dir)) {
    if (!wp_mkdir_p($cosmic_js_dir)) {
        wp_die('Failed to create required directory');
    }
    chmod($cosmic_js_dir, 0755);
}

$cosmic_js_file = path_join($cosmic_js_dir, 'search.js');
$cosmic_js_content = 'jQuery(document).ready(function($) {
    var $searchInput = $("#plugin-search");
    var $pluginBrowser = $(".cosmicword-plugin-browser");
    var $loadMore = $("#load-more-plugins");
    var searchTimer;
    var currentPage = 1;
    var isLoading = false;
    var lastSearchTerm = "";
    
    var cosmicNonce = "' . wp_create_nonce('cosmicword_search_nonce') . '";

    function performSearch(initialLoad, newSearch = false) {
        if (isLoading) return;
        
        if (newSearch) {
            currentPage = 1;
            $pluginBrowser.empty();
        }
        
        isLoading = true;
        var searchTerm = initialLoad ? "" : $searchInput.val().trim();
        lastSearchTerm = searchTerm;
        
        $("#search-status").html("<p>Loading plugins...</p>");
        $loadMore.hide();
        
        $.post(ajaxurl, {
            action: "cosmicword_search_plugins",
            search: searchTerm,
            page: currentPage,
            _ajax_nonce: cosmicNonce,
            initial_load: initialLoad ? 1 : 0
        })
        .done(function(response) {
            isLoading = false;
            if (response.success) {
                if (newSearch) {
                    $pluginBrowser.html(response.data.html);
                } else {
                    $pluginBrowser.append(response.data.html);
                }
                
                $("#search-status").html(response.data.total_plugins + " plugins found");
                
                if (response.data.has_more) {
                    $loadMore.show();
                } else {
                    $loadMore.hide();
                }
            } else {
                $("#search-status").html("<p class=\'error\'>" + (response.data || "Search failed") + "</p>");
                $loadMore.hide();
            }
        })
        .fail(function(xhr, status, error) {
            isLoading = false;
            $("#search-status").html("<p class=\'error\'>Search failed: " + error + "</p>");
            $loadMore.hide();
        });
    }

    $searchInput.on("input", function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            if ($searchInput.val().trim().length < 3) {
                $("#search-status").html("<p>Please enter at least 3 characters to search</p>");
                return;
            }
            performSearch(false, true);
        }, 500);
    });

    $loadMore.on("click", function(e) {
        e.preventDefault();
        currentPage++;
        performSearch(false, false);
    });

    $("#plugin-search-button").on("click", function(e) {
        e.preventDefault();
        if ($searchInput.val().trim().length < 3) {
            $("#search-status").html("<p>Please enter at least 3 characters to search</p>");
            return;
        }
        performSearch(false, true);
    });
    
    performSearch(true, true);
});';

if (!file_exists($cosmic_js_file) || md5_file($cosmic_js_file) !== md5($cosmic_js_content)) {
    if (file_put_contents($cosmic_js_file, $cosmic_js_content) === false) {
        wp_die('Failed to write JS file');
    }
    chmod($cosmic_js_file, 0644);
}

function cosmicword_get_metadata() {
    static $metadata = null;

    if ($metadata === null) {
        $metadata = get_transient('cosmicword_plugin_metadata');
        
        if ($metadata === false) {
            $response = wp_safe_remote_get(COSMIC_METADATA_URL, array(
                'timeout' => 15,
                'sslverify' => true
            ));
            
            if (!is_wp_error($response)) {
                $data = json_decode(wp_remote_retrieve_body($response), true);
                $metadata = isset($data['plugins']) ? $data['plugins'] : $data;
                set_transient('cosmicword_plugin_metadata', $metadata, HOUR_IN_SECONDS);
            }
        }
    }

    return is_array($metadata) ? $metadata : array();
}

function cosmicword_ajax_search() {
    check_ajax_referer('cosmicword_search_nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    $initial_load = isset($_POST['initial_load']) && $_POST['initial_load'] == 1;
    $search_term = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $current_page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
    
    $plugins = cosmicword_get_metadata();
    
    if (empty($plugins)) {
        wp_send_json_error('No plugins available');
        return;
    }

    $filtered_plugins = $plugins;
    if (!$initial_load && !empty($search_term)) {
        if (strlen($search_term) < 3) {
            wp_send_json_error('Please enter at least 3 characters to search');
            return;
        }

        $search_term = strtolower($search_term);
        $filtered_plugins = array_filter($plugins, function($plugin) use ($search_term) {
            if (!isset($plugin['slug'])) {
                return false;
            }
            $searchable_text = strtolower(implode(' ', array(
                $plugin['slug'],
                isset($plugin['name']) ? $plugin['name'] : '',
                isset($plugin['description']) ? $plugin['description'] : ''
            )));
            return strpos($searchable_text, $search_term) !== false;
        });
    } elseif ($initial_load) {
        $filtered_plugins = array_filter($plugins, function($plugin) {
            return !empty($plugin['featured']) || !empty($plugin['popular']);
        });
    }

    $total_plugins = count($filtered_plugins);
    $offset = ($current_page - 1) * COSMIC_PLUGINS_PER_PAGE;
    $current_plugins = array_slice($filtered_plugins, $offset, COSMIC_PLUGINS_PER_PAGE);

    ob_start();
    if (!empty($current_plugins)) {
        foreach ($current_plugins as $plugin) {
            ?>
            <div class="plugin-card">
                <h3><?php echo esc_html(isset($plugin['name']) ? $plugin['name'] : ucwords(str_replace(array('-', '_'), ' ', $plugin['slug']))); ?></h3>
                <?php if (isset($plugin['description'])): ?>
                    <p><?php echo esc_html($plugin['description']); ?></p>
                <?php endif; ?>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <?php wp_nonce_field('cosmicword_install_plugin_' . $plugin['slug']); ?>
                    <input type="hidden" name="action" value="cosmicword_install_plugin">
                    <input type="hidden" name="plugin_slug" value="<?php echo esc_attr($plugin['slug']); ?>">
                    <button type="submit" class="button button-primary"><?php esc_html_e('Install', 'cosmicword'); ?></button>
                </form>
            </div>
            <?php
        }
    } else {
        echo '<p>' . esc_html__('No plugins found.', 'cosmicword') . '</p>';
    }
    $html = ob_get_clean();

    wp_send_json_success(array(
        'html' => $html,
        'total_plugins' => $total_plugins,
        'has_more' => ($offset + COSMIC_PLUGINS_PER_PAGE) < $total_plugins
    ));
}
add_action('wp_ajax_cosmicword_search_plugins', 'cosmicword_ajax_search');

function cosmicword_add_menu_item() {
    if (!current_user_can('manage_options')) {
        return;
    }
    add_submenu_page(
        'plugins.php',
        esc_html__('CosmicWord Plugins', 'cosmicword'),
        esc_html__('CosmicWord Plugins', 'cosmicword'),
        'manage_options',
        'cosmicword-custom-plugins',
        'cosmicword_render_page'
    );
}
add_action('admin_menu', 'cosmicword_add_menu_item');

function cosmicword_handle_installation() {
    if (!current_user_can('install_plugins')) {
        wp_die(esc_html__('You do not have sufficient permissions to install plugins.', 'cosmicword'));
    }

    if (empty($_POST['plugin_slug'])) {
        wp_die(esc_html__('Invalid plugin', 'cosmicword'));
    }

    $plugin_slug = sanitize_key($_POST['plugin_slug']);
    check_admin_referer('cosmicword_install_plugin_' . $plugin_slug);

    $metadata = cosmicword_get_metadata();
    $valid_slugs = wp_list_pluck($metadata, 'slug');
    if (!in_array($plugin_slug, $valid_slugs, true)) {
        wp_die(esc_html__('Invalid plugin slug', 'cosmicword'));
    }

    $download_link = esc_url_raw(COSMIC_PLUGINS_URL . $plugin_slug . '.zip');

    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    $upgrader = new Plugin_Upgrader(new WP_Ajax_Upgrader_Skin());
    
    add_filter('http_request_args', function($args) {
        $args['headers']['X-WP-Nonce'] = wp_create_nonce('wp_rest');
        return $args;
    });
    
    $result = $upgrader->install($download_link);

    if (is_wp_error($result)) {
        wp_die($result->get_error_message());
    }

    wp_safe_redirect(add_query_arg('installed', 'true', admin_url('plugins.php?page=cosmicword-custom-plugins')));
    exit;
}
add_action('admin_post_cosmicword_install_plugin', 'cosmicword_handle_installation');

function cosmicword_enqueue_scripts($hook) {
    if ($hook != 'plugins_page_cosmicword-custom-plugins') {
        return;
    }

    wp_enqueue_style('plugin-install');
    wp_add_inline_style('plugin-install', '
        #plugin-search-container { margin: 20px 0; }
        #plugin-search { width: 300px; padding: 5px; margin-right: 10px; }
        #search-status { margin: 10px 0; font-style: italic; }
        .cosmicword-plugin-browser { margin-top: 20px; }
        .plugin-card { margin-bottom: 20px; padding: 15px; background: #fff; border: 1px solid #ddd; }
        #load-more-plugins { margin: 20px 0; width: 100%; text-align: center; }
    ');

    wp_enqueue_script('jquery');
    wp_enqueue_script(
        'cosmicword-search',
        admin_url('js/cosmicword/search.js'),
        array('jquery'),
        filemtime(ABSPATH . 'wp-admin/js/cosmicword/search.js'),
        true
    );
    
    wp_localize_script('cosmicword-search', 'cosmicwordL10n', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('cosmicword_search_nonce')
    ));
}
add_action('admin_enqueue_scripts', 'cosmicword_enqueue_scripts');

function cosmicword_render_page() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'cosmicword'));
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('CosmicWord Plugins', 'cosmicword'); ?></h1>
        <div id="plugin-search-container">
            <input type="text" id="plugin-search" placeholder="<?php esc_attr_e('Search plugins (min. 3 characters)...', 'cosmicword'); ?>" />
            <button id="plugin-search-button" class="button"><?php esc_html_e('Search', 'cosmicword'); ?></button>
        </div>
        <div id="search-status"></div>
        <div class="cosmicword-plugin-browser"></div>
        <button id="load-more-plugins" class="button" style="display: none;"><?php esc_html_e('Load More', 'cosmicword'); ?></button>
    </div>
    <?php
}