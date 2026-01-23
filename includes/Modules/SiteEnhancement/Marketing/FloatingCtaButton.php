<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Marketing;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

class FloatingCtaButton {
    use Singleton;

    private $settings_cache;

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-floating-cta-button');
        wp_enqueue_script('xero-floating-cta-button');
    }

    public function render_effect() {
        $text = $this->settings_cache->get_setting('global.marketing.floating_cta_button.text', 'Get Started');
        $url = $this->settings_cache->get_setting('global.marketing.floating_cta_button.url', '#');
        $position = $this->settings_cache->get_setting('global.marketing.floating_cta_button.position', 'bottom-right');
        $bg_color = $this->settings_cache->get_setting('global.marketing.floating_cta_button.bg_color', '#667eea');
        $text_color = $this->settings_cache->get_setting('global.marketing.floating_cta_button.text_color', '#ffffff');
        $icon = $this->settings_cache->get_setting('global.marketing.floating_cta_button.icon', '🎯');
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
