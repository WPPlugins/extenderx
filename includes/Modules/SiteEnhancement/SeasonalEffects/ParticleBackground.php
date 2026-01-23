<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class ParticleBackground {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_effect']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-particle-background');
        wp_enqueue_script('xero-particle-background');
    }

    public function render_effect() {
        $settings = get_option('extenderx_settings', []);
        $count = $settings['global']['seasonal_effects']['particle_bg']['count'] ?? 50;
        $speed = $settings['global']['seasonal_effects']['particle_bg']['speed'] ?? 50;
        $lines = $settings['global']['seasonal_effects']['particle_bg']['lines'] ?? false;
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
