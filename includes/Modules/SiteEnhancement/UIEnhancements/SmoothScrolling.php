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
        'lerp'                   => 0.1,
        'duration'               => 1.2,
        'easing'                 => 'default',
        'orientation'            => 'vertical',
        'gesture_orientation'    => 'vertical',
        'smooth_wheel'           => true,
        'sync_touch'             => false,
        'wheel_multiplier'       => 1,
        'touch_multiplier'       => 2,
        'infinite'               => false,
        'anchor_links'           => true,
        'anchor_offset'          => 0,
        'respect_reduced_motion' => true,
        'show_on_mobile'         => true,
        'show_on_tablet'         => true,
        'show_on_desktop'        => true,
        'show_on_post_types'     => [],
        'exclude_page_ids'       => [],
        'exclude_post_ids'       => [],
        'exclude_url_patterns'   => [],
        'visible_to_roles'       => [],
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

        wp_enqueue_script('xero-smooth-scrolling');
        wp_enqueue_style('xero-smooth-scrolling');

        wp_localize_script('xero-smooth-scrolling', 'extenderxSmoothScrollingSettings', [
            'lerp'                 => floatval($this->settings['lerp']),
            'duration'             => floatval($this->settings['duration']),
            'easing'               => sanitize_text_field($this->settings['easing']),
            'orientation'          => sanitize_text_field($this->settings['orientation']),
            'gestureOrientation'   => sanitize_text_field($this->settings['gesture_orientation']),
            'smoothWheel'          => (bool)$this->settings['smooth_wheel'],
            'syncTouch'            => (bool)$this->settings['sync_touch'],
            'wheelMultiplier'      => floatval($this->settings['wheel_multiplier']),
            'touchMultiplier'      => floatval($this->settings['touch_multiplier']),
            'infinite'             => (bool)$this->settings['infinite'],
            'anchorLinks'          => (bool)$this->settings['anchor_links'],
            'anchorOffset'         => intval($this->settings['anchor_offset']),
            'respectReducedMotion' => (bool)$this->settings['respect_reduced_motion'],
        ]);
    }
}
