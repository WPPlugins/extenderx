<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\UIEnhancements;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

class StickyFooter {
    use Singleton;

    private $settings = [];
    private $settings_cache;
    private $default_settings = [
        'selector'              => 'auto',
        'custom_selector'       => '',
        'bg_color'              => '#ffffff',
        'text_color'            => '#333333',
        'bg_opacity'            => 100,
        'shadow'                => 'medium',
        'z_index'               => 9998,
        'auto_hide_on_scroll'   => false,
        'blur_background'       => false,
        'show_on_mobile'        => true,
        'show_on_tablet'        => true,
        'show_on_desktop'       => true,
        'show_on_post_types'    => [],
        'exclude_page_ids'      => [],
        'exclude_post_ids'      => [],
        'exclude_url_patterns'  => [],
        'visible_to_roles'      => [],
    ];

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        $this->load_settings();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    private function load_settings() {
        $stored = $this->settings_cache->get_setting('global.ui_enhancements.sticky_footer', []);
        $this->settings = wp_parse_args($stored, $this->default_settings);
    }

    private function should_apply_on_current_page() {
        if (!$this->check_device_visibility()) return false;
        if (!$this->check_user_role_visibility()) return false;

        $allowed_post_types = $this->settings['show_on_post_types'];
        if (!empty($allowed_post_types) && is_array($allowed_post_types)) {
            $current_post_type = get_post_type();
            if ($current_post_type && !in_array($current_post_type, $allowed_post_types, true)) {
                return false;
            }
        }

        // Page rules (include/exclude list; see Core\PageFilter).
        if (\WPXERO\ExtenderX\Core\PageFilter::blocks($this->settings)) {
            return false;
        }

        return true;
    }

    private function check_device_visibility() {
        $show_mobile  = $this->settings['show_on_mobile'] ?? true;
        $show_tablet  = $this->settings['show_on_tablet'] ?? true;
        $show_desktop = $this->settings['show_on_desktop'] ?? true;
        if (function_exists('wp_is_mobile') && wp_is_mobile()) return $show_mobile || $show_tablet;
        return $show_desktop;
    }

    private function check_user_role_visibility() {
        $roles = $this->settings['visible_to_roles'] ?? [];
        if (empty($roles) || !is_array($roles)) return true;
        if (!is_user_logged_in()) return in_array('logged_out', $roles, true);
        $current_user = wp_get_current_user();
        foreach ($current_user->roles as $role) {
            if (in_array($role, $roles, true)) return true;
        }
        return false;
    }

    public function enqueue_assets() {
        if (!$this->should_apply_on_current_page()) return;

        wp_enqueue_script('xero-sticky-footer');
        wp_enqueue_style('xero-sticky-footer');

        $inline_css = $this->generate_inline_css();
        if (!empty($inline_css)) {
            wp_add_inline_style('xero-sticky-footer', $inline_css);
        }

        $selector = $this->settings['selector'] === 'custom' && !empty($this->settings['custom_selector'])
            ? sanitize_text_field($this->settings['custom_selector'])
            : 'footer, .site-footer, #colophon, .footer';

        wp_localize_script('xero-sticky-footer', 'extenderxStickyFooterSettings', [
            'selector'          => $selector,
            'autoHideOnScroll'  => (bool)$this->settings['auto_hide_on_scroll'],
            'showOnMobile'      => (bool)$this->settings['show_on_mobile'],
        ]);
    }

    private function generate_inline_css() {
        $bg_color   = sanitize_text_field($this->settings['bg_color']) ?: '#ffffff';
        $text_color = sanitize_text_field($this->settings['text_color']) ?: '#333333';
        $bg_opacity = intval($this->settings['bg_opacity']);
        $z_index    = intval($this->settings['z_index']);
        $blur       = (bool)$this->settings['blur_background'];
        $show_mobile = (bool)$this->settings['show_on_mobile'];

        $r = hexdec(substr($bg_color, 1, 2));
        $g = hexdec(substr($bg_color, 3, 2));
        $b = hexdec(substr($bg_color, 5, 2));
        $r_t = hexdec(substr($text_color, 1, 2));
        $g_t = hexdec(substr($text_color, 3, 2));
        $b_t = hexdec(substr($text_color, 5, 2));

        $alpha = $bg_opacity / 100;

        $shadow_map = [
            'none'   => 'none',
            'light'  => '0 -2px 10px rgba(0, 0, 0, 0.05)',
            'medium' => '0 -2px 20px rgba(0, 0, 0, 0.1)',
            'heavy'  => '0 -4px 30px rgba(0, 0, 0, 0.15)',
        ];
        $shadow = $shadow_map[$this->settings['shadow']] ?? $shadow_map['medium'];

        $css = ".xero-sticky-footer {
            position: fixed !important;
            bottom: 0 !important;
            left: 0 !important;
            right: 0 !important;
            width: 100% !important;
            background: rgba({$r}, {$g}, {$b}, {$alpha}) !important;
            box-shadow: {$shadow} !important;
            z-index: {$z_index} !important;
            animation: xeroSlideUp 0.3s ease-out;
            color: rgba({$r_t}, {$g_t}, {$b_t}, {$alpha}) !important;
        }
        @keyframes xeroSlideUp {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }";

        if ($blur) {
            $css .= ".xero-sticky-footer { backdrop-filter: blur(10px) !important; -webkit-backdrop-filter: blur(10px) !important; }";
        }

        if (!$show_mobile) {
            $css .= "@media (max-width: 768px) { .xero-sticky-footer { position: relative !important; } }";
        }

        return $css;
    }
}
