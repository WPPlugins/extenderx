<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Animations;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class FloatingElements {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-floating-elements');
        wp_enqueue_script('xero-floating-elements');
    }

    public function render_effect() {
        $settings = get_option('extenderx_settings', []);
?>
        <div id="xero-floating-elements" class="xero-animation-effect"></div>
<?php
    }
}
