<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

class ChristmasLights {
    use Singleton;

    private $settings_cache;

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_effect']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-christmas-lights');
        wp_enqueue_script('xero-christmas-lights');
    }

    public function render_effect() {
        $style = $this->settings_cache->get_setting('global.seasonal_effects.christmas_lights.style', 'classic');
?>
        <div id="xero-christmas-lights" data-style="<?php echo esc_attr($style); ?>" class="xero-seasonal-effect"></div>
<?php
    }
}
