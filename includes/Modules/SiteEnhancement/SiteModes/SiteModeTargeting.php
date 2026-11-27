<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SiteModes;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shared visibility / targeting helpers for the banner & popup site modes
 * (age verification, cookie consent, GDPR notice, exit intent).
 *
 * Consuming classes must expose:
 *   - array $settings  Parsed settings (with defaults merged).
 *   - bool  $is_pro    Whether the advanced bundle is licensed.
 *
 * All checks are Pro-only: the consuming module should call
 * {@see passes_targeting()} only when $is_pro is true.
 *
 * @since 1.0.0
 */
trait SiteModeTargeting {

    /**
     * Combined schedule + device + role + page targeting check.
     */
    protected function passes_targeting(): bool {
        return $this->passes_schedule()
            && $this->passes_device_visibility()
            && $this->passes_role_visibility()
            && !$this->is_excluded_page();
    }

    protected function passes_schedule(): bool {
        if (empty($this->settings['schedule_enabled'])) {
            return true;
        }
        $now   = current_time('timestamp');
        $start = !empty($this->settings['start_date']) ? strtotime((string) $this->settings['start_date']) : false;
        $end   = !empty($this->settings['end_date']) ? strtotime((string) $this->settings['end_date']) : false;
        if ($start && $now < $start) {
            return false;
        }
        if ($end && $now > $end) {
            return false;
        }
        return true;
    }

    protected function passes_device_visibility(): bool {
        $show_mobile  = $this->settings['show_on_mobile'] ?? true;
        $show_desktop = $this->settings['show_on_desktop'] ?? true;
        if (function_exists('wp_is_mobile') && wp_is_mobile()) {
            return (bool) $show_mobile;
        }
        return (bool) $show_desktop;
    }

    protected function passes_role_visibility(): bool {
        $roles = $this->settings['visible_to_roles'] ?? [];
        if (empty($roles) || !is_array($roles)) {
            return true;
        }
        if (!is_user_logged_in()) {
            return in_array('logged_out', $roles, true);
        }
        $current_user = wp_get_current_user();
        foreach ($current_user->roles as $role) {
            if (in_array($role, $roles, true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Page rules: one list, include or exclude mode (`page_filter_mode`).
     * Despite the method name, in include mode this returns true for every
     * page NOT on the list. Delegates to the shared evaluator.
     */
    protected function is_excluded_page(): bool {
        return \WPXERO\ExtenderX\Core\PageFilter::blocks($this->settings);
    }
}
