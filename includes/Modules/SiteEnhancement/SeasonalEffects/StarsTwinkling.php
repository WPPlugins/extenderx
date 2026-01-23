<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class StarsTwinkling {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_effect']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-stars-twinkling');
        wp_enqueue_script('xero-stars-twinkling');
    }

    public function render_effect() {
        $settings = get_option('extenderx_settings', []);
        $count = $settings['global']['seasonal_effects']['stars_twinkling']['count'] ?? 100;
        $speed = $settings['global']['seasonal_effects']['stars_twinkling']['speed'] ?? 50;
        $background = $settings['global']['seasonal_effects']['stars_twinkling']['background'] ?? 'dark';
?>
        <canvas id="xero-stars-twinkling"
            class="xero-seasonal-effect xero-background-effect"
            data-count="<?php echo esc_attr($count); ?>"
            data-speed="<?php echo esc_attr($speed); ?>"
            data-background="<?php echo esc_attr($background); ?>">
        </canvas>
<?php
    }
}
