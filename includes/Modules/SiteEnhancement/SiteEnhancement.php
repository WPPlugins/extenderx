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

    public function __construct() {
        SiteModes::instance();
        Utilities::instance();
        UIEnhancements::instance();
        SeasonalEffects::instance();
        Appearance::instance();
        Animations::instance();
        Marketing::instance();
    }
}
