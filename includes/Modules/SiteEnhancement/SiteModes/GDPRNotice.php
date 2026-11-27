<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SiteModes;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;
use WPXERO\ExtenderX\Core\ProFeatures;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * GDPR Notice
 *
 * Free: message, button label, privacy link, position, colors, preset,
 *       remember window, font size.
 * Pro (advanced): auto-hide, custom CSS, device/role/page/schedule targeting.
 *
 * @since 1.0.0
 */
class GDPRNotice {
    use Singleton;
    use SiteModeTargeting;

    private const SETTINGS_PATH   = 'global.site_modes.gdpr_notice';

    /** @var SettingsCache */
    private $settings_cache;

    /** @var bool */
    private $is_pro = false;

    /** @var array<string, mixed> */
    private $settings = [];

    /** @var array<string, mixed> */
    private $default_settings = [
        // Free.
        'preset'        => 'compact-bar',
        'message'       => 'We care about your privacy. This website uses cookies and processes personal data in accordance with GDPR regulations.',
        'button_label'  => 'I Understand',
        'position'      => 'bottom',
        'bg_color'      => '#111827',
        'text_color'    => '#f9fafb',
        'accent_color'  => '#10b981',
        'font_size'     => 13,
        'remember_days' => 180,
        'privacy_url'   => '',
        'privacy_text'  => 'Privacy Policy',
        'show_icon'     => true,
        // Pro — behaviour.
        'auto_hide'  => 0,
        'custom_css' => '',
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
        add_action('wp_footer', [$this, 'render_gdpr_notice']);
    }

    private function load_settings() {
        $stored = $this->settings_cache->get_setting(self::SETTINGS_PATH, []);
        if (!is_array($stored)) {
            $stored = [];
        }
        $this->settings = wp_parse_args($stored, $this->default_settings);
    }

    private function should_render(): bool {
        if (isset($_COOKIE['xero_gdpr_accepted'])) {
            return false;
        }
        if (trim((string) $this->settings['message']) === '') {
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

        wp_enqueue_style('xero-gdpr-notice');
        wp_enqueue_script('xero-gdpr-notice');

        wp_localize_script('xero-gdpr-notice', 'extenderxGdprNotice', [
            'rememberDays' => max(1, (int) $this->settings['remember_days']),
            'autoHide'     => $this->is_pro ? max(0, (int) $this->settings['auto_hide']) : 0,
        ]);

        if ($this->is_pro && trim((string) $this->settings['custom_css']) !== '') {
            wp_add_inline_style('xero-gdpr-notice', (string) $this->settings['custom_css']);
        }
    }

    public function render_gdpr_notice() {
        if (!$this->should_render()) {
            return;
        }

        $preset       = sanitize_html_class((string) $this->settings['preset']) ?: 'compact-bar';
        $position     = in_array($this->settings['position'], ['top', 'bottom'], true) ? $this->settings['position'] : 'bottom';
        $bg_color     = sanitize_hex_color((string) $this->settings['bg_color']) ?: '#111827';
        $text_color   = sanitize_hex_color((string) $this->settings['text_color']) ?: '#f9fafb';
        $accent_color = sanitize_hex_color((string) $this->settings['accent_color']) ?: '#10b981';
        $font_size    = max(11, min(20, (int) $this->settings['font_size']));
        $show_icon    = (bool) $this->settings['show_icon'];

        $privacy_url = trim((string) $this->settings['privacy_url']) !== ''
            ? (string) $this->settings['privacy_url']
            : (string) get_privacy_policy_url();

        $style = sprintf(
            '--xero-sm-bg:%s;--xero-sm-text:%s;--xero-sm-accent:%s;--xero-sm-font:%dpx;',
            esc_attr($bg_color),
            esc_attr($text_color),
            esc_attr($accent_color),
            $font_size
        );
?>
        <div id="xero-gdpr-notice-bar"
            class="xero-site-mode xero-gdpr-notice xero-preset-<?php echo esc_attr($preset); ?> is-position-<?php echo esc_attr($position); ?>"
            data-position="<?php echo esc_attr($position); ?>"
            style="<?php echo esc_attr($style); ?>">
            <div class="xero-gdpr-content">
                <?php if ($show_icon): ?>
                    <div class="xero-gdpr-icon" aria-hidden="true">🔒</div>
                <?php endif; ?>
                <div class="xero-gdpr-text">
                    <p><?php echo wp_kses_post((string) $this->settings['message']); ?></p>
                </div>
                <div class="xero-gdpr-actions">
                    <?php if ($privacy_url !== ''): ?>
                        <a class="xero-gdpr-privacy" href="<?php echo esc_url($privacy_url); ?>" target="_blank" rel="noopener noreferrer">
                            <?php echo esc_html((string) $this->settings['privacy_text']); ?>
                        </a>
                    <?php endif; ?>
                    <button type="button" id="xero-gdpr-accept" class="xero-btn xero-btn-primary"><?php echo esc_html((string) $this->settings['button_label']); ?></button>
                </div>
            </div>
        </div>
<?php
    }
}
