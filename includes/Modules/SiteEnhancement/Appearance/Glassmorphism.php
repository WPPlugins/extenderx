<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class Glassmorphism {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-glassmorphism');
        wp_enqueue_script('xero-glassmorphism');
    }

    public function render_effect() {
        $settings = get_option('extenderx_settings', []);
        $intensity = $settings['global']['appearance']['glassmorphism']['intensity'] ?? 50;
?>
        <div id="xero-glassmorphism"
            class="xero-appearance-effect"
            data-intensity="<?php echo esc_attr($intensity); ?>">
        </div>
<?php
    }
}
