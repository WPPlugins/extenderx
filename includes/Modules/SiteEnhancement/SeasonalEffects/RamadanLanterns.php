<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

class RamadanLanterns {
    use Singleton;

    private $settings_cache;

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_effect']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-ramadan-lanterns');
        wp_enqueue_script('xero-ramadan-lanterns');
    }

    public function render_effect() {
        $intensity = $this->settings_cache->get_setting('global.seasonal_effects.ramadan_lanterns.intensity', 50);
        $speed = $this->settings_cache->get_setting('global.seasonal_effects.ramadan_lanterns.speed', 50);
?>
        <div id="xero-ramadan-lanterns"
            class="xero-seasonal-effect"
            data-intensity="<?php echo esc_attr($intensity); ?>"
            data-speed="<?php echo esc_attr($speed); ?>">
        </div>
<?php
    }
}
