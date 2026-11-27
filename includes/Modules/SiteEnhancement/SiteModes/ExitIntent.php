<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SiteModes;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;
use WPXERO\ExtenderX\Core\ProFeatures;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Exit Intent Popup
 *
 * Free: heading, message, primary/secondary button text + URL, icon, colors,
 *       preset, show-once behaviour, trigger sensitivity.
 * Pro (advanced): email capture, coupon reveal, mobile back-button trigger,
 *                 frequency cap, custom CSS, device/role/page/schedule targeting.
 *
 * @since 1.0.0
 */
class ExitIntent {
    use Singleton;
    use SiteModeTargeting;

    private const SETTINGS_PATH   = 'global.site_modes.exit_intent';

    /** @var SettingsCache */
    private $settings_cache;

    /** @var bool */
    private $is_pro = false;

    /** @var array<string, mixed> */
    private $settings = [];

    /** @var array<string, mixed> */
    private $default_settings = [
        // Free.
        'preset'           => 'offer-card',
        'icon'             => '🎁',
        'heading'          => "Wait! Don't Leave Yet!",
        'message'          => 'Before you go, check out our special offer just for you!',
        'primary_label'    => 'Show Me the Offer',
        'primary_url'      => '',
        'secondary_label'  => 'No Thanks',
        'bg_color'         => '#ffffff',
        'text_color'       => '#111827',
        'accent_color'     => '#6366f1',
        'show_once'        => true,
        'sensitivity'      => 20,
        // Pro — email capture.
        'email_capture'    => false,
        'email_placeholder' => 'Enter your email',
        'email_button'     => 'Get the Deal',
        // Pro — coupon.
        'coupon_enabled'   => false,
        'coupon_code'      => '',
        // Pro — behaviour.
        'mobile_trigger'   => false,
        'frequency_days'   => 0,
        'custom_css'       => '',
        // Pro — targeting.
        'schedule_enabled'     => false,
        'start_date'           => '',
        'end_date'             => '',
        'show_on_mobile'       => true,
        'show_on_desktop'      => true,
        'visible_to_roles'     => [],
        'exclude_page_ids'     => [],
        'exclude_url_patterns' => [],
    ];

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        $this->is_pro         = ProFeatures::instance()->has_pro_license();
        $this->load_settings();

        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_exit_popup']);
    }

    private function load_settings() {
        $stored = $this->settings_cache->get_setting(self::SETTINGS_PATH, []);
        if (!is_array($stored)) {
            $stored = [];
        }
        $this->settings = wp_parse_args($stored, $this->default_settings);
    }

    private function should_render(): bool {
        if (trim((string) $this->settings['message']) === '' && trim((string) $this->settings['heading']) === '') {
            return false;
        }
        if ($this->is_pro && !$this->passes_targeting()) {
            return false;
        }
        return true;
    }

    public function enqueue_assets() {
        if (!$this->should_render()) {
            return;
        }

        wp_enqueue_style('xero-exit-intent');
        wp_enqueue_script('xero-exit-intent');

        wp_localize_script('xero-exit-intent', 'extenderxExitIntent', [
            'showOnce'       => (bool) $this->settings['show_once'],
            'sensitivity'    => max(0, min(100, (int) $this->settings['sensitivity'])),
            'mobileTrigger'  => $this->is_pro && !empty($this->settings['mobile_trigger']),
            'frequencyDays'  => $this->is_pro ? max(0, (int) $this->settings['frequency_days']) : 0,
        ]);

        if ($this->is_pro && trim((string) $this->settings['custom_css']) !== '') {
            wp_add_inline_style('xero-exit-intent', (string) $this->settings['custom_css']);
        }
    }

    public function render_exit_popup() {
        if (!$this->should_render()) {
            return;
        }

        $preset       = sanitize_html_class((string) $this->settings['preset']) ?: 'offer-card';
        $bg_color     = sanitize_hex_color((string) $this->settings['bg_color']) ?: '#ffffff';
        $text_color   = sanitize_hex_color((string) $this->settings['text_color']) ?: '#111827';
        $accent_color = sanitize_hex_color((string) $this->settings['accent_color']) ?: '#6366f1';
        $icon         = (string) $this->settings['icon'];

        $primary_url    = trim((string) $this->settings['primary_url']);
        $email_capture  = $this->is_pro && !empty($this->settings['email_capture']);
        $coupon_enabled = $this->is_pro && !empty($this->settings['coupon_enabled']) && trim((string) $this->settings['coupon_code']) !== '';

        $style = sprintf(
            '--xero-sm-bg:%s;--xero-sm-text:%s;--xero-sm-accent:%s;',
            esc_attr($bg_color),
            esc_attr($text_color),
            esc_attr($accent_color)
        );
?>
        <div id="xero-exit-intent-overlay"
            class="xero-site-mode xero-exit-intent xero-preset-<?php echo esc_attr($preset); ?>"
            style="<?php echo esc_attr($style); ?>">
            <div class="xero-exit-intent-popup" role="dialog" aria-modal="true" aria-labelledby="xero-exit-heading">
                <button type="button" class="xero-exit-close" aria-label="<?php esc_attr_e('Close', 'extenderx'); ?>">&times;</button>
                <?php if ($icon !== ''): ?>
                    <div class="xero-exit-icon" aria-hidden="true"><?php echo esc_html($icon); ?></div>
                <?php endif; ?>
                <h2 id="xero-exit-heading"><?php echo esc_html((string) $this->settings['heading']); ?></h2>
                <p class="xero-exit-message"><?php echo wp_kses_post((string) $this->settings['message']); ?></p>

                <?php if ($coupon_enabled): ?>
                    <div class="xero-exit-coupon" data-code="<?php echo esc_attr((string) $this->settings['coupon_code']); ?>">
                        <span class="xero-exit-coupon-code"><?php echo esc_html((string) $this->settings['coupon_code']); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($email_capture): ?>
                    <form class="xero-exit-form" novalidate>
                        <input type="email" class="xero-exit-email" placeholder="<?php echo esc_attr((string) $this->settings['email_placeholder']); ?>" required />
                        <button type="submit" class="xero-btn xero-btn-primary"><?php echo esc_html((string) $this->settings['email_button']); ?></button>
                    </form>
                <?php else: ?>
                    <div class="xero-exit-actions">
                        <?php if ($primary_url !== ''): ?>
                            <a class="xero-btn xero-btn-primary xero-exit-primary" href="<?php echo esc_url($primary_url); ?>"><?php echo esc_html((string) $this->settings['primary_label']); ?></a>
                        <?php else: ?>
                            <button type="button" class="xero-btn xero-btn-primary xero-exit-primary"><?php echo esc_html((string) $this->settings['primary_label']); ?></button>
                        <?php endif; ?>
                        <button type="button" class="xero-btn xero-btn-secondary xero-exit-dismiss"><?php echo esc_html((string) $this->settings['secondary_label']); ?></button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
<?php
    }
}
