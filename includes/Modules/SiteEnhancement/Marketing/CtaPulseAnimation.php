<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Marketing;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

class CtaPulseAnimation {
    use Singleton;

    private $settings_cache;

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-cta-pulse-animation');
        wp_enqueue_script('xero-cta-pulse-animation');
    }

    public function render_effect() {
        $color = $this->settings_cache->get_setting('global.marketing.cta_pulse_animation.color', '#667eea');
        $intensity = $this->settings_cache->get_setting('global.marketing.cta_pulse_animation.intensity', 50);
?>
        <div id="xero-cta-pulse"
            class="xero-marketing-effect"
            data-color="<?php echo esc_attr($color); ?>"
            data-intensity="<?php echo esc_attr($intensity); ?>">
        </div>
<?php
    }
}
