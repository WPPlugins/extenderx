<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

class Fireflies {
    use Singleton;

    private $settings_cache;

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_effect']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-fireflies');
        wp_enqueue_script('xero-fireflies');
    }

    public function render_effect() {
        $count = $this->settings_cache->get_setting('global.seasonal_effects.fireflies.count', 30);
        $glow = $this->settings_cache->get_setting('global.seasonal_effects.fireflies.glow', 50);
        $color = $this->settings_cache->get_setting('global.seasonal_effects.fireflies.color', 'yellow');
?>
        <canvas id="xero-fireflies"
            class="xero-seasonal-effect"
            data-count="<?php echo esc_attr($count); ?>"
            data-glow="<?php echo esc_attr($glow); ?>"
            data-color="<?php echo esc_attr($color); ?>">
        </canvas>
<?php
    }
}
