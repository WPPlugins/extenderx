<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SiteModes;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;
use WPXERO\ExtenderX\Core\ProFeatures;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cookie Consent Bar
 *
 * Free: message, button labels, position, layout, colors, preset template,
 *       privacy link, remember window, font size.
 * Pro (advanced): granular cookie categories, auto-hide, custom CSS,
 *                 device/role/page/schedule targeting.
 *
 * @since 1.0.0
 */
class CookieConsent {
    use Singleton;
    use SiteModeTargeting;

    private const SETTINGS_PATH   = 'global.site_modes.cookie_consent';

    /** @var SettingsCache */
    private $settings_cache;

    /** @var bool */
    private $is_pro = false;

    /** @var array<string, mixed> */
    private $settings = [];

    /** @var array<string, mixed> */
    private $default_settings = [
        // Free.
        'preset'        => 'minimal-bar',
        'message'       => 'We use cookies to enhance your browsing experience and analyze our traffic. By clicking "Accept", you consent to our use of cookies.',
        'accept_label'  => 'Accept All',
        'decline_label' => 'Decline',
        'position'      => 'bottom',
        'layout'        => 'bar',
        'bg_color'      => '#1f2937',
        'text_color'    => '#f9fafb',
        'accent_color'  => '#6366f1',
        'font_size'     => 14,
        'remember_days' => 180,
        'privacy_url'   => '',
        'privacy_text'  => 'Privacy Policy',
        'show_icon'     => true,
        // Pro — granular categories.
        'categories_enabled' => false,
        'cat_analytics'      => true,
        'cat_marketing'      => true,
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
        add_action('wp_footer', [$this, 'render_consent_bar']);
    }

    private function load_settings() {
        $stored = $this->settings_cache->get_setting(self::SETTINGS_PATH, []);
        if (!is_array($stored)) {
            $stored = [];
        }
        $this->settings = wp_parse_args($stored, $this->default_settings);
    }

    private function should_render(): bool {
        if (isset($_COOKIE['xero_cookie_consent'])) {
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

        wp_enqueue_style('xero-cookie-consent');
        wp_enqueue_script('xero-cookie-consent');

        wp_localize_script('xero-cookie-consent', 'extenderxCookieConsent', [
            'rememberDays' => max(1, (int) $this->settings['remember_days']),
            'autoHide'     => $this->is_pro ? max(0, (int) $this->settings['auto_hide']) : 0,
            'categories'   => $this->is_pro && !empty($this->settings['categories_enabled']),
        ]);

        if ($this->is_pro && trim((string) $this->settings['custom_css']) !== '') {
            wp_add_inline_style('xero-cookie-consent', (string) $this->settings['custom_css']);
        }
    }

    public function render_consent_bar() {
        if (!$this->should_render()) {
            return;
        }

        $preset       = sanitize_html_class((string) $this->settings['preset']) ?: 'minimal-bar';
        $position     = in_array($this->settings['position'], ['top', 'bottom', 'float-left', 'float-right'], true) ? $this->settings['position'] : 'bottom';
        $layout       = in_array($this->settings['layout'], ['bar', 'card'], true) ? $this->settings['layout'] : 'bar';
        $bg_color     = sanitize_hex_color((string) $this->settings['bg_color']) ?: '#1f2937';
        $text_color   = sanitize_hex_color((string) $this->settings['text_color']) ?: '#f9fafb';
        $accent_color = sanitize_hex_color((string) $this->settings['accent_color']) ?: '#6366f1';
        $font_size    = max(11, min(20, (int) $this->settings['font_size']));
        $show_icon    = (bool) $this->settings['show_icon'];
        $categories   = $this->is_pro && !empty($this->settings['categories_enabled']);

        $style = sprintf(
            '--xero-sm-bg:%s;--xero-sm-text:%s;--xero-sm-accent:%s;--xero-sm-font:%dpx;',
            esc_attr($bg_color),
            esc_attr($text_color),
            esc_attr($accent_color),
            $font_size
        );
?>
        <div id="xero-cookie-consent-bar"
            class="xero-site-mode xero-cookie-consent xero-preset-<?php echo esc_attr($preset); ?> is-position-<?php echo esc_attr($position); ?> is-layout-<?php echo esc_attr($layout); ?>"
            data-position="<?php echo esc_attr($position); ?>"
            style="<?php echo esc_attr($style); ?>">
            <div class="xero-cookie-content">
                <?php if ($show_icon): ?>
                    <div class="xero-cookie-icon" aria-hidden="true">🍪</div>
                <?php endif; ?>
                <div class="xero-cookie-text">
                    <p><?php echo wp_kses_post((string) $this->settings['message']); ?></p>
                    <?php if (trim((string) $this->settings['privacy_url']) !== ''): ?>
                        <a class="xero-cookie-privacy" href="<?php echo esc_url((string) $this->settings['privacy_url']); ?>" target="_blank" rel="noopener noreferrer">
                            <?php echo esc_html((string) $this->settings['privacy_text']); ?>
                        </a>
                    <?php endif; ?>
                </div>
                <?php if ($categories): ?>
                    <div class="xero-cookie-categories">
                        <label class="xero-cookie-cat is-locked">
                            <input type="checkbox" checked disabled> <?php esc_html_e('Necessary', 'extenderx'); ?>
                        </label>
                        <?php if (!empty($this->settings['cat_analytics'])): ?>
                            <label class="xero-cookie-cat">
                                <input type="checkbox" data-category="analytics" checked> <?php esc_html_e('Analytics', 'extenderx'); ?>
                            </label>
                        <?php endif; ?>
                        <?php if (!empty($this->settings['cat_marketing'])): ?>
                            <label class="xero-cookie-cat">
                                <input type="checkbox" data-category="marketing" checked> <?php esc_html_e('Marketing', 'extenderx'); ?>
                            </label>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="xero-cookie-actions">
                    <button type="button" id="xero-cookie-decline" class="xero-btn xero-btn-secondary"><?php echo esc_html((string) $this->settings['decline_label']); ?></button>
                    <button type="button" id="xero-cookie-accept" class="xero-btn xero-btn-primary"><?php echo esc_html((string) $this->settings['accept_label']); ?></button>
                </div>
            </div>
        </div>
<?php
    }
}
