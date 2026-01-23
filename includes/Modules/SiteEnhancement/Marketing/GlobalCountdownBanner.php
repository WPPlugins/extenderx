<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Marketing;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

class GlobalCountdownBanner {
    use Singleton;

    private $settings_cache;

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-global-countdown-banner');
        wp_enqueue_script('xero-global-countdown-banner');
    }

    public function render_effect() {
        $title = $this->settings_cache->get_setting('global.marketing.global_countdown_banner.title', 'Limited Time Offer!');
        $end_date = $this->settings_cache->get_setting('global.marketing.global_countdown_banner.end_date', date('Y-m-d H:i:s', strtotime('+7 days')));
        $position = $this->settings_cache->get_setting('global.marketing.global_countdown_banner.position', 'top');
        $bg_color = $this->settings_cache->get_setting('global.marketing.global_countdown_banner.bg_color', '#ff4757');
        $text_color = $this->settings_cache->get_setting('global.marketing.global_countdown_banner.text_color', '#ffffff');
        $show_cta = $this->settings_cache->get_setting('global.marketing.global_countdown_banner.show_cta', true);
        $cta_text = $this->settings_cache->get_setting('global.marketing.global_countdown_banner.cta_text', 'Shop Now');
        $cta_url = $this->settings_cache->get_setting('global.marketing.global_countdown_banner.cta_url', '#');
?>
        <div id="xero-countdown-banner"
            class="xero-marketing-effect xero-countdown-<?php echo esc_attr($position); ?>"
            data-end-date="<?php echo esc_attr($end_date); ?>"
            data-position="<?php echo esc_attr($position); ?>"
            style="background-color: <?php echo esc_attr($bg_color); ?>; color: <?php echo esc_attr($text_color); ?>;">
            <div class="xero-countdown-content">
                <span class="xero-countdown-title"><?php echo esc_html($title); ?></span>
                <div class="xero-countdown-timer">
                    <div class="xero-countdown-item">
                        <span class="xero-countdown-value" data-unit="days">00</span>
                        <span class="xero-countdown-label">Days</span>
                    </div>
                    <div class="xero-countdown-item">
                        <span class="xero-countdown-value" data-unit="hours">00</span>
                        <span class="xero-countdown-label">Hours</span>
                    </div>
                    <div class="xero-countdown-item">
                        <span class="xero-countdown-value" data-unit="minutes">00</span>
                        <span class="xero-countdown-label">Minutes</span>
                    </div>
                    <div class="xero-countdown-item">
                        <span class="xero-countdown-value" data-unit="seconds">00</span>
                        <span class="xero-countdown-label">Seconds</span>
                    </div>
                </div>
                <?php if ($show_cta): ?>
                    <a href="<?php echo esc_url($cta_url); ?>" class="xero-countdown-cta">
                        <?php echo esc_html($cta_text); ?>
                    </a>
                <?php endif; ?>
                <button class="xero-countdown-close" aria-label="Close">×</button>
            </div>
        </div>
<?php
    }
}
