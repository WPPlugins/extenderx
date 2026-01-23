<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class MouseTrail {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_effect']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-mouse-trail');
        wp_enqueue_script('xero-mouse-trail');
    }

    public function render_effect() {
        $settings = get_option('extenderx_settings', []);
        $style = $settings['global']['seasonal_effects']['mouse_trail']['style'] ?? 'glow';
        $size = $settings['global']['seasonal_effects']['mouse_trail']['size'] ?? 50;
        $color = $settings['global']['seasonal_effects']['mouse_trail']['color'] ?? 'rainbow';
?>
        <canvas id="xero-mouse-trail"
            class="xero-seasonal-effect"
            data-style="<?php echo esc_attr($style); ?>"
            data-size="<?php echo esc_attr($size); ?>"
            data-color="<?php echo esc_attr($color); ?>">
        </canvas>
<?php
    }
}
