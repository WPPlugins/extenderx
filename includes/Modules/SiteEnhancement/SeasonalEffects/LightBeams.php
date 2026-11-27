<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

class LightBeams {
    use Singleton;

    private $settings_cache;

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_effect']);
    }

    public function enqueue_assets() {
        if (!EffectTargeting::passes('light_beams')) {
            return;
        }
        wp_enqueue_style('xero-light-beams');
        wp_enqueue_script('xero-light-beams');
    }

    public function render_effect() {
        if (!EffectTargeting::passes('light_beams')) {
            return;
        }
        $style = $this->settings_cache->get_setting('global.seasonal_effects.light_beams.style', 'spotlight');
        $intensity = $this->settings_cache->get_setting('global.seasonal_effects.light_beams.intensity', 50);
        $color = $this->settings_cache->get_setting('global.seasonal_effects.light_beams.color', 'white');
?>
        <div id="xero-light-beams"
            class="xero-seasonal-effect"
            data-style="<?php echo esc_attr($style); ?>"
            data-intensity="<?php echo esc_attr($intensity); ?>"
            data-color="<?php echo esc_attr($color); ?>">
        </div>
<?php
    }
}
