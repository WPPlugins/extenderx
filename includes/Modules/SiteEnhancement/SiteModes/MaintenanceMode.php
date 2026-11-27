<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SiteModes;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;
use WPXERO\ExtenderX\Core\ProFeatures;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Maintenance Mode
 *
 * Full-page 503 takeover rendered via wp_die (self-contained HTML — enqueued
 * front-end assets do not load inside wp_die, so styles/scripts are inlined).
 *
 * Free: heading, message, icon, preset theme, colors, footer note.
 * Pro (advanced): live countdown, logo + background image, notify form,
 *                 social links, allowed roles + IP whitelist, custom CSS.
 *
 * @since 1.0.0
 */
class MaintenanceMode {
    use Singleton;

    private const SETTINGS_PATH   = 'global.site_modes.maintenance_mode';

    /** @var SettingsCache */
    private $settings_cache;

    /** @var bool */
    private $is_pro = false;

    /** @var array<string, mixed> */
    private $settings = [];

    /** @var array<string, mixed> */
    private $default_settings = [
        // Free.
        'preset'       => 'midnight-navy',
        'icon'         => '🚧',
        'heading'      => 'Under Maintenance',
        'message'      => "We are currently performing scheduled maintenance to improve your experience. We'll be back soon!",
        'footer_text'  => '',
        'bg_start'     => '#0b1120',
        'bg_end'       => '#1e3a8a',
        'text_color'   => '#ffffff',
        'accent_color' => '#22d3ee',
        // Pro.
        'show_countdown' => false,
        'end_date'       => '',
        'logo_url'       => '',
        'bg_image_url'   => '',
        'social_links'   => [],
        'allowed_roles'  => [],
        'allowed_ips'    => [],
        'custom_css'     => '',
    ];

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        $this->is_pro         = ProFeatures::instance()->has_pro_license();
        $this->load_settings();

        add_action('template_redirect', [$this, 'maybe_show_page']);
        SiteModeFullPage::instance()->add_admin_bar_notice(__('Maintenance mode on', 'extenderx'));
    }

    private function load_settings() {
        $stored = $this->settings_cache->get_setting(self::SETTINGS_PATH, []);
        if (!is_array($stored)) {
            $stored = [];
        }
        $this->settings = wp_parse_args($stored, $this->default_settings);
    }

    /**
     * Visitors who may bypass the takeover (admins always; Pro: extra roles/IPs).
     */
    private function user_can_bypass(): bool {
        if (is_user_logged_in() && current_user_can('manage_options')) {
            return true;
        }

        if (!$this->is_pro) {
            return false;
        }

        $roles = $this->settings['allowed_roles'] ?? [];
        if (is_array($roles) && !empty($roles) && is_user_logged_in()) {
            foreach (wp_get_current_user()->roles as $role) {
                if (in_array($role, $roles, true)) {
                    return true;
                }
            }
        }

        $ips = $this->settings['allowed_ips'] ?? [];
        if (is_array($ips) && !empty($ips)) {
            $current_ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
            if ($current_ip !== '' && in_array($current_ip, array_map('trim', $ips), true)) {
                return true;
            }
        }

        return false;
    }

    public function maybe_show_page() {
        if ($this->user_can_bypass()) {
            return;
        }

        SiteModeFullPage::instance()->render_and_exit($this->get_render_args(), 503);
    }

    private function get_render_args(): array {
        return [
            'body_class'   => 'xero-fullpage-maintenance',
            'preset'       => sanitize_html_class((string) $this->settings['preset']) ?: 'midnight-navy',
            'icon'         => (string) $this->settings['icon'],
            'heading'      => (string) $this->settings['heading'],
            'message'      => (string) $this->settings['message'],
            'footer_text'  => (string) $this->settings['footer_text'],
            'bg_start'     => sanitize_hex_color((string) $this->settings['bg_start']) ?: '#0b1120',
            'bg_end'       => sanitize_hex_color((string) $this->settings['bg_end']) ?: '#1e3a8a',
            'text_color'   => sanitize_hex_color((string) $this->settings['text_color']) ?: '#ffffff',
            'accent_color' => sanitize_hex_color((string) $this->settings['accent_color']) ?: '#22d3ee',
            'is_pro'       => $this->is_pro,
            'logo_url'     => $this->is_pro ? trim((string) $this->settings['logo_url']) : '',
            'bg_image_url' => $this->is_pro ? trim((string) $this->settings['bg_image_url']) : '',
            'countdown'    => $this->is_pro && !empty($this->settings['show_countdown']) ? (string) $this->settings['end_date'] : '',
            'social_links' => $this->is_pro && is_array($this->settings['social_links']) ? $this->settings['social_links'] : [],
            'custom_css'   => $this->is_pro ? (string) $this->settings['custom_css'] : '',
            'show_progress' => true,
        ];
    }
}
