<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects;

use WPXERO\ExtenderX\Core\ModuleBase;
use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects\WinterSnow;
use WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects\SpringPetals;
use WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects\Summer;
use WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects\BlackFriday;
use WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects\CyberMonday;
use WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects\ChristmasLights;
use WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects\Fireworks;
use WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects\AutumnLeaves;
use WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects\FallingHearts;
use WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects\HalloweenBats;
use WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects\RamadanLanterns;
use WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects\WeatherEffects;
use WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects\ParticleBackground;
use WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects\Fireflies;
use WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects\StarsTwinkling;
use WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects\FloatingEmojis;
use WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects\FloatingShapes;
use WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects\LightBeams;
use WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects\MouseTrail;

if (!defined('ABSPATH')) {
    exit;
}

class SeasonalEffects extends ModuleBase {
    use Singleton;

    private const SETTINGS_PREFIX = 'global.seasonal_effects';
    private const MODULE_SETTINGS_KEY = 'global';

    private const SEASONAL_EFFECT_FEATURES = [
        'winter_snow.enabled'         => WinterSnow::class,
        'spring_petals.enabled'       => SpringPetals::class,
        'summer_sun.enabled'          => Summer::class,
        'black_friday.enabled'        => BlackFriday::class,
        'cyber_monday.enabled'        => CyberMonday::class,
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

    /**
     * Initialize enabled features, additionally gating each on its schedule.
     *
     * Extends ModuleBase by adding the EffectSchedule check, evaluated before
     * the effect class is instantiated so an out-of-window effect never hooks
     * wp_enqueue_scripts / wp_footer.
     *
     * @since 1.8.0
     * @return void
     */
    protected function initialize_features(): void {
        foreach ($this->get_features() as $feature_key => $feature_class) {
            if (!$this->is_feature_enabled($feature_key)) {
                continue;
            }
            if (!$this->can_use_feature($feature_key)) {
                continue;
            }

            $effect_key = str_replace('.enabled', '', $feature_key);
            if (!EffectSchedule::should_render($effect_key)) {
                continue;
            }

            $feature_class::instance();
        }
    }
}
