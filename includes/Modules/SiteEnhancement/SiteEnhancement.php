<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Utilities\Utilities;
use WPXERO\ExtenderX\Modules\SiteEnhancement\SiteModes\SiteModes;
use WPXERO\ExtenderX\Modules\SiteEnhancement\UIEnhancements\UIEnhancements;
use WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects\SeasonalEffects;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance\Appearance;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Animations\Animations;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Marketing\Marketing;
use WPXERO\ExtenderX\Modules\SiteEnhancement\CustomCode\CustomCode;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Accessibility\Accessibility;
use WPXERO\ExtenderX\Modules\SiteEnhancement\SaveAndGo\SaveAndGo;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Singleton Trait
 *
 * Provides singleton functionality to classnames_for_block_core_search( $attributes:array )
 */
class SiteEnhancement {
    use Singleton;

    /**
     * Track if modules have been initialized
     *
     * @var bool
     */
    private static $modules_initialized = false;

    /**
     * Initialize the SiteEnhancement module.
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action('init', [$this, 'init_modules'], 10);
    }

    /**
     * Initialize all sub-modules.
     *
     * Deferred to 'init' hook to ensure WordPress is fully loaded
     * and prevent memory issues during plugin initialization.
     *
     * @since 1.0.0
     * @return void
     */
    public function init_modules() {
        // Guard against multiple initializations
        if (self::$modules_initialized) {
            return;
        }
        self::$modules_initialized = true;

        SiteModes::instance();
        Utilities::instance();
        UIEnhancements::instance();
        SeasonalEffects::instance();
        Appearance::instance();
        Animations::instance();
        Marketing::instance();
        CustomCode::instance();
        Accessibility::instance();
        SaveAndGo::instance();
    }
}
