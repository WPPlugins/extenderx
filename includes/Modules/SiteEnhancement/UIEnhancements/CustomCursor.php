<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\UIEnhancements;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class CustomCursor {
    use Singleton;

    public function __construct() {
        add_action('wp_footer', [$this, 'render_cursor']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_script('xero-custom-cursor');
        wp_enqueue_style('xero-custom-cursor');
    }

    public function render_cursor() {
?>
        <div id="xero-custom-cursor">
            <div class="xero-cursor-dot"></div>
            <div class="xero-cursor-outline"></div>
        </div>
<?php
    }
}
