<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

class StarsTwinkling {
    use Singleton;

    private $settings_cache;

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_effect']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-stars-twinkling');
        wp_enqueue_script('xero-stars-twinkling');
    }

    public function render_effect() {
        $count = $this->settings_cache->get_setting('global.seasonal_effects.stars_twinkling.count', 100);
        $speed = $this->settings_cache->get_setting('global.seasonal_effects.stars_twinkling.speed', 50);
        $background = $this->settings_cache->get_setting('global.seasonal_effects.stars_twinkling.background', 'dark');
?>
        <canvas id="xero-stars-twinkling"
            class="xero-seasonal-effect xero-background-effect"
            data-count="<?php echo esc_attr($count); ?>"
            data-speed="<?php echo esc_attr($speed); ?>"
            data-background="<?php echo esc_attr($background); ?>">
        </canvas>
<?php
    }
}
