<?php
// wp-includes/cosmic/cosmic-seo.php

if (!defined('ABSPATH')) {
    exit;
}

if (defined('COSMIC_SEO_LOADED')) {
    return;
}
define('COSMIC_SEO_LOADED', true);

// Add SEO columns to the post list
function cosmic_add_seo_columns($columns) {
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            $new_columns['seo_title'] = __('SEO Title');
            $new_columns['seo_description'] = __('SEO Description');
            $new_columns['seo_keywords'] = __('SEO Keywords');
        }
    }
    return $new_columns;
}

// Render SEO columns in the post list with Quick Edit support
function cosmic_render_seo_column($column, $post_id) {
    switch ($column) {
        case 'seo_title':
        case 'seo_description':
        case 'seo_keywords':
            $value = get_post_meta($post_id, '_' . $column, true);
            // Display the value and add hidden data for quick edit
            echo esc_html($value);
            echo '<div id="' . esc_attr($column) . '-' . $post_id . '" class="hidden">' . esc_html($value) . '</div>';
            break;
    }
}

// Add SEO meta box in the post editor
function cosmic_add_seo_meta_box() {
    add_meta_box(
        'cosmic_seo',
        __('SEO Settings'),
        'cosmic_seo_meta_box_html',
        ['post', 'page'],
        'normal',
        'high'
    );
}

// Render SEO meta box HTML
function cosmic_seo_meta_box_html($post) {
    wp_nonce_field('cosmic_seo', 'cosmic_seo_nonce');
    $seo_title = get_post_meta($post->ID, '_seo_title', true);
    $seo_description = get_post_meta($post->ID, '_seo_description', true);
    $seo_keywords = get_post_meta($post->ID, '_seo_keywords', true);

    $og_title = get_post_meta($post->ID, '_og_title', true);
    $og_description = get_post_meta($post->ID, '_og_description', true);
    $og_image = get_post_meta($post->ID, '_og_image', true);

    $twitter_title = get_post_meta($post->ID, '_twitter_title', true);
    $twitter_description = get_post_meta($post->ID, '_twitter_description', true);
    $twitter_image = get_post_meta($post->ID, '_twitter_image', true);
    ?>
    <div class="cosmic-seo-wrapper">
        <p>
            <label for="seo_title"><?php _e('SEO Title'); ?></label><br>
            <input type="text" id="seo_title" name="seo_title" value="<?php echo esc_attr($seo_title); ?>" style="width: 100%">
            <span class="description"><?php _e('Enter a custom title for search engines. Leave blank to use post title.'); ?></span>
        </p>
        <p>
            <label for="seo_description"><?php _e('Meta Description'); ?></label><br>
            <textarea id="seo_description" name="seo_description" rows="3" style="width: 100%"><?php echo esc_textarea($seo_description); ?></textarea>
            <span class="description"><?php _e('Enter a meta description for search engines.'); ?></span>
        </p>
        <p>
            <label for="seo_keywords"><?php _e('Meta Keywords'); ?></label><br>
            <input type="text" id="seo_keywords" name="seo_keywords" value="<?php echo esc_attr($seo_keywords); ?>" style="width: 100%">
            <span class="description"><?php _e('Enter keywords separated by commas.'); ?></span>
        </p>

        <!-- Open Graph fields -->
        <p>
            <label for="og_title"><?php _e('Open Graph Title'); ?></label><br>
            <input type="text" id="og_title" name="og_title" value="<?php echo esc_attr($og_title); ?>" style="width: 100%">
            <span class="description"><?php _e('Enter a custom title for social media sharing.'); ?></span>
        </p>
        <p>
            <label for="og_description"><?php _e('Open Graph Description'); ?></label><br>
            <textarea id="og_description" name="og_description" rows="3" style="width: 100%"><?php echo esc_textarea($og_description); ?></textarea>
            <span class="description"><?php _e('Enter a description for social media sharing.'); ?></span>
        </p>
        <p>
            <label for="og_image"><?php _e('Open Graph Image URL'); ?></label><br>
            <input type="text" id="og_image" name="og_image" value="<?php echo esc_attr($og_image); ?>" style="width: 100%">
            <span class="description"><?php _e('Enter the URL of the image for social sharing.'); ?></span>
        </p>

        <!-- Twitter Card fields -->
        <p>
            <label for="twitter_title"><?php _e('Twitter Title'); ?></label><br>
            <input type="text" id="twitter_title" name="twitter_title" value="<?php echo esc_attr($twitter_title); ?>" style="width: 100%">
            <span class="description"><?php _e('Enter a custom title for Twitter sharing.'); ?></span>
        </p>
        <p>
            <label for="twitter_description"><?php _e('Twitter Description'); ?></label><br>
            <textarea id="twitter_description" name="twitter_description" rows="3" style="width: 100%"><?php echo esc_textarea($twitter_description); ?></textarea>
            <span class="description"><?php _e('Enter a description for Twitter sharing.'); ?></span>
        </p>
        <p>
            <label for="twitter_image"><?php _e('Twitter Image URL'); ?></label><br>
            <input type="text" id="twitter_image" name="twitter_image" value="<?php echo esc_attr($twitter_image); ?>" style="width: 100%">
            <span class="description"><?php _e('Enter the URL of the image to use on Twitter.'); ?></span>
        </p>
    </div>
    <?php
}

// Quick Edit fields HTML
function cosmic_add_quick_edit($column_name) {
    // Only add fields once when we hit the first SEO column
    if ($column_name !== 'seo_title') {
        return;
    }
    ?>
    <fieldset class="inline-edit-col-right inline-edit-cosmic-seo">
        <div class="inline-edit-col">
            <label>
                <span class="title"><?php _e('SEO Title'); ?></span>
                <span class="input-text-wrap">
                    <input type="text" name="seo_title" value="" />
                </span>
            </label>
            <label>
                <span class="title"><?php _e('SEO Description'); ?></span>
                <span class="input-text-wrap">
                    <textarea name="seo_description" rows="2"></textarea>
                </span>
            </label>
            <label>
                <span class="title"><?php _e('SEO Keywords'); ?></span>
                <span class="input-text-wrap">
                    <input type="text" name="seo_keywords" value="" />
                </span>
            </label>
        </div>
    </fieldset>
    <?php
}

// Quick Edit JavaScript
function cosmic_quick_edit_js() {
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        var wp_inline_edit = inlineEditPost.edit;
        
        inlineEditPost.edit = function(id) {
            wp_inline_edit.apply(this, arguments);
            
            var post_id = 0;
            if (typeof(id) === 'object') {
                post_id = parseInt(this.getId(id));
            }
            
            if (post_id > 0) {
                var edit_row = $('#edit-' + post_id);
                var post_row = $('#post-' + post_id);
                
                var seo_title = $('#seo_title-' + post_id, post_row).text();
                var seo_description = $('#seo_description-' + post_id, post_row).text();
                var seo_keywords = $('#seo_keywords-' + post_id, post_row).text();
                
                $(':input[name="seo_title"]', edit_row).val(seo_title);
                $(':input[name="seo_description"]', edit_row).val(seo_description);
                $(':input[name="seo_keywords"]', edit_row).val(seo_keywords);
            }
        };
    });
    </script>
    <?php
}

// Save SEO meta data
function cosmic_save_seo_meta($post_id) {
    if (wp_is_post_revision($post_id) || !current_user_can('edit_post', $post_id)) return;
    
    // Check for Quick Edit
    if (isset($_POST['_inline_edit']) && !check_admin_referer('inlineeditnonce', '_inline_edit')) {
        return;
    }
    
    // Check for regular edit
    if (!isset($_POST['_inline_edit']) && (!isset($_POST['cosmic_seo_nonce']) || !wp_verify_nonce($_POST['cosmic_seo_nonce'], 'cosmic_seo'))) {
        return;
    }
    
    $fields = [
        'seo_title', 'seo_description', 'seo_keywords',
        'og_title', 'og_description', 'og_image',
        'twitter_title', 'twitter_description', 'twitter_image'
    ];

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $value = in_array($field, ['seo_description', 'og_description', 'twitter_description']) ?
                     sanitize_textarea_field($_POST[$field]) : sanitize_text_field($_POST[$field]);
            update_post_meta($post_id, '_' . $field, $value);
        }
    }
}

// Output SEO meta tags
function cosmic_output_seo_meta() {
    if (is_singular(['post', 'page'])) {
        global $post;

        $seo_title = get_post_meta($post->ID, '_seo_title', true);
        $seo_description = get_post_meta($post->ID, '_seo_description', true);
        $seo_keywords = get_post_meta($post->ID, '_seo_keywords', true);

        $og_title = get_post_meta($post->ID, '_og_title', true) ?: $seo_title;
        $og_description = get_post_meta($post->ID, '_og_description', true) ?: $seo_description;
        $og_image = get_post_meta($post->ID, '_og_image', true);

        $twitter_title = get_post_meta($post->ID, '_twitter_title', true) ?: $seo_title;
        $twitter_description = get_post_meta($post->ID, '_twitter_description', true) ?: $seo_description;
        $twitter_image = get_post_meta($post->ID, '_twitter_image', true);

        if ($seo_title) {
            echo '<title>' . esc_html($seo_title) . '</title>' . "\n";
        }
        if ($seo_description) {
            echo '<meta name="description" content="' . esc_attr($seo_description) . '">' . "\n";
        }
        if ($seo_keywords) {
            echo '<meta name="keywords" content="' . esc_attr($seo_keywords) . '">' . "\n";
        }

        echo '<meta property="og:type" content="article">' . "\n";
        if ($og_title) {
            echo '<meta property="og:title" content="' . esc_attr($og_title) . '">' . "\n";
        }
        if ($og_description) {
            echo '<meta property="og:description" content="' . esc_attr($og_description) . '">' . "\n";
        }
        echo '<meta property="og:url" content="' . esc_url(get_permalink($post)) . '">' . "\n";
        echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
        if ($og_image) {
            echo '<meta property="og:image" content="' . esc_url($og_image) . '">' . "\n";
        }

        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        if ($twitter_title) {
            echo '<meta name="twitter:title" content="' . esc_attr($twitter_title) . '">' . "\n";
        }
        if ($twitter_description) {
            echo '<meta name="twitter:description" content="' . esc_attr($twitter_description) . '">' . "\n";
        }
        if ($twitter_image) {
            echo '<meta name="twitter:image" content="' . esc_url($twitter_image) . '">' . "\n";
        }
    }
}

// Add styles for the SEO meta box and Quick Edit
function cosmic_add_seo_styles() {
    ?>
    <style type="text/css">
        /* Meta Box Styles */
        .cosmic-seo-wrapper .description {
            display: block;
            margin: 2px 0 5px;
            color: #666;
            font-style: italic;
        }
        
        /* Quick Edit Styles */
        .inline-edit-cosmic-seo label {
            display: block;
            margin: .2em 0;
        }
        .inline-edit-cosmic-seo label span.title {
            width: 120px;
            display: block;
            float: left;
            font-weight: bold;
        }
        .inline-edit-cosmic-seo .input-text-wrap {
            margin-left: 120px;
            display: block;
        }
        .inline-edit-cosmic-seo .input-text-wrap input[type="text"],
        .inline-edit-cosmic-seo .input-text-wrap textarea {
            width: 100%;
        }
        .inline-edit-cosmic-seo .input-text-wrap textarea {
            height: 4em;
        }
    </style>
    <?php
}

add_filter('manage_posts_columns', 'cosmic_add_seo_columns');
add_filter('manage_pages_columns', 'cosmic_add_seo_columns');
add_action('manage_posts_custom_column', 'cosmic_render_seo_column', 10, 2);
add_action('manage_pages_custom_column', 'cosmic_render_seo_column', 10, 2);
add_action('add_meta_boxes', 'cosmic_add_seo_meta_box');
add_action('save_post', 'cosmic_save_seo_meta');
add_action('wp_head', 'cosmic_output_seo_meta', 99);
add_action('admin_head', 'cosmic_add_seo_styles');
add_action('quick_edit_custom_box', 'cosmic_add_quick_edit', 10, 1);
add_action('admin_footer-edit.php', 'cosmic_quick_edit_js');
?>