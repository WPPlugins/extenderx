<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class Fireflies {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_effect']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-fireflies');
        wp_enqueue_script('xero-fireflies');
    }

    public function render_effect() {
        $settings = get_option('extenderx_settings', []);
        $count = $settings['global']['seasonal_effects']['fireflies']['count'] ?? 30;
        $glow = $settings['global']['seasonal_effects']['fireflies']['glow'] ?? 50;
        $color = $settings['global']['seasonal_effects']['fireflies']['color'] ?? 'yellow';
?>
        <canvas id="xero-fireflies"
            class="xero-seasonal-effect"
            data-count="<?php echo esc_attr($count); ?>"
            data-glow="<?php echo esc_attr($glow); ?>"
            data-color="<?php echo esc_attr($color); ?>">
        </canvas>
<?php
    }
}
