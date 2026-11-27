<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Spring Petals — premium seasonal effect (shared particle engine).
 */
class SpringPetals {
    use Singleton;

    /** @var SettingsCache */
    private $settings_cache;

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_effect']);
    }

    public function enqueue_assets() {
        if (!EffectTargeting::passes('spring_petals')) {
            return;
        }
        wp_enqueue_style('xero-spring-petals');
        wp_enqueue_script('xero-spring-petals');
    }

    public function render_effect() {
        if (!EffectTargeting::passes('spring_petals')) {
            return;
        }
        $intensity = $this->settings_cache->get_setting('global.seasonal_effects.spring_petals.intensity', 50);
        $speed     = $this->settings_cache->get_setting('global.seasonal_effects.spring_petals.speed', 50);
        $layer     = $this->settings_cache->get_setting('global.seasonal_effects.spring_petals.layer', 'front');
        $layer     = ('behind' === $layer) ? 'behind' : 'front';
?>
        <canvas id="xero-spring-petals"
            class="xero-seasonal-effect xero-layer-<?php echo esc_attr($layer); ?>"
            data-intensity="<?php echo esc_attr($intensity); ?>"
            data-speed="<?php echo esc_attr($speed); ?>">
        </canvas>
<?php
    }
}
