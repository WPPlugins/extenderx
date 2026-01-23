<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class FallingHearts {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_effect']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-falling-hearts');
        wp_enqueue_script('xero-falling-hearts');
    }

    public function render_effect() {
        $settings = get_option('extenderx_settings', []);
        $intensity = $settings['global']['seasonal_effects']['hearts_falling']['intensity'] ?? 50;
        $speed = $settings['global']['seasonal_effects']['hearts_falling']['speed'] ?? 50;
?>
        <canvas id="xero-falling-hearts"
            class="xero-seasonal-effect"
            data-intensity="<?php echo esc_attr($intensity); ?>"
            data-speed="<?php echo esc_attr($speed); ?>">
        </canvas>
<?php
    }
}
