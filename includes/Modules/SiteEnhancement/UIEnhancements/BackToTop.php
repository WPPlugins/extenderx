<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\UIEnhancements;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

class BackToTop {
    use Singleton;

    private $settings = [];
    private $settings_cache;
    private $default_settings = [
        'position'              => 'bottom-right',
        'size'                  => 50,
        'icon_style'            => 'arrow-up',
        'bg_color'              => '#667eea',
        'bg_color_hover'        => '#764ba2',
        'bg_gradient_end'       => '#764ba2',
        'bg_hover_gradient_end' => '#531dab',
        'use_gradient'          => false,
        'icon_color'            => '#ffffff',
        'border_radius'         => 50,
        'scroll_offset'         => 300,
        'scroll_speed'          => 300,
        'show_on_mobile'        => true,
        'show_on_tablet'        => true,
        'show_on_desktop'       => true,
        'show_on_post_types'    => [],
        'exclude_page_ids'      => [],
        'exclude_post_ids'      => [],
        'exclude_url_patterns'  => [],
        'visible_to_roles'      => [],
        'opacity'               => 100,
        'hover_animation'       => 'lift',
    ];

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        $this->load_settings();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_button']);
    }

    private function load_settings() {
        $back_to_top_settings = $this->settings_cache->get_setting('global.ui_enhancements.back_to_top', []);
        $this->settings = wp_parse_args($back_to_top_settings, $this->default_settings);
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

        wp_enqueue_script('xero-back-to-top');
        wp_enqueue_style('xero-back-to-top');

        // Generate dynamic inline CSS
        $inline_css = $this->generate_inline_css();
        if (!empty($inline_css)) {
            wp_add_inline_style('xero-back-to-top', $inline_css);
        }

        // Pass settings to JavaScript
        wp_localize_script(
            'xero-back-to-top',
            'extenderxBackToTopSettings',
            [
                'position'       => sanitize_text_field($this->settings['position']),
                'size'           => intval($this->settings['size']),
                'iconStyle'      => sanitize_text_field($this->settings['icon_style']),
                'scrollOffset'   => intval($this->settings['scroll_offset']),
                'scrollSpeed'    => intval($this->settings['scroll_speed']),
                'showOnMobile'   => (bool)$this->settings['show_on_mobile'],
                'hoverAnimation' => sanitize_text_field($this->settings['hover_animation']),
                'opacity'        => intval($this->settings['opacity']),
            ]
        );
    }

    private function generate_inline_css() {
        $size = intval($this->settings['size']);
        $bg_color = sanitize_hex_color($this->settings['bg_color']) ?: '#667eea';
        $bg_hover = sanitize_hex_color($this->settings['bg_color_hover']) ?: '#764ba2';
        $icon_color = sanitize_hex_color($this->settings['icon_color']) ?: '#ffffff';
        $border_radius = intval($this->settings['border_radius']);
        $opacity = intval($this->settings['opacity']) / 100;
        $position = sanitize_text_field($this->settings['position']);
        $show_on_mobile = (bool)$this->settings['show_on_mobile'];
        $use_gradient = !empty($this->settings['use_gradient']);
        $bg_gradient_end = sanitize_hex_color($this->settings['bg_gradient_end']) ?: '#764ba2';
        $bg_hover_gradient_end = sanitize_hex_color($this->settings['bg_hover_gradient_end']) ?: '#531dab';

        // Build background value
        $bg_value = $bg_color;
        $bg_hover_value = $bg_hover;
        if ($use_gradient) {
            $bg_value = "linear-gradient(135deg, {$bg_color}, {$bg_gradient_end})";
            $bg_hover_value = "linear-gradient(135deg, {$bg_hover}, {$bg_hover_gradient_end})";
        }

        // Position mapping
        $position_css = '';
        switch ($position) {
            case 'bottom-left':
                $position_css = 'bottom: 30px; left: 30px; right: auto;';
                break;
            case 'top-right':
                $position_css = 'top: 30px; right: 30px; bottom: auto;';
                break;
            case 'bottom-center':
                $position_css = 'bottom: 30px; left: 50%; transform: translateX(-50%);';
                break;
            case 'top-center':
                $position_css = 'top: 30px; left: 50%; transform: translateX(-50%);';
                break;
            case 'top-left':
                $position_css = 'top: 30px; left: 30px; right: auto; bottom: auto;';
                break;
            case 'bottom-right':
            default:
                $position_css = 'bottom: 30px; right: 30px;';
                break;
        }

        $css = "#xero-back-to-top {
            {$position_css}
            width: {$size}px;
            height: {$size}px;
            background: {$bg_value};
            color: {$icon_color};
            border-radius: {$border_radius}%;
            opacity: 0;
        }
        #xero-back-to-top.visible {
            opacity: {$opacity};
        }
        #xero-back-to-top:hover {
            background: {$bg_hover_value};
        }";

        if (!$show_on_mobile) {
            $css .= "
            @media (max-width: 768px) {
                #xero-back-to-top { display: none !important; }
            }";
        }

        return $css;
    }

    public function render_button() {
        if (!$this->should_apply_on_current_page()) {
            return;
        }

        $icon_style = sanitize_text_field($this->settings['icon_style']);
        $hover_animation = sanitize_text_field($this->settings['hover_animation']);
        $icon_svg = $this->get_icon_svg($icon_style);
?>
        <button id="xero-back-to-top"
            class="xero-btt-hover-<?php echo esc_attr($hover_animation); ?>"
            aria-label="<?php esc_attr_e('Back to top', 'extenderx'); ?>">
            <?php echo $icon_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG is hardcoded
            ?>
        </button>
<?php
    }

    private function get_icon_svg($style) {
        switch ($style) {
            case 'chevron-up':
                return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>';
            case 'double-arrow':
                return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 11 12 6 7 11"></polyline><polyline points="17 18 12 13 7 18"></polyline></svg>';
            case 'rocket':
                return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/></svg>';
            case 'circle-up':
                return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m16 12-4-4-4 4"/><path d="M12 16V8"/></svg>';
            case 'arrow-up':
            default:
                return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5 12l7-7 7 7"/></svg>';
        }
    }

    public function render_effect() {
        $this->render_button();
    }
}
