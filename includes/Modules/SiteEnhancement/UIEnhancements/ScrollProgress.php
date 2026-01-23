<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\UIEnhancements;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class ScrollProgress {
    use Singleton;

    public function __construct() {
        add_action('wp_footer', [$this, 'render_progress_bar']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_script('xero-scroll-progress');
        wp_enqueue_style('xero-scroll-progress');
    }

    public function render_progress_bar() {
?>
        <div id="xero-scroll-progress">
            <div class="xero-progress-bar"></div>
        </div>
<?php
    }
}
