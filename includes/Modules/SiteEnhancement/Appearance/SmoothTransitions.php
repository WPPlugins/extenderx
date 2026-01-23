<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class SmoothTransitions {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-smooth-transitions');
        wp_enqueue_script('xero-smooth-transitions');
    }

    public function render_effect() {
        $settings = get_option('extenderx_settings', []);
?>
        <div id="xero-smooth-transitions" class="xero-appearance-effect"></div>
<?php
    }
}
