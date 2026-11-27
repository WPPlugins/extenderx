<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Animations;

use WPXERO\ExtenderX\Core\Utils;

defined('ABSPATH') || exit;

/**
 * Conditional-load resolver for Motion & Animations.
 *
 * Centralizes the "should the animation runtime load on this request?"
 * decision so feature classes never enqueue unconditionally (the pre-1.9 stub
 * enqueued on every page). Combines:
 *   1. global master + module master + feature toggle,
 *   2. the per-page `_extenderx_disable_animations` meta,
 *   3. device gating.
 *
 * Pure read-only logic — no side effects, safe to call from enqueue hooks.
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement\Animations
 * @since 1.9.0
 */
class AssetResolver {

    /**
     * Whether animations are enabled at the global + module level.
     *
     * @return bool
     */
    public static function animations_enabled(): bool {
        // Gated only by the site-enhancement master + each feature's own toggle.
        return (bool) Utils::instance()->extenderx_get_setting('global.enabled');
    }

    /**
     * Whether the current (or given) page disables animations via post meta.
     *
     * @param int|null $post_id Optional post ID; defaults to the queried object.
     * @return bool
     */
    public static function page_disabled($post_id = null): bool {
        if ($post_id === null) {
            $post_id = (int) get_queried_object_id();
        }
        if (!$post_id) {
            return false;
        }
        return (bool) get_post_meta($post_id, PageControl::META_KEY, true);
    }

    /**
     * Device gate: when a feature is desktop-only or has mobile disabled, bail
     * on mobile requests.
     *
     * @param bool $mobile_enabled Whether the feature runs on mobile/tablet.
     * @return bool True if the current device passes.
     */
    public static function passes_device(bool $mobile_enabled): bool {
        if ($mobile_enabled) {
            return true;
        }
        return !(function_exists('wp_is_mobile') && wp_is_mobile());
    }

    /**
     * Whether the Pro scroll-linked runtime should load on this request.
     *
     * @return bool
     */
    public static function should_load_scroll(): bool {
        if (is_admin() || !self::animations_enabled()) {
            return false;
        }

        $utils = Utils::instance();
        if (!(bool) $utils->extenderx_get_setting('global.animations.scroll_effects.enabled')) {
            return false;
        }

        // The runtime applies effects only to the configured selectors — with
        // none set it is a guaranteed no-op, so skip the assets entirely.
        if ('' === trim((string) $utils->extenderx_get_setting('global.animations.scroll_effects.selectors', ''))) {
            return false;
        }

        if (self::page_disabled()) {
            return false;
        }

        // Scroll-linked effects default to desktop-only; when that's on, skip
        // mobile. (`desktop_only` true → mobile_enabled false.)
        $desktop_only = (bool) $utils->extenderx_get_setting(
            'global.animations.scroll_effects.desktop_only',
            true
        );

        return self::passes_device(!$desktop_only);
    }
}
