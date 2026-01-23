<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

class SkeletonLoader {
    use Singleton;

    private $settings_cache;

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-skeleton-loader');
        wp_enqueue_script('xero-skeleton-loader');
    }

    public function render_effect() {
        $style = $this->settings_cache->get_setting('global.appearance.skeleton_loader.style', 'basic');
?>
        <div id="xero-skeleton-loader"
            class="xero-appearance-effect"
            data-style="<?php echo esc_attr($style); ?>">
        </div>
<?php
    }
}
