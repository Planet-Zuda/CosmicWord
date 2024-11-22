<?php
if (!defined('ABSPATH')) {
    exit;
}
/** disable pingback */
// Disable pingback functionality completely
add_filter('xmlrpc_methods', function($methods) {
    unset($methods['pingback.ping']);
    unset($methods['pingback.extensions.getPingbacks']);
    return $methods;
});

// Remove pingback headers
add_filter('wp_headers', function($headers) {
    unset($headers['X-Pingback']);
    return $headers;
});

// Disable pingback URLs
add_filter('pings_open', '__return_false');

// Remove pingback links from header
remove_action('wp_head', 'pingback_link');

// Disable pingback XML-RPC ping mechanism
add_filter('xmlrpc_enabled', '__return_false');

// Remove pingback functionality from index
add_filter('bloginfo_url', function($output, $property) {
    return ($property == 'pingback_url') ? '' : $output;
}, 10, 2);

// Clean up pingback comment type
add_filter('comments_array', function($comments) {
    return array_filter($comments, function($comment) {
        return $comment->comment_type != 'pingback';
    });
});
/**
 * Handle block registration conflicts silently
 */
function cosmicword_core_error_handler($errno, $errstr) {
    // List of errors to silently ignore completely
    $ignore_patterns = array(
        'Font collection with slug:',
        'Block bindings source',
        'Block type',
        'is already registered',
        'register_font_collection',
        'WP_Block_Bindings_Registry::register',
        'WP_Block_Type_Registry::register'
    );
    
    // Silently ignore matching errors
    foreach ($ignore_patterns as $pattern) {
        if (strpos($errstr, $pattern) !== false) {
            return true; // Suppress without logging
        }
    }
    
    // Handle other errors normally
    return false;
}

// Register the handler
if (is_admin()) {
    set_error_handler('cosmicword_core_error_handler', E_ALL);
}
 
// Define your custom version
define('COSMIC_VERSION', '1.1.4'); // Replace with your actual version
define('COSMIC_VERSION_URL','https://forkedplugin.com/core/version.php');
// Define the update check interval (e.g., once daily)
define('COSMIC_UPDATE_CHECK_INTERVAL', DAY_IN_SECONDS);
define('COSMIC_LOGO','wp-includes/cosmic/logo.png');
define('COSMIC_DOWNLOAD_LATEST','https://forkedplugin.com/core/latest.zip');
define('COSMIC_REPO_URL','https://forkedplugin.com');

// Add kill switch option
define('COSMIC_KILL_SWITCH_OPTION', 'cosmic_updates_disabled');

function cosmic_get_latest_version() {
    // Check if updates have been permanently disabled
    if (get_option(COSMIC_KILL_SWITCH_OPTION) === 'killed') {
        return COSMIC_VERSION; // Return current version, no checking
    }

    // Check if the transient exists
    $remote_version = get_transient('cosmic_latest_version');

    if (false === $remote_version) {
        // Fetch the latest version from the remote server
        $response = wp_remote_get(COSMIC_VERSION_URL, [
            'timeout'     => 10,
            'sslverify'   => true,
            'headers'     => [
                'Accept' => 'text/plain',
            ],
        ]);

        // Check for errors
        if (!is_wp_error($response) && 200 === wp_remote_retrieve_response_code($response)) {
            $body = wp_remote_retrieve_body($response);
            
            // Check for kill switch signal
            if (strpos($body, 'KILL_UPDATES') !== false) {
                // Permanently disable updates
                update_option(COSMIC_KILL_SWITCH_OPTION, 'killed');
                return COSMIC_VERSION;
            }

            // Validate version format
            if (preg_match('/^\d+\.\d+(\.\d+)?$/', trim($body))) {
                set_transient('cosmic_latest_version', trim($body), COSMIC_UPDATE_CHECK_INTERVAL);
                cosmic_log_update_check(trim($body));
                return trim($body);
            }
        }

        // Cache failure for an hour
        set_transient('cosmic_latest_version', false, HOUR_IN_SECONDS);
        cosmic_log_update_check(false);
        return false;
    }

    return $remote_version;
}

function cosmic_log_update_check($latest_version) {
    if ($latest_version) {
        error_log('CosmicWord Update Check: Latest version ' . $latest_version . ' fetched successfully.');
    } else {
        error_log('CosmicWord Update Check: Failed to fetch the latest version.');
    }
}
// Add to cosmicword-core.php after existing code

function cosmic_modify_admin_menu() {
    remove_submenu_page('index.php', 'update-core.php');
    
    add_submenu_page(
        'index.php',
        __('CosmicWord Updates'),
        __('Updates'),
        'manage_options',
        'cosmic-updates',
        'cosmic_render_updates_page'
    );
}
add_action('admin_menu', 'cosmic_modify_admin_menu', 999);

function cosmic_render_updates_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    if (isset($_POST['cosmic_manual_update_check'])) {
    if (!isset($_POST['cosmic_manual_update_check_nonce']) || 
        !wp_verify_nonce($_POST['cosmic_manual_update_check_nonce'], 'cosmic_manual_update_check')) {
        wp_die(__('Security check failed. Please refresh the page and try again.'));
    }
    delete_transient('cosmic_latest_version');
}
    $download_info = cosmic_get_download_info();
    ?>
    <div class="wrap">
        <h1><?php _e('CosmicWord Updates'); ?></h1>
        
        <p><?php printf(__('You are running CosmicWord version %s.'), esc_html(COSMIC_VERSION)); ?></p>
        
        <p>
            <a href="<?php echo esc_url($download_info['download_url']); ?>" 
               class="button button-primary" 
               target="_blank" 
               rel="noopener noreferrer">
                <?php echo esc_html($download_info['button_label']); ?>
            </a>
        </p>
        
        <?php if ($download_info['is_new_version']) : ?>
            <p class="notice notice-warning">
                <?php _e('A new version is available. It is recommended to update to benefit from the latest features and security improvements.'); ?>
            </p>
        <?php endif; ?>

        <form method="post" action="">
            <?php wp_nonce_field('cosmic_manual_update_check', 'cosmic_manual_update_check_nonce'); ?>
            <input type="hidden" name="cosmic_manual_update_check" value="1">
            <p>
                <input type="submit" 
                       class="button button-secondary" 
                       value="<?php _e('Check for Updates Now'); ?>">
            </p>
        </form>
    </div>
    <?php
}

function cosmic_get_download_info() {
    $latest_version = cosmic_get_latest_version();
    $current_version = COSMIC_VERSION;

    if ($latest_version && version_compare($latest_version, $current_version, '>')) {
        $download_url = COSMIC_DOWNLOAD_LATEST;
        $button_label = sprintf(__('Download CosmicWord %s'), esc_html($latest_version));
        $is_new_version = true;
    } else {
        $download_url = COSMIC_REPO_URL . '/core/cosmicword-' . COSMIC_VERSION . '.zip';
        $button_label = sprintf(__('Re-download CosmicWord %s'), esc_html($current_version));
        $is_new_version = false;
    }

    return [
        'download_url'   => $download_url,
        'button_label'   => $button_label,
        'is_new_version' => $is_new_version,
    ];
}

// Remove standard WordPress update nags
remove_action('admin_notices', 'update_nag', 3);
remove_action('admin_notices', 'maintenance_nag');

// Disable core WordPress updates
add_filter('pre_site_transient_update_core', '__return_null');