<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Winter Snow — free seasonal effect.
 *
 * Renders the shared particle-engine snow overlay. Registered as a free feature
 * (winter_snow is intentionally absent from ProFeatures::PRO_FEATURES); the
 * scheduling / targeting / theme controls are gated separately via
 * winter_snow_advanced.
 */
class WinterSnow {
    use Singleton;

    /** @var SettingsCache */
    private $settings_cache;

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_effect']);
    }

    public function enqueue_assets() {
        if (!EffectTargeting::passes('winter_snow')) {
            return;
        }
        wp_enqueue_style('xero-winter-snow');
        wp_enqueue_script('xero-winter-snow');
    }

    public function render_effect() {
        if (!EffectTargeting::passes('winter_snow')) {
            return;
        }
        $intensity = $this->settings_cache->get_setting('global.seasonal_effects.winter_snow.intensity', 50);
        $speed     = $this->settings_cache->get_setting('global.seasonal_effects.winter_snow.speed', 50);
        $layer     = $this->settings_cache->get_setting('global.seasonal_effects.winter_snow.layer', 'front');
        $layer     = ('behind' === $layer) ? 'behind' : 'front';
?>
        <canvas id="xero-winter-snow"
            class="xero-seasonal-effect xero-layer-<?php echo esc_attr($layer); ?>"
            data-intensity="<?php echo esc_attr($intensity); ?>"
            data-speed="<?php echo esc_attr($speed); ?>">
        </canvas>
<?php
    }
}
