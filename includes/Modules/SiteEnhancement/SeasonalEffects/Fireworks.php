<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class Fireworks {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_effect']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-fireworks');
        wp_enqueue_script('xero-fireworks');
    }

    public function render_effect() {
        $settings = get_option('extenderx_settings', []);
        $intensity = $settings['global']['seasonal_effects']['fireworks']['intensity'] ?? 50;
        $frequency = $settings['global']['seasonal_effects']['fireworks']['frequency'] ?? 50;
        $colors = $settings['global']['seasonal_effects']['fireworks']['colors'] ?? 'multi';
?>
        <canvas id="xero-fireworks"
            class="xero-seasonal-effect"
            data-intensity="<?php echo esc_attr($intensity); ?>"
            data-frequency="<?php echo esc_attr($frequency); ?>"
            data-colors="<?php echo esc_attr($colors); ?>">
        </canvas>
<?php
    }
}
