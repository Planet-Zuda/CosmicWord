<?php if (!defined('ABSPATH')) exit; ?>

<style>
.cosmicword-seo-container {
    text-align: center;
    max-width: 600px;
    margin: 0 auto;
}
.cosmicword-seo-field {
    margin-bottom: 15px;
}
.cosmicword-seo-field label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}
.cosmicword-seo-field input[type="text"],
.cosmicword-seo-field textarea {
    width: 100%;
    padding: 5px;
}
</style>
wp_nonce_field('cosmicword_seo_nonce', 'cosmicword_seo_nonce');

<div class="cosmicword-seo-container">
    <div class="cosmicword-seo-field">
        <label for="seo_title">SEO Title:</label>
        <input type="text" id="seo_title" name="seo_title" value="<?php echo esc_attr($seo_title); ?>">
    </div>

    <div class="cosmicword-seo-field">
        <label for="seo_description">SEO Description:</label>
        <textarea id="seo_description" name="seo_description" rows="4"><?php echo esc_textarea($seo_description); ?></textarea>
    </div>

    <div class="cosmicword-seo-field">
        <label for="seo_keywords">SEO Keywords:</label>
        <input type="text" id="seo_keywords" name="seo_keywords" value="<?php echo esc_attr($seo_keywords); ?>">
    </div>

    <div class="cosmicword-seo-field">
        <label for="seo_canonical">Canonical URL:</label>
        <input type="text" id="seo_canonical" name="seo_canonical" value="<?php echo esc_url($seo_canonical); ?>">
    </div>
</div>