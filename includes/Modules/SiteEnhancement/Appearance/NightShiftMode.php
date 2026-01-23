<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class NightShiftMode {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-night-shift-mode');
        wp_enqueue_script('xero-night-shift-mode');
    }

    public function render_effect() {
        $settings = get_option('extenderx_settings', []);
        $intensity = $settings['global']['appearance']['night_shift_mode']['intensity'] ?? 50;
?>
        <div id="xero-night-shift-mode"
            class="xero-appearance-effect"
            data-intensity="<?php echo esc_attr($intensity); ?>">
        </div>
<?php
    }
}
