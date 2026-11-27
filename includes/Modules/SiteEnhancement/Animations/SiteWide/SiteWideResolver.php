<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Animations\SiteWide;

use WPXERO\ExtenderX\Core\Utils;
use WPXERO\ExtenderX\Core\PageFilter;
use WPXERO\ExtenderX\Core\ProFeatures;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Animations\PageControl;

defined('ABSPATH') || exit;

/**
 * Conditional-load resolver for the rebuilt Site-Wide Animations engine.
 *
 * Pure read-only logic (no side effects) so it is safe to call from enqueue
 * hooks. Encodes the conditional-asset-loading mandate: the runtime loads on a
 * request only when
 *   (a) the feature is enabled (global master + site_wide toggle),
 *   (b) the current page is not opted out via post meta,
 *   (c) the device passes the mobile gate, and
 *   (d) at least one targeted element is plausibly present.
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement\Animations\SiteWide
 * @since 1.10.0
 */
class SiteWideResolver {

    private const PREFIX = 'global.animations.site_wide.';

    /**
     * Whether the site-wide runtime should load on this request.
     *
     * @return bool
     */
    public static function should_load(): bool {
        if (is_admin()) {
            return false;
        }

        $utils = Utils::instance();

        // (a) masters + feature toggle.
        if (!(bool) $utils->extenderx_get_setting('global.enabled')) {
            return false;
        }
        if (!(bool) $utils->extenderx_get_setting(self::PREFIX . 'enabled')) {
            return false;
        }

        // (b) per-page opt-out (shared post meta with the rest of Motion),
        // plus the Pro dashboard exclusion list (pages / posts / URL patterns).
        if (self::page_disabled()) {
            return false;
        }
        if (self::page_excluded()) {
            return false;
        }

        // (c) device gate.
        $mobile_enabled = (bool) $utils->extenderx_get_setting(self::PREFIX . 'mobile.enabled', true);
        if (!$mobile_enabled && function_exists('wp_is_mobile') && wp_is_mobile()) {
            return false;
        }

        // (d) target presence — only cheaply knowable for singular requests in
        // `specific` mode; auto mode and archives always pass (the runtime
        // no-ops harmlessly if it finds nothing, leaving content visible).
        if (!self::has_targets_on_page()) {
            return false;
        }

        return true;
    }

    /**
     * Whether the current (or given) page disables animations via post meta.
     *
     * @param int|null $post_id Optional; defaults to the queried object.
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
     * Whether the Pro dashboard page rules block the current request.
     *
     * Delegates to the shared {@see PageFilter} evaluator (include / exclude
     * modes, empty lists = no restriction) so page rules behave identically
     * across every feature that offers them. Pro-gated at runtime like the
     * marketing modules.
     *
     * @return bool True when the runtime should NOT load on this request.
     */
    public static function page_excluded(): bool {
        if (!ProFeatures::instance()->has_pro_license()) {
            return false;
        }

        $utils = Utils::instance();

        return PageFilter::blocks_by(static function ($key, $default) use ($utils) {
            return $utils->extenderx_get_setting(self::PREFIX . $key, $default);
        });
    }

    /**
     * Best-effort target-presence check.
     *
     * Returns true unless we can cheaply prove the page contains no targeted
     * block: a singular request in `specific` mode whose post content has none
     * of the configured target blocks. Everything else passes.
     *
     * @return bool
     */
    private static function has_targets_on_page(): bool {
        $utils = Utils::instance();

        $mode = $utils->extenderx_get_setting(self::PREFIX . 'target_mode', 'auto');
        if ('specific' !== $mode) {
            return true;
        }

        // Custom CSS selectors (pro) may match anything — don't try to prove absence.
        if ('' !== trim((string) $utils->extenderx_get_setting(self::PREFIX . 'selectors', ''))) {
            return true;
        }

        if (!is_singular()) {
            return true;
        }

        $post = get_post();
        if (!$post || '' === (string) $post->post_content) {
            return true;
        }

        $targets = SiteWideCatalog::sanitize_targets(
            $utils->extenderx_get_setting(self::PREFIX . 'targets', SiteWideCatalog::DEFAULT_TARGETS)
        );
        foreach ($targets as $block_type) {
            if (has_block($block_type, $post)) {
                return true;
            }
        }

        return false;
    }
}
