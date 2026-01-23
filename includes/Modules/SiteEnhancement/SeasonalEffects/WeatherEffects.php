<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class WeatherEffects {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_effect']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-weather-effects');
        wp_enqueue_script('xero-weather-effects');
    }

    public function render_effect() {
        $settings = get_option('extenderx_settings', []);
        $type = $settings['global']['seasonal_effects']['weather']['type'] ?? 'rain';
        $intensity = $settings['global']['seasonal_effects']['weather']['intensity'] ?? 50;
        $apply_to = $settings['global']['seasonal_effects']['weather']['apply_to'] ?? 'entire';
?>
        <canvas id="xero-weather-effects"
            class="xero-seasonal-effect"
            data-type="<?php echo esc_attr($type); ?>"
            data-intensity="<?php echo esc_attr($intensity); ?>"
            data-apply-to="<?php echo esc_attr($apply_to); ?>">
        </canvas>
<?php
    }
}
