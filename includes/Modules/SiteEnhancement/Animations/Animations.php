<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Animations;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\ModuleBase;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Animations\ScrollEffects;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Animations\PageControl;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Animations\SiteWide\SiteWide;

defined('ABSPATH') || exit;

class Animations extends ModuleBase {
    use Singleton;

    private const SETTINGS_PREFIX = 'global.animations';
    private const MODULE_SETTINGS_KEY = 'global';

    // No map-based features — ScrollEffects and SiteWide boot in
    // initialize_features() with their own gating.
    private const ANIMATION_FEATURES = [];

    /**
     * Boot the per-page control (post meta) before the feature gates, so the
     * meta and its REST exposure exist whenever site enhancement is active —
     * independent of whether any animation feature is toggled on.
     */
    public function __construct() {
        if ((bool) $this->utils_setting('global.enabled')) {
            PageControl::instance();
        }
        parent::__construct();
    }

    /**
     * Read a setting before parent::__construct() has populated $this->utils.
     *
     * @param string $path
     * @param mixed  $default
     * @return mixed
     */
    private function utils_setting(string $path, $default = null) {
        return \WPXERO\ExtenderX\Core\Utils::instance()->extenderx_get_setting($path, $default);
    }

    /**
     * Get the settings key for checking if module is enabled.
     *
     * @since 1.0.0
     * @return string
     */
    protected function get_module_settings_key(): string {
        return self::MODULE_SETTINGS_KEY;
    }

    /**
     * Get the settings prefix for module features.
     *
     * @since 1.0.0
     * @return string
     */
    protected function get_settings_prefix(): string {
        return self::SETTINGS_PREFIX;
    }

    /**
     * Get the animation features mapping.
     *
     * @since 1.0.0
     * @return array<string, class-string>
     */
    protected function get_features(): array {
        return self::ANIMATION_FEATURES;
    }

    /**
     * Initialize the map-based features, then the Pro scroll-linked engine.
     *
     * ScrollEffects is gated on the `scroll_animations` premium key, which does
     * not match its settings path (`scroll_effects`), so it is handled here
     * rather than via the generic feature map.
     *
     * @return void
     */
    protected function initialize_features(): void {
        parent::initialize_features();

        if (
            $this->is_feature_enabled('scroll_effects.enabled') &&
            $this->pro_features->can_use_premium_feature('scroll_animations')
        ) {
            ScrollEffects::instance();
        }

        // Rebuilt site-wide reveal engine (free; pro extras gated within).
        // Booted unconditionally so the wp_enqueue/wp_head hooks register; the
        // SiteWideResolver gate decides per-request whether anything loads.
        SiteWide::instance();
    }
}
