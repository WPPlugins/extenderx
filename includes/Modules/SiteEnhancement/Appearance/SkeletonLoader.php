<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class SkeletonLoader {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-skeleton-loader');
        wp_enqueue_script('xero-skeleton-loader');
    }

    public function render_effect() {
        $settings = get_option('extenderx_settings', []);
        $style = $settings['global']['appearance']['skeleton_loader']['style'] ?? 'basic';
?>
        <div id="xero-skeleton-loader"
            class="xero-appearance-effect"
            data-style="<?php echo esc_attr($style); ?>">
        </div>
<?php
    }
}
