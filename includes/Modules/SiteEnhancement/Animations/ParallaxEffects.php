<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Animations;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

class ParallaxEffects {
    use Singleton;

    private $settings_cache;

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-parallax-effects');
        wp_enqueue_script('xero-parallax-effects');
    }

    public function render_effect() {
        $speed = $this->settings_cache->get_setting('global.animations.parallax_effects.speed', 50);
        $elements = $this->settings_cache->get_setting('global.animations.parallax_effects.elements', 'images');
?>
        <div id="xero-parallax-effects"
            class="xero-animation-effect"
            data-speed="<?php echo esc_attr($speed); ?>"
            data-elements="<?php echo esc_attr($elements); ?>">
        </div>
<?php
    }
}
