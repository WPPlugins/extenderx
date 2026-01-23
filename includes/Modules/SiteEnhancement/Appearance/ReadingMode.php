<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class ReadingMode {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-reading-mode');
        wp_enqueue_script('xero-reading-mode');
    }

    public function render_effect() {
?>
        <button id="xero-reading-mode-toggle" class="xero-appearance-effect xero-mode-toggle" aria-label="Toggle reading mode">
            <span class="xero-toggle-icon">📖</span>
        </button>
        <div id="xero-reading-mode" class="xero-appearance-effect"></div>
<?php
    }
}
