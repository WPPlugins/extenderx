<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Marketing;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class FloatingCtaButton {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-floating-cta-button');
        wp_enqueue_script('xero-floating-cta-button');
    }

    public function render_effect() {
        $settings = get_option('extenderx_settings', []);
        $text = $settings['global']['marketing']['floating_cta_button']['text'] ?? 'Get Started';
        $url = $settings['global']['marketing']['floating_cta_button']['url'] ?? '#';
        $position = $settings['global']['marketing']['floating_cta_button']['position'] ?? 'bottom-right';
        $bg_color = $settings['global']['marketing']['floating_cta_button']['bg_color'] ?? '#667eea';
        $text_color = $settings['global']['marketing']['floating_cta_button']['text_color'] ?? '#ffffff';
        $icon = $settings['global']['marketing']['floating_cta_button']['icon'] ?? '🎯';
?>
        <a href="<?php echo esc_url($url); ?>"
            id="xero-floating-cta"
            class="xero-marketing-effect xero-floating-cta-<?php echo esc_attr($position); ?>"
            style="background-color: <?php echo esc_attr($bg_color); ?>; color: <?php echo esc_attr($text_color); ?>;"
            data-position="<?php echo esc_attr($position); ?>">
            <span class="xero-cta-icon"><?php echo esc_html($icon); ?></span>
            <span class="xero-cta-text"><?php echo esc_html($text); ?></span>
        </a>
<?php
    }
}
