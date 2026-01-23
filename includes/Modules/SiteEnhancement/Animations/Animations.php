<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Animations;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\ModuleBase;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Animations\EntranceEffects;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Animations\ParallaxEffects;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Animations\FloatingElements;

defined('ABSPATH') || exit;

class Animations extends ModuleBase {
    use Singleton;

    private const SETTINGS_PREFIX = 'global.animations';
    private const MODULE_SETTINGS_KEY = 'global';

    private const ANIMATION_FEATURES = [
        'entrance_effects.enabled' => EntranceEffects::class,
        'parallax_effects.enabled' => ParallaxEffects::class,
        'floating_effects.enabled' => FloatingElements::class,
    ];

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
}
