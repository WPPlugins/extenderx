<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Summer Bubbles — premium seasonal effect (shared particle engine).
 */
class Summer {
    use Singleton;

    /** @var SettingsCache */
    private $settings_cache;

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_effect']);
    }

    public function enqueue_assets() {
        if (!EffectTargeting::passes('summer_sun')) {
            return;
        }
        wp_enqueue_style('xero-summer-sun');
        wp_enqueue_script('xero-summer-sun');
    }

    public function render_effect() {
        if (!EffectTargeting::passes('summer_sun')) {
            return;
        }
        $intensity = $this->settings_cache->get_setting('global.seasonal_effects.summer_sun.intensity', 50);
        $speed     = $this->settings_cache->get_setting('global.seasonal_effects.summer_sun.speed', 50);
        $layer     = $this->settings_cache->get_setting('global.seasonal_effects.summer_sun.layer', 'front');
        $layer     = ('behind' === $layer) ? 'behind' : 'front';
?>
        <canvas id="xero-summer-sun"
            class="xero-seasonal-effect xero-layer-<?php echo esc_attr($layer); ?>"
            data-intensity="<?php echo esc_attr($intensity); ?>"
            data-speed="<?php echo esc_attr($speed); ?>">
        </canvas>
<?php
    }
}
