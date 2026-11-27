<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

class Fireworks {
    use Singleton;

    private $settings_cache;

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_effect']);
    }

    public function enqueue_assets() {
        if (!EffectTargeting::passes('fireworks')) {
            return;
        }
        wp_enqueue_style('xero-fireworks');
        wp_enqueue_script('xero-fireworks');
    }

    public function render_effect() {
        if (!EffectTargeting::passes('fireworks')) {
            return;
        }
        $intensity = $this->settings_cache->get_setting('global.seasonal_effects.fireworks.intensity', 50);
        $frequency = $this->settings_cache->get_setting('global.seasonal_effects.fireworks.frequency', 50);
        $colors = $this->settings_cache->get_setting('global.seasonal_effects.fireworks.colors', 'multi');
?>
        <canvas id="xero-fireworks"
            class="xero-seasonal-effect"
            data-intensity="<?php echo esc_attr($intensity); ?>"
            data-frequency="<?php echo esc_attr($frequency); ?>"
            data-colors="<?php echo esc_attr($colors); ?>">
        </canvas>
<?php
    }
}
