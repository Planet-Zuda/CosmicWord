<?php
/**
 * CosmicWord Core Updater
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

// Register our update handler
function cosmicword_handle_update() {
    if (!isset($_POST['action']) || $_POST['action'] !== 'cosmicword_do_upgrade' || 
        !check_admin_referer('cosmic-core-upgrade')) {
        return;
    }

    $url = COSMIC_CORE_URL . 'latest.zip';
    $upgrader = new Core_Upgrader();
    $result = $upgrader->upgrade($url);

    if (is_wp_error($result)) {
        wp_die($result);
    }

    wp_redirect(admin_url('admin.php?page=cosmicword-core-update&updated=true'));
    exit;
}
add_action('admin_post_cosmicword_do_upgrade', 'cosmicword_handle_update');

?>
<div class="wrap">
    <h1><?php echo esc_html('CosmicWord Updates'); ?></h1>
    
    <?php
    if (isset($_GET['updated'])) {
        echo '<div class="notice notice-success"><p>' . 
             __('CosmicWord has been successfully updated.') . 
             '</p></div>';
    }
    ?>
    
    <div class="update-php">
        <div id="cosmic-update-status">
            <?php 
            $version = get_bloginfo('version');
            echo '<h2>' . sprintf(__('Current CosmicWord version: %s'), esc_html($version)) . '</h2>';
            
            // Check for updates
            $response = wp_remote_get(COSMIC_VERSION_URL);
            if (!is_wp_error($response)) {
                $version_info = json_decode(wp_remote_retrieve_body($response), true);
                if ($version_info && isset($version_info['version'])) {
                    if (version_compare($version_info['version'], $version, '>')) {
                        echo '<div class="notice notice-warning"><p>' . 
                             sprintf(__('A new version (%s) is available.'), esc_html($version_info['version'])) . 
                             '</p></div>';
                    } else {
                        echo '<div class="notice notice-success"><p>' . 
                             __('You have the latest version of CosmicWord.') . 
                             '</p></div>';
                    }
                }
            }
            ?>
        </div>
        
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="upgrade">
            <?php wp_nonce_field('cosmic-core-upgrade'); ?>
            <input type="hidden" name="action" value="cosmicword_do_upgrade">
            <p>
                <button type="submit" class="button button-primary" id="upgrade-button">
                    <?php _e('Update CosmicWord Core'); ?>
                </button>
            </p>
        </form>
        
        <div class="return-to-dashboard">
            <a href="<?php echo esc_url(admin_url()); ?>"><?php _e('Return to Dashboard'); ?></a>
        </div>
    </div>
</div>
