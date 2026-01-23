<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class BlurBackgroundOnScroll {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-blur-background-on-scroll');
        wp_enqueue_script('xero-blur-background-on-scroll');
    }

    public function render_effect() {
        $settings = get_option('extenderx_settings', []);
        $intensity = $settings['global']['appearance']['blur_background_on_scroll']['intensity'] ?? 50;
?>
        <div id="xero-blur-background-on-scroll"
            class="xero-appearance-effect"
            data-intensity="<?php echo esc_attr($intensity); ?>">
        </div>
<?php
    }
}
