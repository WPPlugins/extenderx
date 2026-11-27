<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Animations\SiteWide;

defined('ABSPATH') || exit;

/**
 * Site-Wide Animations catalog — server-side source of truth (rebuild).
 *
 * The clean-slate replacement for the legacy `entrance_effects` Catalog. Owns
 * the named animation styles, easing whitelist, default block targets, schema
 * defaults, and the free/pro boundary for the rebuilt "set it once, applies
 * everywhere" engine. Persisted under `global.animations.site_wide.*`.
 *
 * Mirrors the JS style/easing catalog shipped to the admin SPA + frontend
 * runtime. The free/pro split is enforced HERE (defense-in-depth) on top of the
 * admin UI gating — a free install can never persist or run a pro style/easing.
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement\Animations\SiteWide
 * @since 1.10.0
 */
class SiteWideCatalog {

    /**
     * Named animation styles available without a Pro license.
     *
     * `light` / `professional` / `crazy` are the curated "packs" ported from the
     * Animate My Site plugin (combined transform + blur recipes); the rest are
     * the granular core set. A deliberately small, well-tuned free tier.
     *
     * @var string[]
     */
    public const FREE_STYLES = [
        'light', 'professional', 'crazy',
        'fade', 'fadeUp', 'fadeDown', 'fadeScale',
    ];

    /**
     * Pro-only styles, layered on top of the free set.
     *
     * @var string[]
     */
    public const PRO_STYLES = [
        'fadeLeft', 'fadeRight', 'slideUp', 'slideDown', 'slideLeft',
        'slideRight', 'scaleIn', 'zoomIn', 'flipX', 'flipY', 'rotateIn',
    ];

    /**
     * Easings available without a Pro license. Pro may use any well-formed
     * easing (keyword | cubic-bezier(4) | steps(n)); see is_valid_easing().
     *
     * @var string[]
     */
    // MUST stay in sync with the admin SPA's shared getEasingOptions()
    // (packages/admin/src/constants/motion.js) — the
    // Site-Wide UI reuses that list, so any value the UI offers as free must
    // validate as free here, or the server silently rewrites the user's choice.
    public const FREE_EASINGS = [
        'linear', 'ease', 'ease-in', 'ease-out',
        'cubic-bezier(0.42, 0, 0.58, 1)',        // ease-in-out
        'cubic-bezier(0.25, 0.46, 0.45, 0.94)',  // ease-out-quad
        'cubic-bezier(0.25, 1, 0.5, 1)',         // expo-out — Animate My Site "Professional" pack (smooth default)
        'cubic-bezier(0.16, 1.2, 0.4, 1)',       // overshoot — Animate My Site "Crazy" pack
    ];

    /**
     * Default core block types the engine targets in `specific` mode.
     *
     * @var string[]
     */
    public const DEFAULT_TARGETS = [
        'core/image', 'core/heading', 'core/paragraph', 'core/columns',
        'core/group', 'core/media-text', 'core/cover', 'core/buttons',
        'core/list', 'core/gallery', 'core/quote',
    ];

    /**
     * The full known style list (free + pro). Used as the sanitize whitelist.
     *
     * @return string[]
     */
    public static function all_styles(): array {
        return array_merge(self::FREE_STYLES, self::PRO_STYLES);
    }

    /**
     * Styles permitted for the current license tier.
     *
     * @param bool $is_pro License flag.
     * @return string[]
     */
    public static function allowed_styles(bool $is_pro): array {
        return $is_pro ? self::all_styles() : self::FREE_STYLES;
    }

    /**
     * Schema defaults for the `global.animations.site_wide` subtree.
     *
     * Defaults to the Animate My Site "Professional" pack (translate + scale +
     * 10px blur over 850ms on an expo-out curve) so the out-of-box reveal feels
     * smooth and premium. Pro-only keys ship with conservative (off / neutral)
     * defaults so a free install behaves identically with or without them.
     *
     * @return array<string,mixed>
     */
    public static function defaults(): array {
        return [
            'enabled'     => false,
            'style'       => 'professional',
            'duration'    => 850,      // ms
            'delay'       => 0,        // ms (base delay before the first reveal)
            'easing'      => 'cubic-bezier(0.25, 1, 0.5, 1)',
            'distance'    => 45,       // px travel for directional styles / packs
            'threshold'   => 0.15,     // IntersectionObserver ratio
            'target_mode' => 'auto',   // 'auto' = all content; 'specific' = `targets` only
            'targets'     => self::DEFAULT_TARGETS,

            // --- Pro-only ---
            'offset'      => 120,      // px: trigger the reveal this far before entry (rootMargin)
            'stagger'     => 0,        // ms cascade between siblings; 0 = simultaneous
            'replay'      => false,    // re-animate on re-enter vs animate once
            'selectors'   => '',       // extra CSS-selector targeting
            'mobile'      => [
                'enabled' => true,     // run on mobile at all
                'reduce'  => false,    // shorter distance / faster timing on small screens
            ],
            // Page filter — the lists share their keys/semantics with the
            // marketing modules (MarketingVisibility::is_excluded_page) so the
            // dashboard UX is consistent across features. `page_filter_mode`
            // decides how the lists apply: 'exclude' = run everywhere except
            // the listed pages; 'include' = run only on the listed pages.
            // Empty lists mean no restriction in either mode.
            'page_filter_mode'     => 'exclude',
            'exclude_page_ids'     => [],
            'exclude_post_ids'     => [],
            'exclude_url_patterns' => [],
        ];
    }

    /**
     * Validate an easing string. Pro allows any well-formed easing; free is
     * limited to FREE_EASINGS.
     *
     * @param string $easing Candidate easing value.
     * @param bool   $is_pro License flag.
     * @return bool
     */
    public static function is_valid_easing(string $easing, bool $is_pro): bool {
        if (in_array($easing, self::FREE_EASINGS, true)) {
            return true;
        }
        if (!$is_pro) {
            return false;
        }
        if (in_array($easing, ['linear', 'ease', 'ease-in', 'ease-out', 'ease-in-out'], true)) {
            return true;
        }
        // cubic-bezier(n, n, n, n)
        if (preg_match('/^cubic-bezier\(\s*-?[0-9.]+\s*,\s*-?[0-9.]+\s*,\s*-?[0-9.]+\s*,\s*-?[0-9.]+\s*\)$/', $easing)) {
            return true;
        }
        // steps(n) / steps(n, position)
        if (preg_match('/^steps\(\s*\d+\s*(,\s*(start|end|jump-[a-z]+)\s*)?\)$/', $easing)) {
            return true;
        }
        return false;
    }

    /**
     * Coerce a style to an allowed value for the current license tier.
     *
     * @param mixed $style  Candidate.
     * @param bool  $is_pro License flag.
     * @return string
     */
    public static function sanitize_style($style, bool $is_pro): string {
        $style = is_string($style) ? $style : '';
        return in_array($style, self::allowed_styles($is_pro), true) ? $style : 'professional';
    }

    /**
     * Coerce an easing to an allowed value for the current license tier.
     *
     * @param mixed $easing Candidate.
     * @param bool  $is_pro License flag.
     * @return string
     */
    public static function sanitize_easing($easing, bool $is_pro): string {
        $easing  = is_string($easing) ? $easing : '';
        $default = self::defaults()['easing'];
        return self::is_valid_easing($easing, $is_pro) ? $easing : $default;
    }

    /**
     * Content roots used to scope the `auto`-mode critical (pre-paint) hide
     * CSS. A deliberately small, safe subset of the runtime's CONTENT_ROOTS
     * (packages/modules/global/animations/site-wide/index.js): only wrappers
     * that unambiguously contain post content, so the pre-hide can never touch
     * site chrome.
     *
     * @var string[]
     */
    public const AUTO_CRITICAL_ROOTS = [
        '.wp-block-post-content',
        '.entry-content',
        'article',
        'main',
    ];

    /**
     * The `auto`-mode animatable content set. MUST mirror CONTENT_ELEMENTS in
     * the JS runtime — the runtime releases (`.xero-sw-in`) every pre-hidden
     * element it decides not to animate, so both sides must agree on the set.
     *
     * @var string[]
     */
    public const AUTO_CONTENT_SELECTORS = [
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'p', 'img', 'figure', 'ul', 'ol', 'blockquote', 'table',
        '.entry-meta', '.post-meta', // post meta rows (date / category / tags)
        '.wp-block-image', '.wp-block-gallery', '.wp-block-cover',
        '.wp-block-media-text', '.wp-block-button', '.wp-block-buttons',
        '.wp-block-embed', '.wp-block-video', '.wp-block-audio',
        '.wp-block-pullquote', '.wp-block-quote',
    ];

    /**
     * Scoped selectors (`<root> <element>`) the auto-mode critical CSS
     * pre-hides. Shared with the runtime (via config) so it can release
     * whatever it does not animate.
     *
     * @return string[]
     */
    public static function auto_critical_selectors(): array {
        $out = [];
        foreach (self::AUTO_CRITICAL_ROOTS as $root) {
            foreach (self::AUTO_CONTENT_SELECTORS as $el) {
                $out[] = $root . ' ' . $el;
            }
        }
        return $out;
    }

    /**
     * Map a block type to the CSS class WordPress renders for it.
     *
     * `core/image` → `.wp-block-image`, `acme/card` → `.wp-block-acme-card`.
     * Shared by the PHP critical-CSS injector and the JS runtime so both target
     * the same elements.
     *
     * @param string $block_type
     * @return string Leading-dot CSS class selector.
     */
    public static function block_to_selector(string $block_type): string {
        return '.wp-block-' . str_replace('/', '-', $block_type);
    }

    /**
     * Build the list of CSS selectors for a set of block-type targets.
     *
     * @param string[] $targets
     * @return string[]
     */
    public static function selectors_for(array $targets): array {
        return array_map([self::class, 'block_to_selector'], $targets);
    }

    /**
     * Keep only well-formed block-type targets (e.g. "core/image").
     *
     * @param mixed $targets
     * @return string[]
     */
    public static function sanitize_targets($targets): array {
        if (!is_array($targets)) {
            return self::DEFAULT_TARGETS;
        }
        $clean = array_values(array_unique(array_filter(
            array_map('strval', $targets),
            static function ($t) {
                return (bool) preg_match('#^[a-z0-9-]+/[a-z0-9-]+$#', $t);
            }
        )));
        return empty($clean) ? self::DEFAULT_TARGETS : $clean;
    }
}
