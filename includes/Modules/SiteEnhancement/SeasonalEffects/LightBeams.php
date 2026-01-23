<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class LightBeams {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_effect']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-light-beams');
        wp_enqueue_script('xero-light-beams');
    }

    public function render_effect() {
        $settings = get_option('extenderx_settings', []);
        $style = $settings['global']['seasonal_effects']['light_beams']['style'] ?? 'spotlight';
        $intensity = $settings['global']['seasonal_effects']['light_beams']['intensity'] ?? 50;
        $color = $settings['global']['seasonal_effects']['light_beams']['color'] ?? 'white';
?>
        <div id="xero-light-beams"
            class="xero-seasonal-effect"
            data-style="<?php echo esc_attr($style); ?>"
            data-intensity="<?php echo esc_attr($intensity); ?>"
            data-color="<?php echo esc_attr($color); ?>">
        </div>
<?php
    }
}
