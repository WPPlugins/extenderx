<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\UIEnhancements;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class BackToTop {
    use Singleton;

    public function __construct() {
        add_action('wp_footer', [$this, 'render_button']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_script('xero-back-to-top');
        wp_enqueue_style('xero-back-to-top');
    }

    public function render_button() {
?>
        <button id="xero-back-to-top" aria-label="<?php esc_attr_e('Back to top', 'extenderx'); ?>">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 19V5M5 12l7-7 7 7" />
            </svg>
        </button>
<?php
    }
}
