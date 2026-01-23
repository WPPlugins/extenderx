<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Marketing;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

class AnnouncementBar {
    use Singleton;

    private $settings_cache;

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-announcement-bar');
        wp_enqueue_script('xero-announcement-bar');
    }

    public function render_effect() {
        $message = $this->settings_cache->get_setting('global.marketing.announcement_bar.message', 'Important announcement!');
        $bg_color = $this->settings_cache->get_setting('global.marketing.announcement_bar.bg_color', '#667eea');
        $text_color = $this->settings_cache->get_setting('global.marketing.announcement_bar.text_color', '#ffffff');
        $position = $this->settings_cache->get_setting('global.marketing.announcement_bar.position', 'top');
        $dismissible = $this->settings_cache->get_setting('global.marketing.announcement_bar.dismissible', true);
?>
        <div id="xero-announcement-bar"
            class="xero-marketing-effect xero-announcement-<?php echo esc_attr($position); ?>"
            data-position="<?php echo esc_attr($position); ?>"
            data-dismissible="<?php echo esc_attr($dismissible ? '1' : '0'); ?>"
            style="background-color: <?php echo esc_attr($bg_color); ?>; color: <?php echo esc_attr($text_color); ?>;">
            <div class="xero-announcement-content">
                <span class="xero-announcement-message"><?php echo wp_kses_post($message); ?></span>
                <?php if ($dismissible): ?>
                    <button class="xero-announcement-close" aria-label="Close">×</button>
                <?php endif; ?>
            </div>
        </div>
<?php
    }
}
