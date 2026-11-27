<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

class HalloweenBats {
    use Singleton;

    private $settings_cache;

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_effect']);
    }

    public function enqueue_assets() {
        if (!EffectTargeting::passes('halloween_bats')) {
            return;
        }
        wp_enqueue_style('xero-halloween-bats');
        wp_enqueue_script('xero-halloween-bats');
    }

    public function render_effect() {
        if (!EffectTargeting::passes('halloween_bats')) {
            return;
        }
        $intensity = $this->settings_cache->get_setting('global.seasonal_effects.halloween_bats.intensity', 50);
        $size  = $this->settings_cache->get_setting('global.seasonal_effects.halloween_bats.size', 50);
        $speed = $this->settings_cache->get_setting('global.seasonal_effects.halloween_bats.speed', 50);
        $layer = $this->settings_cache->get_setting('global.seasonal_effects.halloween_bats.layer', 'front');
        $layer = ('behind' === $layer) ? 'behind' : 'front';
?>
        <canvas id="xero-halloween-bats"
            class="xero-seasonal-effect xero-layer-<?php echo esc_attr($layer); ?>"
            data-intensity="<?php echo esc_attr($intensity); ?>"
            data-speed="<?php echo esc_attr($speed); ?>"
            data-size="<?php echo esc_attr($size); ?>">
        </canvas>
<?php
    }
}
