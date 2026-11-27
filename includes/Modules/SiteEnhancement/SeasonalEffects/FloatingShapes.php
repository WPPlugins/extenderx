<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

class FloatingShapes {
    use Singleton;

    private $settings_cache;

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_effect']);
    }

    public function enqueue_assets() {
        if (!EffectTargeting::passes('floating_shapes')) {
            return;
        }
        wp_enqueue_style('xero-floating-shapes');
        wp_enqueue_script('xero-floating-shapes');
    }

    public function render_effect() {
        if (!EffectTargeting::passes('floating_shapes')) {
            return;
        }
        $type = $this->settings_cache->get_setting('global.seasonal_effects.floating_shapes.type', 'mixed');
        $count = $this->settings_cache->get_setting('global.seasonal_effects.floating_shapes.count', 20);
        $speed = $this->settings_cache->get_setting('global.seasonal_effects.floating_shapes.speed', 50);
?>
        <canvas id="xero-floating-shapes"
            class="xero-seasonal-effect"
            data-type="<?php echo esc_attr($type); ?>"
            data-count="<?php echo esc_attr($count); ?>"
            data-speed="<?php echo esc_attr($speed); ?>">
        </canvas>
<?php
    }
}
