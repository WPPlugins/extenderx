<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\UIEnhancements;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

class ScrollbarStyler {
    use Singleton;

    private $settings = [];
    private $settings_cache;
    private $default_settings = [
        'width'                 => 12,
        'thumb_color'           => '#667eea',
        'thumb_color_hover'     => '#764ba2',
        'track_color'           => '#f1f1f1',
        'border_radius'         => 10,
        'use_gradient'          => true,
        'gradient_end'          => '#764ba2',
        'thumb_border'          => true,
        'dark_mode_support'     => true,
        'dark_track_color'      => '#2d2d2d',
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
        $stored = $this->settings_cache->get_setting('global.ui_enhancements.scrollbar_styler', []);
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

        wp_enqueue_style('xero-scrollbar-styler');

        $inline_css = $this->generate_inline_css();
        if (!empty($inline_css)) {
            wp_add_inline_style('xero-scrollbar-styler', $inline_css);
        }
    }

    private function generate_inline_css() {
        $width        = intval($this->settings['width']);
        $thumb_color  = sanitize_text_field($this->settings['thumb_color']) ?: '#667eea';
        $thumb_hover  = sanitize_text_field($this->settings['thumb_color_hover']) ?: '#764ba2';
        $track_color  = sanitize_text_field($this->settings['track_color']) ?: '#f1f1f1';
        $radius       = intval($this->settings['border_radius']);
        $use_gradient = (bool)$this->settings['use_gradient'];
        $gradient_end = sanitize_text_field($this->settings['gradient_end']) ?: '#764ba2';
        $thumb_border = (bool)$this->settings['thumb_border'];
        $dark_support = (bool)$this->settings['dark_mode_support'];
        $dark_track   = sanitize_text_field($this->settings['dark_track_color']) ?: '#2d2d2d';

        $thumb_bg = $use_gradient
            ? "linear-gradient(135deg, {$thumb_color} 0%, {$gradient_end} 100%)"
            : $thumb_color;

        $thumb_hover_bg = $use_gradient
            ? "linear-gradient(135deg, {$gradient_end} 0%, {$thumb_color} 100%)"
            : $thumb_hover;

        $border_css = $thumb_border ? "border: 2px solid {$track_color};" : '';

        $css = "::-webkit-scrollbar { width: {$width}px; height: {$width}px; }
        ::-webkit-scrollbar-track { background: {$track_color}; border-radius: {$radius}px; }
        ::-webkit-scrollbar-thumb { background: {$thumb_bg}; border-radius: {$radius}px; {$border_css} transition: background 0.3s ease; }
        ::-webkit-scrollbar-thumb:hover { background: {$thumb_hover_bg}; }
        * { scrollbar-width: thin; scrollbar-color: {$thumb_color} {$track_color}; }
        html { scrollbar-gutter: stable; }";

        if ($dark_support) {
            $dark_border = $thumb_border ? "border-color: {$dark_track};" : '';
            $css .= "@media (prefers-color-scheme: dark) {
                ::-webkit-scrollbar-track { background: {$dark_track}; }
                ::-webkit-scrollbar-thumb { {$dark_border} }
                * { scrollbar-color: {$thumb_color} {$dark_track}; }
            }";
        }

        return $css;
    }
}
