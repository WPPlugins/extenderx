<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\UIEnhancements;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

class SmoothScrolling {
    use Singleton;

    private $settings = [];
    private $settings_cache;
    private $default_settings = [
        'speed'                 => 800,
        'easing'                => 'ease',
        'header_offset'         => 80,
        'anchor_links'          => true,
        'back_forward'          => true,
        'global_smooth'         => true,
        'respect_reduced_motion' => true,
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
        $stored = $this->settings_cache->get_setting('global.ui_enhancements.smooth_scrolling', []);
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

        $current_id = get_queried_object_id();

        $exclude_page_ids = $this->settings['exclude_page_ids'] ?? [];
        if (is_array($exclude_page_ids) && !empty($exclude_page_ids) && in_array((string)$current_id, $exclude_page_ids, true)) {
            return false;
        }

        $exclude_post_ids = $this->settings['exclude_post_ids'] ?? [];
        if (is_array($exclude_post_ids) && !empty($exclude_post_ids) && in_array((string)$current_id, $exclude_post_ids, true)) {
            return false;
        }

        $exclude_url_patterns = $this->settings['exclude_url_patterns'] ?? [];
        if (is_array($exclude_url_patterns) && !empty($exclude_url_patterns)) {
            $current_url = $_SERVER['REQUEST_URI'] ?? '';
            foreach ($exclude_url_patterns as $pattern) {
                $pattern = trim($pattern);
                if (empty($pattern)) continue;
                $regex = str_replace(['*', '/'], ['.*', '\/'], $pattern);
                if (preg_match('/^' . $regex . '$/i', $current_url)) return false;
            }
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

        wp_enqueue_script('xero-smooth-scrolling');
        wp_enqueue_style('xero-smooth-scrolling');

        $inline_css = $this->generate_inline_css();
        if (!empty($inline_css)) {
            wp_add_inline_style('xero-smooth-scrolling', $inline_css);
        }

        wp_localize_script('xero-smooth-scrolling', 'extenderxSmoothScrollingSettings', [
            'speed'         => intval($this->settings['speed']),
            'easing'        => sanitize_text_field($this->settings['easing']),
            'headerOffset'  => intval($this->settings['header_offset']),
            'anchorLinks'   => (bool)$this->settings['anchor_links'],
            'backForward'   => (bool)$this->settings['back_forward'],
            'globalSmooth'  => (bool)$this->settings['global_smooth'],
        ]);
    }

    private function generate_inline_css() {
        $global_smooth = (bool)$this->settings['global_smooth'];
        $respect_motion = (bool)$this->settings['respect_reduced_motion'];

        $css = '';

        if ($global_smooth) {
            $css .= "html { scroll-behavior: smooth; }
            * { scroll-behavior: smooth; }";
        }

        if ($respect_motion) {
            $css .= "@media (prefers-reduced-motion: reduce) {
                html, * { scroll-behavior: auto; }
            }";
        }

        return $css;
    }
}
