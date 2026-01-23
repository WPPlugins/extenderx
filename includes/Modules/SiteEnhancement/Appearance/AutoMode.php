<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class AutoMode {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-auto-mode');
        wp_enqueue_script('xero-auto-mode');
    }

    public function render_effect() {
?>
        <div id="xero-auto-mode" class="xero-appearance-effect"></div>
<?php
    }
}
