<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

class Preloader {
    use Singleton;

    private $settings_cache;

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-preloader');
        wp_enqueue_script('xero-preloader');
    }

    public function render_effect() {
        $style = $this->settings_cache->get_setting('global.appearance.preloader.style', 'spinner');
?>
        <div id="xero-preloader"
            class="xero-appearance-effect xero-preloader-overlay"
            data-style="<?php echo esc_attr($style); ?>">
            <div class="xero-preloader-content"></div>
        </div>
<?php
    }
}
