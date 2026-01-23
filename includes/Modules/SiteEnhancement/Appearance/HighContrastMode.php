<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class HighContrastMode {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-high-contrast-mode');
        wp_enqueue_script('xero-high-contrast-mode');
    }

    public function render_effect() {
        $settings = get_option('extenderx_settings', []);
?>
        <div id="xero-high-contrast-mode" class="xero-appearance-effect"></div>
<?php
    }
}
