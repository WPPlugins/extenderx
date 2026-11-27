<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Marketing;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;
use WPXERO\ExtenderX\Core\ProFeatures;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * CTA Pulse Animation
 *
 * Draws attention to call-to-action elements with an attention pulse.
 *
 * Free: pulse colour, intensity, target selector.
 * Pro (advanced): alternate animation styles (glow/ripple/shake), speed,
 *                 pause-on-hover, scheduling, device/role/page targeting.
 *
 * @since 1.0.0
 */
class CtaPulseAnimation {
    use Singleton;
    use MarketingVisibility;

    private const PRO_FEATURE_KEY = 'cta_pulse_animation_advanced';

    private const ALLOWED_STYLES = ['pulse', 'glow', 'ripple', 'shake'];
    private const ALLOWED_SPEEDS = ['slow', 'normal', 'fast'];

    /** @var SettingsCache */
    private $settings_cache;

    /** @var bool */
    private $is_pro = false;

    /** @var array<string, mixed> */
    private $settings = [];

    /** @var array<string, mixed> */
    private $default_settings = [
        // Free.
        'color'     => '#667eea',
        'intensity' => 50,
        'selector'  => '.wp-block-button__link, .btn, button[type="submit"], input[type="submit"]',
        // Pro.
        'style'         => 'pulse',
        'speed'         => 'normal',
        'pause_on_hover' => true,
        // Pro — scheduling.
        'schedule_enabled' => false,
        'start_date'       => '',
        'end_date'         => '',
        // Pro — targeting.
        'show_on_mobile'       => true,
        'show_on_tablet'       => true,
        'show_on_desktop'      => true,
        'visible_to_roles'     => [],
        'exclude_page_ids'     => [],
        'exclude_url_patterns' => [],
    ];

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        $this->is_pro         = ProFeatures::instance()->can_use_premium_feature(self::PRO_FEATURE_KEY);
        $this->load_settings();

        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_effect']);
    }

    private function load_settings() {
        $stored = $this->settings_cache->get_setting('global.marketing.cta_pulse_animation', []);
        if (!is_array($stored)) {
            $stored = [];
        }
        $this->settings = wp_parse_args($stored, $this->default_settings);
    }

    private function should_render(): bool {
        if (trim((string) $this->settings['selector']) === '') {
            return false;
        }
        if (!$this->is_pro) {
            return true;
        }
        return $this->passes_targeting();
    }

    public function enqueue_assets() {
        if (!$this->should_render()) {
            return;
        }
        wp_enqueue_style('xero-cta-pulse-animation');
        wp_enqueue_script('xero-cta-pulse-animation');
    }

    public function render_effect() {
        if (!$this->should_render()) {
            return;
        }

        $color     = sanitize_hex_color((string) $this->settings['color']) ?: '#667eea';
        $intensity = max(0, min(100, (int) $this->settings['intensity']));
        $selector  = (string) $this->settings['selector'];

        $style = $this->is_pro && in_array($this->settings['style'], self::ALLOWED_STYLES, true)
            ? $this->settings['style'] : 'pulse';
        $speed = $this->is_pro && in_array($this->settings['speed'], self::ALLOWED_SPEEDS, true)
            ? $this->settings['speed'] : 'normal';
        $pause_on_hover = $this->is_pro ? (bool) $this->settings['pause_on_hover'] : false;
?>
        <div id="xero-cta-pulse"
            class="xero-marketing-effect"
            hidden
            data-color="<?php echo esc_attr($color); ?>"
            data-intensity="<?php echo esc_attr((string) $intensity); ?>"
            data-selector="<?php echo esc_attr($selector); ?>"
            data-style="<?php echo esc_attr($style); ?>"
            data-speed="<?php echo esc_attr($speed); ?>"
            data-pause-on-hover="<?php echo esc_attr($pause_on_hover ? '1' : '0'); ?>">
        </div>
<?php
    }
}
