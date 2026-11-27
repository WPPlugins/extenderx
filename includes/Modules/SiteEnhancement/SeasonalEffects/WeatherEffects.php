<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

class WeatherEffects {
    use Singleton;

    private $settings_cache;

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_effect']);
    }

    public function enqueue_assets() {
        if (!EffectTargeting::passes('weather')) {
            return;
        }
        wp_enqueue_style('xero-weather-effects');
        wp_enqueue_script('xero-weather-effects');
    }

    public function render_effect() {
        if (!EffectTargeting::passes('weather')) {
            return;
        }
        $type = $this->settings_cache->get_setting('global.seasonal_effects.weather.type', 'rain');
        $intensity = $this->settings_cache->get_setting('global.seasonal_effects.weather.intensity', 50);
        $apply_to = $this->settings_cache->get_setting('global.seasonal_effects.weather.apply_to', 'entire');
?>
        <canvas id="xero-weather-effects"
            class="xero-seasonal-effect"
            data-type="<?php echo esc_attr($type); ?>"
            data-intensity="<?php echo esc_attr($intensity); ?>"
            data-apply-to="<?php echo esc_attr($apply_to); ?>">
        </canvas>
<?php
    }
}
