<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Animations\SiteWide;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;
use WPXERO\ExtenderX\Core\ProFeatures;

defined('ABSPATH') || exit;

/**
 * Site-Wide Animations engine — orchestration (rebuild).
 *
 * Phase 2 scope: the conditional loader wiring + the FOUC-safe `<head>`
 * injection mechanism + runtime config localization. The IntersectionObserver
 * reveal runtime itself (the bundle behind `xero-site-wide`) lands in Phase 3;
 * this class registers/enqueues it behind {@see SiteWideResolver} and hands it
 * the config it will consume.
 *
 * FOUC / no-JS contract (honored by the Phase 3 runtime):
 *   - An inline `<head>` bootstrap adds `xero-sw` to <html> *before paint*, and
 *     schedules a failsafe that removes it after `failsafeMs` if the runtime
 *     never claims the page (sets `html.xero-sw-ready`). So:
 *       • scripting disabled  → bootstrap never runs → no hiding → content shown;
 *       • runtime bundle 404s → failsafe fires → hiding removed → content shown.
 *   - Critical CSS hides targets only while `html.xero-sw` is present and the
 *     element has not yet been revealed (`.xero-sw-in`). In `specific` mode the
 *     server pre-hides the exact block selectors (zero flash); `auto` mode and
 *     pro custom-selector mode rely on the runtime marking elements.
 *   - Hiding is `opacity:0` only — no layout property — so there is no CLS and a
 *     no-JS page reflows identically to a revealed one.
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement\Animations\SiteWide
 * @since 1.10.0
 */
class SiteWide {
    use Singleton;

    /** @var SettingsCache */
    private $settings_cache;

    private const PREFIX = 'global.animations.site_wide.';

    /** Script/style handle (registered in Core\RegisterAssets). */
    public const HANDLE = 'xero-site-wide';

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        add_action('wp_enqueue_scripts', [$this, 'maybe_enqueue']);
        // Critical CSS + bootstrap must land in <head> before paint.
        add_action('wp_head', [$this, 'maybe_inject_head'], 2);

        // Block-level opt-out: stamp the runtime's skip class on any block the
        // editor flagged. Attached only when the feature is on, so disabled
        // installs pay no per-block cost. Reads the same `extenderx.entrance
        // .disabled` attribute the existing "Disable site animations" block
        // toggle writes, so the rebuilt engine honors it independently of the
        // legacy entrance engine.
        if ((bool) $this->settings_cache->get_setting(self::PREFIX . 'enabled', false)) {
            add_filter('render_block', [$this, 'mark_disabled_blocks'], 10, 2);
        }
    }

    /**
     * Add the `xero-sw-skip` class to any block that opted out of site-wide
     * animations via the block inspector. Fast-paths the overwhelming majority
     * of blocks that carry no flag.
     *
     * @param string $block_content Rendered block HTML.
     * @param array  $parsed_block  Parsed block (attrs etc).
     * @return string
     */
    public function mark_disabled_blocks($block_content, $parsed_block) {
        if (empty($parsed_block['attrs']['extenderx']['entrance']['disabled'])) {
            return $block_content;
        }
        if (!class_exists('\WP_HTML_Tag_Processor')) {
            return $block_content;
        }
        $processor = new \WP_HTML_Tag_Processor($block_content);
        if (!$processor->next_tag()) {
            return $block_content;
        }
        $processor->add_class('xero-sw-skip');
        return $processor->get_updated_html();
    }

    /**
     * Conditionally enqueue the runtime + hand it its config.
     *
     * @return void
     */
    public function maybe_enqueue() {
        if (!SiteWideResolver::should_load()) {
            return;
        }

        wp_enqueue_style(self::HANDLE);
        wp_enqueue_script(self::HANDLE);
        // Not wp_localize_script: that string-casts every scalar ("500", "1"),
        // leaving the runtime to coerce types back. json_encode preserves them.
        wp_add_inline_script(
            self::HANDLE,
            'window.xeroSiteWideConfig = ' . wp_json_encode($this->get_config()) . ';',
            'before'
        );
    }

    /**
     * Inject the pre-paint bootstrap script + critical CSS into <head>.
     *
     * @return void
     */
    public function maybe_inject_head() {
        if (!SiteWideResolver::should_load()) {
            return;
        }

        $config       = $this->get_config();
        $failsafe_ms  = (int) $config['failsafeMs'];

        // Bootstrap: add the root class before paint, then a failsafe that
        // reveals everything if the runtime never claims the page.
        $boot = sprintf(
            '(function(d){var r=d.documentElement;r.className+=" xero-sw";' .
            'setTimeout(function(){if(!r.classList.contains("xero-sw-ready")){r.classList.remove("xero-sw");}},%d);})(document);',
            max(1000, $failsafe_ms)
        );
        echo "<script id=\"xero-sw-boot\">{$boot}</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- machine-generated, no user input.

        $css = $this->build_critical_css($config);
        if ('' !== $css) {
            echo "<style id=\"xero-sw-critical\">{$css}</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- selectors are catalog-derived, not user input.
        }
    }

    /**
     * Build the critical (pre-paint) hide CSS.
     *
     * `specific` mode pre-hides the exact block selectors. `auto` mode
     * pre-hides the catalog's content set inside safe content roots — without
     * this, above-the-fold content paints visible, is hidden by the runtime at
     * DOMContentLoaded, then animates back in (a visible double render on slow
     * connections). Both sets are catalog-derived, never raw user input, and
     * the runtime releases every pre-hidden element it does not animate. Pro
     * custom selectors are never pre-hidden (raw user input; they flash-free
     * only after the runtime marks them).
     *
     * @param array $config
     * @return string CSS (no surrounding <style>), or '' when not applicable.
     */
    private function build_critical_css(array $config): string {
        if ('specific' === $config['targetMode']) {
            // Pro custom selectors may extend the target set beyond the known
            // blocks; the block set itself is still safe to pre-hide.
            $selectors = SiteWideCatalog::selectors_for($config['targets']);
        } else {
            $selectors = $config['criticalSelectors'];
        }

        if (empty($selectors)) {
            return '';
        }

        // html.xero-sw .wp-block-image:not(.xero-sw-in):not(.xero-sw-done){opacity:0}
        // `.xero-sw-done` is the runtime's "finished, handed back to the theme"
        // marker — it must stay excluded here or elements re-hide the moment
        // the runtime strips `.xero-sw-in` after the reveal completes.
        $scoped = array_map(
            static function ($sel) {
                return 'html.xero-sw ' . $sel . ':not(.xero-sw-in):not(.xero-sw-done)';
            },
            $selectors
        );

        return implode(',', $scoped) . '{opacity:0}';
    }

    /**
     * Build the runtime config from settings, enforcing the free/pro boundary
     * server-side (defense-in-depth on top of the admin UI gating).
     *
     * @return array<string,mixed>
     */
    private function get_config(): array {
        $is_pro = ProFeatures::instance()->has_pro_license();
        $get    = function ($key, $default) {
            return $this->settings_cache->get_setting(self::PREFIX . $key, $default);
        };

        $defaults = SiteWideCatalog::defaults();

        $duration    = $this->clamp_int($get('duration', $defaults['duration']), 0, 3000);
        $delay       = $this->clamp_int($get('delay', $defaults['delay']), 0, 3000);
        $target_mode = 'specific' === $get('target_mode', $defaults['target_mode']) ? 'specific' : 'auto';
        $targets     = SiteWideCatalog::sanitize_targets($get('targets', $defaults['targets']));

        $config = [
            'style'      => SiteWideCatalog::sanitize_style($get('style', $defaults['style']), $is_pro),
            'easing'     => SiteWideCatalog::sanitize_easing($get('easing', $defaults['easing']), $is_pro),
            'duration'   => $duration,
            'delay'      => $delay,
            'distance'   => $this->clamp_int($get('distance', $defaults['distance']), 0, 200),
            'threshold'  => $this->clamp_float($get('threshold', $defaults['threshold']), 0, 1),
            'targetMode' => $target_mode,
            'targets'    => $targets,
            // Selectors the head CSS pre-hides in auto mode; the runtime must
            // release any matching element it does not animate.
            'criticalSelectors' => 'auto' === $target_mode
                ? SiteWideCatalog::auto_critical_selectors()
                : [],
            'selectors'  => '',
            // Pro extras (off / neutral unless licensed).
            'offset'      => $defaults['offset'],
            'stagger'     => 0,
            'replay'      => false,
            'mobileReduce'=> false,
        ];

        if ($is_pro) {
            $config['offset']       = $this->clamp_int($get('offset', $defaults['offset']), 0, 1000);
            $config['stagger']      = $this->clamp_int($get('stagger', $defaults['stagger']), 0, 2000);
            $config['replay']       = (bool) $get('replay', false);
            $config['mobileReduce'] = (bool) $get('mobile.reduce', false);
            $sel = trim((string) $get('selectors', ''));
            if ('' !== $sel) {
                $config['selectors'] = $sel;
            }
        }

        // Failsafe window: long enough to let the slowest reasonable reveal run,
        // short enough that a broken bundle reveals content quickly.
        $config['failsafeMs'] = max(3000, $duration + $delay + 2000);

        return $config;
    }

    /**
     * @param mixed $value
     * @param int   $min
     * @param int   $max
     * @return int
     */
    private function clamp_int($value, int $min, int $max): int {
        return max($min, min($max, (int) $value));
    }

    /**
     * @param mixed $value
     * @param float $min
     * @param float $max
     * @return float
     */
    private function clamp_float($value, float $min, float $max): float {
        return max($min, min($max, (float) $value));
    }
}
