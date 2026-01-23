<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class LightModeToggle {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-light-mode-toggle');
        wp_enqueue_script('xero-light-mode-toggle');
    }

    public function render_effect() {
        $settings = get_option('extenderx_settings', []);
?>
        <button id="xero-light-mode-toggle" class="xero-appearance-effect xero-mode-toggle" aria-label="Toggle light mode">
            <span class="xero-toggle-icon"></span>
        </button>
<?php
    }
}
