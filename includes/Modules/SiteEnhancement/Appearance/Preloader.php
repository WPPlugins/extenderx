<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class Preloader {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-preloader');
        wp_enqueue_script('xero-preloader');
    }

    public function render_effect() {
        $settings = get_option('extenderx_settings', []);
        $style = $settings['global']['appearance']['preloader']['style'] ?? 'spinner';
?>
        <div id="xero-preloader"
            class="xero-appearance-effect xero-preloader-overlay"
            data-style="<?php echo esc_attr($style); ?>">
            <div class="xero-preloader-content"></div>
        </div>
<?php
    }
}
