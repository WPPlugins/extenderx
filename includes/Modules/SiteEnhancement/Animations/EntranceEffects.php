<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Animations;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

class EntranceEffects {
    use Singleton;

    private $settings_cache;

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-entrance-effects');
        wp_enqueue_script('xero-entrance-effects');
    }

    public function render_effect() {
        $animation = $this->settings_cache->get_setting('global.animations.entrance_effects.animation', 'fade-in');
        $duration = $this->settings_cache->get_setting('global.animations.entrance_effects.duration', 600);
?>
        <div id="xero-entrance-effects"
            class="xero-animation-effect"
            data-animation="<?php echo esc_attr($animation); ?>"
            data-duration="<?php echo esc_attr($duration); ?>">
        </div>
<?php
    }
}
