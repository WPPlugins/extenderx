<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Marketing;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;
use WPXERO\ExtenderX\Core\ProFeatures;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Floating CTA Button
 *
 * Free: text, url, icon, position, colors.
 * Pro (advanced): scheduling, scroll-trigger reveal, entrance/idle animation,
 *                 tooltip, open in new tab, device/role/page targeting.
 *
 * @since 1.0.0
 */
class FloatingCtaButton {
    use Singleton;
    use MarketingVisibility;

    private const PRO_FEATURE_KEY = 'floating_cta_button_advanced';

    /** @var SettingsCache */
    private $settings_cache;

    /** @var bool */
    private $is_pro = false;

    /** @var array<string, mixed> */
    private $settings = [];

    /** @var array<string, mixed> */
    private $default_settings = [
        // Free.
        'text'       => 'Get Started',
        'url'        => '#',
        'icon'       => '🎯',
        'position'   => 'bottom-right',
        'bg_color'   => '#667eea',
        'text_color' => '#ffffff',
        // Pro — behaviour.
        'new_tab'           => false,
        'animation'         => 'none',
        'scroll_trigger'    => false,
        'scroll_offset'     => 300,
        'hide_at_footer'    => false,
        // Pro — tooltip.
        'tooltip_enabled' => false,
        'tooltip_text'    => '',
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
        $stored = $this->settings_cache->get_setting('global.marketing.floating_cta_button', []);
        if (!is_array($stored)) {
            $stored = [];
        }
        $this->settings = wp_parse_args($stored, $this->default_settings);
    }

    private function should_render(): bool {
        if (trim((string) $this->settings['text']) === '') {
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

        wp_enqueue_style('xero-floating-cta-button');
        wp_enqueue_script('xero-floating-cta-button');

        wp_localize_script('xero-floating-cta-button', 'extenderxFloatingCta', [
            'scrollTrigger' => $this->is_pro && !empty($this->settings['scroll_trigger']),
            'scrollOffset'  => max(0, (int) $this->settings['scroll_offset']),
            'hideAtFooter'  => $this->is_pro && !empty($this->settings['hide_at_footer']),
        ]);
    }

    public function render_effect() {
        if (!$this->should_render()) {
            return;
        }

        $text       = (string) $this->settings['text'];
        $url        = (string) $this->settings['url'];
        $icon       = (string) $this->settings['icon'];
        $position   = in_array($this->settings['position'], ['bottom-right', 'bottom-left', 'top-right', 'top-left'], true)
            ? $this->settings['position'] : 'bottom-right';
        $bg_color   = sanitize_hex_color((string) $this->settings['bg_color']) ?: '#667eea';
        $text_color = sanitize_hex_color((string) $this->settings['text_color']) ?: '#ffffff';

        $animation       = $this->is_pro ? sanitize_key((string) $this->settings['animation']) : 'none';
        $new_tab         = $this->is_pro && !empty($this->settings['new_tab']);
        $scroll_trigger  = $this->is_pro && !empty($this->settings['scroll_trigger']);
        $tooltip_enabled = $this->is_pro && !empty($this->settings['tooltip_enabled']) && trim((string) $this->settings['tooltip_text']) !== '';

        $classes = [
            'xero-marketing-effect',
            'xero-floating-cta-' . $position,
            'xero-anim-' . $animation,
        ];
        if ($scroll_trigger) {
            $classes[] = 'xero-scroll-hidden';
        }
?>
        <a href="<?php echo esc_url($url); ?>"
            id="xero-floating-cta"
            class="<?php echo esc_attr(implode(' ', $classes)); ?>"
            <?php echo $new_tab ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
            data-position="<?php echo esc_attr($position); ?>"
            style="background-color: <?php echo esc_attr($bg_color); ?>; color: <?php echo esc_attr($text_color); ?>;">
            <?php if ($icon !== ''): ?>
                <span class="xero-cta-icon"><?php echo esc_html($icon); ?></span>
            <?php endif; ?>
            <span class="xero-cta-text"><?php echo esc_html($text); ?></span>
            <?php if ($tooltip_enabled): ?>
                <span class="xero-cta-tooltip"><?php echo esc_html((string) $this->settings['tooltip_text']); ?></span>
            <?php endif; ?>
        </a>
<?php
    }
}
