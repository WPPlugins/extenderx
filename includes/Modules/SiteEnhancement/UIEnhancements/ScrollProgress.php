<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\UIEnhancements;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

class ScrollProgress {
    use Singleton;

    private $settings = [];
    private $settings_cache;
    private $default_settings = [
        'position'              => 'top',
        'height'                => 4,
        'color'                 => '#667eea',
        'gradient_end'          => '#764ba2',
        'use_gradient'          => false,
        'background_color'      => 'rgba(0, 0, 0, 0.05)',
        'border_radius'         => 0,
        'show_on_mobile'        => true,
        'show_on_tablet'        => true,
        'show_on_desktop'       => true,
        'show_on_post_types'    => [],
        'exclude_page_ids'      => [],
        'exclude_post_ids'      => [],
        'exclude_url_patterns'  => [],
        'visible_to_roles'      => [],
        'opacity'               => 100,
        'z_index'               => 99999,
    ];

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        $this->load_settings();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_progress_bar']);
    }

    private function load_settings() {
        $scroll_progress_settings = $this->settings_cache->get_setting('global.ui_enhancements.scroll_progress', []);
        $this->settings = wp_parse_args($scroll_progress_settings, $this->default_settings);
    }

    private function should_apply_on_current_page() {
        // 1. Device targeting
        if (!$this->check_device_visibility()) {
            return false;
        }

        // 2. User role targeting
        if (!$this->check_user_role_visibility()) {
            return false;
        }

        // 3. Post type restrictions
        $allowed_post_types = $this->settings['show_on_post_types'];
        if (!empty($allowed_post_types) && is_array($allowed_post_types)) {
            $current_post_type = get_post_type();
            if ($current_post_type && !in_array($current_post_type, $allowed_post_types, true)) {
                return false;
            }
        }

        // 4. Page ID exclusions
        $exclude_page_ids = $this->settings['exclude_page_ids'] ?? [];
        if (is_array($exclude_page_ids) && !empty($exclude_page_ids)) {
            $current_id = get_queried_object_id();
            if (in_array((string)$current_id, $exclude_page_ids, true)) {
                return false;
            }
        }

        // 5. Post ID exclusions
        $exclude_post_ids = $this->settings['exclude_post_ids'] ?? [];
        if (is_array($exclude_post_ids) && !empty($exclude_post_ids)) {
            $current_id = get_queried_object_id();
            if (in_array((string)$current_id, $exclude_post_ids, true)) {
                return false;
            }
        }

        // 6. URL pattern exclusions
        $exclude_url_patterns = $this->settings['exclude_url_patterns'] ?? [];
        if (is_array($exclude_url_patterns) && !empty($exclude_url_patterns)) {
            $current_url = $_SERVER['REQUEST_URI'] ?? '';
            foreach ($exclude_url_patterns as $pattern) {
                $pattern = trim($pattern);
                if (empty($pattern)) continue;

                // Convert wildcard pattern to regex
                $regex_pattern = str_replace(['*', '/'], ['.*', '\/'], $pattern);
                if (preg_match('/^' . $regex_pattern . '$/i', $current_url)) {
                    return false;
                }
            }
        }

        return true;
    }

    private function check_device_visibility() {
        $show_on_mobile  = $this->settings['show_on_mobile'] ?? true;
        $show_on_tablet  = $this->settings['show_on_tablet'] ?? true;
        $show_on_desktop = $this->settings['show_on_desktop'] ?? true;

        // Simple device detection using wp_is_mobile()
        if (function_exists('wp_is_mobile') && wp_is_mobile()) {
            // Mobile or tablet - for simplicity, treat tablet as mobile
            // You could add more sophisticated detection using user agent if needed
            return $show_on_mobile || $show_on_tablet;
        }

        // Desktop
        return $show_on_desktop;
    }

    private function check_user_role_visibility() {
        $visible_to_roles = $this->settings['visible_to_roles'] ?? [];

        // Empty array means visible to everyone
        if (empty($visible_to_roles) || !is_array($visible_to_roles)) {
            return true;
        }

        // Check if user is logged out
        if (!is_user_logged_in()) {
            return in_array('logged_out', $visible_to_roles, true);
        }

        // Check if current user has any of the allowed roles
        $current_user = wp_get_current_user();
        if (empty($current_user->roles)) {
            return false;
        }

        foreach ($current_user->roles as $role) {
            if (in_array($role, $visible_to_roles, true)) {
                return true;
            }
        }

        return false;
    }

    public function enqueue_assets() {
        if (!$this->should_apply_on_current_page()) {
            return;
        }

        wp_enqueue_script('xero-scroll-progress');
        wp_enqueue_style('xero-scroll-progress');

        // Generate dynamic inline CSS
        $inline_css = $this->generate_inline_css();
        if (!empty($inline_css)) {
            wp_add_inline_style('xero-scroll-progress', $inline_css);
        }

        // Pass settings to JavaScript
        wp_localize_script(
            'xero-scroll-progress',
            'extenderxScrollProgressSettings',
            [
                'position'     => sanitize_text_field($this->settings['position']),
                'height'       => intval($this->settings['height']),
                'showOnMobile' => (bool)$this->settings['show_on_mobile'],
            ]
        );
    }

    private function generate_inline_css() {
        $position = sanitize_text_field($this->settings['position']);
        $height = intval($this->settings['height']);
        $color = sanitize_text_field($this->settings['color']) ?: '#667eea';
        $gradient_end = sanitize_text_field($this->settings['gradient_end']) ?: '#764ba2';
        $use_gradient = (bool)$this->settings['use_gradient'];
        $bg_color = sanitize_text_field($this->settings['background_color']) ?: 'rgba(0, 0, 0, 0.05)';
        $border_radius = intval($this->settings['border_radius']);
        $opacity = intval($this->settings['opacity']) / 100;
        $z_index = intval($this->settings['z_index']);
        $show_on_mobile = (bool)$this->settings['show_on_mobile'];

        // Position mapping
        $position_css = ($position === 'bottom')
            ? 'bottom: 0; top: auto;'
            : 'top: 0; bottom: auto;';

        // Bar color
        $bar_bg = $use_gradient
            ? "linear-gradient(90deg, {$color} 0%, {$gradient_end} 100%)"
            : $color;

        $css = "#xero-scroll-progress {
            {$position_css}
            height: {$height}px;
            background: {$bg_color};
            z-index: {$z_index};
            opacity: {$opacity};
        }
        #xero-scroll-progress .xero-progress-bar {
            background: {$bar_bg};
            border-radius: {$border_radius}px;
            box-shadow: 0 0 10px {$color}80;
        }";

        // Admin bar offset
        if ($position === 'top') {
            $css .= "
            body.admin-bar #xero-scroll-progress {
                top: 32px;
            }
            @media (max-width: 782px) {
                body.admin-bar #xero-scroll-progress {
                    top: 46px;
                }
            }";
        }

        if (!$show_on_mobile) {
            $css .= "
            @media (max-width: 768px) {
                #xero-scroll-progress { display: none !important; }
            }";
        }

        return $css;
    }

    public function render_progress_bar() {
        if (!$this->should_apply_on_current_page()) {
            return;
        }
?>
        <div id="xero-scroll-progress">
            <div class="xero-progress-bar"></div>
        </div>
<?php
    }

    public function render_effect() {
        $this->render_progress_bar();
    }
}
