<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects;

use WPXERO\ExtenderX\Core\ModuleBase;
use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class SeasonalEffects extends ModuleBase {
    use Singleton;

    private const SETTINGS_PREFIX = 'global.seasonal_effects';
    private const MODULE_SETTINGS_KEY = 'global';

    private const SEASONAL_EFFECT_FEATURES = [
        'christmas_lights.enabled'    => ChristmasLights::class,
        'fireworks.enabled'           => Fireworks::class,
        'autumn_leaves.enabled'       => AutumnLeaves::class,
        'hearts_falling.enabled'      => FallingHearts::class,
        'halloween_bats.enabled'      => HalloweenBats::class,
        'ramadan_lanterns.enabled'    => RamadanLanterns::class,
        'weather.enabled'             => WeatherEffects::class,
        'particle_bg.enabled'         => ParticleBackground::class,
        'fireflies.enabled'           => Fireflies::class,
        'stars_twinkling.enabled'     => StarsTwinkling::class,
        'floating_emojis.enabled'     => FloatingEmojis::class,
        'floating_shapes.enabled'     => FloatingShapes::class,
        'light_beams.enabled'         => LightBeams::class,
        'mouse_trail.enabled'         => MouseTrail::class,
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
     * Get the seasonal effect features mapping.
     *
     * @since 1.0.0
     * @return array<string, class-string>
     */
    protected function get_features(): array {
        return self::SEASONAL_EFFECT_FEATURES;
    }
}
