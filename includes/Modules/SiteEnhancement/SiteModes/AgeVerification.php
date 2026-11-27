<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SiteModes;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;
use WPXERO\ExtenderX\Core\ProFeatures;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Age Verification Popup
 *
 * Free: heading, message, button labels, icon, colors, preset, remember window,
 *       decline redirect URL.
 * Pro (advanced): logo + background image, custom CSS,
 *                 device/role/page/schedule targeting.
 *
 * @since 1.0.0
 */
class AgeVerification {
    use Singleton;
    use SiteModeTargeting;

    private const SETTINGS_PATH   = 'global.site_modes.age_verification';

    /** @var SettingsCache */
    private $settings_cache;

    /** @var bool */
    private $is_pro = false;

    /** @var array<string, mixed> */
    private $settings = [];

    /** @var array<string, mixed> */
    private $default_settings = [
        // Free.
        'preset'         => 'classic-card',
        'icon'           => '🔞',
        'heading'        => 'Age Verification Required',
        'message'        => 'You must be 18 years or older to access this website. Please verify your age to continue.',
        'confirm_label'  => 'I am 18 or Older',
        'decline_label'  => 'I am Under 18',
        'declined_text'  => 'Sorry, you are not old enough to view this content.',
        'bg_color'       => '#0f172a',
        'card_color'     => '#1e293b',
        'text_color'     => '#f8fafc',
        'accent_color'   => '#6366f1',
        'remember_days'  => 30,
        'decline_url'    => '',
        // Pro — branding.
        'logo_url'       => '',
        'bg_image_url'   => '',
        'custom_css'     => '',
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
        add_action('wp_footer', [$this, 'render_popup']);
    }

    private function load_settings() {
        $stored = $this->settings_cache->get_setting(self::SETTINGS_PATH, []);
        if (!is_array($stored)) {
            $stored = [];
        }
        $this->settings = wp_parse_args($stored, $this->default_settings);
    }

    private function should_render(): bool {
        if (isset($_COOKIE['xero_age_verified'])) {
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

        wp_enqueue_style('xero-age-verification');
        wp_enqueue_script('xero-age-verification');

        wp_localize_script('xero-age-verification', 'extenderxAgeVerification', [
            'rememberDays' => max(1, (int) $this->settings['remember_days']),
            'declineUrl'   => esc_url_raw((string) $this->settings['decline_url']),
        ]);

        if ($this->is_pro && trim((string) $this->settings['custom_css']) !== '') {
            wp_add_inline_style('xero-age-verification', (string) $this->settings['custom_css']);
        }
    }

    public function render_popup() {
        if (!$this->should_render()) {
            return;
        }

        $preset       = sanitize_html_class((string) $this->settings['preset']) ?: 'classic-card';
        $bg_color     = sanitize_hex_color((string) $this->settings['bg_color']) ?: '#0f172a';
        $card_color   = sanitize_hex_color((string) $this->settings['card_color']) ?: '#1e293b';
        $text_color   = sanitize_hex_color((string) $this->settings['text_color']) ?: '#f8fafc';
        $accent_color = sanitize_hex_color((string) $this->settings['accent_color']) ?: '#6366f1';
        $icon         = (string) $this->settings['icon'];

        $logo_url = $this->is_pro ? trim((string) $this->settings['logo_url']) : '';
        $bg_image = $this->is_pro ? trim((string) $this->settings['bg_image_url']) : '';

        $style = sprintf(
            '--xero-sm-bg:%s;--xero-sm-card:%s;--xero-sm-text:%s;--xero-sm-accent:%s;',
            esc_attr($bg_color),
            esc_attr($card_color),
            esc_attr($text_color),
            esc_attr($accent_color)
        );
        if ($bg_image !== '') {
            $style .= sprintf("--xero-sm-bg-image:url('%s');", esc_url($bg_image));
        }
?>
        <div id="xero-age-verification-overlay"
            class="xero-site-mode xero-age-verification xero-preset-<?php echo esc_attr($preset); ?><?php echo $bg_image !== '' ? ' has-bg-image' : ''; ?>"
            style="<?php echo esc_attr($style); ?>">
            <div class="xero-age-verification-popup" role="dialog" aria-modal="true" aria-labelledby="xero-age-heading">
                <?php if ($logo_url !== ''): ?>
                    <img class="xero-age-logo" src="<?php echo esc_url($logo_url); ?>" alt="" />
                <?php elseif ($icon !== ''): ?>
                    <div class="xero-age-icon" aria-hidden="true"><?php echo esc_html($icon); ?></div>
                <?php endif; ?>
                <h2 id="xero-age-heading"><?php echo esc_html((string) $this->settings['heading']); ?></h2>
                <p class="xero-age-message"><?php echo wp_kses_post((string) $this->settings['message']); ?></p>
                <div class="xero-age-buttons">
                    <button type="button" id="xero-age-confirm" class="xero-btn xero-btn-primary"><?php echo esc_html((string) $this->settings['confirm_label']); ?></button>
                    <button type="button" id="xero-age-decline" class="xero-btn xero-btn-secondary"><?php echo esc_html((string) $this->settings['decline_label']); ?></button>
                </div>
                <p class="xero-age-declined" hidden><?php echo wp_kses_post((string) $this->settings['declined_text']); ?></p>
            </div>
        </div>
<?php
    }
}
