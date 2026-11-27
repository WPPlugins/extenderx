<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

class SkeletonLoader {
    use Singleton;

    private $settings = [];
    private $settings_cache;
    private $default_settings = [
        'style'                => 'wave',
        'base_color'           => '#e2e5e9',
        'highlight_color'      => '#f2f4f6',
        'background_color'     => '#ffffff',
        'animation_speed'      => 1.5,
        'border_radius'        => 6,
        'lines'                => 4,
        'show_avatar'          => true,
        'show_image'           => false,
        'min_duration'         => 300,
        'fade_duration'        => 300,
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
        $stored = $this->settings_cache->get_setting('global.appearance.skeleton_loader', []);
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

        wp_enqueue_style('xero-skeleton-loader');
        wp_enqueue_script('xero-skeleton-loader');

        wp_localize_script('xero-skeleton-loader', 'extenderxSkeletonLoaderSettings', [
            'minDuration'   => intval($this->settings['min_duration']),
            'fadeDuration'  => intval($this->settings['fade_duration']),
            'disableScroll' => (bool)$this->settings['disable_scroll'],
        ]);
    }

    public function render_effect() {
        if (!$this->should_apply_on_current_page()) return;

        $style      = sanitize_text_field($this->settings['style']);
        $base       = sanitize_hex_color($this->settings['base_color']) ?: '#e2e5e9';
        $highlight  = sanitize_hex_color($this->settings['highlight_color']) ?: '#f2f4f6';
        $bg         = sanitize_hex_color($this->settings['background_color']) ?: '#ffffff';
        $speed      = max(0.2, floatval($this->settings['animation_speed']));
        $radius     = max(0, intval($this->settings['border_radius']));
        $fade       = intval($this->settings['fade_duration']);
        $lines      = max(1, min(12, intval($this->settings['lines'])));
        $show_avatar = (bool)$this->settings['show_avatar'];
        $show_image  = (bool)$this->settings['show_image'];

        $inline_style = sprintf(
            '--xero-skeleton-base:%1$s;--xero-skeleton-highlight:%2$s;--xero-skeleton-bg:%3$s;--xero-skeleton-speed:%4$ss;--xero-skeleton-radius:%5$dpx;--xero-skeleton-fade:%6$dms;',
            esc_attr($base),
            esc_attr($highlight),
            esc_attr($bg),
            esc_attr((string)$speed),
            $radius,
            $fade
        );
?>
        <div id="xero-skeleton-loader"
            class="xero-appearance-effect xero-skeleton-overlay xero-skeleton-style-<?php echo esc_attr($style); ?>"
            data-style="<?php echo esc_attr($style); ?>"
            style="<?php echo esc_attr($inline_style); ?>">
            <div class="xero-skeleton-wrapper">
                <?php if ($show_avatar) : ?>
                    <div class="xero-skeleton-avatar-row">
                        <div class="xero-skeleton-avatar"></div>
                        <div class="xero-skeleton-avatar-meta">
                            <div class="xero-skeleton-line" style="width:40%"></div>
                            <div class="xero-skeleton-line" style="width:25%"></div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($show_image) : ?>
                    <div class="xero-skeleton-image"></div>
                <?php endif; ?>

                <div class="xero-skeleton-header">
                    <div class="xero-skeleton-line" style="width:60%"></div>
                </div>

                <div class="xero-skeleton-content">
                    <?php for ($i = 0; $i < $lines; $i++) :
                        $width = 100 - ($i * 5);
                        if ($width < 70) $width = 70 + (($i % 3) * 8);
                    ?>
                        <div class="xero-skeleton-line" style="width:<?php echo esc_attr($width); ?>%"></div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
<?php
    }
}
