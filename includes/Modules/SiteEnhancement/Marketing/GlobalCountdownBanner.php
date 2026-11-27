<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Marketing;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;
use WPXERO\ExtenderX\Core\ProFeatures;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Global Countdown Banner
 *
 * Free: fixed end-date countdown, title, colours, position, CTA, dismissible.
 * Pro (advanced): evergreen per-visitor timer, on-expire action
 *                 (hide/message/redirect), animation, scheduling, targeting.
 *
 * @since 1.0.0
 */
class GlobalCountdownBanner {
    use Singleton;
    use MarketingVisibility;

    private const PRO_FEATURE_KEY = 'global_countdown_banner_advanced';

    /** @var SettingsCache */
    private $settings_cache;

    /** @var bool */
    private $is_pro = false;

    /** @var array<string, mixed> */
    private $settings = [];

    /** @var array<string, mixed> */
    private $default_settings = [
        // Free.
        'title'       => 'Limited Time Offer!',
        'end_date'    => '',
        'position'    => 'top',
        'bg_color'    => '#ff4757',
        'text_color'  => '#ffffff',
        'dismissible' => true,
        'show_cta'    => true,
        'cta_text'    => 'Shop Now',
        'cta_url'     => '#',
        // Pro — timer mode.
        'mode'              => 'fixed', // fixed | evergreen
        'evergreen_minutes' => 60,
        // Pro — on expire.
        'on_expire'       => 'hide', // hide | message | redirect
        'expire_message'  => 'This offer has ended.',
        'redirect_url'    => '',
        // Pro — presentation.
        'animation' => 'slide',
        // Pro — scheduling.
        'schedule_enabled' => false,
        'start_date'       => '',
        'end_date_show'    => '',
        // Pro — targeting.
        'show_on_mobile'       => true,
        'show_on_tablet'       => true,
        'show_on_desktop'      => true,
        'visible_to_roles'     => [],
        'exclude_page_ids'     => [],
        'exclude_url_patterns' => [],
    ];

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        $this->is_pro         = ProFeatures::instance()->can_use_premium_feature(self::PRO_FEATURE_KEY);
        $this->load_settings();

        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_body_open', [$this, 'render_effect']);
    }

    private function load_settings() {
        $stored = $this->settings_cache->get_setting('global.marketing.global_countdown_banner', []);
        if (!is_array($stored)) {
            $stored = [];
        }
        $this->settings = wp_parse_args($stored, $this->default_settings);
    }

    private function is_evergreen(): bool {
        return $this->is_pro && 'evergreen' === $this->settings['mode'];
    }

    private function should_render(): bool {
        // A fixed countdown needs an end date; evergreen does not.
        if (!$this->is_evergreen() && trim((string) $this->settings['end_date']) === '') {
            return false;
        }
        if (!$this->is_pro) {
            return true;
        }
        return $this->passes_targeting();
    }

    public function enqueue_assets() {
        if (!$this->should_render()) {
            return;
        }

        wp_enqueue_style('xero-global-countdown-banner');
        wp_enqueue_script('xero-global-countdown-banner');

        wp_localize_script('xero-global-countdown-banner', 'extenderxCountdown', [
            'mode'             => $this->is_evergreen() ? 'evergreen' : 'fixed',
            'evergreenMinutes' => max(1, (int) $this->settings['evergreen_minutes']),
            'onExpire'         => $this->is_pro ? sanitize_key((string) $this->settings['on_expire']) : 'hide',
            'expireMessage'    => (string) $this->settings['expire_message'],
            'redirectUrl'      => esc_url_raw((string) $this->settings['redirect_url']),
        ]);
    }

    public function render_effect() {
        if (!$this->should_render()) {
            return;
        }

        $title       = (string) $this->settings['title'];
        $end_date    = (string) $this->settings['end_date'];
        $position    = in_array($this->settings['position'], ['top', 'bottom'], true) ? $this->settings['position'] : 'top';
        $bg_color    = sanitize_hex_color((string) $this->settings['bg_color']) ?: '#ff4757';
        $text_color  = sanitize_hex_color((string) $this->settings['text_color']) ?: '#ffffff';
        $dismissible = (bool) $this->settings['dismissible'];
        $show_cta    = !empty($this->settings['show_cta']) && trim((string) $this->settings['cta_url']) !== '';
        $animation   = $this->is_pro ? sanitize_key((string) $this->settings['animation']) : 'slide';

        $labels = [
            'days'    => __('Days', 'extenderx'),
            'hours'   => __('Hours', 'extenderx'),
            'minutes' => __('Minutes', 'extenderx'),
            'seconds' => __('Seconds', 'extenderx'),
        ];
?>
        <div id="xero-countdown-banner"
            class="xero-marketing-effect xero-countdown-<?php echo esc_attr($position); ?> xero-anim-<?php echo esc_attr($animation); ?>"
            data-end-date="<?php echo esc_attr($end_date); ?>"
            data-position="<?php echo esc_attr($position); ?>"
            style="background-color: <?php echo esc_attr($bg_color); ?>; color: <?php echo esc_attr($text_color); ?>;">
            <div class="xero-countdown-content">
                <span class="xero-countdown-title"><?php echo esc_html($title); ?></span>
                <div class="xero-countdown-timer">
                    <?php foreach ($labels as $unit => $label): ?>
                        <div class="xero-countdown-item">
                            <span class="xero-countdown-value" data-unit="<?php echo esc_attr($unit); ?>">00</span>
                            <span class="xero-countdown-label"><?php echo esc_html($label); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($show_cta): ?>
                    <a href="<?php echo esc_url((string) $this->settings['cta_url']); ?>" class="xero-countdown-cta" style="color: <?php echo esc_attr($bg_color); ?>;">
                        <?php echo esc_html((string) $this->settings['cta_text']); ?>
                    </a>
                <?php endif; ?>
                <?php if ($dismissible): ?>
                    <button class="xero-countdown-close" aria-label="<?php esc_attr_e('Close', 'extenderx'); ?>">&times;</button>
                <?php endif; ?>
            </div>
        </div>
<?php
    }
}
