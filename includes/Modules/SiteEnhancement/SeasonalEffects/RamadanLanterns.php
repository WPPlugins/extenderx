<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class RamadanLanterns {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_effect']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-ramadan-lanterns');
        wp_enqueue_script('xero-ramadan-lanterns');
    }

    public function render_effect() {
        $settings = get_option('extenderx_settings', []);
        $intensity = $settings['global']['seasonal_effects']['ramadan_lanterns']['intensity'] ?? 50;
        $speed = $settings['global']['seasonal_effects']['ramadan_lanterns']['speed'] ?? 50;
?>
        <div id="xero-ramadan-lanterns"
            class="xero-seasonal-effect"
            data-intensity="<?php echo esc_attr($intensity); ?>"
            data-speed="<?php echo esc_attr($speed); ?>">
        </div>
<?php
    }
}
