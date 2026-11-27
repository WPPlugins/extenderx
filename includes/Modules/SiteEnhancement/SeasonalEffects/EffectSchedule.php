<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects;

use WPXERO\ExtenderX\Core\SettingsCache;
use WPXERO\ExtenderX\Core\ProFeatures;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Seasonal effect scheduling.
 *
 * Decides whether a seasonal effect should render *right now*, based on its
 * per-effect schedule settings. Evaluated by SeasonalEffects::initialize_features()
 * BEFORE the effect class is instantiated, so an out-of-window effect never
 * hooks wp_enqueue_scripts / wp_footer — preserving the "no assets unless active"
 * guarantee.
 *
 * Scheduling is a premium capability. Without a Pro license the schedule is
 * ignored and an enabled effect is simply always-on (the schedule controls are
 * Pro-locked in the admin, so free users never set a non-default mode anyway).
 *
 * Modes (per effect, under global.seasonal_effects.<key>.schedule):
 *   - always : render whenever enabled (default).
 *   - auto   : render inside the built-in seasonal window for that effect.
 *   - window : render inside a custom MM-DD → MM-DD window.
 *
 * Windows are evaluated in the site's timezone and may wrap the year boundary
 * (e.g. 12-01 → 02-28).
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects
 * @since 1.8.0
 */
class EffectSchedule {

    /**
     * Built-in auto-activation windows, MM-DD => MM-DD. Mirrors AUTO_WINDOWS in
     * the admin SeasonalEffectControls component.
     *
     * @var array<string, array{0:string,1:string}>
     */
    private const AUTO_WINDOWS = [
        'winter_snow'      => ['12-01', '02-28'],
        'autumn_leaves'    => ['09-21', '11-30'],
        'hearts_falling'   => ['02-01', '02-14'],
        'christmas_lights' => ['12-01', '12-31'],
        'halloween_bats'   => ['10-15', '10-31'],
        'fireworks'        => ['12-31', '01-01'],
    ];

    /**
     * Whether the given seasonal effect should render now.
     *
     * @param string $effect_key Effect key without the `.enabled` suffix.
     * @return bool
     */
    public static function should_render(string $effect_key): bool {
        // Scheduling is premium — free installs are always-on when enabled.
        if (!ProFeatures::instance()->has_pro_license()) {
            return true;
        }

        $cache  = SettingsCache::instance();
        $prefix = 'global.seasonal_effects.' . $effect_key . '.schedule';
        $mode   = $cache->get_setting($prefix . '.mode', 'always');

        if ('always' === $mode) {
            return true;
        }

        if ('auto' === $mode) {
            if (!isset(self::AUTO_WINDOWS[$effect_key])) {
                return true; // No built-in window — fail open.
            }
            [$start, $end] = self::AUTO_WINDOWS[$effect_key];
            return self::within_window($start, $end);
        }

        if ('window' === $mode) {
            $start = (string) $cache->get_setting($prefix . '.start', '');
            $end   = (string) $cache->get_setting($prefix . '.end', '');
            if (!self::is_valid_md($start) || !self::is_valid_md($end)) {
                return true; // Incomplete/invalid — fail open.
            }
            return self::within_window($start, $end);
        }

        return true;
    }

    /**
     * Is "today" (site timezone) inside the MM-DD window? Supports windows that
     * wrap the year boundary.
     *
     * @param string $start MM-DD
     * @param string $end   MM-DD
     * @return bool
     */
    private static function within_window(string $start, string $end): bool {
        $today = wp_date('m-d');

        if ($start <= $end) {
            return ($today >= $start && $today <= $end);
        }

        // Wrap-around window (e.g. 12-01 → 02-28).
        return ($today >= $start || $today <= $end);
    }

    /**
     * Validate an MM-DD string.
     *
     * @param string $value
     * @return bool
     */
    private static function is_valid_md(string $value): bool {
        return (bool) preg_match('/^(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/', $value);
    }
}
