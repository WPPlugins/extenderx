<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects;

use WPXERO\ExtenderX\Core\SettingsCache;
use WPXERO\ExtenderX\Core\ProFeatures;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Seasonal effect page targeting.
 *
 * Server-side enforcement of the per-effect Visibility / targeting settings
 * written by the admin <VisibilityControls> panel (device, role, post type and
 * page/post/URL exclusions). Mirrors the canonical logic used by BackToTop and
 * the marketing modules.
 *
 * Targeting depends on the main query (post type, queried object, device), so
 * — unlike scheduling — it is evaluated at enqueue/render time by each effect
 * class, not at module init.
 *
 * Targeting is a premium capability: without a Pro license the effect renders
 * everywhere (the targeting panel is Pro-locked in the admin).
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects
 * @since 1.8.0
 */
class EffectTargeting {

    /**
     * Whether the effect passes its targeting rules on the current request.
     *
     * @param string $effect_key Effect key without the `.enabled` suffix.
     * @return bool
     */
    public static function passes(string $effect_key): bool {
        // Targeting is premium — free installs render everywhere.
        if (!ProFeatures::instance()->has_pro_license()) {
            return true;
        }

        $cache  = SettingsCache::instance();
        $prefix = 'global.seasonal_effects.' . $effect_key . '.';

        $get = static function ($key, $default) use ($cache, $prefix) {
            return $cache->get_setting($prefix . $key, $default);
        };

        // 1. Device targeting.
        if (!self::passes_device($get)) {
            return false;
        }

        // 2. User role targeting.
        if (!self::passes_role($get)) {
            return false;
        }

        // 3. Post type restriction.
        $allowed_post_types = $get('show_on_post_types', []);
        if (!empty($allowed_post_types) && is_array($allowed_post_types)) {
            $current_post_type = get_post_type();
            if ($current_post_type && !in_array($current_post_type, $allowed_post_types, true)) {
                return false;
            }
        }

        // 4. Page rules (include/exclude list; see Core\PageFilter).
        if (\WPXERO\ExtenderX\Core\PageFilter::blocks_by($get)) {
            return false;
        }

        return true;
    }

    /**
     * @param callable $get
     * @return bool
     */
    private static function passes_device(callable $get): bool {
        $show_on_mobile  = (bool) $get('show_on_mobile', true);
        $show_on_tablet  = (bool) $get('show_on_tablet', true);
        $show_on_desktop = (bool) $get('show_on_desktop', true);

        if (function_exists('wp_is_mobile') && wp_is_mobile()) {
            return $show_on_mobile || $show_on_tablet;
        }

        return $show_on_desktop;
    }

    /**
     * @param callable $get
     * @return bool
     */
    private static function passes_role(callable $get): bool {
        $visible_to_roles = $get('visible_to_roles', []);

        if (empty($visible_to_roles) || !is_array($visible_to_roles)) {
            return true;
        }

        if (!is_user_logged_in()) {
            return in_array('logged_out', $visible_to_roles, true);
        }

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
}
