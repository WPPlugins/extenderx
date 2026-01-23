<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Animations;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class EntranceEffects {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-entrance-effects');
        wp_enqueue_script('xero-entrance-effects');
    }

    public function render_effect() {
        $settings = get_option('extenderx_settings', []);
        $animation = $settings['global']['animations']['entrance_effects']['animation'] ?? 'fade-in';
        $duration = $settings['global']['animations']['entrance_effects']['duration'] ?? 600;
?>
        <div id="xero-entrance-effects"
            class="xero-animation-effect"
            data-animation="<?php echo esc_attr($animation); ?>"
            data-duration="<?php echo esc_attr($duration); ?>">
        </div>
<?php
    }
}
