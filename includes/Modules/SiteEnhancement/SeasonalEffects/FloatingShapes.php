<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class FloatingShapes {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_effect']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-floating-shapes');
        wp_enqueue_script('xero-floating-shapes');
    }

    public function render_effect() {
        $settings = get_option('extenderx_settings', []);
        $type = $settings['global']['seasonal_effects']['floating_shapes']['type'] ?? 'mixed';
        $count = $settings['global']['seasonal_effects']['floating_shapes']['count'] ?? 20;
        $speed = $settings['global']['seasonal_effects']['floating_shapes']['speed'] ?? 50;
?>
        <canvas id="xero-floating-shapes"
            class="xero-seasonal-effect"
            data-type="<?php echo esc_attr($type); ?>"
            data-count="<?php echo esc_attr($count); ?>"
            data-speed="<?php echo esc_attr($speed); ?>">
        </canvas>
<?php
    }
}
