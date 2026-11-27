<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

class Preloader {
    use Singleton;

    private $settings = [];
    private $settings_cache;
    private $default_settings = [
        'style'                => 'spinner',
        'spinner_color'        => '#667eea',
        'background_color'     => '#ffffff',
        'background_opacity'   => 100,
        'size'                 => 48,
        'logo_url'             => '',
        'logo_width'           => 120,
        'min_duration'         => 500,
        'fade_duration'        => 500,
        'hide_on_window_load'  => true,
        'disable_scroll'       => true,
        'show_on_mobile'       => true,
        'show_on_tablet'       => true,
        'show_on_desktop'      => true,
        'show_on_post_types'   => [],
        'exclude_page_ids'     => [],
        'exclude_post_ids'     => [],
        'exclude_url_patterns' => [],
        'visible_to_roles'     => [],
    ];

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        $this->load_settings();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_body_open', [$this, 'render_effect']);
    }

    private function load_settings() {
        $stored = $this->settings_cache->get_setting('global.appearance.preloader', []);
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

        wp_enqueue_style('xero-preloader');
        wp_enqueue_script('xero-preloader');

        wp_localize_script('xero-preloader', 'extenderxPreloaderSettings', [
            'minDuration'      => intval($this->settings['min_duration']),
            'fadeDuration'     => intval($this->settings['fade_duration']),
            'hideOnWindowLoad' => (bool)$this->settings['hide_on_window_load'],
            'disableScroll'    => (bool)$this->settings['disable_scroll'],
        ]);
    }

    public function render_effect() {
        if (!$this->should_apply_on_current_page()) return;

        $style       = sanitize_text_field($this->settings['style']);
        $bg          = sanitize_hex_color($this->settings['background_color']) ?: '#ffffff';
        $spinner     = sanitize_hex_color($this->settings['spinner_color']) ?: '#667eea';
        $opacity     = max(0, min(100, intval($this->settings['background_opacity']))) / 100;
        $size        = max(8, intval($this->settings['size']));
        $fade        = intval($this->settings['fade_duration']);
        $logo_url    = esc_url($this->settings['logo_url']);
        $logo_width  = max(16, intval($this->settings['logo_width']));

        $inline_style = sprintf(
            '--xero-preloader-bg:%1$s;--xero-preloader-opacity:%2$s;--xero-preloader-color:%3$s;--xero-preloader-size:%4$dpx;--xero-preloader-fade:%5$dms;',
            esc_attr($bg),
            esc_attr((string)$opacity),
            esc_attr($spinner),
            $size,
            $fade
        );

        $inner = $this->get_inner_markup($style, $logo_url, $logo_width);
?>
        <div id="xero-preloader"
            class="xero-appearance-effect xero-preloader-overlay xero-preloader-style-<?php echo esc_attr($style); ?>"
            data-style="<?php echo esc_attr($style); ?>"
            style="<?php echo esc_attr($inline_style); ?>">
            <div class="xero-preloader-content">
                <?php echo $inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts below ?>
            </div>
        </div>
<?php
    }

    private function get_inner_markup($style, $logo_url, $logo_width) {
        switch ($style) {
            case 'dots':
                return '<div class="xero-preloader-dots"><span></span><span></span><span></span></div>';
            case 'bars':
                return '<div class="xero-preloader-bars"><span></span><span></span><span></span><span></span></div>';
            case 'pulse':
                return '<div class="xero-preloader-pulse"></div>';
            case 'custom':
                if (!empty($logo_url)) {
                    return sprintf(
                        '<img class="xero-preloader-logo" src="%1$s" alt="%2$s" style="width:%3$dpx" />',
                        esc_url($logo_url),
                        esc_attr__('Loading', 'extenderx'),
                        (int)$logo_width
                    );
                }
                return '<div class="xero-preloader-spinner"></div>';
            case 'spinner':
            default:
                return '<div class="xero-preloader-spinner"></div>';
        }
    }
}
