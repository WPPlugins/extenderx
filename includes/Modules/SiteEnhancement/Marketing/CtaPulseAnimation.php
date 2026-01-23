<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Marketing;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class CtaPulseAnimation {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-cta-pulse-animation');
        wp_enqueue_script('xero-cta-pulse-animation');
    }

    public function render_effect() {
        $settings = get_option('extenderx_settings', []);
        $color = $settings['global']['marketing']['cta_pulse_animation']['color'] ?? '#667eea';
        $intensity = $settings['global']['marketing']['cta_pulse_animation']['intensity'] ?? 50;
?>
        <div id="xero-cta-pulse"
            class="xero-marketing-effect"
            data-color="<?php echo esc_attr($color); ?>"
            data-intensity="<?php echo esc_attr($intensity); ?>">
        </div>
<?php
    }
}
