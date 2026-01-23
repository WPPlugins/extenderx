<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

class DimBackgroundOnIdle {
    use Singleton;

    private $settings_cache;

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-dim-background-on-idle');
        wp_enqueue_script('xero-dim-background-on-idle');
    }

    public function render_effect() {
        $timeout = $this->settings_cache->get_setting('global.appearance.dim_background_on_idle.timeout', 30);
        $opacity = $this->settings_cache->get_setting('global.appearance.dim_background_on_idle.opacity', 50);
?>
        <div id="xero-dim-background-on-idle"
            class="xero-appearance-effect"
            data-timeout="<?php echo esc_attr($timeout); ?>"
            data-opacity="<?php echo esc_attr($opacity); ?>">
        </div>
<?php
    }
}
