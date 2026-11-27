<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Animations;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Pro scroll-linked engine (parallax / scrub).
 *
 * Gated by the `scroll_animations` premium key (enforced in
 * Animations::initialize_features). Conditionally enqueues the rAF-batched
 * runtime and localizes its config. Desktop-only by default.
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement\Animations
 * @since 1.9.0
 */
class ScrollEffects {
    use Singleton;

    /** @var SettingsCache */
    private $settings_cache;

    private const PREFIX = 'global.animations.scroll_effects.';

    // 'sticky' was dropped in 1.10 — it only toggled position:sticky (no
    // animation) and fought theme sticky headers; stored values coerce to
    // 'parallax' via the whitelist below.
    private const TYPES = ['parallax', 'scrub'];

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        add_action('wp_enqueue_scripts', [$this, 'maybe_enqueue']);
    }

    /**
     * Conditionally enqueue the runtime + hand it its config.
     *
     * @return void
     */
    public function maybe_enqueue() {
        if (!AssetResolver::should_load_scroll()) {
            return;
        }

        wp_enqueue_style('xero-scroll-effects');
        wp_enqueue_script('xero-scroll-effects');
        // Not wp_localize_script: that string-casts every scalar ("30", "1"),
        // leaving the runtime to coerce types back. json_encode preserves them.
        wp_add_inline_script(
            'xero-scroll-effects',
            'window.xeroScrollConfig = ' . wp_json_encode($this->get_config()) . ';',
            'before'
        );
    }

    /**
     * Build the runtime config from settings.
     *
     * @return array<string,mixed>
     */
    private function get_config(): array {
        $get = function ($key, $default) {
            return $this->settings_cache->get_setting(self::PREFIX . $key, $default);
        };

        $type = $get('type', 'parallax');
        if (!in_array($type, self::TYPES, true)) {
            $type = 'parallax';
        }

        return [
            'type'        => $type,
            'intensity'   => max(0, min(100, (int) $get('intensity', 30))),
            'desktopOnly' => (bool) $get('desktop_only', true),
            'selectors'   => trim((string) $get('selectors', '')),
        ];
    }
}
