<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class DimBackgroundOnIdle {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-dim-background-on-idle');
        wp_enqueue_script('xero-dim-background-on-idle');
    }

    public function render_effect() {
        $settings = get_option('extenderx_settings', []);
        $timeout = $settings['global']['appearance']['dim_background_on_idle']['timeout'] ?? 30;
        $opacity = $settings['global']['appearance']['dim_background_on_idle']['opacity'] ?? 50;
?>
        <div id="xero-dim-background-on-idle"
            class="xero-appearance-effect"
            data-timeout="<?php echo esc_attr($timeout); ?>"
            data-opacity="<?php echo esc_attr($opacity); ?>">
        </div>
<?php
    }
}
