<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\UIEnhancements;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

class ScrollToBottom {
    use Singleton;

    private $settings = [];
    private $settings_cache;
    private $default_settings = [
        'position'              => 'bottom-right',
        'size'                  => 50,
        'icon_style'            => 'arrow-down',
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
        $scroll_to_bottom_settings = $this->settings_cache->get_setting('global.ui_enhancements.scroll_to_bottom', []);
        $this->settings = wp_parse_args($scroll_to_bottom_settings, $this->default_settings);
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

        // 4-6. Page rules (include/exclude list; see Core\PageFilter).
        if (\WPXERO\ExtenderX\Core\PageFilter::blocks($this->settings)) {
            return false;
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

        wp_enqueue_script('xero-scroll-to-bottom');
        wp_enqueue_style('xero-scroll-to-bottom');

        // Generate dynamic inline CSS
        $inline_css = $this->generate_inline_css();
        if (!empty($inline_css)) {
            wp_add_inline_style('xero-scroll-to-bottom', $inline_css);
        }

        // Pass settings to JavaScript
        wp_localize_script(
            'xero-scroll-to-bottom',
            'extenderxScrollToBottomSettings',
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

        $position_css = $this->get_position_css($position, $this->get_edge_offset($position));

        $css = "#xero-scroll-to-bottom {
            {$position_css}
            width: {$size}px;
            height: {$size}px;
            background: {$bg_value};
            color: {$icon_color};
            border-radius: {$border_radius}%;
            opacity: 0;
        }
        #xero-scroll-to-bottom.visible {
            opacity: {$opacity};
        }
        #xero-scroll-to-bottom:hover {
            background: {$bg_hover_value};
        }";

        if (!$show_on_mobile) {
            $css .= "
            @media (max-width: 768px) {
                #xero-scroll-to-bottom { display: none !important; }
            }";
        }

        return $css;
    }

    /**
     * Distance from the anchoring edge. When Back to Top is enabled on the
     * same position, stack above/beside it instead of overlapping.
     */
    private function get_edge_offset($position) {
        $btt = $this->settings_cache->get_setting('global.ui_enhancements.back_to_top', []);
        if (!empty($btt['enabled']) && ($btt['position'] ?? 'bottom-right') === $position) {
            $btt_size = intval($btt['size'] ?? 50);
            return 30 + $btt_size + 10;
        }
        return 30;
    }

    /**
     * Map a position setting to fixed-position CSS.
     */
    private function get_position_css($position, $edge_offset = 30) {
        $offset = intval($edge_offset);
        switch ($position) {
            case 'bottom-left':
                return "bottom: {$offset}px; left: 30px; right: auto;";
            case 'top-right':
                return "top: {$offset}px; right: 30px; bottom: auto;";
            case 'bottom-center':
                return "bottom: {$offset}px; left: 50%; transform: translateX(-50%);";
            case 'top-center':
                return "top: {$offset}px; left: 50%; transform: translateX(-50%);";
            case 'top-left':
                return "top: {$offset}px; left: 30px; right: auto; bottom: auto;";
            case 'bottom-right':
            default:
                return "bottom: {$offset}px; right: 30px;";
        }
    }

    public function render_button() {
        if (!$this->should_apply_on_current_page()) {
            return;
        }

        $icon_style = sanitize_text_field($this->settings['icon_style']);
        $hover_animation = sanitize_text_field($this->settings['hover_animation']);
        $icon_svg = $this->get_icon_svg($icon_style);
?>
        <button id="xero-scroll-to-bottom"
            class="xero-stb-hover-<?php echo esc_attr($hover_animation); ?>"
            aria-label="<?php esc_attr_e('Scroll to bottom', 'extenderx'); ?>">
            <?php echo $icon_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG is hardcoded
            ?>
        </button>
<?php
    }

    private function get_icon_svg($style) {
        switch ($style) {
            case 'chevron-down':
                return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>';
            case 'double-arrow':
                return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="7 13 12 18 17 13"></polyline><polyline points="7 6 12 11 17 6"></polyline></svg>';
            case 'rocket':
                return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" transform="rotate(180)"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/></svg>';
            case 'circle-down':
                return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m8 12 4 4 4-4"/><path d="M12 8v8"/></svg>';
            case 'arrow-down':
            default:
                return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>';
        }
    }

    public function render_effect() {
        $this->render_button();
    }
}
