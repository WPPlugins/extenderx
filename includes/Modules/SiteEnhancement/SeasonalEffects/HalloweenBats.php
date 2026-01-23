<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class HalloweenBats {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_effect']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-halloween-bats');
        wp_enqueue_script('xero-halloween-bats');
    }

    public function render_effect() {
        $settings = get_option('extenderx_settings', []);
        $intensity = $settings['global']['seasonal_effects']['halloween_bats']['intensity'] ?? 50;
        $speed = $settings['global']['seasonal_effects']['halloween_bats']['speed'] ?? 50;
?>
        <canvas id="xero-halloween-bats"
            class="xero-seasonal-effect"
            data-intensity="<?php echo esc_attr($intensity); ?>"
            data-speed="<?php echo esc_attr($speed); ?>">
        </canvas>
<?php
    }
}
