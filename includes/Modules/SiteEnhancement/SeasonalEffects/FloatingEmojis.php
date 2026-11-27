<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

class FloatingEmojis {
    use Singleton;

    private $settings_cache;

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_effect']);
    }

    public function enqueue_assets() {
        if (!EffectTargeting::passes('floating_emojis')) {
            return;
        }
        wp_enqueue_style('xero-floating-emojis');
        wp_enqueue_script('xero-floating-emojis');
    }

    public function render_effect() {
        if (!EffectTargeting::passes('floating_emojis')) {
            return;
        }
        $emoji = $this->settings_cache->get_setting('global.seasonal_effects.floating_emojis.emoji', '😊');
?>
        <div id="xero-floating-emojis"
            class="xero-seasonal-effect"
            data-emoji="<?php echo esc_attr($emoji); ?>">
        </div>
<?php
    }
}
