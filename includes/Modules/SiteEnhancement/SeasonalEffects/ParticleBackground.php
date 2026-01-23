<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

class ParticleBackground {
    use Singleton;

    private $settings_cache;

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_effect']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-particle-background');
        wp_enqueue_script('xero-particle-background');
    }

    public function render_effect() {
        $count = $this->settings_cache->get_setting('global.seasonal_effects.particle_bg.count', 50);
        $speed = $this->settings_cache->get_setting('global.seasonal_effects.particle_bg.speed', 50);
        $lines = $this->settings_cache->get_setting('global.seasonal_effects.particle_bg.lines', false);
?>
        <canvas id="xero-particle-background"
            class="xero-seasonal-effect xero-background-effect"
            data-count="<?php echo esc_attr($count); ?>"
            data-speed="<?php echo esc_attr($speed); ?>"
            data-lines="<?php echo esc_attr($lines ? 'true' : 'false'); ?>">
        </canvas>
<?php
    }
}
