<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class FloatingEmojis {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_effect']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-floating-emojis');
        wp_enqueue_script('xero-floating-emojis');
    }

    public function render_effect() {
        $settings = get_option('extenderx_settings', []);
        $emoji = $settings['global']['seasonal_effects']['floating_emojis']['emoji'] ?? '😊';
?>
        <div id="xero-floating-emojis"
            class="xero-seasonal-effect"
            data-emoji="<?php echo esc_attr($emoji); ?>">
        </div>
<?php
    }
}
